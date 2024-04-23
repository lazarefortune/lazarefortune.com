export class Spotlight extends HTMLElement {
    constructor() {
        super();
        this.bindMethods();
    }

    bindMethods() {
        this.toggleSpotlight = this.toggleSpotlight.bind(this);
        this.handleInput = this.handleInput.bind(this);
        this.navigateSuggestions = this.navigateSuggestions.bind(this);
        this.blurSpotlight = this.blurSpotlight.bind(this);
    }

    connectedCallback() {
        this.render();
        this.addEventListeners();
    }

    render() {
        this.classList.add('spotlight');
        this.innerHTML = `
            <div class="spotlight__bar">
                <input type="text" placeholder="Où voulez-vous aller ?">
                <ul class="spotlight__suggestions" hidden></ul>
            </div>
        `;

        this.input = this.querySelector('input');
        this.suggestions = this.querySelector('.spotlight__suggestions');
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
        this.input.addEventListener('blur', this.blurSpotlight);
    }

    disconnectedCallback() {
        window.removeEventListener('keydown', this.toggleSpotlight);
    }

    navigateSuggestions(e) {
        if (!['ArrowDown', 'ArrowUp', 'Enter'].includes(e.key)) return;

        e.preventDefault();
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

        // Construction de la regex pour correspondre à chaque caractère de la recherche
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