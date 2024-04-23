import {slideUp} from '../functions/animation.js'

export class Alert extends HTMLElement {
    constructor({type, message} = {}) {
        super()
        if (type !== undefined) {
            this.type = type
        }
        if (this.type === 'error' || this.type === null) {
            this.type = 'danger'
        }
        this.message = message
        this.close = this.close.bind(this)
    }

    connectedCallback() {
        this.type = this.type || this.getAttribute('type')
        if (this.type === 'error' || !this.type) {
            this.type = 'danger'
        }
        const text = this.innerHTML
        const duration = this.getAttribute('duration')
        let progressBar = ''
        if (duration !== null) {
            progressBar = `<div class="alert-progress" style="animation-duration: ${duration}s">`
            window.setTimeout(this.close, duration * 1000)
        }
        this.classList.add('alert')
        this.classList.add(`alert-${this.type}`)
        this.innerHTML = `
        <svg class="icon icon-${this.icon}"
                 viewBox="0 0 24 24"
                 fill="none"
                 stroke="currentColor"
                 stroke-width="1.75"
                 stroke-linecap="round"
                 stroke-linejoin="round">
                <use href="/icons/sprite.svg?#${this.icon}"></use>
            </svg>
        <div>
          ${this.message || text}
        </div>
        <button class="alert-close">
          <svg class="icon"
                 viewBox="0 0 24 24"
                 fill="none"
                 stroke="currentColor"
                 stroke-width="1.75"
                 stroke-linecap="round"
                 stroke-linejoin="round">
                <use href="/icons/sprite.svg?#x"></use>
            </svg>
        </button>
        ${progressBar}`
        this.querySelector('.alert-close').addEventListener('click', e => {
            e.preventDefault()
            this.close()
        })
    }

    close() {
        this.classList.add('out')
        window.setTimeout(async () => {
            await slideUp(this)
            this.parentElement.removeChild(this)
            this.dispatchEvent(new CustomEvent('close'))
        }, 500)
    }

    get icon() {
        switch (this.type) {
            case 'danger':
                return 'alert-octagon'
            case 'success':
                return 'check'
            case 'warning':
                return 'alert-triangle'
            case 'info':
                return 'info'
            default:
                return 'info'
        }
    }
}

export class FloatingAlert extends Alert {
    constructor(options = {}) {
        super(options)
    }

    connectedCallback() {
        super.connectedCallback()
        this.classList.add('is-floating')
    }
}

/**
 * Affiche un message flash flottant sur la page
 *
 * @param {string} message
 * @param {string} type
 * @param {number|null} duration
 */
export function flash(message, type = 'success', duration = 3) {
    const alert = document.createElement('alert-floating')
    if (duration) {
        alert.setAttribute('duration', duration)
    }
    alert.setAttribute('type', type)
    alert.innerText = message
    document.body.appendChild(alert)
}