document.addEventListener('DOMContentLoaded', function () {
    const dateInput = document.querySelector('.appointment-date-choice');
    const prestationInput = document.querySelector('#appointment_form_prestation');

    if (!dateInput) {
        console.error('Date input not found!');
        return;
    }

    // Initialisation de Flatpickr
    const datePicker = initializeDatePicker(dateInput);

    // Fonction pour récupérer les jours fériés et configurer les dates désactivées
    async function updateDisabledDates() {
        const holidays = await fetchHolidays();
        const disabledDates = holidays.map(holiday => ({
            from: holiday.startDate,
            to: holiday.endDate
        }));

        datePicker.set('disable', disabledDates);
        datePicker.redraw();
    }

    // Fonction pour récupérer les jours fériés depuis l'API
    async function fetchHolidays() {
        const response = await fetch('/api/holidays/', {
            method: 'GET',
            headers: {'Content-Type': 'application/json'}
        });

        if (!response.ok) {
            throw new Error('Failed to fetch holidays');
        }

        return response.json();
    }

    // Initialise Flatpickr avec des options de base
    function initializeDatePicker(inputElement) {
        return flatpickr(inputElement, {
            altInput: true,
            disableMobile: true,
            altFormat: "j F Y",
            dateFormat: "Y-m-d",
            minDate: "today"
        });
    }

    // Appel initial pour configurer les jours désactivés
    updateDisabledDates();

    // Mettre à jour Flatpickr lorsque le choix de prestation change
    if (prestationInput) {
        prestationInput.addEventListener('change', updateDisabledDates);
    }
});
