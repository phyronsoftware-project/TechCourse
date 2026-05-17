@extends('admin.layouts.app')

@section('title', 'Settings')

@section('content')
    <form action="{{ route('admin.settings.update') }}" method="POST" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @csrf

        <section class="dashboard-panel rounded-[30px] p-5 sm:p-6">
            <div class="admin-page-header">
                <div>
                    <h3 class="admin-page-title">Website Settings</h3>
                    <p class="admin-page-copy">General website structure placeholder for later implementation.</p>
                </div>
            </div>
        </section>

        <section class="dashboard-panel rounded-[30px] p-5 sm:p-6">
            <div class="admin-page-header">
                <div>
                    <h3 class="admin-page-title">Course Settings</h3>
                    <p class="admin-page-copy">Course display, publishing, and enrollment settings can be added here later.</p>
                </div>
            </div>
        </section>

        <section class="dashboard-panel rounded-[30px] p-5 sm:p-6">
            <div class="admin-page-header">
                <div>
                    <h3 class="admin-page-title">Contact Settings</h3>
                    <p class="admin-page-copy">Contact channels, support info, and company profile placeholders.</p>
                </div>
            </div>
        </section>

        <section class="dashboard-panel rounded-[30px] p-5 sm:p-6 md:col-span-2 xl:col-span-2">
            <div class="admin-page-header">
                <div>
                    <h3 class="admin-page-title">ABA Payment Settings</h3>
                    <p class="admin-page-copy">Prepared for future PayWay integration on both web and app.</p>
                </div>
                <span class="admin-chip {{ $abaSummary['is_ready'] ? '!bg-emerald-50 !text-emerald-600' : '!bg-amber-50 !text-amber-600' }}">
                    {{ $abaSummary['is_ready'] ? 'Ready' : 'Partial' }}
                </span>
            </div>

            <div class="mt-5 grid gap-4 md:grid-cols-2">
                @foreach ([
                    'Merchant ID' => $abaSummary['merchant_id_masked'],
                    'API Key' => $abaSummary['api_key_masked'],
                    'Currency' => $abaSummary['currency'],
                    'Payment Option' => $abaSummary['payment_option'],
                    'Purchase URL' => $abaSummary['purchase_url'],
                    'Generate QR URL' => $abaSummary['generate_qr_url'],
                    'Check Transaction URL' => $abaSummary['check_transaction_url'],
                    'Return URL' => $abaSummary['return_url'],
                    'Cancel URL' => $abaSummary['cancel_url'],
                    'Callback URL' => $abaSummary['callback_url'],
                ] as $label => $value)
                    <div class="rounded-[24px] border border-white/70 bg-white/80 p-4">
                        <p class="text-sm text-slate-500">{{ $label }}</p>
                        <p class="mt-1 break-all text-sm font-semibold text-slate-900">{{ $value ?: '-' }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 rounded-[24px] border border-white/70 bg-white/80 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500">RSA Public Key</p>
                        <p class="mt-1 text-base font-semibold text-slate-900">{{ $abaSummary['has_public_key'] ? 'Loaded' : 'Missing' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">RSA Private Key</p>
                        <p class="mt-1 text-base font-semibold text-slate-900">{{ $abaSummary['has_private_key'] ? 'Loaded' : 'Missing' }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="dashboard-panel rounded-[30px] p-5 sm:p-6">
            <div class="admin-page-header">
                <div>
                    <h3 class="admin-page-title">SEO Settings</h3>
                    <p class="admin-page-copy">Meta title, meta description, and social card placeholders.</p>
                </div>
            </div>
        </section>

        <div class="md:col-span-2 xl:col-span-3">
            <button type="submit" class="admin-btn admin-btn-primary">Save Placeholder Settings</button>
        </div>
    </form>
@endsection
