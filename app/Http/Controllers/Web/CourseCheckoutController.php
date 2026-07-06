<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Services\AbaPayWayService;
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
    public function show(
        string $course,
        BakongOpenApiService $bakongOpenApiService,
        AbaPayWayService $abaPaywayService
    ): View|RedirectResponse
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

        $checkoutQrProvider = $this->resolveCheckoutQrProvider($abaPaywayService);
        [$order, $payment] = $this->ensurePendingCheckout($courseModel, $checkoutQrProvider);
        $khqrError = null;
        $khqrPreviewUrl = $this->resolveKhqrPreviewUrl($payment);
        $khqrDeepLink = $payment->khqr_deeplink;
        $khqrReferenceMd5 = null;
        $khqrModalTitle = $checkoutQrProvider === 'aba' ? 'ABA KHQR' : 'Bakong KHQR';
        $khqrCaption = $checkoutQrProvider === 'aba'
            ? __('Scan with ABA app or any banking app that supports ABA KHQR.')
            : __('Scan our official Bakong or ACLEDA KHQR with any banking app that supports KHQR, then verify your payment reference below.');

        try {
            $payment = $this->ensureKhqrPrepared($courseModel, $order, $payment, $abaPaywayService);
            $khqrPreviewUrl = $this->resolveKhqrPreviewUrl($payment);
            $khqrDeepLink = $payment->khqr_deeplink ?: $payment->abapay_deeplink;
        } catch (Throwable $exception) {
            $khqrError = $exception->getMessage();
        }

        return view('web.pages.courses.checkout', [
            'course' => $courseModel,
            'order' => $order,
            'payment' => $payment,
            'bakong' => $bakongOpenApiService->summary(),
            'checkoutQrProvider' => $checkoutQrProvider,
            'khqrModalTitle' => $khqrModalTitle,
            'khqrCaption' => $khqrCaption,
            'khqrPreviewUrl' => $khqrPreviewUrl,
            'khqrDeepLink' => $khqrDeepLink,
            'khqrReferenceMd5' => $khqrReferenceMd5,
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
            $request->session()->flash('ga4_events', [[
                'name' => 'purchase',
                'params' => [
                    'transaction_id' => $order->order_no,
                    'currency' => $payment->currency,
                    'value' => (float) $payment->amount,
                    'items' => [[
                        'item_id' => 'course_' . $courseModel->id,
                        'item_name' => $courseModel->title,
                        'item_category' => 'course',
                        'price' => (float) $payment->amount,
                        'quantity' => 1,
                    ]],
                ],
            ]]);

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

    protected function ensurePendingCheckout(Course $course, string $checkoutQrProvider = 'bakong'): array
    {
        return DB::transaction(function () use ($course, $checkoutQrProvider) {
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
                    $paymentProvider = $this->resolvePaymentProviderValue($checkoutQrProvider);
                    $paymentMethod = $checkoutQrProvider === 'aba' ? 'aba_payway' : 'bakong_khqr';
                    $paymentOption = $checkoutQrProvider === 'aba' ? 'abapay_khqr' : 'bakong_khqr';

                    // Keep any pending checkout aligned with the latest course price from dashboard edits.
                    $existingOrder->forceFill([
                        'total_amount' => $amount,
                        'currency' => $currency,
                        'payment_method' => $paymentMethod,
                    ])->save();

                    OrderItem::query()
                        ->where('order_id', $existingOrder->id)
                        ->where('course_id', $course->id)
                        ->update([
                            'course_title' => $course->title,
                            'price' => $amount,
                        ]);

                    $existingPayload = is_array($existingPayment->response_payload) ? $existingPayment->response_payload : [];

                    // Reset old QR leftovers so the refreshed checkout uses the latest selected provider only.
                    $existingPayment->forceFill([
                        'payment_provider' => $paymentProvider,
                        'transaction_id' => null,
                        'merchant_id' => null,
                        'abapay_deeplink' => null,
                        'khqr_deeplink' => null,
                        'qr_image_url' => null,
                        'amount' => $amount,
                        'currency' => $currency,
                        'payment_option' => $paymentOption,
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
                                'note' => $checkoutQrProvider === 'aba'
                                    ? 'Pending ABA KHQR checkout prepared from frontend course lock flow.'
                                    : 'Pending Bakong KHQR checkout prepared from frontend course lock flow.',
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
                'payment_method' => $checkoutQrProvider === 'aba' ? 'aba_payway' : 'bakong_khqr',
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'course_id' => $course->id,
                'course_title' => $course->title,
                'price' => $amount,
            ]);

            $paymentProvider = $this->resolvePaymentProviderValue($checkoutQrProvider);

            $payment = Payment::create([
                'order_id' => $order->id,
                'user_id' => $userId,
                'payment_provider' => $paymentProvider,
                'amount' => $amount,
                'currency' => $currency,
                'status' => 'pending',
                'payment_option' => $checkoutQrProvider === 'aba' ? 'abapay_khqr' : 'bakong_khqr',
                'merchant_id' => null,
                'req_time' => now()->format('YmdHis'),
                'response_payload' => [
                    'course_id' => $course->id,
                    'course_title' => $course->title,
                    'note' => $checkoutQrProvider === 'aba'
                        ? 'Pending ABA KHQR checkout prepared from frontend course lock flow.'
                        : 'Pending Bakong KHQR checkout prepared from frontend course lock flow.',
                ],
            ]);

            return [$order, $payment];
        });
    }

    protected function ensureKhqrPrepared(Course $course, Order $order, Payment $payment, AbaPayWayService $abaPaywayService): Payment
    {
        $existingPayload = is_array($payment->response_payload) ? $payment->response_payload : [];

        if (filled($payment->khqr_deeplink) || filled($payment->qr_image_url) || filled(data_get($existingPayload, 'qrImage'))) {
            return $payment;
        }

        $user = Auth::user();

        $tranId = $payment->transaction_id ?: $this->generateAbaTranId($payment);

        $response = $abaPaywayService->generateKhqr([
            'tran_id' => $tranId,
            'amount' => (float) $payment->amount,
            'currency' => $payment->currency,
            'item_name' => $course->title,
            'first_name' => $user?->name ?: 'TechCourse',
            'last_name' => 'User',
            'email' => $user?->email ?: '',
            'phone' => $user?->phone ?: '',
        ]);

        $payment->forceFill([
            'payment_provider' => 'aba_payway',
            'transaction_id' => data_get($response, 'status.tran_id') ?: data_get($response, 'tran_id') ?: $tranId,
            'merchant_id' => config('services.aba_payway.merchant_id'),
            'req_time' => data_get($response, 'req_time') ?: $payment->req_time,
            'status' => 'pending',
            'payment_option' => 'abapay_khqr',
            'abapay_deeplink' => data_get($response, 'abapay_deeplink'),
            'khqr_deeplink' => data_get($response, 'abapay_deeplink'),
            'qr_image_url' => data_get($response, 'qrImage') ?: data_get($response, 'data.qrImage'),
            'response_payload' => array_merge($existingPayload, $response),
        ])->save();

        return $payment->fresh();
    }

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
            $paymentProvider = $this->resolvePaymentProviderValue();

            $payment->forceFill([
                'status' => 'success',
                'payment_provider' => $paymentProvider,
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

    protected function resolvePaymentProviderValue(string $checkoutQrProvider = 'bakong'): string
    {
        if ($checkoutQrProvider === 'aba') {
            return 'aba_payway';
        }

        static $resolvedProvider = null;

        if ($resolvedProvider !== null) {
            return $resolvedProvider;
        }

        try {
            $column = DB::selectOne("
                select column_type
                from information_schema.columns
                where table_schema = schema()
                  and table_name = 'payments'
                  and column_name = 'payment_provider'
                limit 1
            ");

            $columnType = strtolower((string) ($column->column_type ?? ''));
            $resolvedProvider = str_contains($columnType, 'bakong_open_api')
                ? 'bakong_open_api'
                : 'aba_payway';
        } catch (Throwable) {
            $resolvedProvider = 'aba_payway';
        }

        return $resolvedProvider;
    }

    protected function resolveCheckoutQrProvider(AbaPayWayService $abaPaywayService): string
    {
        return $abaPaywayService->summary()['is_ready'] ? 'aba' : 'aba';
    }
}
