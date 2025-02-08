/**
 * Vérifie si l'utilisateur est admin
 *
 * @return {boolean}
 */
export function isAdmin () {
    return window.website.ADMIN === true
}

/**
 * Vérifie si l'utilisateur est connecté
 *
 * @return {boolean}
 */
export function isAuthenticated () {
    return window.website.USER !== null
}


export function lastNotificationRead () {
    return window.website.NOTIFICATION
}

/**
 * Renvoie l'id de l'utilisateur
 *
 * @return {number|null}
 */
export function getUserId () {
    return window.website.USER
}

/**
 * Vérifie si l'utilisateur connecté correspond à l'id passé en paramètre
 *
 * @param {number} userId
 * @return {boolean}
 */
export function canManage (userId) {
    if (isAdmin()) {
        return true
    }
    if (!userId) {
        return false
    }
    return window.website.USER === parseInt(userId, 10)
}