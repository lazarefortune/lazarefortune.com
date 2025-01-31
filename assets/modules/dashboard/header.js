function initHeaderUserDropdown() {
    /* --- User Dropdown --- */
    const headerDropdown = document.querySelector('#header-dropdown-button');
    const headerDropdownMenu = document.querySelector('#header-dropdown-menu');

    if (headerDropdown && headerDropdownMenu) {
        headerDropdown.addEventListener('click', (event) => {
            event.stopPropagation(); // Empêche la propagation du clic au document

            // Bascule la visibilité du menu déroulant associé
            headerDropdownMenu.classList.toggle('is-visible');

            // Met à jour l'attribut aria-expanded pour l'accessibilité
            const isExpanded = headerDropdown.getAttribute('aria-expanded') === 'true';
            headerDropdown.setAttribute('aria-expanded', !isExpanded);
        });

        document.addEventListener('click', () => {
            headerDropdownMenu.classList.remove('is-visible');
            headerDropdown.setAttribute('aria-expanded', 'false');
        });
    }
}

document.addEventListener('DOMContentLoaded', initHeaderUserDropdown);

