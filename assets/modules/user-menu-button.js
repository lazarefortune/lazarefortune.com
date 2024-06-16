function initUserNotifications() {
    const userBtn = document.getElementById('user-button');
    const userDropdown = document.getElementById('user-dropdown');

    if (userBtn && userDropdown) {
        userBtn.addEventListener('click', () => {
            if (userDropdown.classList.contains('is-visible')) {
                userDropdown.classList.remove('is-visible');
            } else {
                userDropdown.classList.add('is-visible');
            }
        });

        // Ferme le menu utilisateur si on clique en dehors
        document.addEventListener('click', e => {
            if (!e.target.closest('#user-dropdown') && !e.target.closest('#user-button')) {
                userDropdown.classList.remove('is-visible')
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', initUserNotifications);