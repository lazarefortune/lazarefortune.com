function initializeDropdowns() {
    const dropdownButtons = document.querySelectorAll('.dropdown-user-button');

    function toggleDropdownVisibility(menu) {
        // Toggle classes for visibility and animations
        const classesToShow = ['opacity-100', 'scale-100', 'visible', 'pointer-events-auto', 'z-50'];
        const classesToHide = ['opacity-0', 'scale-95', 'invisible', 'pointer-events-none'];

        if (menu.classList.contains('invisible')) {
            menu.classList.remove(...classesToHide);
            menu.classList.add(...classesToShow);
        } else {
            menu.classList.add(...classesToHide);
            menu.classList.remove(...classesToShow);
        }
    }

    dropdownButtons.forEach(button => {
        const menuId = button.getAttribute('aria-controls');
        const menu = document.getElementById(menuId);

        button.addEventListener('click', () => toggleDropdownVisibility(menu));
    });
}

document.addEventListener('DOMContentLoaded', initializeDropdowns);

document.addEventListener('DOMContentLoaded', () => {
    const dropdowns = document.querySelectorAll('.dropdown');

    dropdowns.forEach(dropdown => {
        const dropdownButton = dropdown.querySelector('.dropdown-button');
        const dropdownMenu = dropdown.querySelector('.dropdown-menu');
        const dropdownIcon = dropdown.querySelector('.dropdown-button-icon');

        function adjustDropdownPosition() {
            if (!dropdownMenu.classList.contains('visible')) {
                return;
            }

            const rect = dropdownButton.getBoundingClientRect();
            const menuHeight = dropdownMenu.offsetHeight;
            const menuWidth = dropdownMenu.offsetWidth;
            const viewportHeight = window.innerHeight;
            const viewportWidth = window.innerWidth;

            dropdownMenu.classList.remove('dropdown-menu--right', 'dropdown-menu--up');

            if (rect.bottom + menuHeight > viewportHeight) {
                dropdownMenu.classList.add('dropdown-menu--up');
            }

            if (rect.left + menuWidth > viewportWidth) {
                dropdownMenu.classList.add('dropdown-menu--right');
            }
        }

        if (!dropdownButton || !dropdownMenu) {
            return;
        }

        dropdownButton.addEventListener('click', (event) => {
            event.stopPropagation();
            // Close all other dropdowns
            dropdowns.forEach(otherDropdown => {
                if (otherDropdown !== dropdown) {
                    const otherDropdownMenu = otherDropdown.querySelector('.dropdown-menu');
                    otherDropdownMenu.classList.remove('visible');
                }
            });
            dropdownMenu.classList.toggle('visible');
            adjustDropdownPosition();

            if (!dropdownIcon) {
                return;
            }
            dropdownIcon.classList.toggle('rotate-180');
        });

        document.addEventListener('click', (event) => {
            if (!dropdown.contains(event.target)) {
                dropdownMenu.classList.remove('visible');
            }
        });

        window.addEventListener('resize', adjustDropdownPosition);
        window.addEventListener('scroll', adjustDropdownPosition, true);
    });
});