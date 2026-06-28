@php
    $notification = $notification ?? null;
    $typeValue = old('type', $notification?->type ?? 'general');
@endphp

<div class="admin-field">
    <label>Notification Type</label>
    <div class="admin-input-group">
        <select name="type" class="admin-select" required data-notification-type>
            <option value="general" @selected($typeValue === 'general')>General</option>
            <option value="specific" @selected($typeValue === 'specific')>Specific User</option>
        </select>
        <span class="admin-input-addon">TP</span>
    </div>
</div>

<div class="admin-field" data-notification-user-field>
    <label>Recipient User</label>
    <div class="admin-input-group">
        <select name="user_id" class="admin-select">
            <option value="">Select user</option>
            @foreach ($users as $user)
                <option value="{{ $user->id }}" @selected((string) old('user_id', $notification?->user_id) === (string) $user->id)>
                    {{ $user->name }} ({{ $user->email }})
                </option>
            @endforeach
        </select>
        <span class="admin-input-addon">USR</span>
    </div>
</div>

<div class="admin-field" data-notification-audience-field>
    <label>Audience</label>
    <div class="admin-input-group">
        <select name="audience" class="admin-select" required>
            <option value="all" @selected(old('audience', $notification?->audience ?? 'all') === 'all')>All</option>
            <option value="users" @selected(old('audience', $notification?->audience ?? 'all') === 'users')>Users</option>
            <option value="admins" @selected(old('audience', $notification?->audience ?? 'all') === 'admins')>Admins</option>
        </select>
        <span class="admin-input-addon">AU</span>
    </div>
</div>

<div class="admin-field">
    <label>Channel</label>
    <div class="admin-input-group">
        <select name="channel" class="admin-select" required>
            <option value="web" @selected(old('channel', $notification?->channel ?? 'web') === 'web')>Web</option>
            <option value="app" @selected(old('channel', $notification?->channel ?? 'web') === 'app')>App</option>
            <option value="all" @selected(old('channel', $notification?->channel ?? 'web') === 'all')>All</option>
        </select>
        <span class="admin-input-addon">CH</span>
    </div>
</div>

<div class="admin-field">
    <label>Alert Style</label>
    <div class="admin-input-group">
        <select name="style" class="admin-select" required>
            <option value="info" @selected(old('style', $notification?->style ?? 'info') === 'info')>Info</option>
            <option value="success" @selected(old('style', $notification?->style ?? 'info') === 'success')>Success</option>
            <option value="warning" @selected(old('style', $notification?->style ?? 'info') === 'warning')>Warning</option>
            <option value="error" @selected(old('style', $notification?->style ?? 'info') === 'error')>Error</option>
        </select>
        <span class="admin-input-addon">AL</span>
    </div>
</div>

<div class="admin-field" style="grid-column: 1 / -1;">
    <label>Title</label>
    <div class="admin-input-group">
        <input type="text" name="title" value="{{ old('title', $notification?->title) }}" class="admin-input" placeholder="Notification title" required>
        <span class="admin-input-addon">Aa</span>
    </div>
</div>

<div class="admin-field" style="grid-column: 1 / -1;">
    <label>Message</label>
    <textarea name="message" rows="5" class="admin-textarea" placeholder="Write the notification message here" required>{{ old('message', $notification?->message) }}</textarea>
</div>

<div class="admin-field" style="grid-column: 1 / -1;">
    <label>Link URL</label>
    <div class="admin-input-group">
        <input type="text" name="link_url" value="{{ old('link_url', $notification?->link_url) }}" class="admin-input" placeholder="/courses or https://example.com/page">
        <span class="admin-input-addon">URL</span>
    </div>
</div>

<div class="admin-field">
    <label>Starts At</label>
    <input type="datetime-local" name="starts_at" value="{{ old('starts_at', $notification?->starts_at ? $notification->starts_at->timezone($notificationTimezone)->format('Y-m-d\\TH:i') : null) }}" class="admin-input">
</div>

<div class="admin-field">
    <label>Ends At</label>
    <input type="datetime-local" name="ends_at" value="{{ old('ends_at', $notification?->ends_at ? $notification->ends_at->timezone($notificationTimezone)->format('Y-m-d\\TH:i') : null) }}" class="admin-input">
</div>

<label class="admin-checkbox">
    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $notification?->is_active ?? true)) class="rounded border-slate-300">
    Active notification
</label>

<label class="admin-checkbox">
    <input type="checkbox" name="send_as_popup" value="1" @checked(old('send_as_popup', $notification?->send_as_popup ?? true)) class="rounded border-slate-300">
    Show as web popup alert
</label>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const typeSelect = document.querySelector('[data-notification-type]');
        const userField = document.querySelector('[data-notification-user-field]');
        const audienceField = document.querySelector('[data-notification-audience-field]');

        if (!typeSelect || !userField || !audienceField) {
            return;
        }

        const syncFields = () => {
            const isSpecific = typeSelect.value === 'specific';
            userField.style.display = isSpecific ? '' : 'none';
            audienceField.style.display = isSpecific ? 'none' : '';
        };

        typeSelect.addEventListener('change', syncFields);
        syncFields();
    });
</script>
