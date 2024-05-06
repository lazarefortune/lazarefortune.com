function initializeDropdowns() {
    const dropdownButtons = document.querySelectorAll('.dropdown-button');

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
