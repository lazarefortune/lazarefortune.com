export function cookie(name, value = undefined, options = {}) {
    if (value === undefined) {
        return getCookie(name);
    }

    setCookie(name, value, options);
}

function getCookie(name) {
    const cookies = document.cookie.split(';');
    const cookiePair = cookies.find(cookie => cookie.trim().startsWith(`${name}=`));
    return cookiePair ? decodeURIComponent(cookiePair.split('=')[1]) : null;
}

function setCookie(name, value, options) {
    if (value === null) {
        value = '';
        options.expires = -365;
    }

    let cookieValue = encodeURIComponent(value);
    cookieValue += formatOptions(options);
    document.cookie = `${name}=${cookieValue}`;
}

function formatOptions(options) {
    let cookieOptions = '';

    if (options.expires) {
        const date = new Date();
        date.setDate(date.getDate() + options.expires);
        cookieOptions += `; expires=${date.toUTCString()}`;
    }

    cookieOptions += `; path=${options.path || '/'}`;

    if (options.domain) {
        cookieOptions += `; domain=${options.domain}`;
    }

    if (options.secure) {
        cookieOptions += '; secure';
    }

    return cookieOptions;
}
