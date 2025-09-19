import { cookie } from "../functions/cookie";

export class ThemeSwitcher extends HTMLElement {
    connectedCallback() {
        this.initMarkup();
        this.setupEventListeners();
        this.applyInitialTheme();
    }

    initMarkup() {
        this.classList.add('theme-switcher');
        this.innerHTML = `
            <input type="checkbox" id="theme-switcher" class="theme-switcher__input" aria-label="Changer de thème">
            <label for="theme-switcher" class="theme-switcher__label">
                <svg class="icon icon-moon" viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <use href="/icons/sprite.svg?#moon"></use>
                </svg>
                <svg class="icon icon-sun" viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <use href="/icons/sprite.svg?#sun"></use>
                </svg>
            </label>
        `;
    }

    setupEventListeners() {
        const input = this.querySelector('.theme-switcher__input');
        if (!input) {
            console.error('No input found in ThemeSwitcher');
            return;
        }

        input.addEventListener('change', this.handleThemeChange.bind(this));
    }

    handleThemeChange(event) {
        const themeChosen = event.currentTarget.checked ? 'dark' : 'light';
        document.documentElement.classList.toggle('dark', themeChosen === 'dark');
        cookie('theme', themeChosen, { expires: 7 });
    }

    applyInitialTheme() {
        const input = this.querySelector('.theme-switcher__input');
        if (!input) {
            return;
        }

        const savedTheme = cookie('theme');
        if (savedTheme === undefined || savedTheme === null) {
            input.checked = window.matchMedia('(prefers-color-scheme: dark)').matches;
        } else {
            input.checked = savedTheme === 'dark';
        }

        document.documentElement.classList.toggle('dark', input.checked);
    }
}
