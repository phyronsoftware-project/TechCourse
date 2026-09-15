<div class="admin-bulk-toolbar" data-bulk-delete-toolbar="{{ $formId }}">
    <span class="admin-bulk-count" aria-live="polite">
        <strong data-bulk-delete-count>0</strong> selected
    </span>

    <form id="{{ $formId }}" action="{{ $action }}" method="POST">
        @csrf
        @method('DELETE')
        <button type="submit" class="admin-bulk-delete" data-bulk-delete-button disabled>
            Delete Selected
        </button>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Synchronize select-all, selected count, and the bulk delete confirmation.
        const formId = @js($formId);
        const itemLabel = @js($itemLabel);
        const toolbar = document.querySelector(`[data-bulk-delete-toolbar="${formId}"]`);
        const form = document.getElementById(formId);
        const selectAll = document.querySelector(`[data-bulk-select-all="${formId}"]`);
        const checkboxes = [...document.querySelectorAll(`[data-bulk-select-item="${formId}"]`)];
        const count = toolbar?.querySelector('[data-bulk-delete-count]');
        const button = toolbar?.querySelector('[data-bulk-delete-button]');

        if (! toolbar || ! form || ! selectAll || ! count || ! button || ! checkboxes.length) {
            return;
        }

        const refreshSelection = () => {
            const selectedCount = checkboxes.filter((checkbox) => checkbox.checked).length;
            count.textContent = String(selectedCount);
            button.disabled = selectedCount === 0;
            selectAll.checked = selectedCount === checkboxes.length;
            selectAll.indeterminate = selectedCount > 0 && selectedCount < checkboxes.length;
        };

        selectAll.addEventListener('change', () => {
            checkboxes.forEach((checkbox) => {
                checkbox.checked = selectAll.checked;
            });
            refreshSelection();
        });

        checkboxes.forEach((checkbox) => checkbox.addEventListener('change', refreshSelection));

        form.addEventListener('submit', (event) => {
            const selectedCount = checkboxes.filter((checkbox) => checkbox.checked).length;

            if (! selectedCount || ! window.confirm(`Delete ${selectedCount} selected ${itemLabel}? This action cannot be undone.`)) {
                event.preventDefault();
                return;
            }

            button.disabled = true;
        });

        refreshSelection();
    });
</script>
