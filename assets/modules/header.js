import { throttle } from '../functions/timers.js'

const headerElement = document.querySelector('.header');
const scrollThreshold = 20;
let scrollOffset = headerElement ? headerElement.offsetHeight : 0;
let previousScrollTop = 0;
let isScrolling = false;

// Enum pour définir les états du header
const HeaderState = {
    FIXED: 0,
    HIDDEN: 1,
    DEFAULT: 2
};

let currentHeaderState = HeaderState.DEFAULT;

/**
 * Modifie l'état du header en fonction de l'état passé en paramètre
 * @param {number} newState - Nouvel état du header
 */
function setHeaderState(newState) {
    if (newState === currentHeaderState) return;

    if (newState === HeaderState.HIDDEN) {
        headerElement.classList.add('is-hidden')
    } else if (newState === HeaderState.FIXED) {
        headerElement.classList.remove('is-hidden')
        headerElement.classList.add('is-fixed')
    } else if (newState === HeaderState.DEFAULT) {
        headerElement.classList.remove('is-hidden')
        headerElement.classList.remove('is-fixed')
    }

    currentHeaderState = newState;
}

/**
 * Fonction appelée pour gérer le masquage automatique du header lors du défilement
 */
function autoHideHeader() {
    if (!headerElement) {
        return;
    }

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
 * Attache l'événement de défilement pour gérer le comportement du header.
 * Utilise `throttle` pour optimiser les performances.
 * @return {function(): void} Fonction de nettoyage pour retirer l'écouteur d'événements
 */
export function registerHeaderBehavior() {
    const scrollHandler = throttle(() => {
        if (!isScrolling) {
            isScrolling = true;
            window.requestAnimationFrame(autoHideHeader);
        }
    },100);

    window.addEventListener('scroll',scrollHandler);

    return () => {
        window.removeEventListener('scroll',scrollHandler);
    };
}
