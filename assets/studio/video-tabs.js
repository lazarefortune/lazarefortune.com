const DEFAULT_TAB = 'content';
const VALID_TABS = new Set(['content', 'source', 'classification', 'publication']);
const TAB_ALIASES = {
    video: 'source',
};

function resolveTabId(hash) {
    if (VALID_TABS.has(hash)) {
        return hash;
    }

    return TAB_ALIASES[hash] ?? DEFAULT_TAB;
}

function getTabFromHash() {
    const hash = window.location.hash.replace('#', '');

    return resolveTabId(hash);
}

function activateTab(tabId) {
    const root = document.querySelector('[data-studio-video-tabs]');
    if (!(root instanceof HTMLElement)) {
        return;
    }

    root.querySelectorAll('[data-studio-tab]').forEach((tab) => {
        if (!(tab instanceof HTMLElement)) {
            return;
        }

        const isActive = tab.getAttribute('data-studio-tab') === tabId;
        tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
        tab.classList.toggle('ds-tabs__tab--active', isActive);
    });

    root.querySelectorAll('[data-studio-tab-panel]').forEach((panel) => {
        if (!(panel instanceof HTMLElement)) {
            return;
        }

        const isActive = panel.getAttribute('data-studio-tab-panel') === tabId;
        panel.hidden = !isActive;
    });
}

function syncHash(tabId) {
    const nextHash = `#${tabId}`;
    if (window.location.hash === nextHash) {
        return;
    }

    window.history.replaceState(null, '', `${window.location.pathname}${window.location.search}${nextHash}`);
}

function initVideoTabs() {
    const root = document.querySelector('[data-studio-video-tabs]');
    if (!(root instanceof HTMLElement)) {
        return;
    }

    const initialTab = getTabFromHash();
    syncHash(initialTab);
    activateTab(initialTab);

    root.querySelectorAll('[data-studio-tab]').forEach((tab) => {
        if (!(tab instanceof HTMLAnchorElement)) {
            return;
        }

        tab.addEventListener('click', (event) => {
            const tabId = tab.getAttribute('data-studio-tab');
            if (tabId === null || !VALID_TABS.has(tabId)) {
                return;
            }

            event.preventDefault();
            syncHash(tabId);
            activateTab(tabId);
        });
    });

    window.addEventListener('hashchange', () => {
        activateTab(getTabFromHash());
    });
}

document.addEventListener('DOMContentLoaded', initVideoTabs);
