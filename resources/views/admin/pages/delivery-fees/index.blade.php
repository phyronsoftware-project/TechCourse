@extends('admin.layouts.app')

@section('title', 'Delivery Fees')

@section('content')
    <form action="{{ route('admin.delivery-fees.update') }}" method="POST" data-delivery-fee-form>
            @csrf
            @method('PUT')

            <div class="grid gap-5 md:grid-cols-2">
                <section class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-600">Capital</p>
                            <h3 class="mt-1 text-lg font-bold text-slate-900">Phnom Penh</h3>
                            <p class="mt-1 text-sm text-slate-500">ភ្នំពេញ</p>
                        </div>
                        <span class="admin-chip">1 province</span>
                    </div>

                    <label for="phnom-penh-fee" class="mt-6 block text-sm font-semibold text-slate-700">Delivery fee (USD)</label>
                    <div class="admin-input-group mt-2">
                        <input id="phnom-penh-fee" type="number" name="phnom_penh_fee" value="{{ old('phnom_penh_fee', $phnomPenh?->delivery_fee ?? 0) }}" min="0" max="9999.99" step="0.01" class="admin-input" required>
                        <span class="admin-input-addon">USD</span>
                    </div>
                </section>

                <section class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.16em] text-emerald-600">Nationwide</p>
                            <h3 class="mt-1 text-lg font-bold text-slate-900">Other Provinces</h3>
                            <p class="mt-1 text-sm text-slate-500">គ្រប់ខេត្តក្រៅពីភ្នំពេញ</p>
                        </div>
                        <span class="admin-chip">{{ $otherProvinces->count() }} provinces</span>
                    </div>

                    <label for="other-provinces-fee" class="mt-6 block text-sm font-semibold text-slate-700">Shared delivery fee (USD)</label>
                    <div class="admin-input-group mt-2">
                        <input id="other-provinces-fee" type="number" name="other_provinces_fee" value="{{ old('other_provinces_fee', $otherProvinces->first()?->delivery_fee ?? 0) }}" min="0" max="9999.99" step="0.01" class="admin-input" required>
                        <span class="admin-input-addon">USD</span>
                    </div>
                </section>
            </div>

    </form>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('[data-delivery-fee-form]');

            // Save both fee groups automatically after either value changes.
            form?.querySelectorAll('input[type="number"]').forEach((input) => {
                input.addEventListener('change', () => {
                    if (form.checkValidity()) {
                        form.requestSubmit();
                    }
                });
            });
        });
    </script>
@endsection
