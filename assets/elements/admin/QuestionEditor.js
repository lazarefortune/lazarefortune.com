import Sortable from 'sortablejs';

class QuestionsEditor extends HTMLElement {
    constructor() {
        super();
        this.textarea = this.querySelector('textarea');
        if (!this.textarea) {
            throw new Error('QuestionsEditor doit contenir un <textarea> enfant.');
        }
        this.questions = JSON.parse(this.textarea.value || '[]');
        this.updateTextarea = this.updateTextarea.bind(this);
    }

    connectedCallback() {
        this.renderEditor();

        // Initialiser Sortable pour les questions
        Sortable.create(this.listElement, {
            handle: '.questions-editor__question-handle',
            animation: 150,
            onEnd: () => {
                this.updateQuestionsOrder();
            }
        });
    }

    renderEditor() {
        // Ne pas supprimer le textarea
        const container = document.createElement('div');
        container.classList.add('questions-editor__container');

        container.innerHTML = `
            <ul class="questions-editor__list"></ul>
            <button type="button" class="questions-editor__add-question-btn">
                <!-- Icône d'ajout -->
                <svg class="questions-editor__icon questions-editor__icon--add" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <use href="/icons/sprite.svg#plus"></use>
                </svg>
                Ajouter une question
            </button>
        `;

        this.listElement = container.querySelector('.questions-editor__list');
        this.addQuestionButton = container.querySelector('.questions-editor__add-question-btn');

        this.addQuestionButton.addEventListener('click', () => {
            this.addQuestion();
        });

        this.questions.forEach(question => {
            this.addQuestionElement(question);
        });

        // Insérer le container après le textarea
        this.insertAdjacentElement('beforeend', container);
    }

    addQuestion(question = null) {
        const newQuestion = question || {
            text: '',
            type: 'choice',
            timeLimit: 30,
            answers: []
        };
        this.questions.push(newQuestion);
        this.addQuestionElement(newQuestion);
        this.updateTextarea();
    }

    addQuestionElement(question) {
        const questionElement = document.createElement('li');
        questionElement.className = 'questions-editor__question';

        // Stocker la donnée sur l'élément DOM
        questionElement.questionData = question;

        // Créer les éléments pour éditer la question
        questionElement.innerHTML = `
            <div class="questions-editor__question-header">
                <div class="questions-editor__question-toggle">
                    <!-- Icône de repli/dépliement -->
                    <svg class="questions-editor__icon questions-editor__icon--toggle" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <use href="/icons/sprite.svg#chevron-down"></use>
                    </svg>
                </div>
                <input type="text" class="questions-editor__question-text" value="${question.text}" placeholder="Entrez une question">
                <div class="questions-editor__question-handle">
                    <!-- Icône de déplacement -->
                    <svg class="questions-editor__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <use href="/icons/sprite.svg#grip-vertical"></use>
                    </svg>
                </div>
                <button type="button" class="questions-editor__delete-question-btn">
                    <!-- Icône de suppression -->
                    <svg class="questions-editor__icon questions-editor__icon--delete" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <use href="/icons/sprite.svg#trash-2"></use>
                    </svg>
                </button>
            </div>
            <div class="questions-editor__question-content hidden">
                <div class="questions-editor__question-settings">
                    <div>
                        <label class="questions-editor__label">Type de question :</label>
                        <select class="questions-editor__question-type">
                            <option value="choice" ${question.type === 'choice' ? 'selected' : ''}>Choix unique</option>
                            <option value="multiple_choice" ${question.type === 'multiple_choice' ? 'selected' : ''}>
                                <svg class="questions-editor__icon questions-editor__icon--add" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                    <use href="/icons/sprite.svg#plus"></use>
                                </svg>
                                Choix multiples 
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="questions-editor__label">Temps limite (en secondes) :</label>
                        <input type="number" class="questions-editor__question-timeLimit" value="${question.timeLimit}">
                    </div>
                </div>
                <label class="questions-editor__label">Réponses :</label>
                <ul class="questions-editor__options-list"></ul>
                <button type="button" class="questions-editor__add-option-btn">
                    <!-- Icône d'ajout -->
                    <svg class="questions-editor__icon questions-editor__icon--add" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <use href="/icons/sprite.svg#plus"></use>
                    </svg>
                    Ajouter une réponse
                </button>
            </div>
        `;

        // Récupérer les éléments
        const questionHeader = questionElement.querySelector('.questions-editor__question-header');
        const questionContent = questionElement.querySelector('.questions-editor__question-content');
        const toggleButton = questionElement.querySelector('.questions-editor__question-toggle');
        const textInput = questionElement.querySelector('.questions-editor__question-text');
        const typeSelect = questionElement.querySelector('.questions-editor__question-type');
        const timeLimitInput = questionElement.querySelector('.questions-editor__question-timeLimit');
        const deleteButton = questionElement.querySelector('.questions-editor__delete-question-btn');
        const optionsList = questionElement.querySelector('.questions-editor__options-list');
        const addOptionButton = questionElement.querySelector('.questions-editor__add-option-btn');

        // Événement pour le repli/dépliement
        toggleButton.addEventListener('click', () => {
            questionContent.classList.toggle('hidden');
            // Changer l'icône
            const iconUse = toggleButton.querySelector('use');
            const isExpanded = !questionContent.classList.contains('hidden');
            iconUse.setAttribute('href', isExpanded ? '/icons/sprite.svg#chevron-up' : '/icons/sprite.svg#chevron-down');
        });

        // Gestion des événements
        textInput.addEventListener('input', (e) => {
            question.text = e.target.value;
            this.updateTextarea();
        });

        typeSelect.addEventListener('change', (e) => {
            question.type = e.target.value;
            // Mettre à jour le type des inputs isCorrect des options
            const optionInputs = optionsList.querySelectorAll('.questions-editor__option-isCorrect');
            optionInputs.forEach(input => {
                input.type = question.type === 'choice' ? 'radio' : 'checkbox';
            });
            this.updateTextarea();
        });

        timeLimitInput.addEventListener('input', (e) => {
            question.timeLimit = parseInt(e.target.value, 10);
            this.updateTextarea();
        });

        deleteButton.addEventListener('click', () => {
            const index = this.questions.indexOf(question);
            if (index > -1) {
                this.questions.splice(index, 1);
                questionElement.remove();
                this.updateTextarea();
            }
        });

        addOptionButton.addEventListener('click', () => {
            this.addOption(question, optionsList);
            // Ouvrir la section si elle est fermée
            if (questionContent.classList.contains('hidden')) {
                questionContent.classList.remove('hidden');
                const iconUse = toggleButton.querySelector('use');
                iconUse.setAttribute('href', '/icons/sprite.svg#chevron-up');
            }
        });

        // Initialiser Sortable pour les options
        Sortable.create(optionsList, {
            handle: '.questions-editor__option-handle',
            animation: 150,
            onEnd: () => {
                this.updateOptionsOrder(question, optionsList);
            }
        });

        // Ajouter les options existantes
        question.answers = question.answers || []; // S'assurer que `answers` est un tableau
        question.answers.forEach(answer => {
            this.addOptionElement(question, answer, optionsList);
        });

        this.listElement.appendChild(questionElement);
    }

