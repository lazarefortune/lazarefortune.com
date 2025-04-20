import { redirect } from '../functions/url.js'

export class SwitchRedirect extends HTMLInputElement {
    connectedCallback () {
        if (this.type !== 'checkbox') return

        // Activation uniquement si data-redirect est présent
        if (this.dataset.redirect === undefined) return

        this.addEventListener('change', () => {
            const params = new URLSearchParams(window.location.search)

            if (this.checked) {
                params.set(this.name, this.value)
            } else {
                params.delete(this.name)
            }

            // On reset la pagination si elle est active
            params.delete('page')

            redirect(`${location.pathname}?${params}`)
        })
    }

    disconnectedCallback () {
        // Si besoin, tu peux détacher ici les events
        // this.removeEventListener('change', ...)
    }
}
