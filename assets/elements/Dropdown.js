class DropdownButton extends HTMLElement {
    constructor() {
        super();
        this.attachShadow({mode: 'open'});
        this.shadowRoot.innerHTML = `
            <!-- Votre HTML ici -->
        `;

        this.button = this.shadowRoot.querySelector('.dropdown-button');
        this.content = this.shadowRoot.querySelector('.dropdown-content');

        this.toggleContent = this.toggleContent.bind(this);
        this.handleClickOutside = this.handleClickOutside.bind(this);
    }

    connectedCallback() {
        this.button.addEventListener('click', this.toggleContent);
        document.addEventListener('click', this.handleClickOutside);
    }

    disconnectedCallback() {
        this.button.removeEventListener('click', this.toggleContent);
        document.removeEventListener('click', this.handleClickOutside);
    }

    toggleContent() {
        this.content.classList.toggle('hidden');
    }

    handleClickOutside(event) {
        if (!this.shadowRoot.contains(event.target)) {
            this.content.classList.add('hidden');
        }
    }
}

export {DropdownButton};
