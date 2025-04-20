/**
 * @param {object} obj
 * @return {URLSearchParams}
 */
export function objToSearchParams (obj) {
    if (obj === undefined || obj === null) {
        return new URLSearchParams()
    }
    const params = new URLSearchParams()
    Object.keys(obj).forEach(k => {
        params.append(k, obj[k])
    })
    return params
}

/**
 * Redirect to a specific url
 */
export function redirect(url) {
    return new Promise((resolve) => {
        window.location.href = url;
        // Optionnel on attend que la page se charge
        window.addEventListener('load', () => {
            resolve();
        });
    });
}
