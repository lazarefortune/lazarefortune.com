function initUserNotifications() {
    const userBtn = document.getElementById('user-button');
    const userDropdown = document.getElementById('user-dropdown');

    if (userBtn && userDropdown) {
        userBtn.addEventListener('click', () => {
            if (userDropdown.classList.contains('invisible')) {
                userDropdown.classList.remove('opacity-0', 'scale-95', 'invisible', 'pointer-events-none');
                userDropdown.classList.add('opacity-100', 'scale-100', 'visible', 'pointer-events-auto');
            } else {
                userDropdown.classList.add('opacity-0', 'scale-95', 'invisible', 'pointer-events-none');
                userDropdown.classList.remove('opacity-100', 'scale-100', 'visible', 'pointer-events-auto');
            }
        });

        // Ferme le menu utilisateur si on clique en dehors
        document.addEventListener('click', e => {
            if (!e.target.closest('#user-dropdown') && !e.target.closest('#user-button')) {
                userDropdown.classList.add('opacity-0', 'scale-95', 'invisible', 'pointer-events-none');
                userDropdown.classList.remove('opacity-100', 'scale-100', 'visible', 'pointer-events-auto');
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', initUserNotifications);