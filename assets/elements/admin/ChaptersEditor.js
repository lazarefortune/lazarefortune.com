import { closest, html } from '../../functions/dom.js'
import { enterKeyListener } from '../../functions/keyboard.js'
import Sortable from 'sortablejs'
import { jsonFetch } from '../../functions/api.js'

/**
 * Construit un élément représentant un chapitre
 *
 * @param {IChapter} chapter
 * @param {function} onUpdate
 * @param onRemove
 * @param onAdd
 * @param editPath
 * @return {HTMLLIElement}
 */
function Chapter ({ chapter, onUpdate, onRemove, onAdd, editPath }) {
    function deleteChapter (e) {
        e.preventDefault()
        e.stopPropagation()
        const li = closest(e.currentTarget, 'li')
        li.parentElement.removeChild(li)
        onUpdate()
    }
    return html`
    <li data-title="${chapter.title}">
      <input type="text" value="${chapter.title}" class="chapters-editor__chapter" onblur=${onUpdate} />
      <button type="button" onclick=${deleteChapter} class="chapters-editor__delete">
          <svg class="icon icon-delete"
               viewBox="0 0 24 24"
               fill="none"
               stroke="currentColor"
               stroke-width="1.75"
               stroke-linecap="round"
               stroke-linejoin="round">
              <use href="/icons/sprite.svg?#trash"></use>
          </svg>
      </button>
      <ul>
        ${chapter.modules.map(
        c =>
            html`
              <${Module} course=${c} onRemove=${onRemove} editPath=${editPath} />
            `
        )}
        <${AddButton} placeholder="Ajouter un cours" onAdd=${onAdd} />
      </ul>
    </li>
  `
}

/**
 * Construit un élément représentant un cours
 *
 * @param {ICourse} course
 * @param {function} onRemove
 * @param editPath
 * @return {HTMLLIElement}
 */
function Module ({ course, onRemove, editPath }) {
    const url = editPath.replace('/0', `/${course.id}`)
    return html`
    <li class="chapters-editor__course" data-title=${course.title} data-id=${course.id}>
      <a href=${url} target="_blank">${course.title}</a>
      <button type="button" onclick=${onRemove} class="chapters-editor__delete">
        <svg class="icon icon-delete"
             viewBox="0 0 24 24"
             fill="none"
             stroke="currentColor"
             stroke-width="1.75"
             stroke-linecap="round"
             stroke-linejoin="round">
            <use href="/icons/sprite.svg?#delete"></use>
        </svg>
      </button>
    </li>
  `
}

/**
 * Bouton d'ajout de contenu
 *
 * @param {string} placeholder
 * @param {function(string, HTMLLIElement)} onAdd
 * @return HTMLLIElement
 * @constructor
 */
function AddButton ({ placeholder, onAdd }) {
    const callback = function (e) {
        e.preventDefault()
        e.stopPropagation()
        const li = closest(e.currentTarget, 'li')
        const input = li.querySelector('input')
        onAdd(input.value, li)
        input.value = ''
        input.focus()
    }
    return html`
    <li class="chapters-editor__add">
      <input type="text" placeholder=${placeholder} onkeydown=${enterKeyListener(callback)} />
      <button type="button" onclick=${callback}>
        <svg class="icon icon-add">
            <svg class="icon"
                 viewBox="0 0 24 24"
                 fill="none"
                 stroke="currentColor"
                 stroke-width="1.75"
                 stroke-linecap="round"
                 stroke-linejoin="round">
                <use href="/icons/sprite.svg?#delete"></use>
            </svg>
        </svg>
      </button>
    </li>
  `
}

/**
 * CustomElement pour la gestion des chapitres associé aux formations
 *
 * @property {HTMLUListElement} list <ul> contenant la liste des chapitres
 * @property {string} editPath URL d'édition d'un cours
 * @typedef {{title: string, modules: ICourse[]}} IChapter
 * @typedef {{id: number, title: string}} ICourse
 */
