import { offsetTop } from "../functions/dom.js";

/**
 * Exemple d'utilisation :
 * ```html
 * <div is="auto-scroll" data-to="[checked]"></div>
 * ```
 */
export class AutoScroll extends HTMLDivElement {
    connectedCallback() {
        const target = document.querySelector(this.dataset.to);
        if(target) {
            this.scrollTo(
                0,
                offsetTop(target, this) - target.getBoundingClientRect().height / 2 - this.getBoundingClientRect().height / 2
            );
            target.classList.add("is-selected");
        }
    }
}
