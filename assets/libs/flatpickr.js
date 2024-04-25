import {French} from 'flatpickr/dist/l10n/fr.js';
import flatpickr from "flatpickr";
import 'flatpickr/dist/themes/light.css';

flatpickr.localize(French);

const baseConfig = {
    disableMobile: true,
    locale: French,
    time_24hr: true,
};

const dateConfig = {
    ...baseConfig,
    altInput: true,
    altFormat: "j F Y",
    dateFormat: "Y-m-d",
};

const dateTimeConfig = {
    ...baseConfig,
    enableTime: true,
    noCalendar: false,
    dateFormat: "Y-m-d H:i",
    altInput: true,
    altFormat: "j F Y \\à H\\hi",
};

const timeConfig = {
    ...baseConfig,
    enableTime: true,
    noCalendar: true,
    dateFormat: "H:i",
    altInput: true,
    altFormat: "H \\h i",
};

const customConfigs = [
    // dates
    {selector: ".flatpickr-date-input", options: {...dateConfig}},
    {selector: ".flatpickr-date-input-after-today", options: {...dateConfig, minDate: "today"}},
    {
        selector: ".flatpickr-date-after-today-default-today",
        options: {...dateConfig, minDate: "today", defaultDate: "today"}
    },
    {
        selector: ".flatpickr-date-after-today-default-after-month",
        options: {...dateConfig, minDate: "today", defaultDate: new Date().fp_incr(30)}
    },
    {selector: ".flatpickr-date-birthday", options: {...dateConfig, maxDate: new Date().fp_incr(-16 * 365)}},
    {selector: ".flatpickr-date-realisation", options: {...dateConfig, minDate: new Date(2023, 0, 1)}},
    {selector: ".flatpickr-date-default-today", options: {...dateConfig, defaultDate: "today"}},
    {selector: ".flatpickr-date-default-tomorrow", options: {...dateConfig, defaultDate: "tomorrow"}},
    {selector: ".flatpickr-date-wrap", options: {...dateConfig, wrap: true}},

    // times
    {selector: ".flatpickr-time", options: {...timeConfig}},
    {selector: ".flatpickr-time-wrap", options: {...timeConfig, wrap: true}},
    {selector: ".flatpickr-time-input", options: {...timeConfig}},
    {selector: ".flatpickr-default-today", options: {...timeConfig, defaultDate: "today"}},
    {selector: ".flatpickr-service-startTime", options: {...timeConfig, defaultDate: "08:00"}},
    {selector: ".flatpickr-service-endTime", options: {...timeConfig, defaultDate: "19:00"}},

    // datetime
    {selector: ".flatpickr-datetime", options: {...dateTimeConfig}},
    {selector: ".flatpickr-datetime-after-today", options: {...dateTimeConfig, minDate: "today"}},
];

customConfigs.forEach(config => {
    flatpickr(config.selector, config.options);
});