export class ChaptersEditor extends HTMLTextAreaElement {
    constructor () {
        super()
        this.sortables = []
        this.updateInput = this.updateInput.bind(this)
        this.removeCourse = this.removeCourse.bind(this)
        this.addCourse = this.addCourse.bind(this)
        this.addChapter = this.addChapter.bind(this)
        this.sortableOptions = {
            group: 'nested',
            animation: 150,
            fallbackOnBody: true,
            swapThreshold: 0.65,
            filter: '.chapters-editor__add',
            preventOnFilter: false,
            onEnd: this.updateInput
        }
    }

    connectedCallback () {
        this.editPath = this.getAttribute('endpoint-edit')
        this.list = this.renderList()
        this.bindSortable()
        this.insertAdjacentElement('beforebegin', this.list)
    }

    /**
     * Construit la liste de chapitre
     *
     * @param {IChapter[]} chapters
     * @return HTMLUListElement
     */
    renderList () {
        const chapters = JSON.parse(this.value)
        return html`
      <ul class="chapters-editor stack">
        ${chapters.map(
            chapter =>
                html`
              <${Chapter}
                chapter=${chapter}
                onUpdate=${this.updateInput}
                onRemove=${this.removeCourse}
                onAdd="${this.addCourse}"
                editPath="${this.editPath}"
              />
            `
        )}
        <${AddButton} placeholder="Ajouter un chapitre" onAdd=${this.addChapter} />
      </ul>
    `
    }

    /**
     * Ajoute un cours
     *
     * @param value
     * @param li
     */
    async addCourse (value, li) {
        if (value === '') {
            return
        }
        const endpoint = this.getAttribute('endpoint').replace('0', value)
        try {
            const course = await jsonFetch(endpoint)
            const courseLi = Module({ course, onRemove: this.removeCourse, editPath: this.editPath })
            li.insertAdjacentElement('beforebegin', courseLi)
            this.updateInput()
        } catch (e) {
            alert(e.detail || e)
        }
    }

    /**
     * Supprime un cours de la liste des chapitres
     *
     * @param {MouseEvent} e
     */
    async removeCourse (e) {
        e.preventDefault()
        if (e.currentTarget instanceof HTMLButtonElement) {
            const li = closest(e.currentTarget, 'li')
            li.parentElement.removeChild(li)
            this.updateInput()
        }
    }

    /**
     * @param title
     * @param li
     */
    addChapter (title, li) {
        const chapter = {
            title,
            modules: []
        }
        const chapterLi = html`
      <${Chapter}
        chapter=${chapter}
        onUpdate=${this.updateInput}
        onRemove=${this.removeCourse}
        onAdd=${this.addCourse}
        editPath=${this.editPath}
      />
    `
        li.insertAdjacentElement('beforebegin', chapterLi)
        this.sortables.push(new Sortable(chapterLi.querySelector('ul'), this.sortableOptions))
    }

    /**
     * Greffer le comportement sortablejs
     */
    bindSortable () {
        this.sortables = Array.from(this.list.querySelectorAll('ul')).map(u => {
            return new Sortable(u, this.sortableOptions)
        })
        this.sortables.push(
            new Sortable(this.list, {
                ...this.sortableOptions,
                group: 'parent'
            })
        )
    }

    /**
     * Met à jour le champs avec les nouvelles données
     *
     * @param {HTMLUListElement} ul
     * @param {HTMLTextAreaElement} input
     */
    updateInput () {
        const newChapters = []
        Array.from(this.list.children).forEach(li => {
            const modules = li.querySelectorAll('li')
            // Il n'y a plus de cours dans ce chapitre
            if (modules.length === 0) {
                return
            }
            // On ajoute le chapitre au tableau
            newChapters.push({
                title: li.querySelector('input').value,
                modules: Array.from(modules)
                    .map(l => {
                        if (l.dataset.id === undefined) {
                            return null
                        }
                        return {
                            id: l.dataset.id,
                            title: l.dataset.title
                        }
                    })
                    .filter(c => c !== null)
            })
        })
        this.value = JSON.stringify(newChapters, null, 2)
    }

    disconnectedCallback () {
        this.sortables.forEach(sortable => sortable.destroy())
        if (this.list.parentElement) {
            this.list.parentElement.removeChild(this.list)
        }
    }
}