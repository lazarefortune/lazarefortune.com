document.addEventListener('DOMContentLoaded', () => {
    const inputGroups = document.querySelectorAll('.password-toggle-group');
    inputGroups.forEach((inputGroup) => {
        inputGroup
            .querySelector('.password-toggle-button')
            .addEventListener('click', (e) => {
                e.preventDefault();

                const inputToggle = inputGroup.querySelector('.password-toggle-input');
                const iconToggleShow = inputGroup.querySelector('.password-toggle-icon-show');
                const iconToggleHide = inputGroup.querySelector('.password-toggle-icon-hide');

                if (inputToggle.getAttribute('type') === 'password') {
                    inputToggle.setAttribute('type', 'text');
                    iconToggleShow.classList.add('hidden');
                    iconToggleHide.classList.remove('hidden');
                } else {
                    inputToggle.setAttribute('type', 'password');
                    iconToggleShow.classList.remove('hidden');
                    iconToggleHide.classList.add('hidden');
                }
            })
    })
})