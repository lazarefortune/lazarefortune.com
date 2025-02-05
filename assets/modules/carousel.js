document.addEventListener('DOMContentLoaded', () => {

    // Sélectionne tous les wrappers ayant data-carousel
    const carousels = document.querySelectorAll('[data-carousel]');

    if (!carousels.length) return; // si pas de carousel

    carousels.forEach(wrapper => initCarousel(wrapper));

    function initCarousel(wrapper) {
        // On cherche à l'intérieur du wrapper
        const container = wrapper.querySelector('.carousel-container');
        const btnPrev = wrapper.querySelector('.carousel-button.prev');
        const btnNext = wrapper.querySelector('.carousel-button.next');
        const cards = [...container.querySelectorAll('.carousel-item')];

        if (!container || !cards.length) return; // si pas de container ou de cartes

        let positions = [];

        function updatePositions() {
            positions = cards.map(card => card.offsetLeft);
        }
        function isAtStart() {
            return container.scrollLeft <= 5;
        }
        function isAtEnd() {
            const maxScrollLeft = container.scrollWidth - container.clientWidth;
            return container.scrollLeft + 5 >= maxScrollLeft;
        }
        function getStep() {
            const w = window.innerWidth;
            if (w >= 1280) return 3;
            if (w >= 768) return 2;
            return 1;
        }
        function getCurrentIndex() {
            let currentIndex = 0;
            let minDist = Infinity;
            const left = container.scrollLeft;
            positions.forEach((pos, idx) => {
                const dist = Math.abs(pos - left);
                if (dist < minDist) {
                    minDist = dist;
                    currentIndex = idx;
                }
            });
            return currentIndex;
        }
        function scrollToCard(idx) {
            const target = positions[idx] ?? 0;
            container.scrollTo({ left: target, behavior: 'smooth' });
        }
        function updateButtons() {
            if (!btnPrev || !btnNext) return; // si pas de bouton
            if (isAtStart()) {
                btnPrev.style.opacity = '0';
                btnPrev.style.pointerEvents = 'none';
            } else {
                btnPrev.style.display = 'flex';
                btnPrev.style.opacity = '1';
                btnPrev.style.pointerEvents = 'auto';
            }
            if (isAtEnd()) {
                btnNext.style.opacity = '0';
                btnNext.style.pointerEvents = 'none';
            } else {
                btnNext.style.display = 'flex';
                btnNext.style.opacity = '1';
                btnNext.style.pointerEvents = 'auto';
            }
        }

        // Events
        btnNext?.addEventListener('click', () => {
            const current = getCurrentIndex();
            const step = getStep();
            const newIndex = Math.min(current + step, cards.length - 1);
            scrollToCard(newIndex);
        });
        btnPrev?.addEventListener('click', () => {
            const current = getCurrentIndex();
            const step = getStep();
            const newIndex = Math.max(current - step, 0);
            scrollToCard(newIndex);
        });
        container.addEventListener('scroll', updateButtons);
        window.addEventListener('resize', () => {
            updatePositions();
            updateButtons();
        });

        // Init
        updatePositions();
        updateButtons();
    }

});