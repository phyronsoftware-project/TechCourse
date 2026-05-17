const setupAdminUi = () => {
    const sidebar = document.querySelector('[data-sidebar]');
    const backdrop = document.querySelector('[data-sidebar-backdrop]');
    const openButton = document.querySelector('[data-sidebar-toggle]');
    const closeButton = document.querySelector('[data-sidebar-close]');
    const submenuButtons = document.querySelectorAll('[data-submenu-toggle]');

    const setSidebarState = (open) => {
        if (!sidebar || !backdrop) {
            return;
        }

        sidebar.classList.toggle('translate-x-0', open);
        sidebar.classList.toggle('-translate-x-full', !open);
        backdrop.classList.toggle('hidden', !open);
        document.body.classList.toggle('overflow-hidden', open && window.innerWidth < 1024);
    };

    openButton?.addEventListener('click', () => setSidebarState(true));
    closeButton?.addEventListener('click', () => setSidebarState(false));
    backdrop?.addEventListener('click', () => setSidebarState(false));

    submenuButtons.forEach((button) => {
        const submenu = button.parentElement?.querySelector('[data-submenu]');

        if (!submenu) {
            return;
        }

        const isOpen = button.getAttribute('aria-expanded') === 'true';
        button.classList.toggle('is-active', isOpen);
        submenu.classList.toggle('is-open', isOpen);
        submenu.style.maxHeight = isOpen ? `${submenu.scrollHeight}px` : '0px';

        button.addEventListener('click', () => {
            const isCurrentlyOpen = button.getAttribute('aria-expanded') === 'true';

            submenuButtons.forEach((otherButton) => {
                if (otherButton === button) {
                    return;
                }

                const otherSubmenu = otherButton.parentElement?.querySelector('[data-submenu]');

                if (!otherSubmenu) {
                    return;
                }

                otherButton.setAttribute('aria-expanded', 'false');
                otherButton.classList.remove('is-active');
                otherSubmenu.classList.remove('is-open');
                otherSubmenu.style.maxHeight = '0px';
            });

            button.setAttribute('aria-expanded', String(!isCurrentlyOpen));
            button.classList.toggle('is-active', !isCurrentlyOpen);
            submenu.classList.toggle('is-open', !isCurrentlyOpen);
            submenu.style.maxHeight = isCurrentlyOpen ? '0px' : `${submenu.scrollHeight}px`;
        });
    });

    requestAnimationFrame(() => {
        submenuButtons.forEach((button) => {
            const submenu = button.parentElement?.querySelector('[data-submenu]');

            submenu?.classList.add('is-ready');
        });
    });

    const actionMenus = document.querySelectorAll('.admin-action-list');

    document.addEventListener('click', (event) => {
        actionMenus.forEach((menu) => {
            if (!menu.contains(event.target)) {
                menu.removeAttribute('open');
            }
        });
    });

    const fileInputs = document.querySelectorAll('[data-file-input]');

    fileInputs.forEach((input) => {
        const targetName = input.getAttribute('data-file-name');
        const targetPreview = input.getAttribute('data-file-preview');
        const filenameNode = targetName ? document.querySelector(targetName) : null;
        const previewNode = targetPreview ? document.querySelector(targetPreview) : null;
        const previewImage = previewNode?.querySelector('img');
        const previewText = previewNode?.querySelector('[data-preview-text]');

        input.addEventListener('change', () => {
            const [file] = input.files ?? [];

            if (filenameNode) {
                filenameNode.textContent = file ? file.name : 'No file selected';
            }

            if (!previewNode) {
                return;
            }

            if (!file || !file.type.startsWith('image/')) {
                if (previewImage) {
                    previewImage.removeAttribute('src');
                    previewImage.classList.add('hidden');
                }

                previewText?.classList.remove('hidden');
                return;
            }

            const reader = new FileReader();
            reader.onload = ({ target }) => {
                if (previewImage) {
                    previewImage.src = String(target?.result ?? '');
                    previewImage.classList.remove('hidden');
                }

                previewText?.classList.add('hidden');
            };

            reader.readAsDataURL(file);
        });
    });
};

document.addEventListener('DOMContentLoaded', setupAdminUi);
