function closeDropdown(root) {
    const toggle = root.querySelector('[data-dropdown-toggle]');
    const menu = root.querySelector('[data-dropdown-menu]');

    if (!(toggle instanceof HTMLElement) || !(menu instanceof HTMLElement)) {
        return;
    }

    menu.hidden = true;
    toggle.setAttribute('aria-expanded', 'false');
    root.classList.remove('ds-dropdown--open');
}

function openDropdown(root) {
    document.querySelectorAll('[data-dropdown].ds-dropdown--open').forEach((other) => {
        if (other !== root) {
            closeDropdown(other);
        }
    });

    const toggle = root.querySelector('[data-dropdown-toggle]');
    const menu = root.querySelector('[data-dropdown-menu]');

    if (!(toggle instanceof HTMLElement) || !(menu instanceof HTMLElement)) {
        return;
    }

    menu.hidden = false;
    toggle.setAttribute('aria-expanded', 'true');
    root.classList.add('ds-dropdown--open');

    const firstItem = menu.querySelector('[role="menuitem"]:not([disabled])');
    if (firstItem instanceof HTMLElement) {
        firstItem.focus();
    }
}

function isDropdownOpen(root) {
    const menu = root.querySelector('[data-dropdown-menu]');

    return menu instanceof HTMLElement && !menu.hidden;
}

function focusMenuItem(menu, direction) {
    const items = Array.from(menu.querySelectorAll('[role="menuitem"]:not([disabled])'));
    if (items.length === 0) {
        return;
    }

    const activeIndex = items.findIndex((item) => item === document.activeElement);
    let nextIndex = 0;

    if (direction === 'first') {
        nextIndex = 0;
    } else if (direction === 'last') {
        nextIndex = items.length - 1;
    } else if (activeIndex === -1) {
        nextIndex = direction === 'prev' ? items.length - 1 : 0;
    } else {
        nextIndex = direction === 'prev'
            ? (activeIndex - 1 + items.length) % items.length
            : (activeIndex + 1) % items.length;
    }

    const nextItem = items[nextIndex];
    if (nextItem instanceof HTMLElement) {
        nextItem.focus();
    }
}

function initDropdown(root) {
    const toggle = root.querySelector('[data-dropdown-toggle]');
    const menu = root.querySelector('[data-dropdown-menu]');

    if (!(toggle instanceof HTMLElement) || !(menu instanceof HTMLElement)) {
        return;
    }

    if (root.dataset.dropdownInit === 'true') {
        return;
    }

    root.dataset.dropdownInit = 'true';

    if (root.dataset.dropdownStaticOpen === 'true' && menu.hidden) {
        menu.hidden = false;
        toggle.setAttribute('aria-expanded', 'true');
        root.classList.add('ds-dropdown--open');
    }

    if (!toggle.hasAttribute('aria-expanded')) {
        toggle.setAttribute('aria-expanded', menu.hidden ? 'false' : 'true');
    }

    if (toggle.hasAttribute('aria-controls') && !menu.id) {
        menu.id = toggle.getAttribute('aria-controls');
    }

    toggle.addEventListener('click', (event) => {
        event.preventDefault();
        event.stopPropagation();

        if (isDropdownOpen(root)) {
            closeDropdown(root);
        } else {
            openDropdown(root);
        }
    });

    menu.querySelectorAll('[role="menuitem"]').forEach((item) => {
        item.addEventListener('click', () => {
            closeDropdown(root);
            toggle.focus();
        });
    });

    menu.addEventListener('keydown', (event) => {
        switch (event.key) {
            case 'ArrowDown':
                event.preventDefault();
                focusMenuItem(menu, 'next');
                break;
            case 'ArrowUp':
                event.preventDefault();
                focusMenuItem(menu, 'prev');
                break;
            case 'Home':
                event.preventDefault();
                focusMenuItem(menu, 'first');
                break;
            case 'End':
                event.preventDefault();
                focusMenuItem(menu, 'last');
                break;
            case 'Escape':
                event.preventDefault();
                closeDropdown(root);
                toggle.focus();
                break;
            default:
                break;
        }
    });
}

function initDropdowns() {
    document.querySelectorAll('[data-dropdown]').forEach((root) => {
        if (root instanceof HTMLElement) {
            initDropdown(root);
        }
    });

    document.addEventListener('click', (event) => {
        if (!(event.target instanceof Node)) {
            return;
        }

        document.querySelectorAll('[data-dropdown].ds-dropdown--open').forEach((root) => {
            if (root instanceof HTMLElement && !root.contains(event.target)) {
                closeDropdown(root);
            }
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') {
            return;
        }

        document.querySelectorAll('[data-dropdown].ds-dropdown--open').forEach((root) => {
            if (root instanceof HTMLElement) {
                const toggle = root.querySelector('[data-dropdown-toggle]');
                closeDropdown(root);
                if (toggle instanceof HTMLElement) {
                    toggle.focus();
                }
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', initDropdowns);
