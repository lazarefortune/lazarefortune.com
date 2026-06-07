const STORAGE_KEY = 'theme';

export function getPreferredTheme() {
    const stored = localStorage.getItem(STORAGE_KEY);

    if (stored === 'light' || stored === 'dark') {
        return stored;
    }

    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
}

export function applyTheme(theme) {
    const resolvedTheme = theme === 'dark' ? 'dark' : 'light';

    document.documentElement.setAttribute('data-theme', resolvedTheme);
    localStorage.setItem(STORAGE_KEY, resolvedTheme);

    const metaThemeColor = document.querySelector('meta[name="theme-color"]');
    if (metaThemeColor instanceof HTMLMetaElement) {
        metaThemeColor.content = resolvedTheme === 'dark' ? '#171933' : '#3076e0';
    }

    syncThemeToggleButtons(resolvedTheme);
}

function syncThemeToggleButtons(theme) {
    const isDark = theme === 'dark';

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        if (!(button instanceof HTMLButtonElement)) {
            return;
        }

        button.setAttribute('aria-pressed', isDark ? 'true' : 'false');
        button.setAttribute('aria-label', isDark ? 'Activer le mode clair' : 'Activer le mode sombre');

        button.querySelectorAll('[data-theme-icon]').forEach((icon) => {
            if (!(icon instanceof HTMLElement)) {
                return;
            }

            const iconName = icon.getAttribute('data-theme-icon');
            const visible = (iconName === 'sun' && isDark) || (iconName === 'moon' && !isDark);
            icon.classList.toggle('hidden', !visible);
        });
    });
}

function initThemeToggle() {
    syncThemeToggleButtons(document.documentElement.getAttribute('data-theme') ?? 'light');

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        if (!(button instanceof HTMLButtonElement)) {
            return;
        }

        button.addEventListener('click', () => {
            const currentTheme = document.documentElement.getAttribute('data-theme') ?? 'light';
            applyTheme(currentTheme === 'dark' ? 'light' : 'dark');
        });
    });
}

document.addEventListener('DOMContentLoaded', initThemeToggle);
