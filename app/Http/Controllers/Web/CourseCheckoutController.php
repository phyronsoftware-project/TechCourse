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
use App\Services\BakongPaymentService;
use App\Services\CourseAccessService;
use App\Services\PaymentHistoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class CourseCheckoutController extends Controller
{
    public function __construct(
        protected PaymentHistoryService $paymentHistoryService,
        protected CourseAccessService $courseAccessService
    ) {}

    public function show(
        string $course,
        BakongOpenApiService $bakongOpenApiService,
        BakongKhqrService $bakongKhqrService
    ): View|RedirectResponse {
        $courseModel = $this->resolveCourse($course);

        if (! $courseModel) {
            throw new NotFoundHttpException;
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

        $checkoutQrProvider = $this->resolveCheckoutQrProvider();
        [$order, $payment] = $this->ensurePendingCheckout($courseModel, $checkoutQrProvider);
        $khqrPreviewUrl = $this->resolveKhqrPreviewUrl($payment);
        $khqrDeepLink = $payment->khqr_deeplink;
        $khqrReferenceMd5 = $payment->khqr_md5;
        $khqrModalTitle = 'Bakong KHQR';
        $khqrCaption = __('Scan our official Bakong KHQR with any banking app that supports KHQR, then verify your payment after transfer.');

        try {
            $payment = $this->ensureKhqrPrepared($courseModel, $order, $payment, $bakongKhqrService);
            $khqrPreviewUrl = $this->resolveKhqrPreviewUrl($payment);
            $khqrDeepLink = $payment->khqr_deeplink;
            $khqrReferenceMd5 = $payment->khqr_md5;
        } catch (Throwable $exception) {
            // Keep checkout infrastructure details in server logs instead of exposing them to customers.
            Log::error('Course KHQR preparation failed.', [
                'course_id' => $courseModel->id,
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'exception' => $exception,
            ]);
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
        ]);
    }

    // Verify a Bakong reference and unlock the course only after success.
    public function verify(Request $request, string $course, BakongOpenApiService $bakongOpenApiService): RedirectResponse
    {
        $courseModel = $this->resolveCourse($course);

        if (! $courseModel) {
            throw new NotFoundHttpException;
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
                    'currency' => 'USD',
                ],
            );
        } catch (Throwable $exception) {
            // Log the full verification failure while returning a customer-safe message.
            Log::error('Course Bakong verification failed.', [
                'course_id' => $courseModel->id,
                'payment_id' => $payment->id,
                'exception' => $exception,
            ]);

            return redirect()
                ->route('courses.checkout', $courseModel->slug ?: $courseModel->id)
                ->with('error', __('Unable to verify the payment right now. Please try again shortly.'));
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
                        'item_id' => 'course_'.$courseModel->id,
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

        // Keep unsuccessful Bakong verification attempts visible in backend history.
        $this->paymentHistoryService->log($payment->fresh('order'), 'verification_failed', 'Bakong verification did not return a successful status.', [
            'reference_type' => $payload['reference_type'],
            'reference_value' => $payload['reference_value'],
            'bakong_verification' => $verification,
        ]);

        return redirect()
            ->route('courses.checkout', $courseModel->slug ?: $courseModel->id)
            ->with('error', __('Bakong verification shows this transaction is not successful yet.'));
    }

    // Poll only the authenticated customer's payment and confirm it through Bakong server-side.
    public function status(Payment $payment, BakongPaymentService $bakongPaymentService): JsonResponse
    {
        abort_unless((int) $payment->user_id === (int) Auth::id(), 404);

        try {
            $payment = $bakongPaymentService->checkPaymentStatus($payment);

            return response()->json([
                'success' => true,
                'message' => 'Payment status checked',
                'data' => [
                    'payment_id' => $payment->id,
                    'status' => $payment->status,
                    'transaction_hash' => $payment->transaction_hash,
                    'paid_at' => optional($payment->paid_at)?->toIso8601String(),
                    'expired_at' => optional($payment->expired_at)?->toIso8601String(),
                ],
            ]);
        } catch (Throwable $exception) {
            // Prevent polling responses from exposing database or provider internals.
            Log::error('Course Bakong payment status check failed.', [
                'payment_id' => $payment->id,
                'exception' => $exception,
            ]);

            return response()->json([
                'success' => false,
                'message' => __('Unable to check payment status right now.'),
                'data' => ['status' => $payment->fresh()->status],
            ], 503);
        }
    }

    protected function ensurePendingCheckout(Course $course, string $checkoutQrProvider = 'bakong'): array
    {
        return DB::transaction(function () use ($course, $checkoutQrProvider) {
            $userId = (int) Auth::id();
            // Keep course checkout in USD so the KHQR amount follows the dollar-based pricing shown in the project.
            $amount = round((float) ($course->price ?? 0), 2);
            $currency = 'USD';

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
                    $paymentMethod = 'bakong_khqr';
                    $paymentOption = 'bakong_khqr';

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
                        'payment_no' => $existingPayment->payment_no ?: $this->generatePaymentNumber(),
                        'transaction_id' => null,
                        'transaction_hash' => null,
                        'merchant_id' => null,
                        'abapay_deeplink' => null,
                        'khqr_deeplink' => null,
                        'qr_image_url' => null,
                        'amount' => $amount,
                        'currency' => $currency,
                        'khqr_string' => null,
                        'khqr_md5' => null,
                        'bakong_response' => null,
                        'payment_option' => $paymentOption,
                        'expired_at' => now()->addMinutes(max(1, (int) config('bakong.dynamic_expire_minutes', 10))),
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

                    // Keep each reopened pending checkout visible in backend history.
                    $this->paymentHistoryService->log($existingPayment->fresh('order'), 'checkout_refreshed', 'Pending Bakong checkout refreshed for course payment.', [
                        'course_id' => $course->id,
                        'course_title' => $course->title,
                    ]);

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

            $paymentProvider = $this->resolvePaymentProviderValue($checkoutQrProvider);

            $payment = Payment::create([
                'order_id' => $order->id,
                'user_id' => $userId,
                'payment_provider' => $paymentProvider,
                'payment_no' => $this->generatePaymentNumber(),
                'amount' => $amount,
                'currency' => $currency,
                'status' => 'pending',
                'payment_option' => 'bakong_khqr',
                'merchant_id' => null,
                'req_time' => now()->format('YmdHis'),
                'expired_at' => now()->addMinutes(max(1, (int) config('bakong.dynamic_expire_minutes', 10))),
                'response_payload' => [
                    'course_id' => $course->id,
                    'course_title' => $course->title,
                    'note' => 'Pending Bakong KHQR checkout prepared from frontend course lock flow.',
                ],
            ]);

            // Store the first course checkout creation event for backend tracking.
            $this->paymentHistoryService->log($payment->fresh('order'), 'checkout_created', 'Course checkout order and payment created.', [
                'course_id' => $course->id,
                'course_title' => $course->title,
            ]);

            return [$order, $payment];
        });
    }

    protected function ensureKhqrPrepared(Course $course, Order $order, Payment $payment, BakongKhqrService $bakongKhqrService): Payment
    {
        $existingPayload = is_array($payment->response_payload) ? $payment->response_payload : [];

        // Regenerate a fresh Bakong KHQR on every checkout open so customers do not scan an older QR.
        $response = $bakongKhqrService->generateCheckoutKhqr([
            'amount' => (float) $payment->amount,
            'currency' => 'USD',
            'order_no' => $order->order_no,
            'bill_number' => $payment->payment_no ?: $order->order_no,
            'course_title' => $course->title,
        ]);

        $payment->forceFill([
            'payment_provider' => $this->resolvePaymentProviderValue(),
            'payment_no' => $payment->payment_no ?: $this->generatePaymentNumber(),
            'transaction_id' => null,
            'transaction_hash' => null,
            'merchant_id' => null,
            'req_time' => $payment->req_time ?: now()->format('YmdHis'),
            'status' => 'pending',
            'payment_option' => 'bakong_khqr',
            'abapay_deeplink' => null,
            'currency' => 'USD',
            'khqr_string' => data_get($response, 'qr_string'),
            'khqr_md5' => data_get($response, 'md5'),
            'khqr_deeplink' => data_get($response, 'deep_link'),
            'qr_image_url' => data_get($response, 'image_data_uri'),
            'expired_at' => now()->addMinutes(max(1, (int) config('bakong.dynamic_expire_minutes', 10))),
            'response_payload' => array_merge($existingPayload, [
                'bakong_checkout' => $response,
            ]),
        ])->save();

        // Log the fresh KHQR regeneration so backend can trace the exact active QR.
        $this->paymentHistoryService->log($payment->fresh('order'), 'khqr_regenerated', 'Fresh Bakong KHQR regenerated for checkout page.', [
            'order_no' => $order->order_no,
            'payment_no' => $payment->payment_no,
            'khqr_md5' => data_get($response, 'md5'),
        ]);

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

        // Save the latest manual verification request in backend history.
        $this->paymentHistoryService->log($payment->fresh('order'), 'verification_received', 'Bakong verification response stored on payment.', [
            'reference_type' => $payload['reference_type'],
            'reference_value' => $payload['reference_value'],
            'bakong_verification' => $verification,
        ]);

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

        // Keep the final successful checkout result visible in admin backend.
        $this->paymentHistoryService->log($payment->fresh('order'), 'checkout_paid', 'Course order marked as paid after Bakong verification.', [
            'course_id' => $course->id,
            'course_title' => $course->title,
        ]);
    }

    // ABA checkout uses a short unique tran id for KHQR generation requests.
    protected function userHasCourseAccess(int $courseId): bool
    {
        // Avoid charging a learner who already owns the course through a plan.
        return $this->courseAccessService->userHasCourseAccess(Auth::user(), $courseId);
    }

    protected function courseIsFree(Course $course): bool
    {
        return (bool) $course->is_free || (float) ($course->price ?? 0) <= 0;
    }

    protected function generateOrderNumber(): string
    {
        return 'TC-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));
    }

    // Keep Bakong payments on their own reference number format for easier support tracking.
    protected function generatePaymentNumber(): string
    {
        return 'PAY-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
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
            $resolvedProvider = 'bakong_open_api';
        }

        return $resolvedProvider;
    }

    protected function resolveCheckoutQrProvider(): string
    {
        // Keep ABA checkout paused for now until the bank-specific work resumes later.
        return 'bakong';
    }
}
