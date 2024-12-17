// Fonction helper pour recalculer le offsetTop
function offsetTop(el, parent) {
    let top = 0;
    let element = el;
    while (element && element !== parent) {
        top += element.offsetTop;
        element = element.offsetParent;
    }
    return top;
}

function reAutoScroll() {
    const drawer = document.querySelector('.chapters-wrapper');
    if (!drawer) return;
    const targetSelector = drawer.dataset.to;
    if (targetSelector) {
        const target = document.querySelector(targetSelector);
        if (target) {
            drawer.scrollTo(
                0,
                offsetTop(target, drawer) - target.getBoundingClientRect().height / 2 - drawer.getBoundingClientRect().height / 2
            );
            target.classList.add("is-selected");
        }
    }
}

(function () {
    const toggle = document.querySelector('.chapters-toggle')
    const container = document.querySelector('.is-chapter')
    if (toggle) {
        toggle.addEventListener('click',function () {
            container.classList.toggle('is-folded')
        })
    }
})()

document.addEventListener('DOMContentLoaded', () => {
    // Mobile drawer
    const drawer = document.querySelector('.chapters-wrapper');
    const mobileBtn = document.getElementById('chapters-mobile-button');
    const mobileClose = document.querySelector('.chapters-mobile-close');
    const backdrop = document.querySelector('.chapters-backdrop');

    if (mobileBtn && drawer && backdrop) {
        mobileBtn.addEventListener('click', () => {
            drawer.style.transform = 'translateY(0)';
            drawer.classList.toggle('open');
            mobileBtn.classList.toggle('open');

            backdrop.classList.add('open');
            document.body.style.overflow = 'hidden';
            // relancer auto-scroll
            reAutoScroll();
        });

        backdrop.addEventListener('click', (e) => {
            // ferme si clic sur le backdrop
            drawer.classList.remove('open');
            backdrop.classList.remove('open');
            drawer.style.transform = 'translateY(100%)';
            document.body.style.overflow = '';
        });
    }

    if (mobileClose && drawer) {
        mobileClose.addEventListener('click', () => {
            drawer.classList.remove('open');
            backdrop.classList.remove('open');
            drawer.style.transform = 'translateY(100%)';
            document.body.style.overflow = '';
        });
    }


    let startY = 0, currentY = 0, dragging = false;

    drawer.addEventListener('touchstart', (e) => {
        const rec = drawer.getBoundingClientRect();
        const handleX = rec.left  + rec.width/2 - 20;
        const handleY =  rec.top + 2;
        const handleWidth = 40;
        const handleHeight = 20;

        const touch  = e.touches[0];
        const x = touch.clientX;
        const y = touch.clientY;

        if (x >= handleX && x <= handleX + handleWidth && y >= handleY && y <= handleY + handleHeight) {
            dragging = true;
            startY = y;
            drawer.style.transition = 'none';
            console.log('start', startY, "dragging", dragging);
        }
    })

    drawer.addEventListener('touchmove', (e) => {
        if (!dragging) return;
        const touch = e.touches[0];
        currentY = touch.clientY;
        let deltaY = currentY - startY;
        if (deltaY > 0) {
            deltaY = Math.min(deltaY, 200);
            drawer.style.transform = `translateY(${deltaY}px)`;
        }
    }, {passive:true});

    drawer.addEventListener('touchend', () => {
        if (!dragging) return;
        dragging = false;
        let deltaY = currentY - startY;
        drawer.style.transition = 'transform 0.3s ease';
        if (deltaY > 100) {
            drawer.classList.remove('open');
            backdrop.classList.remove('open');
            document.body.style.overflow = '';
            drawer.style.transform = `translateY(100%)`;
        } else {
            drawer.style.transform = `translateY(0)`;
        }
    });
})