document.addEventListener('DOMContentLoaded', () => {
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
        drawer.classList.toggle('translate-x-0', open);
        drawer.classList.toggle('-translate-x-full', !open);
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
});
