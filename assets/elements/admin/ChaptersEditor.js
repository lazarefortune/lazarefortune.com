// JavaScript code for the Chapters Editor

import { closest, html } from '../../functions/dom.js';
import Sortable from 'sortablejs';

/**
 * Construit un élément représentant un chapitre.
 */
function Chapter({ chapter, onUpdate, onRemoveCourse, onAddCourse, editPath, searchCourses }) {
    function deleteChapter(e) {
        e.preventDefault();
        e.stopPropagation();
        const li = closest(e.currentTarget, 'li');
        li.parentElement.removeChild(li);
        onUpdate();
    }

    function moveCursorToEnd(e) {
        const valueLength = e.target.value.length;
        setTimeout(() => {
            e.target.setSelectionRange(valueLength, valueLength);
        }, 0);
    }

    return html`
        <li class="chapters-editor__chapter" data-title="${chapter.title}">
            <div class="chapters-editor__chapter-header">
                <div class="chapters-editor__chapter-handle">
                    <svg
                            class="w-4 h-4"
                            data-chapter-title="${chapter.id}"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.75"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                    >
                        <use
                                data-chapter-title="${chapter.id}"
                                href="/icons/sprite.svg?#move"
                        ></use>
                    </svg>
                </div>
                <input
                        type="text"
                        value="${chapter.title}"
                        class="chapters-editor__chapter-input"
                        placeholder="Titre du chapitre"
                        onblur=${onUpdate}
                        onfocus=${moveCursorToEnd}
                />
                <button
                        type="button"
                        class="chapters-editor__chapter-delete"
                        onclick=${deleteChapter}
                >
                    <!-- Icône SVG intégrée avec attribut unique -->
                    <svg
                            class="w-4 h-4"
                            data-chapter-title="${chapter.title}"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.75"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                    >
                        <use
                                data-chapter-title="${chapter.title}"
                                href="/icons/sprite.svg?#trash"
                        ></use>
                    </svg>
                </button>
            </div>
            <ul class="chapters-editor__chapter-courses">
                ${chapter.modules.map(
                        (course) =>
                                html`<${Course}
                                        course=${course}
                                        onRemoveCourse=${onRemoveCourse}
                                        editPath=${editPath}
                                />`
                )}
                ${AddCourseButton({ onAddCourse, searchCourses })}
            </ul>
        </li>
    `;
}

/**
 * Construit un élément représentant un cours.
 */
function Course({ course, onRemoveCourse, editPath }) {
    const url = editPath.replace('/0', `/${course.id}`);
    return html`
        <li
                class="chapters-editor__course"
                data-id="${course.id}"
                data-title="${course.title}"
        >
            <div class="chapters-editor__course-handle">
                <svg
                        class="w-4 h-4"
                        data-course-id="${course.id}"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.75"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                >
                    <use
                            data-course-id="${course.id}"
                            href="/icons/sprite.svg?#move"
                    ></use>
                </svg>
            </div>
            <a href=${url} target="_blank" class="chapters-editor__course-link"
            >${course.title}</a
            >
            <button
                    type="button"
                    class="chapters-editor__course-delete"
                    onclick=${onRemoveCourse}
            >
                <svg
                        class="w-4 h-4"
                        data-course-id="${course.id}"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.75"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                >
                    <use data-course-id="${course.id}" href="/icons/sprite.svg?#trash"></use>
                </svg>
            </button>
        </li>
    `;
}

/**
 * Bouton pour ajouter un cours avec autocomplétion et navigation au clavier.
 */
function AddCourseButton({ onAddCourse, searchCourses }) {
    let suggestions = [];
    let selectedIndex = -1;
    let selectedCourse = null;

    const li = document.createElement('li');
    li.className = 'chapters-editor__add-course';

    // Créer un conteneur pour le champ de saisie et les suggestions
    const inputWrapper = document.createElement('div');
    inputWrapper.className = 'chapters-editor__add-course-input-wrapper';

    const input = document.createElement('input');
    input.type = 'text';
    input.className = 'chapters-editor__add-course-input';
    input.placeholder = 'Rechercher un cours';
    input.addEventListener('input', handleInput);
    input.addEventListener('keydown', handleKeyDown);

    const suggestionsContainer = document.createElement('ul');
    suggestionsContainer.className = 'chapters-editor__suggestions';

    inputWrapper.appendChild(input);
    inputWrapper.appendChild(suggestionsContainer);

    li.appendChild(inputWrapper);

    function handleInput(e) {
        const query = e.target.value.trim();
        selectedIndex = -1;
        if (query.length > 1) {
            searchCourses(query).then((results) => {
                suggestions = results;
                updateSuggestionsList();
            });
        } else {
            suggestions = [];
            updateSuggestionsList();
        }
    }

    function handleKeyDown(e) {
        if (suggestions.length > 0) {
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                selectedIndex = (selectedIndex + 1) % suggestions.length;
                updateSuggestionsHighlight();
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                selectedIndex = (selectedIndex - 1 + suggestions.length) % suggestions.length;
                updateSuggestionsHighlight();
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (selectedIndex >= 0 && selectedIndex < suggestions.length) {
                    handleSuggestionClick(suggestions[selectedIndex]);
                }
            }
        }
    }

    function handleSuggestionClick(course) {
        selectedCourse = course;
        input.value = '';
        suggestions = [];
        selectedIndex = -1;
        updateSuggestionsList();
        onAddCourse(selectedCourse, li);
        selectedCourse = null;
    }

    function updateSuggestionsList() {
        suggestionsContainer.style.display = suggestions.length > 0 ? 'block' : 'none';
        suggestionsContainer.innerHTML = '';
        suggestions.forEach((course, index) => {
            const item = document.createElement('li');
            item.textContent = course.title;
            item.className = 'chapters-editor__suggestion';
            if (index === selectedIndex) {
                item.classList.add('selected');
            }
            item.addEventListener('click', () => handleSuggestionClick(course));
            suggestionsContainer.appendChild(item);
        });
    }

    function updateSuggestionsHighlight() {
        const items = suggestionsContainer.querySelectorAll('.chapters-editor__suggestion');
        items.forEach((item, index) => {
            if (index === selectedIndex) {
                item.classList.add('selected');
                selectedCourse = suggestions[selectedIndex];
                item.scrollIntoView({ block: 'nearest' });
            } else {
                item.classList.remove('selected');
            }
        });
    }

    return li;
}

