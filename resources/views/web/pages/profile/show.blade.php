@extends('web.layouts.app')

@section('title', __('My Profile'))

@section('content')
    @php
        $userInitial = strtoupper(mb_substr($user->name ?? 'U', 0, 1));
    @endphp

    <style>
        .profile-shell {
            width: min(1080px, 100%);
            margin: 0 auto;
            display: grid;
            gap: 18px;
            padding-bottom: 36px;
        }

        .profile-stage {
            display: grid;
            grid-template-columns: 250px minmax(0, 1fr);
            gap: 18px;
            align-items: start;
        }

        .profile-card {
            border-radius: 18px;
            background: #ffffff;
            border: 1px solid #e3eaf3;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
            overflow: hidden;
        }

        .profile-sidebar {
            padding: 22px 18px 18px;
        }

        .profile-sidebar__top {
            display: grid;
            justify-items: center;
            text-align: center;
            gap: 10px;
            padding-bottom: 18px;
        }

        .profile-avatar-wrap {
            position: relative;
            display: inline-flex;
        }

        .profile-avatar-lg,
        .profile-icon-lg {
            width: 88px;
            height: 88px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1d8cff, #1570ef);
            color: #fff;
            font-family: var(--font-lato);
            font-size: 34px;
            font-weight: 700;
            box-shadow: 0 12px 24px rgba(21, 112, 239, 0.18);
        }

        .profile-avatar-image {
            width: 100%;
            height: 100%;
            border-radius: inherit;
            object-fit: cover;
            display: block;
        }

        .profile-avatar-upload {
            position: absolute;
            right: 0;
            bottom: 0;
            width: 30px;
            height: 30px;
            border-radius: 999px;
            border: 2px solid #ffffff;
            background: #2563eb;
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 18px rgba(37, 99, 235, 0.22);
            cursor: pointer;
        }

        .profile-avatar-upload i {
            font-size: 12px;
        }

        .profile-avatar-input {
            display: none;
        }

        .profile-icon-lg {
            font-size: 30px;
        }

        .profile-sidebar__top h2 {
            margin: 0;
            color: #0f172a;
            font-family: var(--font-lato);
            font-size: 15px;
        }

        .profile-sidebar__top p {
            margin: 0;
            color: #64748b;
            font-size: 12px;
            line-height: 1.5;
        }

        .profile-stat-list {
            display: grid;
            margin: 0 -18px;
            border-top: 1px solid #edf2f7;
            border-bottom: 1px solid #edf2f7;
        }

        .profile-stat-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 14px 18px;
            border-bottom: 1px solid #edf2f7;
            color: #334155;
            font-size: 13px;
        }

        .profile-stat-item:last-child {
            border-bottom: 0;
        }

        .profile-stat-item strong {
            color: #1570ef;
            font-family: var(--font-lato);
            font-size: 15px;
        }

        .profile-side-action {
            margin-top: 16px;
        }

        .profile-side-link {
            width: 100%;
            min-height: 40px;
            border-radius: 12px;
            border: 1px solid #dce6f1;
            background: #ffffff;
            color: #0f172a;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
        }

        .profile-main {
            display: grid;
            gap: 18px;
        }

        .profile-settings-card {
            overflow: hidden;
        }

        .profile-tabs {
            display: flex;
            align-items: center;
            gap: 22px;
            min-height: 58px;
            padding: 0 20px;
            border-bottom: 1px solid #edf2f7;
            background: #ffffff;
            overflow-x: auto;
            scrollbar-width: none;
        }

        .profile-tabs::-webkit-scrollbar {
            display: none;
        }

        .profile-tab {
            position: relative;
            padding: 0;
        }

        .profile-tab-btn {
            position: relative;
            padding: 18px 0 16px;
            border: 0;
            background: transparent;
            color: #94a3b8;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
            cursor: pointer;
        }

        .profile-tab-btn.is-active {
            color: #0f172a;
        }

        .profile-tab-btn.is-active::after {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            height: 2px;
            border-radius: 999px;
            background: #4f46e5;
        }

        .profile-panel[hidden] {
            display: none !important;
        }

        .profile-card__body {
            padding: 20px;
        }

        .profile-section-title {
            margin: 0 0 14px;
            color: #0f172a;
            font-family: var(--font-lato);
            font-size: 16px;
        }

        .profile-form-grid {
            display: grid;
            gap: 14px;
        }

        .profile-form-grid--2 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .profile-field label {
            display: block;
            margin-bottom: 6px;
            color: #0f172a;
            font-size: 12px;
            font-weight: 700;
        }

        .profile-field input {
            width: 100%;
            min-height: 44px;
            padding: 0 14px;
            border-radius: 12px;
            border: 1px solid #d5e1ee;
            background: #ffffff;
            color: #0f172a;
            font-size: 13px;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .profile-field input:focus {
            border-color: #8ab7eb;
            box-shadow: 0 0 0 3px rgba(29, 140, 255, 0.08);
        }

        .profile-password-wrap {
            position: relative;
        }

        .profile-password-wrap input {
            padding-right: 40px;
        }

        .profile-password-toggle {
            position: absolute;
            top: 50%;
            right: 14px;
            transform: translateY(-50%);
            width: 26px;
            height: 26px;
            border: 0;
            background: transparent;
            color: #64748b;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }

        .profile-actions {
            display: flex;
            justify-content: flex-start;
        }

        .profile-btn {
            min-width: 120px;
            min-height: 42px;
            padding: 0 18px;
            border: 0;
            border-radius: 12px;
            background: linear-gradient(135deg, #1d8cff, #1570ef);
            color: #fff;
            font-family: var(--font-lato);
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 12px 22px rgba(21, 112, 239, 0.16);
        }

        .profile-history {
            padding: 20px;
        }

        .profile-history-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 16px;
        }

        .profile-history-head h2 {
            margin: 0;
            color: #0f172a;
            font-family: var(--font-lato);
            font-size: 16px;
        }

        .profile-history-head p {
            margin: 4px 0 0;
            color: #64748b;
            font-size: 12px;
        }

        .profile-order-list {
            display: grid;
            gap: 12px;
        }

        .profile-order {
            border-radius: 16px;
            border: 1px solid #e2eaf3;
            background: #fbfdff;
            padding: 14px;
        }

        .profile-order__top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 10px;
        }

        .profile-order__top h3 {
            margin: 0;
            color: #0f172a;
            font-family: var(--font-lato);
            font-size: 14px;
        }

        .profile-order__meta {
            margin-top: 4px;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            color: #64748b;
            font-size: 12px;
        }

        .profile-order__badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 28px;
            padding: 0 10px;
            border-radius: 999px;
            background: #e9f3ff;
            color: #155eef;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .profile-order__courses {
            display: grid;
            gap: 10px;
        }

        .profile-order__course {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px;
            border-radius: 14px;
            background: #ffffff;
            border: 1px solid #e7eef5;
        }

        .profile-order__product {
            align-items: flex-start;
        }

        .profile-order__product-main {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .profile-order__product-thumb {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            border: 1px solid #e2eaf3;
            background: #f8fbff;
            overflow: hidden;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .profile-order__product-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .profile-order__product-fallback {
            color: #94a3b8;
            font-size: 18px;
        }

        .profile-order__course-info {
            min-width: 0;
        }

        .profile-order__course-info strong {
            display: block;
            color: #0f172a;
            font-size: 13px;
        }

        .profile-order__course-info span {
            display: block;
            margin-top: 4px;
            color: #64748b;
            font-size: 12px;
        }

        .profile-order__course-actions {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }

        .profile-course-price {
            color: #0f172a;
            font-size: 12px;
            font-weight: 700;
        }

        .profile-library-list {
            display: grid;
            gap: 12px;
        }

        .profile-library-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 14px;
            border-radius: 16px;
            border: 1px solid #e2eaf3;
            background: #fbfdff;
        }

        .profile-library-item__info {
            min-width: 0;
        }

        .profile-library-item__info strong {
            display: block;
            color: #0f172a;
            font-size: 13px;
        }

        .profile-library-item__info span {
            display: block;
            margin-top: 4px;
            color: #64748b;
            font-size: 12px;
        }

        .profile-course-link {
            min-height: 32px;
            padding: 0 12px;
            border-radius: 10px;
            background: #eff6ff;
            color: #155eef;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-size: 12px;
            font-weight: 700;
        }

        .profile-empty {
            padding: 20px 16px;
            border-radius: 16px;
            border: 1px dashed #d7e3f0;
            text-align: center;
            color: #64748b;
            background: #fbfdff;
            font-size: 12px;
        }

        .profile-errors {
            margin-bottom: 14px;
            padding: 12px 14px;
            border-radius: 12px;
            border: 1px solid #fecaca;
            background: #fff1f2;
            color: #be123c;
            font-size: 12px;
        }

        .profile-errors div + div {
            margin-top: 6px;
        }

        @media (max-width: 991px) {
            .profile-stage,
            .profile-order__top,
            .profile-order__course {
                grid-template-columns: 1fr;
            }

            .profile-stage {
                gap: 14px;
            }

            .profile-form-grid--2 {
                grid-template-columns: 1fr;
            }

            .profile-sidebar {
                padding: 18px 16px;
            }
        }

        @media (max-width: 768px) {
            .profile-tabs {
                gap: 16px;
                padding: 0 14px;
            }

            .profile-card__body,
            .profile-history {
                padding: 16px;
            }
        }
    </style>

    <section class="profile-shell">
        <div class="profile-stage">
            <aside class="profile-card profile-sidebar">
                <div class="profile-sidebar__top">
                    <div class="profile-avatar-wrap">
                        <div class="profile-avatar-lg">
                            @if ($supportsAvatar && $user->avatar_url)
                                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="profile-avatar-image" id="profile-avatar-preview">
                            @else
                                <span id="profile-avatar-fallback">{{ $userInitial }}</span>
                                <img src="" alt="{{ $user->name }}" class="profile-avatar-image" id="profile-avatar-preview" style="display:none;">
                            @endif
                        </div>
                        @if ($supportsAvatar)
                            <label for="profile_avatar" class="profile-avatar-upload" title="{{ __('Upload Photo') }}">
                                <i class="fa-solid fa-camera"></i>
                            </label>
                        @endif
                    </div>
                    <h2>{{ $user->name }}</h2>
                    <p>{{ $user->email }}</p>
                </div>

                <div class="profile-stat-list">
                    <div class="profile-stat-item">
                        <span>{{ __('Orders') }}</span>
                        <strong>{{ $orders->count() }}</strong>
                    </div>
                    <div class="profile-stat-item">
                        <span>{{ __('Bought Courses') }}</span>
                        <strong>{{ $orders->flatMap->items->count() }}</strong>
                    </div>
                    <div class="profile-stat-item">
                        <span>{{ __('Bought Products') }}</span>
                        <strong>{{ $shopOrders->flatMap->items->sum('qty') }}</strong>
                    </div>
                </div>

                <div class="profile-side-action">
                    <a href="{{ route('home') }}" class="profile-side-link">{{ __('View Public Profile') }}</a>
                </div>
            </aside>

            <div class="profile-main">
                <div class="profile-card profile-settings-card">
                    <div class="profile-tabs">
                        <span class="profile-tab"><button type="button" class="profile-tab-btn is-active" data-profile-tab="account">{{ __('Account Settings') }}</button></span>
                        <span class="profile-tab"><button type="button" class="profile-tab-btn" data-profile-tab="password">{{ __('Change Password') }}</button></span>
                        <span class="profile-tab"><button type="button" class="profile-tab-btn" data-profile-tab="history">{{ __('Bought Course History') }}</button></span>
                        <span class="profile-tab"><button type="button" class="profile-tab-btn" data-profile-tab="shop-history">{{ __('Bought Product History') }}</button></span>
                        <span class="profile-tab"><button type="button" class="profile-tab-btn" data-profile-tab="liked">{{ __('Liked Courses') }}</button></span>
                        <span class="profile-tab"><button type="button" class="profile-tab-btn" data-profile-tab="saved">{{ __('Saved Courses') }}</button></span>
                    </div>
                </div>

                <div class="profile-card" data-profile-panel="account">
                    <div class="profile-card__body">
                        <h2 class="profile-section-title">{{ __('Account Information') }}</h2>

                        @if ($errors->profile->any())
                            <div class="profile-errors">
                                @foreach ($errors->profile->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="profile-form-grid">
                            @csrf
                            <div class="profile-form-grid profile-form-grid--2">
                                <div class="profile-field">
                                    <label for="profile_name">{{ __('Full Name') }}</label>
                                    <input id="profile_name" type="text" name="name" value="{{ old('name', $user->name) }}" required>
                                </div>

                                <div class="profile-field">
                                    <label for="profile_email">{{ __('Email') }}</label>
                                    <input id="profile_email" type="email" name="email" value="{{ old('email', $user->email) }}" required>
                                </div>
                            </div>

                            @if ($supportsPhone)
                                <div class="profile-field">
                                    <label for="profile_phone">{{ __('Phone') }}</label>
                                    <input id="profile_phone" type="text" name="phone" value="{{ old('phone', $user->phone) }}">
                                </div>
                            @endif

                            @if ($supportsAddress)
                                <div class="profile-field">
                                    <label for="profile_address">{{ __('Address') }}</label>
                                    <input id="profile_address" type="text" name="address" value="{{ old('address', $user->address) }}">
                                </div>
                            @endif

                            <div class="profile-form-grid profile-form-grid--2">
                                @if ($supportsCity)
                                    <div class="profile-field">
                                        <label for="profile_city">{{ __('City') }}</label>
                                        <input id="profile_city" type="text" name="city" value="{{ old('city', $user->city) }}">
                                    </div>
                                @endif

                                @if ($supportsProvince)
                                    <div class="profile-field">
                                        <label for="profile_province">{{ __('Province') }}</label>
                                        <input id="profile_province" type="text" name="province" value="{{ old('province', $user->province) }}">
                                    </div>
                                @endif
                            </div>

                            @if ($supportsPostalCode)
                                <div class="profile-field">
                                    <label for="profile_postal_code">{{ __('Postal Code') }}</label>
                                    <input id="profile_postal_code" type="text" name="postal_code" value="{{ old('postal_code', $user->postal_code) }}">
                                </div>
                            @endif

                            @if ($supportsAvatar)
                                <input id="profile_avatar" type="file" name="avatar" accept=".jpg,.jpeg,.png,.webp" class="profile-avatar-input">
                            @endif

                            <div class="profile-actions">
                                <button type="submit" class="profile-btn">{{ __('Update Profile') }}</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="profile-card profile-panel" data-profile-panel="password" hidden>
                    <div class="profile-card__body">
                        <h2 class="profile-section-title">{{ __('Change Password') }}</h2>

                        @if ($errors->passwordUpdate->any())
                            <div class="profile-errors">
                                @foreach ($errors->passwordUpdate->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        <form action="{{ route('profile.password.update') }}" method="POST" class="profile-form-grid">
                            @csrf
                            <div class="profile-field">
                                <label for="current_password">{{ __('Current Password') }}</label>
                                <div class="profile-password-wrap">
                                    <input id="current_password" type="password" name="current_password" required>
                                    <button type="button" class="profile-password-toggle" data-profile-password-toggle aria-label="Toggle password visibility">
                                        <i class="fa-regular fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="profile-form-grid profile-form-grid--2">
                                <div class="profile-field">
                                    <label for="new_password">{{ __('New Password') }}</label>
                                    <div class="profile-password-wrap">
                                        <input id="new_password" type="password" name="password" required>
                                        <button type="button" class="profile-password-toggle" data-profile-password-toggle aria-label="Toggle password visibility">
                                            <i class="fa-regular fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="profile-field">
                                    <label for="new_password_confirmation">{{ __('Confirm Password') }}</label>
                                    <div class="profile-password-wrap">
                                        <input id="new_password_confirmation" type="password" name="password_confirmation" required>
                                        <button type="button" class="profile-password-toggle" data-profile-password-toggle aria-label="Toggle password visibility">
                                            <i class="fa-regular fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="profile-actions">
                                <button type="submit" class="profile-btn">{{ __('Update Password') }}</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="profile-card profile-history profile-panel" data-profile-panel="history" hidden>
                    <div class="profile-history-head">
                        <div>
                            <h2>{{ __('Bought Course History') }}</h2>
                            <p>{{ __('Review your order records and open the course pages you already purchased.') }}</p>
                        </div>
                    </div>

                    @if ($orders->isEmpty())
                        <div class="profile-empty">{{ __('No course orders yet. Once you buy courses, your history will appear here.') }}</div>
                    @else
                        <div class="profile-order-list">
                            @foreach ($orders as $order)
                                @php
                                    $latestPayment = $order->payments->first();
                                    $orderStatus = $latestPayment?->status ?: $order->status ?: 'pending';
                                @endphp
                                <div class="profile-order">
                                    <div class="profile-order__top">
                                        <div>
                                            <h3>{{ __('Order') }}: {{ $order->order_no }}</h3>
                                            <div class="profile-order__meta">
                                                <span>{{ __('Date') }}: {{ optional($order->created_at)->format('d M Y h:i A') ?: '-' }}</span>
                                                <span>{{ __('Amount') }}: {{ $order->currency ?: 'USD' }} {{ number_format((float) $order->total_amount, 2) }}</span>
                                                @if ($latestPayment?->payment_provider)
                                                    <span>{{ __('Payment') }}: {{ strtoupper($latestPayment->payment_provider) }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <span class="profile-order__badge">{{ ucfirst($orderStatus) }}</span>
                                    </div>

                                    <div class="profile-order__courses">
                                        @foreach ($order->items as $item)
                                            <div class="profile-order__course">
                                                <div class="profile-order__course-info">
                                                    <strong>{{ $item->course?->title ?: $item->course_title ?: __('Course') }}</strong>
                                                    <span>{{ $item->course?->category?->name ?: __('General') }}</span>
                                                </div>

                                                <div class="profile-order__course-actions">
                                                    <span class="profile-course-price">{{ $order->currency ?: 'USD' }} {{ number_format((float) $item->price, 2) }}</span>
                                                    @if ($item->course)
                                                        <a href="{{ route('courses.show', $item->course->slug ?: $item->course->id) }}" class="profile-course-link">{{ __('View Course') }}</a>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="profile-card profile-history profile-panel" data-profile-panel="shop-history" hidden>
                    <div class="profile-history-head">
                        <div>
                            <h2>{{ __('Bought Product History') }}</h2>
                            <p>{{ __('Review your shopping purchase records and open the product pages you already bought.') }}</p>
                        </div>
                    </div>

                    @if ($shopOrders->isEmpty())
                        <div class="profile-empty">{{ __('No product orders yet. Once you buy products, your history will appear here.') }}</div>
                    @else
                        <div class="profile-order-list">
                            @foreach ($shopOrders as $shopOrder)
                                <div class="profile-order">
                                    <div class="profile-order__top">
                                        <div>
                                            <h3>{{ __('Order') }}: {{ $shopOrder->order_no ?: ('#' . $shopOrder->id) }}</h3>
                                            <div class="profile-order__meta">
                                                <span>{{ __('Date') }}: {{ \Illuminate\Support\Carbon::parse($shopOrder->created_at)->format('d M Y h:i A') }}</span>
                                                <span>{{ __('Amount') }}: {{ $shopOrder->currency ?: 'USD' }} {{ number_format((float) $shopOrder->total_amount, 2) }}</span>
                                                @if ($shopOrder->payment_method)
                                                    <span>{{ __('Payment') }}: {{ strtoupper((string) $shopOrder->payment_method) }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <span class="profile-order__badge">{{ ucfirst((string) ($shopOrder->status ?: 'pending')) }}</span>
                                    </div>

                                    <div class="profile-order__courses">
                                        @foreach ($shopOrder->items as $item)
                                            <div class="profile-order__course profile-order__product">
                                                <div class="profile-order__product-main">
                                                    <div class="profile-order__product-thumb">
                                                        @if ($item->image_url)
                                                            <img src="{{ $item->image_url }}" alt="{{ $item->name }}">
                                                        @else
                                                            <span class="profile-order__product-fallback">
                                                                <i class="fa-solid fa-box-open"></i>
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <div class="profile-order__course-info">
                                                        <strong>{{ $item->name }}</strong>
                                                        <span>{{ __('SKU') }}: {{ $item->sku ?: '-' }}</span>
                                                        <span>{{ __('Qty') }}: {{ $item->qty }}</span>
                                                    </div>
                                                </div>

                                                <div class="profile-order__course-actions">
                                                    <span class="profile-course-price">{{ $shopOrder->currency ?: 'USD' }} {{ number_format((float) ($item->line_total ?: $item->unit_price), 2) }}</span>
                                                    @if ($item->slug || $item->product_id)
                                                        <a href="{{ route('shop.show', $item->slug ?: $item->product_id) }}" class="profile-course-link">{{ __('View Product') }}</a>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="profile-card profile-history profile-panel" data-profile-panel="liked" hidden>
                    <div class="profile-history-head">
                        <div>
                            <h2>{{ __('Liked Courses') }}</h2>
                            <p>{{ __('Review the courses you marked as liked.') }}</p>
                        </div>
                    </div>

                    @if ($likedCourses->isEmpty())
                        <div class="profile-empty">{{ __('No liked courses yet.') }}</div>
                    @else
                        <div class="profile-library-list">
                            @foreach ($likedCourses as $favorite)
                                @if ($favorite->course)
                                    <div class="profile-library-item">
                                        <div class="profile-library-item__info">
                                            <strong>{{ $favorite->course->title }}</strong>
                                            <span>{{ $favorite->course->category?->name ?: __('General') }}</span>
                                        </div>

                                        <a href="{{ route('courses.show', $favorite->course->slug ?: $favorite->course->id) }}" class="profile-course-link">{{ __('View Course') }}</a>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="profile-card profile-history profile-panel" data-profile-panel="saved" hidden>
                    <div class="profile-history-head">
                        <div>
                            <h2>{{ __('Saved Courses') }}</h2>
                            <p>{{ __('Review the courses you saved for later.') }}</p>
                        </div>
                    </div>

                    @if ($savedCourses->isEmpty())
                        <div class="profile-empty">{{ __('No saved courses yet.') }}</div>
                    @else
                        <div class="profile-library-list">
                            @foreach ($savedCourses as $savedCourse)
                                @if ($savedCourse->course)
                                    <div class="profile-library-item">
                                        <div class="profile-library-item__info">
                                            <strong>{{ $savedCourse->course->title }}</strong>
                                            <span>{{ $savedCourse->course->category?->name ?: __('General') }}</span>
                                        </div>

                                        <a href="{{ route('courses.show', $savedCourse->course->slug ?: $savedCourse->course->id) }}" class="profile-course-link">{{ __('View Course') }}</a>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection

@push('web_scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-profile-password-toggle]').forEach((toggle) => {
                toggle.addEventListener('click', () => {
                    const wrap = toggle.closest('.profile-password-wrap');
                    const input = wrap ? wrap.querySelector('input') : null;
                    const icon = toggle.querySelector('i');

                    if (!input || !icon) {
                        return;
                    }

                    const showing = input.type === 'text';
                    input.type = showing ? 'password' : 'text';
                    icon.className = showing ? 'fa-regular fa-eye' : 'fa-regular fa-eye-slash';
                });
            });

            const avatarInput = document.getElementById('profile_avatar');
            const avatarPreview = document.getElementById('profile-avatar-preview');
            const avatarFallback = document.getElementById('profile-avatar-fallback');

            avatarInput?.addEventListener('change', (event) => {
                const [file] = event.target.files || [];

                if (!file || !avatarPreview) {
                    return;
                }

                const previewUrl = URL.createObjectURL(file);
                avatarPreview.src = previewUrl;
                avatarPreview.style.display = 'block';

                if (avatarFallback) {
                    avatarFallback.style.display = 'none';
                }
            });

            const profileTabs = document.querySelectorAll('[data-profile-tab]');
            const profilePanels = document.querySelectorAll('[data-profile-panel]');

            profileTabs.forEach((tab) => {
                tab.addEventListener('click', () => {
                    const target = tab.getAttribute('data-profile-tab');

                    profileTabs.forEach((item) => {
                        item.classList.toggle('is-active', item === tab);
                    });

                    profilePanels.forEach((panel) => {
                        panel.hidden = panel.getAttribute('data-profile-panel') !== target;
                    });
                });
            });
        });
    </script>
@endpush
