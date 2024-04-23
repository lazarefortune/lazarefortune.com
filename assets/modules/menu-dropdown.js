function initDropdown() {
    document.querySelectorAll('.sidebar-dropdown-toggle').forEach(toggle => {
        toggle.addEventListener('click', () => {
            const dropdownIcon = toggle.querySelector('.sidebar-dropdown-toggle-icon');
            dropdownIcon.classList.toggle('rotate-90');
            const menu = toggle.nextElementSibling;
            menu.classList.toggle('hidden');
        });
    });

    // Ferme les menus déroulants si on clique en dehors d'eux
    document.addEventListener('click', e => {
        if (!e.target.closest('.sidebar-dropdown-toggle') && !e.target.closest('.sidebar-dropdown-menu')) {
            document.querySelectorAll('.sidebar-dropdown-menu').forEach(dropdown => {
                dropdown.classList.add('hidden');
                const dropdownIcon = dropdown.previousElementSibling.querySelector('.sidebar-dropdown-toggle-icon');
                if (dropdownIcon) {
                    dropdownIcon.classList.remove('rotate-90');
                }
            });
        }
    });
}

document.addEventListener('DOMContentLoaded', initDropdown);