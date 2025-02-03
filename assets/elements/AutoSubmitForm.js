export class AutoSubmitForm extends HTMLElement {
    connectedCallback() {
        // Recherche le formulaire contenu dans cet élément
        const form = this.querySelector('form');
        if (!form) {
            console.error('Aucun formulaire trouvé dans <auto-submit-form>');
            return;
        }

        const autoSubmitFields = form.querySelectorAll('[data-auto-submit]');
        // On parcourt uniquement les inputs de type file et les selects
        autoSubmitFields.forEach(field => {
            const tagName = field.tagName.toLowerCase();
            if (tagName === 'input' && field.type === 'file') {
                field.addEventListener('change', () => form.submit());
            } else if (tagName === 'select') {
                field.addEventListener('change', () => form.submit());
            } else if ((tagName === 'input' && field.type === 'text') || field.tagName === 'textarea') {
                field.addEventListener('blur', () => form.submit());
            } else {
                field.addEventListener('change', () => form.submit());
            }
        });
    }
}
