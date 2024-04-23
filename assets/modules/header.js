const headerHamburger = document.querySelector('#header-hamburger');
const headerMenu = document.querySelector('#header');

if (headerHamburger && headerMenu) {
    headerHamburger.addEventListener('click', () => {
        headerMenu.classList.toggle('is-open');
    });
}
