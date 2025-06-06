import Sortable from 'sortablejs';

function html(strings, ...values) {
    const template = document.createElement('template');
    template.innerHTML = strings.reduce(
        (result, string, i) => result + string + (values[i] || ''),
        ''
    );
    return template.content.firstElementChild;
}

function Chapter({ chapter, onUpdate, onRemoveCourse, onAddCourse, editPath, searchCourses }) {
    const li = document.createElement('li');
    li.className = 'chapters-editor__chapter';
    li.dataset.title = chapter.title;

    li.innerHTML = `
        <div class="chapters-editor__chapter-header">
            <button type="button" class="chapters-editor__chapter-toggle">
                <svg class="questions-editor__icon questions-editor__icon--toggle" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <use href="/icons/sprite.svg#chevron-up"></use>
                </svg>
            </button>
            <input
                type="text"
                value="${chapter.title}"
                class="chapters-editor__chapter-input"
                placeholder="Titre du chapitre"
            />
            <div class="chapters-editor__chapter-handle">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <use href="/icons/sprite.svg?#grip-vertical"></use>
                </svg>
            </div>
            <button type="button" class="chapters-editor__chapter-delete">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <use href="/icons/sprite.svg?#trash-2"></use>
                </svg>
            </button>
        </div>
        <ul class="chapters-editor__chapter-courses"></ul>
    `;

    const toggleButton = li.querySelector('.chapters-editor__chapter-toggle');
    const coursesList = li.querySelector('.chapters-editor__chapter-courses');
    const chevronIcon = toggleButton.querySelector('svg');

    toggleButton.addEventListener('click', () => {
        coursesList.classList.toggle('hidden');
        li.classList.toggle('is-open');
        chevronIcon.classList.toggle('rotate-180'); // rotation visuelle de l'icône
    });

    const titleInput = li.querySelector('.chapters-editor__chapter-input');
    titleInput.addEventListener('blur', onUpdate);

    const deleteButton = li.querySelector('.chapters-editor__chapter-delete');
    deleteButton.addEventListener('click', () => {
        li.remove();
        onUpdate();
    });

    chapter.modules.forEach((course) => {
        const courseElement = Course({ course, onRemoveCourse, editPath });
        coursesList.appendChild(courseElement);
    });

    const addCourseButton = AddCourseButton({ onAddCourse, searchCourses });
    coursesList.appendChild(addCourseButton);

    return li;
}

function Course({ course, onRemoveCourse, editPath }) {
    const li = document.createElement('li');
    li.className = 'chapters-editor__course';
    li.dataset.id = course.id;
    li.dataset.title = course.title;

    li.innerHTML = `
        <a href="${editPath.replace('/0', `/${course.id}`)}" target="_blank" class="chapters-editor__course-link">
            <svg class="min-w-4 min-h-4 max-w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                <use href="/icons/sprite.svg?#video"></use>
            </svg>
            ${course.title} 
        </a>
        <div class="chapters-editor__course-handle">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                <use href="/icons/sprite.svg?#grip-vertical"></use>
            </svg>
        </div>
        <button type="button" class="chapters-editor__course-delete">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                <use href="/icons/sprite.svg?#trash-2"></use>
            </svg>
        </button>
    `;

    const deleteButton = li.querySelector('.chapters-editor__course-delete');
    deleteButton.addEventListener('click', () => {
        li.remove();
        onRemoveCourse();
    });

    return li;
}


