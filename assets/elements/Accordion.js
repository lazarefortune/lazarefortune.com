export class AccordionGroup extends HTMLElement {
    constructor() {
        super();
        this.attachShadow({mode: 'open'});
        this.shadowRoot.innerHTML = `
            <style>
                :host {
                    display: block;
                }
                details {
                    border: 1px solid #d1d5db;
                    border-radius: 0.25rem;
                    margin-bottom: 0.5rem;
                    padding: 0.5rem;
                    background-color: #f3f4f6;
                }
                summary {
                    cursor: pointer;
                    font-weight: 600;
                    list-style: none;
                }
                summary::-webkit-details-marker {
                    display: none;
                }
                p {
                    padding: 0.5rem;
                    border-top: 1px solid #d1d5db; // Gris-300 de Tailwind
                    transition: max-height 0.2s ease-out;
                    overflow: hidden;
                    max-height: 0;
                }
                details[open] p {
                    max-height: 100px;
                }
            </style>
            <slot></slot>
        `;

        this.addEventListener('click', e => {
            if (this.hasAttribute('data-linked')) {
                if (e.target.tagName.toLowerCase() === 'summary') {
                    const details = Array.from(this.querySelectorAll('details'));
                    details.forEach((detail) => {
                        if (detail !== e.target.parentNode) {
                            detail.removeAttribute('open');
                        }
                    });
                }
            }
        });
    }
}

customElements.define('accordion-group', AccordionGroup);
