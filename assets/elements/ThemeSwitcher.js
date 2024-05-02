import { cookie } from "../functions/cookie";

export class ThemeSwitcher extends HTMLElement {
    connectedCallback() {
        this.classList.add('theme-switcher');
        this.innerHTML = `
                <input type="checkbox" id="theme-switcher" class="theme-switcher__input" aria-label="Changer de thème">
                <label for="theme-switcher" class="theme-switcher__label">
                    <svg class="icon icon-moon" 
                        viewBox="0 0 24 24"
                            width="17"
                            height="17"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="1.75"
                         stroke-linecap="round"
                         stroke-linejoin="round"
                    >
                        <use href="/icons/sprite.svg?#moon"></use>
                    </svg>
                    <svg class="icon icon-sun" 
                         width="17"
                         height="17"
                         viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="1.75"
                         stroke-linecap="round"
                         stroke-linejoin="round"    
                    >
                        <use href="/icons/sprite.svg?#sun"></use>
                    </svg>
                </label>
        `;

        const input = this.querySelector('.theme-switcher__input');
        input.addEventListener('change', e => {
            const themeChoose = e.currentTarget.checked ? 'dark' : 'light';
            document.documentElement.classList.toggle('dark', themeChoose === 'dark');
            cookie('theme', themeChoose, { expires: 7 });
        });

        const savedTheme = cookie('theme');
        if (savedTheme === undefined || savedTheme === null) {
            input.checked = window.matchMedia('(prefers-color-scheme: dark)').matches;
        } else {
            input.checked = savedTheme === 'dark';
            document.documentElement.classList.toggle('dark', savedTheme === 'dark');
        }
    }
}
