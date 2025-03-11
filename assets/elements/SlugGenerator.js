export class SlugGenerator extends HTMLElement {
    constructor() {
        super();
        this.isSlugEditable = false;
    }

    connectedCallback() {
        this.titleInput = this.querySelector("[data-slug-title]");
        this.slugInput = this.querySelector("[data-slug-input]");
        this.editSlugBtn = this.querySelector("[data-slug-edit-btn]");
        this.iconEdit = this.querySelector("[data-slug-icon-edit]");
        this.iconLock = this.querySelector("[data-slug-icon-lock]");

        if (!this.titleInput || !this.slugInput || !this.editSlugBtn) {
            console.error("SlugGenerator: Missing required elements.");
            return;
        }

        this.updateSlugInput()
        this.titleInput.addEventListener("input", () => this.updateSlug());
        this.editSlugBtn.addEventListener("click", () => this.toggleSlugEditing());
    }

    updateSlug() {
        if (!this.isSlugEditable) {
            this.slugInput.value = this.generateSlug(this.titleInput.value);
        }
    }

    updateSlugInput() {
        this.slugInput.readOnly = !this.isSlugEditable;
        this.slugInput.classList.toggle("bg-zinc-100", !this.isSlugEditable);
        this.slugInput.classList.toggle("dark:bg-slate-800", !this.isSlugEditable);
        this.slugInput.classList.toggle("text-slate-700", !this.isSlugEditable);
        this.slugInput.classList.toggle("dark:text-slate-400", !this.isSlugEditable);
    }

    toggleSlugEditing() {
        this.isSlugEditable = !this.isSlugEditable;
        this.updateSlugInput()

        if (this.iconEdit && this.iconLock) {
            this.iconEdit.classList.toggle("hidden", this.isSlugEditable);
            this.iconLock.classList.toggle("hidden", !this.isSlugEditable);
        }
    }

    generateSlug(text) {
        return text.toLowerCase()
            .trim()
            .replace(/[àáâãäå]/g, "a")
            .replace(/[èéêë]/g, "e")
            .replace(/[ìíîï]/g, "i")
            .replace(/[òóôõö]/g, "o")
            .replace(/[ùúûü]/g, "u")
            .replace(/[ñ]/g, "n")
            .replace(/[^a-z0-9 -]/g, "") // Supprime les caractères spéciaux
            .replace(/\s+/g, "-") // Remplace les espaces par des tirets
            .replace(/-+/g, "-"); // Évite les doubles tirets
    }
}
