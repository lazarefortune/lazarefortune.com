export default class InputAttachment extends HTMLInputElement {
    connectedCallback() {
        const preview = this.getAttribute('preview');
        this.insertAdjacentHTML(
            'afterend',
            `
                <div class="input-attachment">
                    <div class="input-attachment__preview" style="background-image:url(${preview || ''})"></div>
                    <button class="input-attachment__button-remove hidden">
                        <svg class="w-4 h-4"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="1.75"
                             stroke-linecap="round"
                             stroke-linejoin="round">
                            <use href="/icons/sprite.svg?#trash"></use>
                        </svg>
                    </button>
                </div>
            `
        );
        this.style.display = 'none';
        this.container = this.parentElement.querySelector('.input-attachment');
        this.container.addEventListener('dragleave', this.ondragleave.bind(this));
        this.container.addEventListener('dragenter', this.onDragEnter.bind(this));
        this.container.addEventListener('dragover', this.onDragOver);
        this.container.addEventListener('drop', this.onDrop.bind(this));
        this.container.addEventListener('click', this.onClick.bind(this));
        this.removeButton = this.container.querySelector('.input-attachment__button-remove');
        this.removeButton.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.setAttachment({ id: '', url: '' }); // Réinitialise l'attachement
        });
        this.preview = this.container.querySelector('.input-attachment__preview');
        this.overwrite = this.getAttribute('overwrite') !== null;

        // Afficher/masquer le bouton de suppression au démarrage
        this.toggleRemoveButton(!!preview);
    }

    onDragEnter(e) {
        e.preventDefault();
        this.container.classList.add('is-hovered');
    }

    ondragleave(e) {
        e.preventDefault();
        this.container.classList.remove('is-hovered');
    }

    onDragOver(e) {
        e.preventDefault();
    }

    async onDrop(e) {
        e.preventDefault();
        if (!this.container.contains(e.target)) {
            console.warn('Drop event ignoré, cible incorrecte.');
            return;
        }

        this.container.classList.add('is-hovered');
        const loader = document.createElement('spinning-dots');
        loader.classList.add('input-attachment__loader');
        this.container.appendChild(loader);

        const files = e.dataTransfer.files;
        if (files.length === 0) return false;
        const data = new FormData();
        data.append('file', files[0]);

        let url = this.getAttribute('data-endpoint');
        if (this.attachmentId !== '' && this.overwrite) {
            url = `${url}/${this.attachmentId}`;
        }

        const response = await fetch(url, {
            method: 'POST',
            body: data
        });
        const responseData = await response.json();

        if (response.ok) {
            this.setAttachment(responseData);
        } else {
            const alert = document.createElement('alert-message');
            alert.innerHTML = "Impossible d'envoyer l'image";
            document.body.appendChild(alert);
        }
        this.container.removeChild(loader);
        this.container.classList.remove('is-hovered');
    }

    onClick(e) {
        e.preventDefault();
        const modal = document.createElement('modal-dialog');
        modal.classList.add('input-attachement__modal-dialog');

        const modalHeader = document.createElement('div');
        modalHeader.classList.add('input-attachement__modal-header');

        const closeButton = document.createElement('button');
        closeButton.innerHTML = `
        <svg class="w-4 h-4"
             viewBox="0 0 24 24"
             fill="none"
             stroke="currentColor"
             stroke-width="1.75"
             stroke-linecap="round"
             stroke-linejoin="round">
            <use href="/icons/sprite.svg?#x"></use>
        </svg>`;
        closeButton.classList.add('input-attachement__modal-close');
        closeButton.setAttribute('aria-label', 'Close');
        closeButton.addEventListener('click', () => {
            modal.remove();
        });

        modalHeader.appendChild(closeButton);

        const modalBody = document.createElement('div');
        modalBody.classList.add('input-attachement__modal-body');

        const fm = document.createElement('file-manager');
        fm.setAttribute('endpoint', this.getAttribute('data-endpoint'));
        modalBody.appendChild(fm);

        modal.appendChild(modalHeader);
        modal.appendChild(modalBody);
        modal.setAttribute('overlay-close', 'true');

        fm.addEventListener('selectfile', (e) => {
            this.setAttachment(e.detail);
            modal.remove();
        });

        document.body.appendChild(modal);
    }

    setAttachment(attachment) {
        this.preview.style.backgroundImage = `url(${attachment.url})`;
        this.value = attachment.id;
        const changeEvent = document.createEvent('HTMLEvents');
        changeEvent.initEvent('change', false, true);
        this.dispatchEvent(changeEvent);
        this.dispatchEvent(new CustomEvent('attachment', { detail: attachment }));

        // Afficher/masquer le bouton de suppression
        this.toggleRemoveButton(!!attachment.url);
    }

    toggleRemoveButton(show) {
        if (show) {
            this.removeButton.classList.remove('hidden');
        } else {
            this.removeButton.classList.add('hidden');
        }
    }

    /**
     * @return {string}
     */
    get attachmentId() {
        return this.value;
    }
}
