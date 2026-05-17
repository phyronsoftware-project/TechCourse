@php
    $type = session('success') ? 'success' : (session('error') ? 'error' : (session('warning') ? 'warning' : (session('info') ? 'info' : null)));
    $message = $type ? session($type) : null;
    $titles = [
        'success' => __('Success'),
        'error' => __('Error'),
        'warning' => __('Warning'),
        'info' => __('Information'),
    ];
    $icons = [
        'success' => 'fa-solid fa-circle-check',
        'error' => 'fa-solid fa-circle-xmark',
        'warning' => 'fa-solid fa-triangle-exclamation',
        'info' => 'fa-solid fa-circle-info',
    ];
@endphp

@if ($message)
    <div class="web-alert web-alert--{{ $type }}" data-web-alert role="status" aria-live="polite">
        <div class="web-alert__icon" aria-hidden="true">
            <i class="{{ $icons[$type] }}"></i>
        </div>
        <div class="web-alert__content">
            <div class="web-alert__title">{{ $titles[$type] }}</div>
            <div class="web-alert__message">{{ $message }}</div>
        </div>
        <button type="button" class="web-alert__close" data-web-alert-close aria-label="{{ __('Close alert') }}">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
@endif
