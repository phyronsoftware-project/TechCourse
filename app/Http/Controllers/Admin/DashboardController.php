<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $trendMonths = collect(range(5, 0))->map(fn ($monthsAgo) => $today->copy()->subMonths($monthsAgo)->startOfMonth());

        $trendData = $trendMonths->map(function (Carbon $month): array {
            $start = $month->copy()->startOfMonth();
            $end = $month->copy()->endOfMonth();

            return [
                'label' => $month->format('M Y'),
                'orders' => $this->countBetween('orders', 'created_at', $start, $end),
                'revenue' => $this->sumBetween('payments', 'amount', 'paid_at', $start, $end, ['status' => 'success']),
            ];
        });

        $recentOrders = $this->safeCollection(function () {
            return DB::table('orders')
                ->leftJoin('users', 'orders.user_id', '=', 'users.id')
                ->select([
                    'orders.id',
                    'orders.order_no',
                    'orders.total_amount',
                    'orders.currency',
                    'orders.status',
                    'orders.payment_method',
                    'orders.created_at',
                    'users.name as user_name',
                ])
                ->latest('orders.created_at')
                ->limit(5)
                ->get();
        }, 'orders');

        $recentPayments = $this->safeCollection(function () {
            return DB::table('payments')
                ->leftJoin('users', 'payments.user_id', '=', 'users.id')
                ->leftJoin('orders', 'payments.order_id', '=', 'orders.id')
                ->select([
                    'payments.id',
                    'payments.transaction_id',
                    'payments.amount',
                    'payments.currency',
                    'payments.status',
                    'payments.payment_provider',
                    'payments.paid_at',
                    'orders.order_no',
                    'users.name as user_name',
                ])
                ->latest('payments.created_at')
                ->limit(5)
                ->get();
        }, 'payments');

        $popularCourses = $this->safeCollection(function () {
            return DB::table('courses')
                ->leftJoin('course_enrollments', 'courses.id', '=', 'course_enrollments.course_id')
                ->select([
                    'courses.id',
                    'courses.title',
                    'courses.slug',
                    'courses.total_students',
                    'courses.total_lessons',
                    DB::raw('COUNT(course_enrollments.id) as enrollments_count'),
                ])
                ->groupBy('courses.id', 'courses.title', 'courses.slug', 'courses.total_students', 'courses.total_lessons')
                ->orderByDesc(DB::raw('COALESCE(courses.total_students, 0)'))
                ->orderByDesc('enrollments_count')
                ->limit(5)
                ->get();
        }, 'courses');

        $activitySchedule = $this->buildActivitySchedule();

        return view('admin.pages.dashboard.index', [
            'pageTitle' => 'Dashboard',
            'stats' => [
                [
                    'label' => 'Total Users',
                    'value' => $this->count('users'),
                    'meta_primary' => $this->countVerifiedUsers() . ' verified',
                    'meta_secondary' => 'registered users',
                    'icon_bg' => 'bg-blue',
                    'icon' => 'users',
                    'route' => route('admin.users.index'),
                ],
                [
                    'label' => 'Active Enrollments',
                    'value' => $this->countWhere('course_enrollments', 'status', 'active'),
                    'meta_primary' => $this->count('course_enrollments') . ' total',
                    'meta_secondary' => 'learning records',
                    'icon_bg' => 'bg-teal',
                    'icon' => 'check',
                    'route' => route('admin.enrollments.index'),
                ],
                [
                    'label' => 'Revenue Generated',
                    'value' => '$' . number_format($this->sum('payments', 'amount', ['status' => 'success']), 2),
                    'meta_primary' => $this->countWhere('payments', 'status', 'success') . ' success',
                    'meta_secondary' => 'paid transactions',
                    'icon_bg' => 'bg-orange',
                    'icon' => 'coins',
                    'route' => route('admin.payments.index'),
                ],
                [
                    'label' => 'Total Orders',
                    'value' => $this->count('orders'),
                    'meta_primary' => $this->countWhere('payments', 'status', 'pending') . ' pending',
                    'meta_secondary' => 'order records',
                    'icon_bg' => 'bg-red',
                    'icon' => 'ticket',
                    'route' => route('admin.orders.index'),
                ],
            ],
            'trendData' => $trendData,
            'courseBreakdown' => [
                'free' => $this->countWhere('courses', 'is_free', 1),
                'paid' => $this->countWhere('courses', 'is_free', 0),
            ],
            'recentOrders' => $recentOrders,
            'recentPayments' => $recentPayments,
            'popularCourses' => $popularCourses,
            'activitySchedule' => $activitySchedule,
        ]);
    }

    protected function buildActivitySchedule(): Collection
    {
        $items = collect();

        if (Schema::hasTable('payments')) {
            $items = $items->merge($this->safeCollection(function () {
                return DB::table('payments')
                    ->select([
                        DB::raw("'payment' as item_type"),
                        'transaction_id as title',
                        'status as subtitle',
                        DB::raw('COALESCE(paid_at, created_at) as activity_date'),
                    ])
                    ->orderByDesc(DB::raw('COALESCE(paid_at, created_at)'))
                    ->limit(4)
                    ->get();
            }, 'payments'));
        }

        if (Schema::hasTable('courses')) {
            $items = $items->merge($this->safeCollection(function () {
                return DB::table('courses')
                    ->select([
                        DB::raw("'course' as item_type"),
                        'title',
                        'status as subtitle',
                        DB::raw('COALESCE(published_at, created_at) as activity_date'),
                    ])
                    ->orderByDesc(DB::raw('COALESCE(published_at, created_at)'))
                    ->limit(4)
                    ->get();
            }, 'courses')->map(function ($item) {
                $item->title = $item->title ?? 'Course';
                return $item;
            }));
        }

        return $items
            ->filter(fn ($item) => !empty($item->activity_date))
            ->sortByDesc('activity_date')
            ->take(6)
            ->values();
    }

    protected function countVerifiedUsers(): int
    {
        if (!Schema::hasTable('users')) {
            return 0;
        }

        return $this->safeValue(fn () => DB::table('users')->whereNotNull('email_verified_at')->count(), 0);
    }

    protected function count(string $table): int
    {
        if (!Schema::hasTable($table)) {
            return 0;
        }

        return $this->safeValue(fn () => DB::table($table)->count(), 0);
    }

    protected function countWhere(string $table, string $column, mixed $value): int
    {
        if (!Schema::hasTable($table)) {
            return 0;
        }

        return $this->safeValue(fn () => DB::table($table)->where($column, $value)->count(), 0);
    }

    protected function countBetween(string $table, string $column, Carbon $start, Carbon $end): int
    {
        if (!Schema::hasTable($table)) {
            return 0;
        }

        return $this->safeValue(
            fn () => DB::table($table)->whereBetween($column, [$start, $end])->count(),
            0,
        );
    }

    protected function sum(string $table, string $column, array $conditions = []): float
    {
        if (!Schema::hasTable($table)) {
            return 0;
        }

        return $this->safeValue(function () use ($table, $column, $conditions) {
            $query = DB::table($table);

            foreach ($conditions as $conditionColumn => $conditionValue) {
                $query->where($conditionColumn, $conditionValue);
            }

            return (float) $query->sum($column);
        }, 0);
    }

    protected function sumBetween(string $table, string $column, string $dateColumn, Carbon $start, Carbon $end, array $conditions = []): float
    {
        if (!Schema::hasTable($table)) {
            return 0;
        }

        return $this->safeValue(function () use ($table, $column, $dateColumn, $start, $end, $conditions) {
            $query = DB::table($table)->whereBetween($dateColumn, [$start, $end]);

            foreach ($conditions as $conditionColumn => $conditionValue) {
                $query->where($conditionColumn, $conditionValue);
            }

            return (float) $query->sum($column);
        }, 0);
    }

    protected function safeCollection(callable $callback, ?string $requiredTable = null): Collection
    {
        if ($requiredTable && !Schema::hasTable($requiredTable)) {
            return collect();
        }

        return $this->safeValue(fn () => collect($callback()), collect());
    }

    protected function safeValue(callable $callback, mixed $fallback): mixed
    {
        try {
            return $callback();
        } catch (Throwable) {
            return $fallback;
        }
    }
}
