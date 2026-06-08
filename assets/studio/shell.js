document.addEventListener('DOMContentLoaded', () => {
    initMobileDrawer();
    initHeaderUserMenu();
});

function initMobileDrawer() {
    const toggleButton = document.querySelector('[data-studio-menu-toggle]');
    const drawer = document.querySelector('[data-studio-mobile-drawer]');
    const overlay = document.querySelector('[data-studio-mobile-overlay]');
    const closeButtons = document.querySelectorAll('[data-studio-menu-close]');

    if (!(toggleButton instanceof HTMLButtonElement) || !(drawer instanceof HTMLElement) || !(overlay instanceof HTMLElement)) {
        return;
    }

    const navLinks = drawer.querySelectorAll('[data-studio-nav-link]');

    const setOpen = (open) => {
        toggleButton.setAttribute('aria-expanded', open ? 'true' : 'false');
        drawer.setAttribute('aria-hidden', open ? 'false' : 'true');
        overlay.setAttribute('aria-hidden', open ? 'false' : 'true');
        drawer.classList.toggle('is-open', open);
        overlay.classList.toggle('hidden', !open);
        document.body.classList.toggle('overflow-hidden', open);
    };

    toggleButton.addEventListener('click', () => {
        const isOpen = toggleButton.getAttribute('aria-expanded') === 'true';
        setOpen(!isOpen);
    });

    overlay.addEventListener('click', () => setOpen(false));

    closeButtons.forEach((button) => {
        button.addEventListener('click', () => setOpen(false));
    });

    navLinks.forEach((link) => {
        link.addEventListener('click', () => setOpen(false));
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && toggleButton.getAttribute('aria-expanded') === 'true') {
            setOpen(false);
            toggleButton.focus();
        }
    });
}

function initHeaderUserMenu() {
    const root = document.querySelector('[data-studio-header-user]');
    const toggle = document.querySelector('[data-studio-user-toggle]');
    const menu = document.querySelector('[data-studio-user-dropdown]');

    if (!(root instanceof HTMLElement) || !(toggle instanceof HTMLButtonElement) || !(menu instanceof HTMLElement)) {
        return;
    }

    const setOpen = (open) => {
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        menu.classList.toggle('is-open', open);
        if (!open) {
            menu.setAttribute('hidden', '');
        } else {
            menu.removeAttribute('hidden');
        }
    };

    toggle.addEventListener('click', (event) => {
        event.stopPropagation();
        const isOpen = toggle.getAttribute('aria-expanded') === 'true';
        setOpen(!isOpen);
    });

    document.addEventListener('click', (event) => {
        if (!root.contains(event.target)) {
            setOpen(false);
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') {
            setOpen(false);
            toggle.focus();
        }
    });
}