/**
 * Élément personnalisé pour gérer l'éditeur de chapitres.
 */
export class ChaptersEditor extends HTMLTextAreaElement {
    constructor() {
        super();
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
            filter: '.chapters-editor__add-course',
            preventOnFilter: false,
            onEnd: this.updateInput,
        };
    }

    connectedCallback() {
        this.editPath = this.getAttribute('endpoint-edit');
        this.searchEndpoint = this.getAttribute('endpoint-search');
        this.list = this.renderList();
        this.bindSortable();
        this.insertAdjacentElement('beforebegin', this.list);

        // Masquer le textarea
        this.style.display = 'none';
    }

    /**
     * Construit la liste des chapitres.
     */
    renderList() {
        const chapters = JSON.parse(this.value || '[]');
        return html`
            <ul class="chapters-editor__chapters">
                ${chapters.map(
                        (chapter) => html`
                            <${Chapter}
                                    chapter=${chapter}
                                    onUpdate=${this.updateInput}
                                    onRemoveCourse=${this.removeCourse}
                                    onAddCourse=${this.addCourse}
                                    editPath=${this.editPath}
                                    searchCourses=${this.searchCourses}
                            />
                        `
                )}
                <li class="chapters-editor__add-chapter">
                    <input
                            type="text"
                            class="chapters-editor__add-chapter-input"
                            placeholder="Ajouter un chapitre"
                            onkeydown=${(e) =>
                                    e.key === 'Enter' &&
                                    this.addChapter(e.target.value, e.target.closest('li'))}
                    />
                    <button
                            type="button"
                            class="chapters-editor__add-chapter-button"
                            onclick=${(e) =>
                                    this.addChapter(
                                            e.target.previousElementSibling.value,
                                            e.target.closest('li')
                                    )}
                    >
                        +
                    </button>
                </li>
            </ul>
        `;
    }

    /**
     * Ajoute un chapitre.
     */
    addChapter(title, li) {
        if (!title.trim()) return;
        const chapter = { title, modules: [] };
        const chapterLi = html`
            <${Chapter}
                    chapter=${chapter}
                    onUpdate=${this.updateInput}
                    onRemoveCourse=${this.removeCourse}
                    onAddCourse=${this.addCourse}
                    editPath=${this.editPath}
                    searchCourses=${this.searchCourses}
            />
        `;
        li.insertAdjacentElement('beforebegin', chapterLi);
        this.sortables.push(
            new Sortable(chapterLi.querySelector('.chapters-editor__chapter-courses'), {
                ...this.sortableOptions,
                handle: '.chapters-editor__course-handle',
            })
        );
        this.updateInput();

        // Réinitialiser le champ de saisie
        const input = li.querySelector('.chapters-editor__add-chapter-input');
        input.value = '';
        input.focus();
    }

    /**
     * Ajoute un cours.
     */
    addCourse(course, li) {
        const courseLi = Course({
            course,
            onRemoveCourse: this.removeCourse,
            editPath: this.editPath,
        });
        li.insertAdjacentElement('beforebegin', courseLi);
        this.updateInput();

        // Réinitialiser le champ de saisie
        const input = li.querySelector('.chapters-editor__add-course-input');
        input.value = '';
        input.focus();
    }

    /**
     * Recherche des cours en fonction de la requête.
     */
    async searchCourses(query) {
        const url = new URL(this.searchEndpoint, window.location.origin);
        url.searchParams.set('q', query);
        try {
            const courses = await fetch(url.toString()).then((res) => res.json());
            return courses; // Doit être une liste d'objets { id, title }
        } catch (e) {
            console.error('Erreur lors de la recherche des cours.', e);
            return [];
        }
    }

    /**
     * Supprime un cours.
     */
    removeCourse(e) {
        const li = closest(e.currentTarget, 'li');
        li.parentElement.removeChild(li);
        this.updateInput();
    }

    /**
     * Initialise le comportement de drag-and-drop avec Sortable.js.
     */
    bindSortable() {
        // Pour les cours
        this.sortables = Array.from(
            this.list.querySelectorAll('.chapters-editor__chapter-courses')
        ).map((ul) =>
            new Sortable(ul, {
                ...this.sortableOptions,
                handle: '.chapters-editor__course-handle',
            })
        );

        // Pour les chapitres
        this.sortables.push(
            new Sortable(this.list, {
                ...this.sortableOptions,
                group: 'chapters',
                handle: '.chapters-editor__chapter-handle',
            })
        );
    }

    /**
     * Met à jour le JSON dans le textarea.
     */
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
        this.value = JSON.stringify(chapters, null, 2);
    }

    disconnectedCallback() {
        this.sortables.forEach((sortable) => sortable.destroy());
        this.list.remove();
    }
}
