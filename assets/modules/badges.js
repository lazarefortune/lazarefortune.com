import { ApiError, HTTP_FORBIDDEN, HTTP_UNPROCESSABLE_ENTITY, jsonFetch } from '../functions/api.js'
import { strToDom } from '../functions/dom.js'
import { isAuthenticated } from '../functions/auth.js'
import { onNotification } from '../api/notifications.js'
import { isActiveWindow } from '../functions/window.js'
import { flash } from '../elements/Alert.js'

/**
 * Enregistre l'alerte de déblocage de badege
 */
let cleanBadgeListener
export function registerBadgeAlert () {
    if (cleanBadgeListener) {
        cleanBadgeListener()
    }
    cleanBadgeListener = onNotification('badge', data => {
        if (!isActiveWindow()) {
            return
        }
        document.body.append(
            strToDom(
                `<badge-unlock name="${data.name}" description="${data.description}" image="${data.image}" theme="${data.theme}"></badge-unlock>`
            )
        )
    })
}