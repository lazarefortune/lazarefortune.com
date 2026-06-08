const ID_PATTERN = /^[a-zA-Z0-9_-]{11}$/;

function isValidId(candidate) {
    return ID_PATTERN.test(candidate);
}

function isYoutubeHost(host) {
    return host === 'youtube.com'
        || host === 'www.youtube.com'
        || host.endsWith('.youtube.com');
}

function extractFromUrl(url) {
    let parsed;

    try {
        parsed = new URL(url);
    } catch {
        return null;
    }

    const host = parsed.hostname.toLowerCase();
    const path = parsed.pathname.replace(/^\/+|\/+$/g, '');

    if (host === 'youtu.be') {
        const candidate = path.split('/')[0] ?? '';

        return isValidId(candidate) ? candidate : null;
    }

    if (isYoutubeHost(host)) {
        if (path.startsWith('watch')) {
            const candidate = parsed.searchParams.get('v') ?? '';

            return isValidId(candidate) ? candidate : null;
        }

        if (path.startsWith('shorts/')) {
            const candidate = path.slice('shorts/'.length);

            return isValidId(candidate) ? candidate : null;
        }
    }

    return null;
}

export function extractYoutubeId(input) {
    const value = input.trim();
    if (value === '') {
        return null;
    }

    if (isValidId(value)) {
        return value;
    }

    if (!/^https?:\/\//i.test(value)) {
        return null;
    }

    return extractFromUrl(value);
}
