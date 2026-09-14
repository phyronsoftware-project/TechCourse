import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.css';

// Enhance the shop's native multiple select with searchable, removable category tags.
const categorySelect = document.querySelector('[data-shop-category-select]');

if (categorySelect) {
    let submitTimer;

    // Build the picker before listening, so its initial selected tags do not submit the form.
    const categoryPicker = new TomSelect(categorySelect, {
        plugins: {
            remove_button: {
                title: categorySelect.dataset.removeLabel || 'Remove category',
            },
        },
        create: false,
        closeAfterSelect: false,
        hideSelected: true,
        placeholder: categorySelect.dataset.placeholder || 'Select categories...',
    });

    // Auto-apply additions and removals after a short pause for multi-selection.
    categoryPicker.on('change', () => {
        window.clearTimeout(submitTimer);
        submitTimer = window.setTimeout(() => categorySelect.form?.requestSubmit(), 450);
    });
}
