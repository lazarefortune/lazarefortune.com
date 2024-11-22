export class FileManager extends HTMLElement {
    connectedCallback() {
        this.baseUrl = this.getAttribute('endpoint');

        this.innerHTML = `
            <div class="file-manager">
                <div class="file-manager__header">
                    <div class="flex gap-2">
                        <select class="file-manager__year-select" aria-label="Sélectionnez une année"></select>
                        <select class="file-manager__month-select" aria-label="Sélectionnez un mois"></select>
                    </div>
                    <div class="flex gap-2 items-center">
                        <input type="text" class="file-manager__search-input" placeholder="Rechercher..." aria-label="Rechercher des fichiers" />
                        <button class="file-manager__upload-btn">Uploader</button>
                        <input type="file" class="file-manager__upload-input hidden" />
                    </div>
                </div>
                <div class="file-manager__body">
                    <div class="file-manager__content"></div>
                    <div id="file-manager__loader" class="absolute inset-0 w-full h-full bg-white dark:bg-primary-950 bg-opacity-70 flex items-center justify-center z-50">
                        <spinning-dots class="hidden"></spinning-dots>
                    </div>
                </div>
            </div>
        `;

        this.initListeners();
        this.loadYears();
    }

    initListeners() {
        const yearSelect = this.querySelector('.file-manager__year-select');
        const monthSelect = this.querySelector('.file-manager__month-select');
        const searchInput = this.querySelector('.file-manager__search-input');
        const uploadButton = this.querySelector('.file-manager__upload-btn');
        const uploadInput = this.querySelector('.file-manager__upload-input');

        yearSelect.addEventListener('change', () => {
            this.currentYear = yearSelect.value;
            this.loadMonthsFromFolders(this.folders);
        });

        monthSelect.addEventListener('change', () => {
            this.currentMonth = monthSelect.value;
            this.loadFiles();
        });

        searchInput.addEventListener('input', () => {
            const query = searchInput.value.trim();
            this.searchFiles(query);
        });

        uploadButton.addEventListener('click', () => {
            uploadInput.click();
        });

        uploadInput.addEventListener('change', async (e) => {
            const file = e.target.files[0];
            if (file) {
                await this.uploadFile(file);
                this.loadFiles();
            }
        });
    }

    async loadYears() {
        try {
            this.showLoader();
            const response = await fetch(`${this.baseUrl}/folders`);
            if (!response.ok) {
                throw new Error('Erreur lors du chargement des années.');
            }
            this.folders = await response.json();

            const years = this.folders.filter(folder => folder.parent === null).sort((a, b) => b.name - a.name);
            const yearSelect = this.querySelector('.file-manager__year-select');

            yearSelect.innerHTML = years
                .map(year => `<option value="${year.id}">${year.name}</option>`)
                .join('');

            if (years.length > 0) {
                this.currentYear = years[0].id;
                yearSelect.value = this.currentYear;
                this.loadMonthsFromFolders(this.folders);
            } else {
                yearSelect.innerHTML = '<option disabled selected>Aucune année disponible</option>';
            }
        } catch (error) {
            console.error(error);
            alert('Impossible de charger les années.');
        } finally {
            this.hideLoader();
        }
    }

    loadMonthsFromFolders(folders) {
        if (!this.currentYear) return;

        const months = folders
            .filter(folder => folder.parent === this.currentYear)
            .map(folder => ({
                ...folder,
                id: folder.id.replace(/\/(\d)$/, '/0$1'),
                name: folder.name.padStart(2, '0'),
            }))
            .sort((a, b) => b.name - a.name);

        const monthSelect = this.querySelector('.file-manager__month-select');
        monthSelect.innerHTML = months
            .map(month => `<option value="${month.id.split('/')[1]}">${month.name}</option>`)
            .join('');

        if (months.length > 0) {
            this.currentMonth = months[0].id.split('/')[1];
            monthSelect.value = this.currentMonth;
            this.loadFiles();
        } else {
            monthSelect.innerHTML = '<option disabled selected>Aucun mois disponible</option>';
            this.clearFiles();
        }
    }

    async loadFiles() {
        if (!this.currentYear || !this.currentMonth) return;

        try {
            this.showLoader();
            const response = await fetch(`${this.baseUrl}/files?path=${this.currentYear}/${this.currentMonth}`);
            if (!response.ok) {
                throw new Error('Erreur lors du chargement des fichiers.');
            }
            this.files = await response.json(); // Sauvegarder les fichiers pour la recherche
            this.renderFiles(this.files);
        } catch (error) {
            console.error(error);
            alert('Impossible de charger les fichiers.');
        } finally {
            this.hideLoader();
        }
    }

    renderFiles(files) {
        const contentContainer = this.querySelector('.file-manager__content');

        if (files.length > 0) {
            contentContainer.innerHTML = files
                .map(file => `
                    <div class="file" data-id="${file.id}" data-url="${file.url}">
                        <img src="${file.thumbnail}" alt="${file.name}" class="file__image" />
                        <div class="file__details">
                            <span class="file__name">${file.name}</span>
                            <button class="delete-btn" data-id="${file.id}">
                                <svg class="w-4 h-4"
                                     viewBox="0 0 24 24"
                                     fill="none"
                                     stroke="currentColor"
                                     stroke-width="1.75"
                                     stroke-linecap="round"
                                     stroke-linejoin="round">
                                    <use href="/icons/sprite.svg?#trash"></use>
                                </svg>
                                Supprimer 
                            </button>
                        </div>
                    </div>
                `)
                .join('');
            this.initFileSelectListeners();
            this.initDeleteListeners();
        } else {
            contentContainer.innerHTML = '<p class="no-files">Aucun fichier disponible.</p>';
        }
    }

    async searchFiles(query) {
        if ( !query || query.trim() === '') {
            await this.loadFiles();
            return;
        }

        if (query.trim().length <= 2) {
            return;
        }

        try {
            this.showLoader();
            const response = await fetch(`${this.baseUrl}/files?q=${query}`);
            if (!response.ok) {
                throw new Error('Erreur lors du chargement des fichiers.');
            }
            this.files = await response.json();
            this.renderFiles(this.files);
        } catch (error) {
            console.error(error);
            alert('Impossible d\'uploader le fichier.');
        } finally {
            this.hideLoader();
        }
    }

    async uploadFile(file) {
        try {
            this.showLoader();
            const formData = new FormData();
            formData.append('file', file);
            formData.append('path', `${this.currentYear}/${this.currentMonth}`);

            const response = await fetch(`${this.baseUrl}`, {
                method: 'POST',
                body: formData,
            });

            if (!response.ok) {
                throw new Error('Erreur lors de l\'upload du fichier.');
            }

            await this.loadYears()
        } catch (error) {
            console.error(error);
            alert('Impossible d\'uploader le fichier.');
        } finally {
            this.hideLoader();
        }
    }

    initFileSelectListeners() {
        const files = this.querySelectorAll('.file');
        files.forEach(file => {
            file.addEventListener('click', () => {
                const event = new CustomEvent('selectfile', { detail: { id: file.dataset.id, url: file.dataset.url } });
                this.dispatchEvent(event);
            });
        });
    }

    initDeleteListeners() {
        const deleteButtons = this.querySelectorAll('.delete-btn');
        deleteButtons.forEach(button => {
            button.addEventListener('click', async (e) => {
                e.stopPropagation();
                const confirmDelete = confirm('Voulez-vous vraiment supprimer ce fichier ?');
                if (!confirmDelete) return;

                try {
                    this.showLoader();
                    const fileId = button.dataset.id;
                    await fetch(`${this.baseUrl}/files/${fileId}`, { method: 'DELETE' });
                    this.loadYears();
                    this.loadFiles();
                } catch (error) {
                    console.error(error);
                    alert('Impossible de supprimer le fichier.');
                } finally {
                    this.hideLoader();
                }
            });
        });
    }

    showLoader() {
        const loaderContainer = this.querySelector('#file-manager__loader');
        loaderContainer.classList.remove('hidden');
        loaderContainer.querySelector('spinning-dots').classList.remove('hidden');
    }

    hideLoader() {
        const loaderContainer = this.querySelector('#file-manager__loader');
        loaderContainer.classList.add('hidden');
        loaderContainer.querySelector('spinning-dots').classList.add('hidden');
    }

    clearFiles() {
        this.querySelector('.file-manager__content').innerHTML = '<p class="no-files">Aucun fichier disponible.</p>';
    }
}
