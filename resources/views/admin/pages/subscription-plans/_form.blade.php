@php
    $selectedCourses = collect(old('course_ids', isset($plan) ? $plan->courses->pluck('id')->all() : []))->map(fn ($id) => (int) $id)->all();
@endphp

<div class="admin-field">
    <label>Name</label>
    <input type="text" name="name" value="{{ old('name', $plan->name ?? '') }}" class="admin-input" required>
</div>
<div class="admin-field">
    <label>Slug</label>
    <input type="text" name="slug" value="{{ old('slug', $plan->slug ?? '') }}" class="admin-input" placeholder="pro-monthly">
</div>
<div class="admin-field">
    <label>Price (USD)</label>
    <input type="number" min="0" step="0.01" name="price" value="{{ old('price', $plan->price ?? '') }}" class="admin-input" required>
</div>
<div class="admin-field">
    <label>Duration Days</label>
    <input type="number" min="1" name="duration_days" value="{{ old('duration_days', $plan->duration_days ?? 30) }}" class="admin-input" required>
</div>
<div class="admin-field">
    <label>Status</label>
    <select name="status" class="admin-select">
        <option value="active" @selected(old('status', $plan->status ?? 'active') === 'active')>Active</option>
        <option value="inactive" @selected(old('status', $plan->status ?? '') === 'inactive')>Inactive</option>
    </select>
</div>
<div class="admin-field">
    <label>Sort Order</label>
    <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $plan->sort_order ?? 0) }}" class="admin-input">
</div>
<div class="admin-field" style="grid-column: 1 / -1;">
    <label>Description</label>
    <textarea name="description" rows="4" class="admin-input">{{ old('description', $plan->description ?? '') }}</textarea>
</div>
<div class="admin-field" style="grid-column: 1 / -1;">
    <label>Included Courses</label>
    {{-- Allow one plan to grant access to multiple existing courses. --}}
    <select name="course_ids[]" class="admin-select" multiple size="10">
        @foreach ($courses as $course)
            <option value="{{ $course->id }}" @selected(in_array((int) $course->id, $selectedCourses, true))>{{ $course->title }}</option>
        @endforeach
    </select>
    <small>Hold Ctrl/Command to select multiple courses.</small>
</div>
<div class="admin-field" style="grid-column: 1 / -1;">
    <label><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $plan->is_featured ?? false))> Featured plan</label>
    <input type="hidden" name="currency" value="USD">
</div>
