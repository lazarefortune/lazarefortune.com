// Calcule le décalage vertical d'un élément par rapport à un parent
function offsetTop(el, parent) {
    let top = 0;
    let element = el;
    while (element && element !== parent) {
        top += element.offsetTop;
        element = element.offsetParent;
    }
    return top;
}

// Réinitialise le scroll du drawer sur la cible spécifiée
function reAutoScroll() {
    const drawer = document.querySelector('.chapters-wrapper');
    if (!drawer) return;

    const targetSelector = drawer.dataset.to;
    if (!targetSelector) return;

    const target = document.querySelector(targetSelector);
    if (!target) return;

    const drawerRect = drawer.getBoundingClientRect();
    const scrollPos = offsetTop(target, drawer) - drawerRect.height / 2 - drawerRect.height / 2;
    drawer.scrollTo(0, scrollPos);
    target.classList.add("is-selected");
}

// Initialise le toggle desktop
(function initializeDesktopToggle() {
    const toggle = document.querySelector('.chapters-toggle');
    const container = document.querySelector('.is-chapter');
    if (toggle && container) {
        toggle.addEventListener('click', () => {
            container.classList.toggle('is-folded');
        });
    }
})();

document.addEventListener('DOMContentLoaded', () => {
    const drawer = document.querySelector('.chapters-wrapper');
    const mobileBtn = document.getElementById('chapters-mobile-button');
    const mobileClose = document.querySelector('.chapters-mobile-close');
    const backdrop = document.querySelector('.chapters-backdrop');

    let startY = 0;
    let currentY = 0;
    let dragging = false;

    // Ouvre le drawer mobile
    function openDrawer() {
        drawer.style.transform = 'translateY(0)';
        drawer.classList.add('open');
        mobileBtn?.classList.add('open');
        backdrop?.classList.add('open');
        document.body.style.overflow = 'hidden';
        reAutoScroll();
    }

    // Ferme le drawer mobile
    function closeDrawer() {
        drawer.classList.remove('open');
        mobileBtn?.classList.remove('open');
        backdrop?.classList.remove('open');
        drawer.style.transform = 'translateY(100%)';
        document.body.style.overflow = '';
    }

    // Gestion du clic sur le bouton mobile (ouverture du drawer)
    if (mobileBtn && drawer && backdrop) {
        mobileBtn.addEventListener('click', () => {
            if (drawer.classList.contains('open')) {
                closeDrawer();
            } else {
                openDrawer();
            }
        });

        backdrop.addEventListener('click', () => {
            closeDrawer();
        });
    }

    // Gestion du clic sur le bouton fermer mobile
    if (mobileClose && drawer) {
        mobileClose.addEventListener('click', () => {
            closeDrawer();
        });
    }

    // On cible le header
    const drawerHeader = document.querySelector('.chapters-wrapper__header');
    if (drawerHeader) {
        drawerHeader.addEventListener('touchstart', (e) => {
            dragging = true;
            startY = e.touches[0].clientY;
            drawer.style.transition = 'none';
        }, { passive: true });

        drawerHeader.addEventListener('touchmove', (e) => {
            if (!dragging) return;
            currentY = e.touches[0].clientY;
            let deltaY = currentY - startY;
            // On limite le drawer à 200px max
            if (deltaY > 0) {
                deltaY = Math.min(deltaY, 200);
                drawer.style.transform = `translateY(${deltaY}px)`;
            }
        }, { passive: true });

        drawerHeader.addEventListener('touchend', () => {
            if (!dragging) return;
            dragging = false;
            drawer.style.transition = 'transform 0.3s ease';

            let deltaY = currentY - startY;
            if (deltaY > 100) {
                closeDrawer();
            } else {
                drawer.style.transform = 'translateY(0)';
            }
        }, { passive: true });
    }
});