function AddCourseButton({ onAddCourse, searchCourses }) {
    const li = document.createElement('li');
    li.className = 'chapters-editor__add-course';

    // Wrapper pour l'input de recherche et les suggestions
    const inputWrapper = document.createElement('div');
    inputWrapper.className = 'chapters-editor__add-course-input-wrapper';

    const input = document.createElement('input');
    input.type = 'text';
    input.className = 'chapters-editor__add-course-input';
    input.placeholder = 'Rechercher un cours';

    const suggestionsContainer = document.createElement('ul');
    suggestionsContainer.className = 'chapters-editor__suggestions';

    // Création du loader
    const loader = document.createElement('spinning-dots');
    loader.className = 'chapters-editor__loader';
    loader.style.display = 'none'; // Masquer par défaut

    inputWrapper.appendChild(input);
    li.appendChild(inputWrapper);
    li.appendChild(loader); // Ajouter le loader
    li.appendChild(suggestionsContainer);

    let suggestions = [];
    let selectedIndex = -1;

    // Fonction pour mettre à jour les suggestions
    function updateSuggestions(query) {
        if (!query) {
            suggestionsContainer.innerHTML = '';
            suggestionsContainer.style.display = 'none';
            loader.style.display = 'none'; // Cacher le loader si la recherche est vide
            return;
        }

        loader.style.display = 'block'; // Afficher le loader
        suggestionsContainer.style.display = 'none'; // Cacher temporairement les suggestions

        searchCourses(query).then((results) => {
            suggestions = results;
            suggestionsContainer.innerHTML = '';
            loader.style.display = 'none'; // Cacher le loader une fois la recherche terminée

            if (suggestions.length) {
                suggestionsContainer.style.display = 'block';
                suggestions.forEach((course, index) => {
                    const suggestionItem = document.createElement('li');
                    suggestionItem.textContent = course.title;
                    suggestionItem.className = 'chapters-editor__suggestion';
                    if (index === selectedIndex) {
                        suggestionItem.classList.add('selected');
                    }
                    suggestionItem.addEventListener('click', () => {
                        onAddCourse(course, li);
                        input.value = '';
                        suggestionsContainer.style.display = 'none';
                    });
                    suggestionsContainer.appendChild(suggestionItem);
                });
            } else {
                // Afficher un message s'il n'y a pas de résultats
                const noResultsMessage = document.createElement('li');
                noResultsMessage.textContent = 'Aucun résultat trouvé';
                noResultsMessage.className = 'chapters-editor__no-results';
                suggestionsContainer.appendChild(noResultsMessage);
                suggestionsContainer.style.display = 'block';
            }
        });
    }

    // Gérer les événements du clavier
    input.addEventListener('input', (e) => {
        const query = e.target.value.trim();
        updateSuggestions(query);
    });

    input.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            selectedIndex = (selectedIndex + 1) % suggestions.length;
            updateSuggestions(input.value);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            selectedIndex = (selectedIndex - 1 + suggestions.length) % suggestions.length;
            updateSuggestions(input.value);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (selectedIndex >= 0 && suggestions[selectedIndex]) {
                onAddCourse(suggestions[selectedIndex], li);
                input.value = '';
                suggestionsContainer.style.display = 'none';
            }
        }
    });

    // Masquer les suggestions si l'utilisateur clique en dehors
    document.addEventListener('click', (e) => {
        if (!li.contains(e.target)) {
            suggestionsContainer.style.display = 'none';
            loader.style.display = 'none';
        }
    });

    return li;
}

export class ChaptersEditor extends HTMLElement {
    constructor() {
        super();

        this.textarea = this.querySelector('textarea');
        if (!this.textarea) {
            throw new Error('ChaptersEditor doit contenir un <textarea> enfant.');
        }

        this.sortables = [];
        this.updateInput = this.updateInput.bind(this);
        this.removeCourse = this.removeCourse.bind(this);
        this.addCourse = this.addCourse.bind(this);
        this.addChapter = this.addChapter.bind(this);
        this.searchCourses = this.searchCourses.bind(this);
        this.sortableOptions = {
            group: 'nested',
            animation: 150,
            fallbackOnBody: true,
            swapThreshold: 0.65,
            ghostClass: 'chapters-editor__sortable-ghost',
            chosenClass: 'chapters-editor__sortable-chosen',
            filter: '.chapters-editor__add-course',
            preventOnFilter: false,
            onEnd: this.updateInput,
        };
    }

    connectedCallback() {
        this.editPath = this.textarea.getAttribute('endpoint-edit');
        this.searchEndpoint = this.textarea.getAttribute('endpoint-search');
        this.list = this.renderList();
        this.bindSortable();
        this.insertAdjacentElement('beforebegin', this.list);

        this.textarea.style.display = 'none';
    }