    updateQuestionsOrder() {
        const newOrder = Array.from(this.listElement.children).map(item => item.questionData);
        this.questions = newOrder;
        this.updateTextarea();
    }

    addOption(question, optionsList, option = null) {
        const newOption = option || {
            id: Date.now(),
            text: '',
            isCorrect: false
        };
        question.answers.push(newOption);
        this.addOptionElement(question, newOption, optionsList);
        this.updateTextarea();
    }

    addOptionElement(question, option, optionsList) {
        const optionElement = document.createElement('li');
        optionElement.className = 'questions-editor__option';

        // Stocker la donnée sur l'élément DOM
        optionElement.optionData = option;

        optionElement.innerHTML = `
            <input type="${question.type === 'choice' ? 'radio' : 'checkbox'}" class="questions-editor__option-isCorrect" ${option.isCorrect ? 'checked' : ''} name="question_${this.questions.indexOf(question)}">
            <input type="text" class="questions-editor__option-text" placeholder="" value="${option.text}">
            <div class="questions-editor__option-handle">
                <!-- Icône de déplacement -->
                <svg class="questions-editor__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <use href="/icons/sprite.svg#grip-vertical"></use>
                </svg>
            </div>
            <button type="button" class="questions-editor__delete-option-btn">
                <!-- Icône de suppression -->
                <svg class="questions-editor__icon questions-editor__icon--delete" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <use href="/icons/sprite.svg#trash-2"></use>
                </svg>
            </button>
        `;

        const textInput = optionElement.querySelector('.questions-editor__option-text');
        const isCorrectInput = optionElement.querySelector('.questions-editor__option-isCorrect');
        const deleteButton = optionElement.querySelector('.questions-editor__delete-option-btn');

        textInput.addEventListener('input', (e) => {
            option.text = e.target.value;
            this.updateTextarea();
        });

        isCorrectInput.addEventListener('change', (e) => {
            if (question.type === 'choice') {
                // Pour les questions à choix unique, désélectionner les autres options
                question.answers.forEach(opt => opt.isCorrect = false);
                option.isCorrect = e.target.checked;
                // Mettre à jour l'état des autres inputs
                const optionInputs = optionsList.querySelectorAll('.questions-editor__option-isCorrect');
                optionInputs.forEach(input => {
                    if (input !== e.target) {
                        input.checked = false;
                    }
                });
            } else {
                option.isCorrect = e.target.checked;
            }
            this.updateTextarea();
        });

        deleteButton.addEventListener('click', () => {
            const index = question.answers.indexOf(option);
            if (index > -1) {
                question.answers.splice(index, 1);
                optionElement.remove();
                this.updateTextarea();
            }
        });

        optionsList.appendChild(optionElement);
    }

    updateOptionsOrder(question, optionsList) {
        const newOrder = Array.from(optionsList.children).map(item => item.optionData);
        question.answers = newOrder;
        this.updateTextarea();
    }

    updateTextarea() {
        this.textarea.value = JSON.stringify(this.questions, null, 2);
    }
}

customElements.define('questions-editor', QuestionsEditor);
