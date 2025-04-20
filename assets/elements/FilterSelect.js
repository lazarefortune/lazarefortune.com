export class FilterSelect extends HTMLElement {
    connectedCallback() {
        const select = this.querySelector('select')
        if (!select) return

        // Si options déjà présentes (render côté serveur), on skippe
        if (select.options.length > 1) {
            this.handleChange(select)
            return
        }

        if (select.dataset.remote) {
            this.loadOptions(select)
        }

        this.handleChange(select)
    }

    handleChange(select) {
        select.addEventListener('change', () => {
            const params = new URLSearchParams(window.location.search)
            if (select.value === '') {
                params.delete(select.name)
            } else {
                params.set(select.name, select.value)
            }
            params.delete('page')
            window.location.href = `${location.pathname}?${params.toString()}`
        })
    }

    async loadOptions(select) {
        const url = `${select.dataset.remote}?q=`
        const res = await fetch(url)
        const data = await res.json()

        const valueKey = select.dataset.value || 'slug'
        const labelKey = select.dataset.label || 'name'

        data.forEach(option => {
            const opt = document.createElement('option')
            opt.value = option[valueKey]
            opt.textContent = option[labelKey]
            select.appendChild(opt)
        })
    }
}