    renderList() {
        const chapters = JSON.parse(this.textarea.value || '[]');
        const ul = document.createElement('ul');
        ul.className = 'chapters-editor__chapters';

        chapters.forEach((chapter) => {
            const chapterElement = Chapter({
                chapter,
                onUpdate: this.updateInput,
                onRemoveCourse: this.removeCourse,
                onAddCourse: this.addCourse,
                editPath: this.editPath,
                searchCourses: this.searchCourses,
            });
            ul.appendChild(chapterElement);
        });

        // Ajouter un champ pour créer de nouveaux chapitres
        const addChapterLi = document.createElement('li');
        addChapterLi.className = 'chapters-editor__add-chapter';

        const input = document.createElement('input');
        input.type = 'text';
        input.placeholder = 'Ajouter un chapitre';
        input.className = 'chapters-editor__add-chapter-input';

        const button = document.createElement('button');
        button.type = 'button';
        button.textContent = '+';
        button.className = 'chapters-editor__add-chapter-button';
        button.addEventListener('click', () => {
            this.addChapter(input.value, addChapterLi);
        });

        addChapterLi.appendChild(input);
        addChapterLi.appendChild(button);
        ul.appendChild(addChapterLi);

        return ul;
    }

    addChapter(title, li) {
        if (!title.trim()) return;
        const chapter = { title, modules: [] };
        const chapterElement = Chapter({
            chapter,
            onUpdate: this.updateInput,
            onRemoveCourse: this.removeCourse,
            onAddCourse: this.addCourse,
            editPath: this.editPath,
            searchCourses: this.searchCourses,
        });
        li.insertAdjacentElement('beforebegin', chapterElement);

        // Re-binder Sortable pour les cours dans le nouveau chapitre
        const coursesList = chapterElement.querySelector('.chapters-editor__chapter-courses');
        this.sortables.push(
            new Sortable(coursesList, {
                ...this.sortableOptions,
                handle: '.chapters-editor__course-handle',
                ghostClass: 'chapters-editor__sortable-ghost',
                chosenClass: 'chapters-editor__sortable-chosen',
            })
        );

        this.updateInput();

        const input = li.querySelector('.chapters-editor__add-chapter-input');
        input.value = '';
        input.focus();
    }


    async searchCourses(query) {
        const url = new URL(this.searchEndpoint, window.location.origin);
        url.searchParams.set('q', query);
        try {
            return await fetch(url.toString()).then((res) => res.json());
        } catch (e) {
            console.error('Erreur lors de la recherche des cours.', e);
            return [];
        }
    }

    addCourse(course, li) {
        const courseLi = Course({
            course,
            onRemoveCourse: this.removeCourse,
            editPath: this.editPath,
        });
        li.insertAdjacentElement('beforebegin', courseLi);

        // Réinitialiser le drag-and-drop pour le parent (au cas où)
        const parentUl = li.closest('.chapters-editor__chapter-courses');
        const sortable = this.sortables.find((s) => s.el === parentUl);
        if (sortable) {
            sortable.destroy(); // Supprimer l'ancien binding
            this.sortables = this.sortables.filter((s) => s !== sortable); // Retirer de la liste
        }

        // Réinitialiser le binding avec Sortable
        this.sortables.push(
            new Sortable(parentUl, {
                ...this.sortableOptions,
                handle: '.chapters-editor__course-handle',
                ghostClass: 'chapters-editor__sortable-ghost',
                chosenClass: 'chapters-editor__sortable-chosen',
            })
        );

        this.updateInput();

        const input = li.querySelector('.chapters-editor__add-course-input');
        if (input) {
            input.value = '';
            input.focus();
        }
    }


    removeCourse() {
        this.updateInput();
    }

    updateInput() {
        const chapters = Array.from(
            this.list.querySelectorAll('.chapters-editor__chapter')
        ).map((chapterLi) => {
            const title = chapterLi.querySelector('.chapters-editor__chapter-input').value;
            const modules = Array.from(
                chapterLi.querySelectorAll('.chapters-editor__course')
            ).map((courseLi) => ({
                id: courseLi.dataset.id,
                title: courseLi.dataset.title,
            }));
            return { title, modules };
        });
        this.textarea.value = JSON.stringify(chapters, null, 2);
    }

    bindSortable() {
        this.sortables = Array.from(
            this.list.querySelectorAll('.chapters-editor__chapter-courses')
        ).map((ul) =>
            new Sortable(ul, {
                ...this.sortableOptions,
                handle: '.chapters-editor__course-handle',
                ghostClass: 'chapters-editor__sortable-ghost',
                chosenClass: 'chapters-editor__sortable-chosen',
            })
        );

        this.sortables.push(
            new Sortable(this.list, {
                ...this.sortableOptions,
                group: 'chapters',
                handle: '.chapters-editor__chapter-handle',
            })
        );
    }

    disconnectedCallback() {
        this.sortables.forEach((sortable) => sortable.destroy());
        this.sortables = [];
        this.list.remove();
    }
}
