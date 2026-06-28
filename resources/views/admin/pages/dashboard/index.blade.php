@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
    @php
        $currentMonthLabel = \Carbon\Carbon::today()->format('M Y');
        $calendarStart = \Carbon\Carbon::today()->startOfMonth()->startOfWeek(\Carbon\Carbon::SUNDAY);
        $calendarEnd = \Carbon\Carbon::today()->endOfMonth()->endOfWeek(\Carbon\Carbon::SATURDAY);
        $calendarDays = collect();
        for ($date = $calendarStart->copy(); $date->lte($calendarEnd); $date->addDay()) {
            $calendarDays->push($date->copy());
        }

        $eventDates = $activitySchedule
            ->map(fn ($item) => optional($item->activity_date)->format('Y-m-d'))
            ->filter()
            ->values()
            ->all();

        $trendMax = max(
            1,
            (int) collect($trendData)->max('orders'),
            (int) ceil(collect($trendData)->max('revenue') ?: 0)
        );
        $chartWidth = 720;
        $chartHeight = 300;
        $chartLeft = 42;
        $chartRight = 22;
        $chartTop = 22;
        $chartBottom = 38;
        $usableHeight = $chartHeight - $chartTop - $chartBottom;
        $step = count($trendData) > 1 ? ($chartWidth - $chartLeft - $chartRight) / (count($trendData) - 1) : 0;
        $orderPoints = collect($trendData)->values()->map(function ($item, $index) use ($trendMax, $chartHeight, $chartLeft, $chartBottom, $usableHeight, $step) {
            $x = $chartLeft + ($step * $index);
            $y = $chartHeight - $chartBottom - (($item['orders'] / $trendMax) * $usableHeight);

            return ['x' => round($x, 2), 'y' => round($y, 2), 'label' => $item['label'], 'value' => $item['orders']];
        });
        $revenuePoints = collect($trendData)->values()->map(function ($item, $index) use ($trendMax, $chartHeight, $chartLeft, $chartBottom, $usableHeight, $step) {
            $x = $chartLeft + ($step * $index);
            $y = $chartHeight - $chartBottom - (($item['revenue'] / $trendMax) * $usableHeight);

            return ['x' => round($x, 2), 'y' => round($y, 2), 'label' => $item['label'], 'value' => $item['revenue']];
        });
        $orderLine = $orderPoints->map(fn ($point) => $point['x'] . ',' . $point['y'])->implode(' ');
        $revenueLine = $revenuePoints->map(fn ($point) => $point['x'] . ',' . $point['y'])->implode(' ');

        $freeCount = (int) ($courseBreakdown['free'] ?? 0);
        $paidCount = (int) ($courseBreakdown['paid'] ?? 0);
        $courseTotal = max(1, $freeCount + $paidCount);
        $freeDeg = round(($freeCount / $courseTotal) * 360, 2);
        $donutStyle = "background: conic-gradient(#0b84a5 0deg {$freeDeg}deg, #14b8a6 {$freeDeg}deg 360deg);";
    @endphp

    <style>
        .tourism-analytics {
            --surface: #ffffff;
            --surface-soft: #f5f9fc;
            --line: #d9e4ec;
            --text: #172b4d;
            --muted: #7a8ca4;
            --primary: #0b84a5;
            --teal: #14b8a6;
            --orange: #f59e0b;
            --danger: #ef6351;
            --shadow: 0 18px 38px rgba(15, 23, 42, 0.06);
            color: var(--text);
        }

        .tourism-analytics .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 14px;
        }

        .tourism-analytics .topbar h2 {
            margin: 0;
            font-size: 1.15rem;
            font-weight: 800;
        }

        .tourism-analytics .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 14px;
            margin-bottom: 16px;
        }

        .tourism-analytics .panel,
        .tourism-analytics .stat-card {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 22px;
            box-shadow: var(--shadow);
        }

        .tourism-analytics .stat-card {
            padding: 18px 18px 16px;
        }

        .tourism-analytics a.stat-card {
            display: block;
            text-decoration: none;
            color: inherit;
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        }

        .tourism-analytics a.stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 22px 42px rgba(15, 23, 42, 0.1);
            border-color: #bfd4e2;
        }

        .tourism-analytics .stat-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 8px;
        }

        .tourism-analytics .stat-label {
            color: var(--muted);
            font-size: 13px;
            font-weight: 700;
        }

        .tourism-analytics .stat-icon {
            width: 38px;
            height: 38px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
        }

        .tourism-analytics .stat-icon svg {
            width: 18px;
            height: 18px;
        }

        .tourism-analytics .bg-blue { background: var(--primary); }
        .tourism-analytics .bg-teal { background: var(--teal); }
        .tourism-analytics .bg-orange { background: var(--orange); }
        .tourism-analytics .bg-red { background: var(--danger); }

        .tourism-analytics .stat-value {
            font-size: 2rem;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 4px;
        }

        .tourism-analytics .stat-meta {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            font-size: 12px;
            color: var(--muted);
            font-weight: 600;
        }

        .tourism-analytics .trend-up { color: #16a34a; }
        .tourism-analytics .trend-down { color: #dc2626; }

        .tourism-analytics .chart-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 16px;
            margin-bottom: 16px;
        }

        .tourism-analytics .bottom-grid {
            display: grid;
            grid-template-columns: 1.2fr 1fr 1fr;
            gap: 16px;
        }

        .tourism-analytics .panel {
            padding: 18px;
        }

        .tourism-analytics .panel-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 14px;
        }

        .tourism-analytics .panel-title {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 800;
        }

        .tourism-analytics .panel-subtitle {
            margin-top: 4px;
            color: var(--muted);
            font-size: 12px;
            font-weight: 600;
        }

        .tourism-analytics .panel-filter {
            color: var(--muted);
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }

        .tourism-analytics .chart-box {
            position: relative;
            height: 300px;
        }

        .tourism-analytics .chart-box.small {
            height: 260px;
        }

        .tourism-analytics .chart-svg {
            width: 100%;
            height: 100%;
        }

        .tourism-analytics .donut-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            gap: 16px;
        }

        .tourism-analytics .donut-chart {
            width: 180px;
            height: 180px;
            border-radius: 999px;
            position: relative;
        }

        .tourism-analytics .donut-chart::after {
            content: "";
            position: absolute;
            inset: 26px;
            border-radius: 999px;
            background: #fff;
            box-shadow: inset 0 0 0 1px #eef4f8;
        }

        .tourism-analytics .donut-center {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            z-index: 1;
            text-align: center;
        }

        .tourism-analytics .donut-center strong {
            font-size: 1.5rem;
            line-height: 1;
        }

        .tourism-analytics .legend-list {
            display: grid;
            gap: 10px;
            width: 100%;
        }

        .tourism-analytics .legend-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            font-size: 13px;
            font-weight: 700;
        }

        .tourism-analytics .legend-label {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: var(--muted);
        }

        .tourism-analytics .legend-dot {
            width: 10px;
            height: 10px;
            border-radius: 999px;
            display: inline-block;
        }

        .tourism-analytics table {
            width: 100%;
            margin: 0;
        }

        .tourism-analytics thead th {
            font-size: 12px;
            color: var(--muted);
            font-weight: 700;
            border-bottom: 1px solid var(--line);
            text-transform: uppercase;
            padding: 0 0 12px;
        }

        .tourism-analytics tbody td {
            font-size: 13px;
            vertical-align: middle;
            border-top: 1px solid #edf3f7;
            padding: 14px 0;
        }

        .tourism-analytics .detail-link {
            color: var(--primary);
            font-weight: 800;
            text-decoration: none;
        }

        .tourism-analytics .tool-shortcut-card {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 18px;
            align-items: center;
            margin-bottom: 16px;
            padding: 18px 20px;
            text-decoration: none;
            color: inherit;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .tourism-analytics .tool-shortcut-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 22px 42px rgba(15, 23, 42, 0.1);
        }

        .tourism-analytics .tool-shortcut-card__label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-height: 32px;
            padding: 0 12px;
            border-radius: 999px;
            background: #e0f2fe;
            color: #0b84a5;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .tourism-analytics .tool-shortcut-card__title {
            margin: 12px 0 6px;
            font-size: 1.15rem;
            font-weight: 800;
            color: #172b4d;
        }

        .tourism-analytics .tool-shortcut-card__copy {
            margin: 0;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.7;
        }

        .tourism-analytics .tool-shortcut-card__icon {
            width: 62px;
            height: 62px;
            border-radius: 20px;
            background: linear-gradient(135deg, #0b84a5, #14b8a6);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 18px 30px rgba(11, 132, 165, 0.22);
        }

        .tourism-analytics .tool-shortcut-card__icon svg {
            width: 28px;
            height: 28px;
        }

        .tourism-analytics .destination-list {
            display: grid;
            gap: 12px;
        }

        .tourism-analytics .destination-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
            padding: 14px 16px;
            border: 1px solid var(--line);
            border-radius: 18px;
            background: linear-gradient(180deg, #fbfdff 0%, #f6fbff 100%);
        }

        .tourism-analytics .destination-rank {
            width: 34px;
            height: 34px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #dff1f7;
            color: var(--primary);
            font-weight: 800;
        }

        .tourism-analytics .mini-kpi {
            text-align: right;
            font-size: 12px;
            color: var(--muted);
            font-weight: 700;
        }

        .tourism-analytics .mini-kpi strong {
            display: block;
            color: var(--text);
            font-size: 1rem;
        }

        .tourism-analytics .calendar-head {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 8px;
            margin-bottom: 10px;
            text-align: center;
            color: var(--muted);
            font-size: 11px;
            font-weight: 700;
        }

        .tourism-analytics .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 8px;
        }

        .tourism-analytics .calendar-day {
            min-height: 38px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
            color: var(--text);
            background: #fbfdff;
            border: 1px solid #edf3f7;
        }

        .tourism-analytics .calendar-day.muted {
            color: #b5c0cf;
            background: #f8fbfd;
        }

        .tourism-analytics .calendar-day.today {
            background: #0b84a5;
            color: #fff;
            border-color: #0b84a5;
        }

        .tourism-analytics .calendar-day.has-event {
            color: #dc2626;
        }

        .tourism-analytics .schedule-list {
            display: grid;
            gap: 10px;
            margin-top: 16px;
        }

        .tourism-analytics .schedule-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            border: 1px solid var(--line);
            border-radius: 16px;
            background: #fbfdff;
        }

        .tourism-analytics .schedule-date {
            min-width: 52px;
            text-align: center;
            padding: 8px 6px;
            border-radius: 14px;
            background: #e6f5f8;
            color: var(--primary);
            font-weight: 800;
            line-height: 1.1;
            font-size: 12px;
        }

        .tourism-analytics .empty-state {
            border: 1px dashed var(--line);
            border-radius: 18px;
            background: #fbfdff;
            padding: 18px;
            color: var(--muted);
            font-size: 13px;
            font-weight: 600;
        }

        @media (max-width: 1200px) {
            .tourism-analytics .stats-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .tourism-analytics .bottom-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 992px) {
            .tourism-analytics .chart-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .tourism-analytics .topbar {
                flex-direction: column;
                align-items: flex-start;
            }

            .tourism-analytics .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="tourism-analytics">
        <a href="{{ route('admin.tools.sound') }}" class="panel tool-shortcut-card">
            <div>
                <span class="tool-shortcut-card__label">Business Tool</span>
                <h3 class="tool-shortcut-card__title">Khmer / English Sound Tool</h3>
                <p class="tool-shortcut-card__copy">Convert Khmer or English text to speech, and use browser microphone input to convert speech back to text for your other business workflow tools.</p>
            </div>
            <span class="tool-shortcut-card__icon">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 14a3 3 0 0 0 3-3V7a3 3 0 1 0-6 0v4a3 3 0 0 0 3 3Zm5-3a1 1 0 1 1 2 0 7 7 0 0 1-6 6.92V21h3a1 1 0 1 1 0 2H8a1 1 0 1 1 0-2h3v-3.08A7 7 0 0 1 5 11a1 1 0 1 1 2 0 5 5 0 1 0 10 0Z"/></svg>
            </span>
        </a>

        <div class="topbar">
            <h2>Statistics Cards</h2>
        </div>

        <div class="stats-grid">
            @foreach ($stats as $stat)
                <a href="{{ $stat['route'] ?? route('admin.dashboard') }}" class="stat-card">
                    <div class="stat-head">
                        <div class="stat-label">{{ $stat['label'] }}</div>
                        <span class="stat-icon {{ $stat['icon_bg'] }}">
                            @switch($stat['icon'])
                                @case('check')
                                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="m9.55 18.2-5.3-5.3 1.4-1.4 3.9 3.9 8.8-8.8 1.4 1.4-10.2 10.2Z"/></svg>
                                    @break
                                @case('coins')
                                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 3C7.03 3 3 4.79 3 7v10c0 2.21 4.03 4 9 4s9-1.79 9-4V7c0-2.21-4.03-4-9-4Zm0 2c4.42 0 7 .99 7 2s-2.58 2-7 2-7-.99-7-2 2.58-2 7-2Zm0 12c-4.42 0-7-.99-7-2v-2.14C6.55 13.59 9.06 14 12 14s5.45-.41 7-1.14V15c0 1.01-2.58 2-7 2Z"/></svg>
                                    @break
                                @case('ticket')
                                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M3 7.5A2.5 2.5 0 0 1 5.5 5h13A2.5 2.5 0 0 1 21 7.5V10a2 2 0 1 0 0 4v2.5a2.5 2.5 0 0 1-2.5 2.5h-13A2.5 2.5 0 0 1 3 16.5V14a2 2 0 1 0 0-4V7.5Z"/></svg>
                                    @break
                                @case('bell')
                                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a6 6 0 0 0-6 6v2.16c0 .54-.14 1.08-.4 1.55L4.1 14.6A1.5 1.5 0 0 0 5.4 17h13.2a1.5 1.5 0 0 0 1.3-2.25l-1.5-2.89c-.26-.47-.4-1.01-.4-1.55V8a6 6 0 0 0-6-6Zm0 20a3 3 0 0 0 2.83-2H9.17A3 3 0 0 0 12 22Z"/></svg>
                                    @break
                                @default
                                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M16 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM8 12a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm8 1c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5ZM8 13c-2.67 0-8 1.34-8 4v2h7v-2.5c0-.95.36-1.82 1.02-2.56.13-.14.27-.28.42-.4-.48-.03-.95-.04-1.44-.04Z"/></svg>
                            @endswitch
                        </span>
                    </div>
                    <div class="stat-value">{{ $stat['value'] }}</div>
                    <div class="stat-meta">
                        <span class="trend-up">{{ $stat['meta_primary'] }}</span>
                        <span>{{ $stat['meta_secondary'] }}</span>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="chart-grid">
            <div class="panel">
                <div class="panel-head">
                    <div>
                        <h3 class="panel-title">Orders vs. Revenue</h3>
                        <div class="panel-subtitle">Monthly trend for order volume and successful payment revenue.</div>
                    </div>
                    <div class="panel-filter">Showing: {{ $trendData->first()['label'] ?? '-' }} - {{ $trendData->last()['label'] ?? '-' }}</div>
                </div>
                <div class="chart-box">
                    <svg viewBox="0 0 {{ $chartWidth }} {{ $chartHeight }}" class="chart-svg" preserveAspectRatio="none" aria-hidden="true">
                        @for ($i = 0; $i < 5; $i++)
                            @php
                                $y = $chartTop + ($i * (($chartHeight - $chartTop - $chartBottom) / 4));
                            @endphp
                            <line x1="{{ $chartLeft }}" y1="{{ $y }}" x2="{{ $chartWidth - $chartRight }}" y2="{{ $y }}" stroke="#edf3f7" stroke-width="1" />
                        @endfor
                        <polyline points="{{ $orderLine }}" fill="none" stroke="#0b84a5" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" />
                        <polyline points="{{ $revenueLine }}" fill="none" stroke="#ef6351" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" />
                        @foreach ($orderPoints as $point)
                            <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="5" fill="#ffffff" stroke="#0b84a5" stroke-width="3" />
                            <text x="{{ $point['x'] }}" y="{{ $chartHeight - 12 }}" text-anchor="middle" font-size="12" fill="#8aa0b6">{{ \Illuminate\Support\Str::of($point['label'])->before(' ') }}</text>
                        @endforeach
                        @foreach ($revenuePoints as $point)
                            <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="4" fill="#ffffff" stroke="#ef6351" stroke-width="3" />
                        @endforeach
                    </svg>
                </div>
            </div>

            <div class="panel">
                <div class="panel-head">
                    <div>
                        <h3 class="panel-title">Course Streams</h3>
                        <div class="panel-subtitle">Distribution between free courses and paid courses.</div>
                    </div>
                    <div class="panel-filter">Showing: {{ $currentMonthLabel }}</div>
                </div>
                <div class="chart-box small">
                    <div class="donut-wrap">
                        <div class="donut-chart" style="{{ $donutStyle }}">
                            <div class="donut-center">
                                <strong>{{ $courseTotal }}</strong>
                                <span class="panel-subtitle mt-0">Total</span>
                            </div>
                        </div>
                        <div class="legend-list">
                            <div class="legend-item">
                                <span class="legend-label"><span class="legend-dot" style="background:#0b84a5;"></span> Free Courses</span>
                                <span>{{ $freeCount }}</span>
                            </div>
                            <div class="legend-item">
                                <span class="legend-label"><span class="legend-dot" style="background:#14b8a6;"></span> Paid Courses</span>
                                <span>{{ $paidCount }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bottom-grid">
            <div class="panel">
                <div class="panel-head">
                    <div>
                        <h3 class="panel-title">Recent Orders</h3>
                        <div class="panel-subtitle">Latest course purchase orders created in the system.</div>
                    </div>
                    <a href="{{ route('admin.orders.index') }}" class="panel-filter" style="text-decoration:none;">See More</a>
                </div>
                @if ($recentOrders->isEmpty())
                    <div class="empty-state">No order rows yet. Once orders are created, latest transactions will appear here.</div>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Order</th>
                                    <th>User</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentOrders as $order)
                                    <tr>
                                        <td>{{ $order->created_at ? \Carbon\Carbon::parse($order->created_at)->format('d/m/y') : '-' }}</td>
                                        <td>{{ $order->order_no ?: '-' }}</td>
                                        <td>{{ $order->user_name ?: '-' }}</td>
                                        <td><span class="detail-link">{{ ucfirst($order->status ?: 'pending') }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="panel">
                <div class="panel-head">
                    <div>
                        <h3 class="panel-title">Recent Payments</h3>
                        <div class="panel-subtitle">Latest payment rows from successful or pending checkouts.</div>
                    </div>
                    <a href="{{ route('admin.payments.index') }}" class="panel-filter" style="text-decoration:none;">Payment List</a>
                </div>
                @if ($recentPayments->isEmpty())
                    <div class="empty-state">No payment rows yet. New payments will appear here after orders are paid.</div>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Payment</th>
                                    <th>Status</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentPayments as $payment)
                                    <tr>
                                        <td>{{ $payment->order_no ?: '-' }}</td>
                                        <td>{{ $payment->transaction_id ?: '-' }}</td>
                                        <td><span class="detail-link">{{ ucfirst($payment->status ?: 'pending') }}</span></td>
                                        <td>
                                            ${{ number_format((float) ($payment->amount ?? 0), 2) }}
                                            <div class="panel-subtitle mt-0">{{ strtoupper($payment->currency ?: 'usd') }}</div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="panel">
                <div class="panel-head">
                    <div>
                        <h3 class="panel-title">Popular Courses</h3>
                        <div class="panel-subtitle">Top courses by students and enrollment activity.</div>
                    </div>
                    <div class="panel-filter">Showing: Top 5</div>
                </div>
                @if ($popularCourses->isEmpty())
                    <div class="empty-state">No course rows found yet. Popular courses will show after course data is available.</div>
                @else
                    <div class="destination-list">
                        @foreach ($popularCourses as $index => $course)
                            <div class="destination-row">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="destination-rank">{{ $index + 1 }}</span>
                                    <div>
                                        <div class="fw-bold">{{ $course->title }}</div>
                                        <div class="panel-subtitle mt-0">{{ $course->slug ?: 'course-slug' }}</div>
                                    </div>
                                </div>
                                <div class="mini-kpi">
                                    <strong>{{ $course->total_students ?? $course->enrollments_count ?? 0 }}</strong>
                                    {{ $course->total_lessons ?? 0 }} lessons
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="panel">
                <div class="panel-head">
                    <div>
                        <h3 class="panel-title">{{ \Carbon\Carbon::today()->format('F, Y') }}</h3>
                        <div class="panel-subtitle">Recent admin activity calendar and publishing schedule.</div>
                    </div>
                </div>

                <div class="calendar-head">
                    <div>SUN</div>
                    <div>MON</div>
                    <div>TUE</div>
                    <div>WED</div>
                    <div>THU</div>
                    <div>FRI</div>
                    <div>SAT</div>
                </div>
                <div class="calendar-grid">
                    @foreach ($calendarDays as $day)
                        @php
                            $classes = [];
                            if (! $day->isSameMonth(\Carbon\Carbon::today())) {
                                $classes[] = 'muted';
                            }
                            if ($day->isToday()) {
                                $classes[] = 'today';
                            }
                            if (in_array($day->format('Y-m-d'), $eventDates, true) && ! $day->isToday()) {
                                $classes[] = 'has-event';
                            }
                        @endphp
                        <div class="calendar-day {{ implode(' ', $classes) }}">
                            {{ $day->format('d') }}
                        </div>
                    @endforeach
                </div>

                <div class="schedule-list">
                    @forelse ($activitySchedule as $item)
                        <div class="schedule-item">
                            <div class="schedule-date">
                                {{ \Carbon\Carbon::parse($item->activity_date)->format('d M') }}
                            </div>
                            <div>
                                <div class="fw-bold">{{ $item->title ?: 'Activity' }}</div>
                                <div class="panel-subtitle mt-0">{{ ucfirst(str_replace('_', ' ', $item->subtitle ?: $item->item_type)) }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">No recent activity scheduled yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
