@php
    $platformValue = old('platform', $link->platform ?? $defaultPlatform ?? 'web');
    $iconValue = old('icon', $link->icon ?? 'fa-brands fa-facebook-f');
@endphp

<div class="admin-field">
    <label>Platform</label>
    <div class="admin-input-group">
        <select name="platform" class="admin-select" required>
            <option value="web" @selected($platformValue === 'web')>Web</option>
            <option value="app" @selected($platformValue === 'app')>App</option>
            <option value="all" @selected($platformValue === 'all')>All</option>
        </select>
        <span class="admin-input-addon">PL</span>
    </div>
</div>

<div class="admin-field">
    <label>Sort Order</label>
    <div class="admin-input-group">
        <input type="number" name="sort_order" value="{{ old('sort_order', $link->sort_order ?? 0) }}" class="admin-input">
        <span class="admin-input-addon">#</span>
    </div>
</div>

<div class="admin-field">
    <label>Social Name</label>
    <div class="admin-input-group">
        <input type="text" name="name" value="{{ old('name', $link->name ?? '') }}" class="admin-input" placeholder="Facebook" required>
        <span class="admin-input-addon">Aa</span>
    </div>
</div>

<div class="admin-field">
    <label>Icon</label>
    <div class="admin-input-group">
        <select name="icon" class="admin-select" required data-social-icon-select>
            @foreach ($iconOptions as $value => $label)
                <option value="{{ $value }}" @selected($iconValue === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <span class="admin-input-addon">IC</span>
    </div>
    <div class="mt-3 inline-flex items-center gap-3 rounded-2xl border border-[#dbe6f1] bg-[#fbfdff] px-4 py-3 text-[#173f87]">
        <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-[#eef5fd] text-[18px]" data-social-icon-preview>
            <i class="{{ $iconValue }}"></i>
        </span>
        <div class="text-sm">
            <div class="font-semibold text-[#173f87]">Icon Preview</div>
            <div class="text-[#6b7c98]">Choose the icon that matches your social media.</div>
        </div>
    </div>
</div>

<div class="admin-field" style="grid-column: 1 / -1;">
    <label>URL</label>
    <div class="admin-input-group">
        <input type="url" name="url" value="{{ old('url', $link->url ?? '') }}" class="admin-input" placeholder="https://facebook.com/your-page" required>
        <span class="admin-input-addon">URL</span>
    </div>
</div>

<label class="admin-checkbox" style="grid-column: 1 / -1;">
    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $link->is_active ?? true)) class="rounded border-slate-300">
    Active social media link
</label>
