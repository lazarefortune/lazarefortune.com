document.addEventListener('DOMContentLoaded', () => {
    const hamburger = document.getElementById('hamburger')
    if(!hamburger) return;

    hamburger.addEventListener('click', function() {
        let menu = document.querySelector('.mobile-menu');
        const hamburger = document.getElementById('hamburger');

        if (!menu || !hamburger) return;

        menu.classList.toggle('active');

        if (menu.classList.contains('active')) {
            document.body.style.overflow = 'hidden';
            hamburger.classList.add('is-open');
        } else {
            document.body.style.overflow = 'auto';
            hamburger.classList.remove('is-open');
        }
    });

})