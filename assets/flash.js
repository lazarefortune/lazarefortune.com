const AUTO_DISMISS_MS = 5000;

const ALERT_ICONS = {
    info: '<path d="M12 16v-4"/><path d="M12 8h.01"/><circle cx="12" cy="12" r="10"/>',
    success: '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/>',
    warning: '<path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"/><path d="M12 9v4"/><path d="M12 17h.01"/>',
    danger: '<circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/>',
};

function buildAlertIcon(variant) {
    const paths = ALERT_ICONS[variant] ?? ALERT_ICONS.info;

    return `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide inline-block shrink-0" aria-hidden="true">${paths}</svg>`;
}

function buildDismissIcon() {
    return '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide inline-block shrink-0" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>';
}

function removeFlashItem(item) {
    if (!(item instanceof HTMLElement)) {
        return;
    }

    item.remove();

    const container = item.closest('[data-flash-messages], [data-toast-stack]');
    if (!(container instanceof HTMLElement)) {
        return;
    }

    if (container.querySelector('[data-flash-item]') === null) {
        container.remove();
    }
}

function dismissFlashItem(item) {
    item.classList.add('ds-flash-item--out');

    window.setTimeout(() => {
        removeFlashItem(item);
    }, 250);
}

function bindDismissButton(button) {
    if (!(button instanceof HTMLButtonElement) || button.dataset.flashBound === 'true') {
        return;
    }

    button.dataset.flashBound = 'true';
    button.addEventListener('click', () => {
        const item = button.closest('[data-flash-item]');
        if (item instanceof HTMLElement) {
            dismissFlashItem(item);
        }
    });
}

function bindAutoDismiss(item) {
    if (!(item instanceof HTMLElement) || item.dataset.autoDismissBound === 'true') {
        return;
    }

    if (item.getAttribute('data-flash-auto-dismiss') !== 'true') {
        return;
    }

    item.dataset.autoDismissBound = 'true';

    let timeoutId = null;
    let remaining = AUTO_DISMISS_MS;
    let startedAt = null;

    const clearTimer = () => {
        if (timeoutId === null) {
            return;
        }

        window.clearTimeout(timeoutId);
        timeoutId = null;
    };

    const startTimer = () => {
        startedAt = Date.now();
        clearTimer();
        timeoutId = window.setTimeout(() => {
            dismissFlashItem(item);
        }, remaining);
    };

    const pauseTimer = () => {
        if (timeoutId === null || startedAt === null) {
            return;
        }

        remaining -= Date.now() - startedAt;
        if (remaining <= 0) {
            dismissFlashItem(item);

            return;
        }

        clearTimer();
    };

    item.addEventListener('mouseenter', pauseTimer);
    item.addEventListener('mouseleave', () => {
        if (timeoutId === null) {
            startTimer();
        }
    });
    item.addEventListener('focusin', pauseTimer);
    item.addEventListener('focusout', (event) => {
        if (event.relatedTarget instanceof Node && item.contains(event.relatedTarget)) {
            return;
        }

        if (timeoutId === null) {
            startTimer();
        }
    });

    startTimer();
}

function collectFlashItems(root) {
    if (!(root instanceof HTMLElement)) {
        return [];
    }

    const items = [...root.querySelectorAll('[data-flash-item]')];
    if (root.matches('[data-flash-item]')) {
        items.unshift(root);
    }

    return items;
}

function initFlashItems(root) {
    const scope = root instanceof HTMLElement ? root : document;

    scope.querySelectorAll('[data-flash-dismiss]').forEach(bindDismissButton);
    collectFlashItems(scope).forEach(bindAutoDismiss);
}

function getToastStack() {
    let stack = document.querySelector('[data-toast-stack]');
    if (stack instanceof HTMLElement) {
        return stack;
    }

    stack = document.createElement('div');
    stack.className = 'ds-toast-stack';
    stack.setAttribute('data-toast-stack', '');
    stack.setAttribute('aria-live', 'polite');
    stack.setAttribute('aria-label', 'Toasts');
    document.body.appendChild(stack);

    return stack;
}

function showToast(message, variant, autoDismiss) {
    const stack = getToastStack();
    const item = document.createElement('div');
    const safeVariant = ['success', 'info', 'warning', 'danger'].includes(variant) ? variant : 'info';

    item.className = `ds-alert ds-alert-${safeVariant} ds-toast ds-flash-item`;
    if (autoDismiss) {
        item.classList.add('ds-alert--timed');
        item.style.setProperty('--ds-alert-duration', `${AUTO_DISMISS_MS}ms`);
    }

    item.setAttribute('role', safeVariant === 'danger' ? 'alert' : 'status');
    item.setAttribute('data-flash-item', '');
    item.setAttribute('data-flash-type', safeVariant);

    if (autoDismiss) {
        item.setAttribute('data-flash-auto-dismiss', 'true');
    }

    item.innerHTML = `
        <span class="ds-alert__icon mt-0.5 shrink-0" aria-hidden="true">${buildAlertIcon(safeVariant)}</span>
        <div class="min-w-0 flex-1"><p></p></div>
        <button type="button" class="ds-flash-dismiss shrink-0" data-flash-dismiss aria-label="Fermer la notification">${buildDismissIcon()}</button>
        ${autoDismiss ? '<span class="ds-alert-progress" data-flash-progress aria-hidden="true"></span>' : ''}
    `;

    const paragraph = item.querySelector('p');
    if (paragraph instanceof HTMLParagraphElement) {
        paragraph.textContent = message;
    }

    stack.appendChild(item);
    initFlashItems(item);
}

function initFlashDismiss() {
    initFlashItems(document);
}

document.addEventListener('DOMContentLoaded', initFlashDismiss);

window.dsShowToast = showToast;
