<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Services\AbaPaywayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CourseCheckoutController extends Controller
{
    public function show(string $course, AbaPaywayService $abaPaywayService): View|RedirectResponse
    {
        $courseModel = Course::query()
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

        try {
            $payment = $this->ensureKhqrPrepared($courseModel, $order, $payment, $abaPaywayService);
        } catch (Throwable $exception) {
            $khqrError = $exception->getMessage();
        }

        return view('web.pages.courses.checkout', [
            'course' => $courseModel,
            'order' => $order,
            'payment' => $payment,
            'aba' => $abaPaywayService->summary(),
            'khqrError' => $khqrError,
        ]);
    }

    protected function ensurePendingCheckout(Course $course): array
    {
        return DB::transaction(function () use ($course) {
            $userId = (int) Auth::id();

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
                    return [$existingOrder, $existingPayment];
                }
            }

            $amount = (float) ($course->price ?? 0);
            $currency = $course->currency ?: 'USD';

            $order = Order::create([
                'user_id' => $userId,
                'order_no' => $this->generateOrderNumber(),
                'total_amount' => $amount,
                'currency' => $currency,
                'status' => 'pending',
                'payment_method' => 'aba_payway',
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
                'payment_provider' => 'aba_payway',
                'amount' => $amount,
                'currency' => $currency,
                'status' => 'pending',
                'payment_option' => config('services.aba_payway.payment_option', 'abapay_deeplink'),
                'merchant_id' => config('services.aba_payway.merchant_id'),
                'req_time' => now()->format('YmdHis'),
                'response_payload' => [
                    'course_id' => $course->id,
                    'course_title' => $course->title,
                    'note' => 'Pending checkout prepared from frontend course lock flow.',
                ],
            ]);

            return [$order, $payment];
        });
    }

    protected function ensureKhqrPrepared(Course $course, Order $order, Payment $payment, AbaPaywayService $abaPaywayService): Payment
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
            'response_payload' => array_merge($existingPayload, $response),
        ])->save();

        return $payment->fresh();
    }

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
