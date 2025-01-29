import { throttle } from '../../functions/timers.js';

const headerIdentifier = 'app-header';
const headerElement = document.getElementById(headerIdentifier);
const scrollThreshold = 20;
let scrollOffset = headerElement ? headerElement.offsetHeight : 0;
let previousScrollTop = 0;
let isScrolling = false;

// Enum pour définir les états du header
const HeaderState = {
    FIXED: 0,
    HIDDEN: 1,
    DEFAULT: 2,
};

let currentHeaderState = HeaderState.DEFAULT;

/**
 * Modifie l'état du header en fonction du nouvel état
 * @param {number} newState - Nouvel état du header
 */
function setHeaderState(newState) {
    if (newState === currentHeaderState) return;

    headerElement.classList.toggle('is-hidden', newState === HeaderState.HIDDEN);
    headerElement.classList.toggle('is-fixed', newState === HeaderState.FIXED);

    if (newState === HeaderState.DEFAULT) {
        headerElement.classList.remove('is-hidden', 'is-fixed');
    }

    currentHeaderState = newState;
}

/**
 * Gère le masquage automatique du header lors du défilement
 */
function autoHideHeader() {
    if (!headerElement) return;

    const currentScrollTop = document.documentElement.scrollTop;

    if (currentScrollTop > scrollOffset) {
        if (currentScrollTop - previousScrollTop > scrollThreshold) {
            setHeaderState(HeaderState.HIDDEN);
        } else if (previousScrollTop - currentScrollTop > scrollThreshold) {
            setHeaderState(HeaderState.FIXED);
        }
    } else {
        setHeaderState(HeaderState.DEFAULT);
    }

    previousScrollTop = currentScrollTop;
    isScrolling = false;
}

/**
 * Attache l'événement de défilement pour gérer le comportement du header
 */
export function registerHeaderBehavior() {
    const scrollHandler = throttle(() => {
        if (!isScrolling) {
            isScrolling = true;
            window.requestAnimationFrame(autoHideHeader);
        }
    }, 100);

    window.addEventListener('scroll', scrollHandler);

    return () => {
        window.removeEventListener('scroll', scrollHandler);
    };
}

/* --- Gestion du menu hamburger --- */
document.addEventListener('DOMContentLoaded', () => {
    const hamburger = document.getElementById('header-hamburger');
    const menu = document.getElementById('mobile-menu');

    if (!hamburger || !menu) return;

    hamburger.addEventListener('click', () => {
        menu.classList.toggle('active');
        hamburger.classList.toggle('is-open');
        document.body.style.overflow = menu.classList.contains('active') ? 'hidden' : 'auto';
    });

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
});

