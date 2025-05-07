import { redirect } from '../functions/url.js'

export class SwitchRedirect extends HTMLElement {
    connectedCallback () {
        if (this.rendered) return; // éviter double appel
        this.rendered = true

        const checkbox = document.createElement('input')
        checkbox.type = 'checkbox'
        checkbox.name = this.getAttribute('name')
        checkbox.value = this.getAttribute('value') || '1'
        checkbox.className = 'form-checkbox'
        checkbox.id = this.getAttribute('id') || 'switch-redirect-' + Math.random().toString(36).substring(7)

        if (this.hasAttribute('data-checked')) {
            checkbox.checked = true
        }

        // Garde le label associé fonctionnel
        const label = this.previousElementSibling
        if (label && label.tagName === 'LABEL') {
            label.setAttribute('for', checkbox.id)
        }

        checkbox.addEventListener('change', () => {
            const params = new URLSearchParams(window.location.search)
            if (checkbox.checked) {
                params.set(checkbox.name, checkbox.value)
            } else {
                params.delete(checkbox.name)
            }
            params.delete('page')
            redirect(`${location.pathname}?${params}`)
        })

        this.appendChild(checkbox)
    }
}
