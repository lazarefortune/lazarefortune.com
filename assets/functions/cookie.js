export function cookie (name, value = undefined, options = {}) {
    if (value === undefined) {
        const cookies = document.cookie.split(';').map(cookie => cookie.split('='));
        console.log(cookies);
        return cookies.find(cookie => cookie[0].trim() === name)?.[1];
    }

    if (value === null) {
        value = '';
        options.expires = -365;
    }else{
        value = String(value);
    }

    if (options.expires) {
        const date = new Date();
        date.setDate(date.getDate() + options.expires);
        value += `; expires=${date.toUTCString()}`;
    }

    if (options.path) {
        value += `; path=${options.path}`;
    } else {
        value += '; path=/';
    }

    if (options.domain) {
        value += `; domain=${options.domain}`;
    }

    document.cookie = `${name}=${value}`;
}