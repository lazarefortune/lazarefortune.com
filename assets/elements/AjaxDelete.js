import { jsonFetch } from '../functions/api.js'
import { closest } from '../functions/dom.js'


export class AjaxDelete extends HTMLElement {
    connectedCallback () {
        this.addEventListener('click', async e => {
            e.preventDefault()

            const modal = document.createElement('modal-dialog')
            modal.setAttribute('overlay-close', 'true')

            modal.innerHTML = `
                <section class="modal-box">
                    <header>Voulez vous vraiment effectuer cette action ?</header>
                    <button data-dismiss aria-label="Close" class="modal-close">
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
                    
                    <p class="text-muted my-4">
                        Cette action est irréversible.
                    </p>
            
                    <div class="text-end">
                        <button type="button" class="btn-light mr-2" data-dismiss>
                            <span>Annuler</span>
                        </button>
                        <button id="confirmButton" class="btn-danger">
                            <span>Supprimer</span>
                        </button>
                    </div>
                </section>
            `

            document.body.appendChild(modal)

            const confirmButton = modal.querySelector('#confirmButton')

            confirmButton.addEventListener('click', async () => {
                modal.close()

                const target = this.getAttribute('target')
                const parent = target ? closest(this, target) : this.parentNode
                const loader = document.createElement('loader-overlay')
                parent.style.position = 'relative'
                parent.appendChild(loader)

                try {
                    await jsonFetch(this.getAttribute('url'), { method: 'DELETE' })
                    loader.hide()
                    parent.remove()

                    const alert = document.createElement('alert-floating')
                    alert.setAttribute('type', 'success')
                    alert.innerHTML = 'L\'élément a bien été supprimé'
                    document.body.appendChild(alert)
                } catch (e) {
                    loader.hide()
                    const alert = document.createElement('alert-floating')
                    alert.innerHTML = e.detail
                    document.body.appendChild(alert)
                }
            })
        })
    }
}