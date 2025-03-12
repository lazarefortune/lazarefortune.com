import React, { useEffect, useRef, useState } from "react"
import { useAsyncEffect, useClickOutside, useNotificationCount, usePrepend } from '../functions/hooks.js'
import { isAuthenticated, lastNotificationRead } from '../functions/auth.js'
import { loadNotifications, onNotification } from '../api/notifications.js'
import { jsonFetch } from '../functions/api.js'
import { SlideIn } from "./SlideIn";
import { Bell, Loader, X } from "lucide-react";

const OPEN = 0
const CLOSE = 1
let notificationsCache = []
let notificationsLoaded = false

function countUnread(notifications, notificationReadAt) {
    return notifications.filter(({ createdAt }) => {
        return notificationReadAt < Date.parse(createdAt)
    }).length
}

/**
 * Composant principal des notifications
 */
export function Notifications() {
    // Hooks
    const [state, setState] = useState(CLOSE)
    const [notifications, pushNotification] = usePrepend(notificationsCache)
    const [notificationReadAt, setNotificationReadAt] = useState(lastNotificationRead())
    const [loading, setLoading] = useState(!notificationsLoaded)
    const unreadCount = countUnread(notifications, notificationReadAt)
    useNotificationCount(unreadCount)
    notificationsCache = notifications

    // Référence pour le conteneur global (bouton + popup)
    const containerRef = useRef(null)
    // Appliquer le hook useClickOutside sur ce conteneur pour fermer la popup
    useClickOutside(containerRef, () => setState(CLOSE))

    // Méthodes
    const openMenu = e => {
        e.preventDefault()
        setState(OPEN)
        if (unreadCount > 0) {
            setNotificationReadAt(new Date())
            jsonFetch('/api/notifications/read', { method: 'post' }).catch(console.error)
        }
    }
    const closeMenu = () => setState(CLOSE)

    // Charger les notifications au premier affichage
    useAsyncEffect(async () => {
        if (isAuthenticated() && notificationsLoaded === false) {
            await loadNotifications()
            setLoading(false)
            notificationsLoaded = true
        }
    }, [])

    // Écouter les notifications en temps réel (via SSE)
    useEffect(() => onNotification('notification', pushNotification), [pushNotification])

    // Écouter quand les notifications sont marquées comme lues
    useEffect(() => {
        return onNotification('markAsRead', () => {
            setNotificationReadAt(new Date())
        })
    }, [])

    // Ne rien afficher si l'utilisateur n'est pas authentifié
    if (!isAuthenticated()) return null

    return (
        <div ref={containerRef} className="relative flex items-center">
            <button onClick={openMenu} aria-label="Voir les notifications">
                <Bell size={24} />
            </button>
            <Badge count={unreadCount} />
            <SlideIn className="notifications" show={state === OPEN}>
                <Popup
                    loading={loading}
                    onClickOutside={closeMenu}
                    notifications={notifications}
                    notificationReadAt={notificationReadAt}
                />
            </SlideIn>
        </div>
    )
}

/**
 * Badge contenant le nombre de notifications
 */
function Badge({ count }) {
    if (count <= 0) {
        return null
    }
    return count < 10 ? <span className="notification-badge">{count}</span> : <span className="notification-badge">9+</span>
}

/**
 * Popup contenant les notifications
 */
function Popup({ notifications = [], onClickOutside = () => {}, loading = false, notificationReadAt, ...props }) {
    const ref = useRef()
    // commenter ou supprimer la ligne suivante si on ne souhaite pas appliquer de double fermeture.
    // useClickOutside(ref, onClickOutside)

    return (
        <div ref={ref} {...props}>
            <div className="notifications_title">
                <h3 className="h5">Notifications</h3>
                <button aria-label="Fermer" onClick={onClickOutside}>
                    <X size={20} />
                </button>
            </div>
            <div className="notifications_body">
                {loading && <Loader size={24} className="spinner" />}
                {notifications.length === 0 ? (
                    <span className="notifications_body-empty">Vous n'avez aucune notification :(</span>
                ) : (
                    notifications.map((n, index) => (
                        <Notification key={`notification-${index}`} notificationReadAt={notificationReadAt} {...n} />
                    ))
                )}
            </div>
            <a href="/notifications" className="notifications_footer">
                Voir toutes les notifications
            </a>
        </div>
    )
}

/**
 * Représente une notification
 */
function Notification({ url, message, createdAt, notificationReadAt }) {
    const isRead = notificationReadAt > new Date(createdAt)
    const className = `notifications_item ${isRead ? 'is-read' : ''}`
    const time = Date.parse(createdAt) / 1000
    return (
        <a href={url} className={className}>
            <div className="relative">
                <div dangerouslySetInnerHTML={{ __html: message }} />
                {!isRead && (
                    <span className="absolute top-0 -right-2 min-w-2 min-h-2 bg-red-500 rounded-full ml-2"></span>
                )}
            </div>
            <small className="text-muted">
                <time-ago time={time} />
            </small>
        </a>
    )
}
