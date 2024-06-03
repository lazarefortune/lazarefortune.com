export class Spotlight extends HTMLElement {
    constructor() {
        super();
        this.bindMethods();
        this.matchesItems = [];
    }

    bindMethods() {
        this.toggleSpotlight = this.toggleSpotlight.bind(this);
        this.handleInput = this.handleInput.bind(this);
        this.navigateSuggestions = this.navigateSuggestions.bind(this);
        this.blurSpotlight = this.blurSpotlight.bind(this);
        this.activateSpotlight = this.activateSpotlight.bind(this);
    }

    connectedCallback() {
        this.render();
        this.addEventListeners();
    }

    render() {
        this.classList.add('spotlight');
        this.innerHTML = `
            <div class="spotlight--bar">
                <div class="spotlight--search-container">
                    <div class="spotlight--search-icon">
                        <svg class="icon"
                                 viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="1.75"
                                 stroke-linecap="round"
                                 stroke-linejoin="round">
                                <use href="/icons/sprite.svg?#search"></use>
                        </svg>
                    </div>
                    <input type="text" placeholder="Où voulez-vous aller ?">
                </div>
                <div class="spotlight--container">
                    <ul class="spotlight--suggestions" hidden></ul>
                    <footer class="spotlight--footer">
                        <span>Entrée</span> pour sélectionner, <span>Échap</span> pour fermer, <span>↑</span> et <span>↓</span> pour naviguer
                    </footer>
                </div>
            </div>
        `;

        this.input = this.querySelector('input');
        this.suggestions = this.querySelector('.spotlight--suggestions');
        this.items = this.initializeItems();
    }

    initializeItems() {
        return Array.from(document.querySelectorAll(this.getAttribute('target')))
            .map(element => {
                const title = element.innerText.trim();
                if (!title) return null;
                const item = new SpotlightItem(title, element.getAttribute('href'));
                this.suggestions.appendChild(item.element);
                return item;
            })
            .filter(item => item !== null);
    }

    addEventListeners() {
        window.addEventListener('keydown', this.toggleSpotlight);
        this.input.addEventListener('input', this.handleInput);
        this.input.addEventListener('keydown', this.navigateSuggestions);
        const spotlight = document.querySelector('.spotlight');
        spotlight.addEventListener('click', (e) => {
            if (e.target.tagName !== 'INPUT') {
                this.blurSpotlight();
            }
        });

        const triggerElement = document.getElementById(this.getAttribute('trigger-id'));
        if (!triggerElement) return;
        const event = (triggerElement.tagName === 'INPUT') ? 'focus' : 'click';
        triggerElement.addEventListener(event, this.activateSpotlight);
    }

    disconnectedCallback() {
        window.removeEventListener('keydown', this.toggleSpotlight);
    }

    navigateSuggestions(e) {
        if (!['ArrowDown', 'ArrowUp', 'Enter'].includes(e.key)) return;

        e.preventDefault();

        if (this.matchesItems.length === 0) return;

        const currentIndex = this.matchesItems.indexOf(this.activeItem);
        const lastIndex = this.matchesItems.length - 1;

        if (e.key === 'ArrowDown') {
            const nextIndex = currentIndex < lastIndex ? currentIndex + 1 : 0;
            this.setActiveItem(nextIndex);
        } else if (e.key === 'ArrowUp') {
            const prevIndex = currentIndex > 0 ? currentIndex - 1 : lastIndex;
            this.setActiveItem(prevIndex);
        } else if (e.key === 'Enter' && this.activeItem) {
            this.activeItem.element.firstElementChild.click();
        }
    }

    handleInput() {
        const search = this.input.value.trim();
        if (search === '') {
            this.items.forEach(item => item.hide());
            this.matchesItems = [];
            this.suggestions.setAttribute('hidden', 'hidden');
            return;
        }

        let regexp = '^(.*)';
        for (const char of search) {
            regexp += `(${char})(.*)`;
        }
        regexp += '$';
        const regex = new RegExp(regexp, 'i');

        this.items.forEach(item => item.match(regex));
        this.matchesItems = this.items.filter(item => item.match(regex));

        if (this.matchesItems.length > 0) {
            this.suggestions.removeAttribute('hidden');
            this.setActiveItem(0);
        } else {
            this.suggestions.setAttribute('hidden', 'hidden');
        }
    }

    showSuggestions() {
        this.suggestions.removeAttribute('hidden');
        this.setActiveItem(0);
    }

    resetSuggestions() {
        this.items.forEach(item => item.hide());
        this.matchesItems = [];
        this.suggestions.setAttribute('hidden', 'hidden');
    }

    setActiveItem(index) {
        if (this.matchesItems.length === 0) return;
        if (this.activeItem) this.activeItem.unselect();
        this.activeItem = this.matchesItems[index];
        this.activeItem.select();
    }

    toggleSpotlight(e) {
        if (e.key === 'k' && (e.ctrlKey || e.metaKey)) {
            e.preventDefault();
            this.classList.toggle('active');
            this.input.value = '';
            this.input.focus();
        } else if (e.key === 'Escape' && document.activeElement === this.input) {
            e.preventDefault();
            this.blurSpotlight();
        }
    }

    activateSpotlight() {
        this.classList.add('active');
        this.input.value = '';
        this.input.focus();
    }

    blurSpotlight() {
        this.classList.remove('active');
        this.resetSuggestions();
    }
}

class SpotlightItem {
    constructor(title, href) {
        this.element = document.createElement('li');
        this.element.innerHTML = `<a href="${href}">${title}</a>`;
        this.element.setAttribute('hidden', 'hidden');
        this.title = title;
    }

    match(regex) {
        const matches = this.title.match(regex);
        if (!matches) {
            this.hide();
            return false;
        }
        this.element.firstElementChild.innerHTML = this.highlightMatches(matches);
        this.element.removeAttribute('hidden');
        return true;
    }

    highlightMatches(matches) {
        return matches.reduce((acc, match, index) => {
            if (index === 0) return acc;
            return acc + (index % 2 === 0 ? `<mark>${match}</mark>` : match);
        }, '');
    }

    hide() {
        this.element.setAttribute('hidden', 'hidden');
    }

    select() {
        this.element.classList.add('active');
    }

    unselect() {
        this.element.classList.remove('active');
    }
}