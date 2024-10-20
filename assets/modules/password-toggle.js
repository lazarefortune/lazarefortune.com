document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.password-toggle-button').forEach(button => {
        button.addEventListener('click', (event) => {
            event.preventDefault();

            const passwordField = button.closest('.relative').querySelector('input');
            const iconEye = button.querySelector('.icon-eye');
            const iconEyeOff = button.querySelector('.icon-eye-off');

            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                iconEye.classList.add('hidden');
                iconEyeOff.classList.remove('hidden');
            } else {
                passwordField.type = 'password';
                iconEye.classList.remove('hidden');
                iconEyeOff.classList.add('hidden');
            }

            passwordField.focus();
        });
    });
});
