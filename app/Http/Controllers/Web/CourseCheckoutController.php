<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Services\BakongKhqrService;
use App\Services\BakongOpenApiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CourseCheckoutController extends Controller
{
    public function show(string $course, BakongOpenApiService $bakongOpenApiService, BakongKhqrService $bakongKhqrService): View|RedirectResponse
    {
        $courseModel = $this->resolveCourse($course);

        if (! $courseModel) {
            throw new NotFoundHttpException();
        }

        if ($this->courseIsFree($courseModel)) {
            return redirect()
                ->route('courses.show', $courseModel->slug ?: $courseModel->id)
                ->with('info', __('This course is free. You can start learning now.'));
        }

        if ($this->userHasCourseAccess($courseModel->id)) {
            $firstLesson = $courseModel->lessons->first();

            if ($firstLesson) {
                return redirect()
                    ->route('learning.show', [$courseModel->slug ?: $courseModel->id, $firstLesson->slug ?: $firstLesson->id])
                    ->with('success', __('This course is already unlocked in your account.'));
            }

            return redirect()
                ->route('courses.show', $courseModel->slug ?: $courseModel->id)
                ->with('success', __('This course is already unlocked in your account.'));
        }

        [$order, $payment] = $this->ensurePendingCheckout($courseModel);
        $khqrError = null;
        $generatedKhqr = null;

        try {
            // Generate a real Bakong KHQR again for the course checkout modal.
            $generatedKhqr = $bakongKhqrService->generateCheckoutKhqr([
                'amount' => (float) $payment->amount,
                'currency' => $payment->currency,
                'order_no' => $order->order_no,
                'course_title' => $courseModel->title,
            ]);
        } catch (Throwable $exception) {
            $khqrError = $exception->getMessage();
        }

        return view('web.pages.courses.checkout', [
            'course' => $courseModel,
            'order' => $order,
            'payment' => $payment,
            'bakong' => $bakongOpenApiService->summary(),
            // Expose KHQR merchant details so the modal card can follow the Bakong layout more closely.
            'khqrMerchantName' => data_get($bakongKhqrService->summary(), 'merchant_name'),
            'khqrAccountId' => data_get($bakongKhqrService->summary(), 'account_id'),
            'khqrPreviewUrl' => $generatedKhqr['image_data_uri'] ?? $this->resolveKhqrPreviewUrl($payment),
            'khqrDeepLink' => $generatedKhqr['deep_link'] ?? null,
            'khqrReferenceMd5' => $generatedKhqr['md5'] ?? null,
            'khqrError' => $khqrError,
        ]);
    }

    // Verify a Bakong reference and unlock the course only after success.
    public function verify(Request $request, string $course, BakongOpenApiService $bakongOpenApiService): RedirectResponse
    {
        $courseModel = $this->resolveCourse($course);

        if (! $courseModel) {
            throw new NotFoundHttpException();
        }

        [$order, $payment] = $this->ensurePendingCheckout($courseModel);

        $payload = $request->validate([
            'reference_type' => ['required', 'in:hash,short_hash,md5,instruction_ref,external_ref'],
            'reference_value' => ['required', 'string', 'max:255'],
        ]);

        try {
            $verification = $bakongOpenApiService->checkTransaction(
                (string) $payload['reference_type'],
                (string) $payload['reference_value'],
                [
                    'amount' => (float) $payment->amount,
                    'currency' => $payment->currency,
                ],
            );
        } catch (Throwable $exception) {
            return redirect()
                ->route('courses.checkout', $courseModel->slug ?: $courseModel->id)
                ->with('error', $exception->getMessage());
        }

        $normalizedStatus = $bakongOpenApiService->normalizeTrackingStatus($verification);
        $payment = $this->storeBakongVerification($payment, $verification, $payload);

        if ($normalizedStatus === 'success') {
            $this->finalizeSuccessfulCheckout($courseModel, $order, $payment, $verification, $payload);

            $firstLesson = $courseModel->lessons->first();

            if ($firstLesson) {
                return redirect()
                    ->route('learning.show', [$courseModel->slug ?: $courseModel->id, $firstLesson->slug ?: $firstLesson->id])
                    ->with('success', __('Bakong payment verified successfully. Your course is now unlocked.'));
            }

            return redirect()
                ->route('courses.show', $courseModel->slug ?: $courseModel->id)
                ->with('success', __('Bakong payment verified successfully. Your course is now unlocked.'));
        }

        if (in_array($normalizedStatus, ['pending', 'unknown'], true)) {
            return redirect()
                ->route('courses.checkout', $courseModel->slug ?: $courseModel->id)
                ->with('warning', __('Bakong transaction is not completed yet. Please verify again after payment is fully processed.'));
        }

        $payment->forceFill(['status' => 'failed'])->save();

        return redirect()
            ->route('courses.checkout', $courseModel->slug ?: $courseModel->id)
            ->with('error', __('Bakong verification shows this transaction is not successful yet.'));
    }

    protected function ensurePendingCheckout(Course $course): array
    {
        return DB::transaction(function () use ($course) {
            $userId = (int) Auth::id();
            $amount = (float) ($course->price ?? 0);
            $currency = $course->currency ?: 'USD';

            $existingOrder = Order::query()
                ->where('user_id', $userId)
                ->whereIn('status', ['pending', 'failed'])
                ->whereHas('items', fn ($query) => $query->where('course_id', $course->id))
                ->latest('id')
                ->first();

            if ($existingOrder) {
                $existingPayment = Payment::query()
                    ->where('order_id', $existingOrder->id)
                    ->where('user_id', $userId)
                    ->whereIn('status', ['initiated', 'pending', 'failed'])
                    ->latest('id')
                    ->first();

                if ($existingPayment) {
                    // Keep any pending checkout aligned with the latest course price from dashboard edits.
                    $existingOrder->forceFill([
                        'total_amount' => $amount,
                        'currency' => $currency,
                        'payment_method' => 'bakong_khqr',
                    ])->save();

                    OrderItem::query()
                        ->where('order_id', $existingOrder->id)
                        ->where('course_id', $course->id)
                        ->update([
                            'course_title' => $course->title,
                            'price' => $amount,
                        ]);

                    $existingPayload = is_array($existingPayment->response_payload) ? $existingPayment->response_payload : [];

                    // Reset old ABA QR leftovers so the refreshed checkout uses the latest Bakong data only.
                    $existingPayment->forceFill([
                        'payment_provider' => 'bakong_open_api',
                        'transaction_id' => null,
                        'merchant_id' => null,
                        'abapay_deeplink' => null,
                        'khqr_deeplink' => null,
                        'qr_image_url' => null,
                        'amount' => $amount,
                        'currency' => $currency,
                        'payment_option' => 'bakong_khqr',
                        'response_payload' => array_merge(
                            collect($existingPayload)
                                ->except([
                                    'amount',
                                    'currency',
                                    'qrString',
                                    'qrImage',
                                    'abapay_deeplink',
                                    'app_store',
                                    'play_store',
                                    'status',
                                ])
                                ->all(),
                            [
                            'course_id' => $course->id,
                            'course_title' => $course->title,
                            'note' => 'Pending Bakong KHQR checkout prepared from frontend course lock flow.',
                            ],
                        ),
                    ])->save();

                    $existingOrder->refresh();
                    $existingPayment->refresh();

                    return [$existingOrder, $existingPayment];
                }
            }

            $order = Order::create([
                'user_id' => $userId,
                'order_no' => $this->generateOrderNumber(),
                'total_amount' => $amount,
                'currency' => $currency,
                'status' => 'pending',
                'payment_method' => 'bakong_khqr',
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'course_id' => $course->id,
                'course_title' => $course->title,
                'price' => $amount,
            ]);

            $payment = Payment::create([
                'order_id' => $order->id,
                'user_id' => $userId,
                'payment_provider' => 'bakong_open_api',
                'amount' => $amount,
                'currency' => $currency,
                'status' => 'pending',
                'payment_option' => 'bakong_khqr',
                'merchant_id' => null,
                'req_time' => now()->format('YmdHis'),
                'response_payload' => [
                    'course_id' => $course->id,
                    'course_title' => $course->title,
                    'note' => 'Pending Bakong KHQR checkout prepared from frontend course lock flow.',
                ],
            ]);

            return [$order, $payment];
        });
    }

    /*
    // ABA KHQR helper is paused again while course checkout is switched back to real Bakong KHQR.
    protected function ensureKhqrPrepared(Course $course, Order $order, Payment $payment, AbaPayWayService $abaPaywayService): Payment
    {
        $existingPayload = is_array($payment->response_payload) ? $payment->response_payload : [];

        if (filled($payment->khqr_deeplink) || filled($payment->qr_image_url) || filled(data_get($existingPayload, 'qrImage'))) {
            return $payment;
        }

        $user = Auth::user();

        $response = $abaPaywayService->generateKhqr([
            'tran_id' => $this->generateAbaTranId($payment),
            'amount' => (float) $payment->amount,
            'currency' => $payment->currency,
            'item_name' => $course->title,
            'first_name' => $user?->name ?: 'TechCourse',
            'last_name' => 'User',
            'email' => $user?->email ?: '',
            'phone' => $user?->phone ?: '',
        ]);

        $payment->forceFill([
            'transaction_id' => data_get($response, 'status.tran_id') ?: $payment->transaction_id,
            'status' => 'pending',
            'payment_option' => 'abapay_khqr',
            'abapay_deeplink' => data_get($response, 'abapay_deeplink'),
            'khqr_deeplink' => data_get($response, 'abapay_deeplink'),
            'qr_image_url' => data_get($response, 'qrImage') ?: data_get($response, 'data.qrImage'),
            'response_payload' => array_merge($existingPayload, $response),
        ])->save();

        return $payment->fresh();
    }
    */

    // Reuse one course lookup path for checkout actions.
    protected function resolveCourse(string $course): ?Course
    {
        return Course::query()
            ->with([
                'category',
                'lessons' => fn ($query) => $query
                    ->when(Schema::hasColumn('course_lessons', 'is_published'), fn ($lessonQuery) => $lessonQuery->where('is_published', true))
                    ->orderBy('sort_order')
                    ->orderBy('id'),
            ])
            ->where('slug', $course)
            ->orWhere('id', $course)
            ->first();
    }

    // Prefer the live KHQR image returned by the payment response when available.
    protected function resolveKhqrPreviewUrl(Payment $payment): ?string
    {
        $payload = is_array($payment->response_payload) ? $payment->response_payload : [];

        return $payment->qr_image_url
            ?: data_get($payload, 'qrImage')
            ?: data_get($payload, 'data.qrImage');
    }

    // Keep the latest Bakong verification payload on the payment record.
    protected function storeBakongVerification(Payment $payment, array $verification, array $payload): Payment
    {
        $existingCallbackPayload = is_array($payment->callback_payload) ? $payment->callback_payload : [];

        $payment->forceFill([
            'callback_payload' => array_merge($existingCallbackPayload, [
                'bakong_verification' => $verification,
                'bakong_reference_type' => $payload['reference_type'],
                'bakong_reference_value' => $payload['reference_value'],
                'bakong_verified_at' => now()->toIso8601String(),
            ]),
        ])->save();

        return $payment->fresh();
    }

    // Mark payment and order as paid, then grant active course access.
    protected function finalizeSuccessfulCheckout(Course $course, Order $order, Payment $payment, array $verification, array $payload): void
    {
        DB::transaction(function () use ($course, $order, $payment, $verification, $payload) {
            $paidAt = now();
            $existingResponsePayload = is_array($payment->response_payload) ? $payment->response_payload : [];

            $payment->forceFill([
                'status' => 'success',
                'payment_provider' => 'bakong_open_api',
                'payment_option' => 'bakong_khqr',
                'transaction_id' => data_get($verification, 'data.hash') ?: data_get($verification, 'data.md5') ?: $payment->transaction_id,
                'paid_at' => $paidAt,
                'response_payload' => array_merge($existingResponsePayload, [
                    'bakong_reference_type' => $payload['reference_type'],
                    'bakong_reference_value' => $payload['reference_value'],
                    'bakong_verification' => $verification,
                ]),
            ])->save();

            $order->forceFill([
                'status' => 'paid',
                'payment_method' => 'bakong_khqr',
                'paid_at' => $paidAt,
            ])->save();

            CourseEnrollment::query()->updateOrCreate(
                [
                    'user_id' => (int) Auth::id(),
                    'course_id' => $course->id,
                ],
                [
                    'order_id' => $order->id,
                    'access_type' => 'paid',
                    'status' => 'active',
                    'started_at' => $paidAt,
                ],
            );
        });
    }

    // ABA checkout uses a short unique tran id for KHQR generation requests.
    protected function generateAbaTranId(Payment $payment): string
    {
        return 'TCP' . $payment->id . Str::upper(Str::random(6));
    }

    protected function userHasCourseAccess(int $courseId): bool
    {
        if (! Auth::check()) {
            return false;
        }

        return CourseEnrollment::query()
            ->where('user_id', Auth::id())
            ->where('course_id', $courseId)
            ->where('status', 'active')
            ->exists();
    }

    protected function courseIsFree(Course $course): bool
    {
        return (bool) $course->is_free || (float) ($course->price ?? 0) <= 0;
    }

    protected function generateOrderNumber(): string
    {
        return 'TC-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4));
    }
}
