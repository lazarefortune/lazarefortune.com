import './bootstrap.js';
import './scss/app.scss';

import {createIcons, icons} from 'lucide';

createIcons({icons});

/* Elements */
import {NavTabs, ScrollTop, ModalDialog} from 'headless-elements'
import {Alert, FloatingAlert} from './elements/Alert'
// import {AccordionGroup} from './elements/Accordion'
/* Libs */
import './libs/flatpickr'

customElements.define('nav-tabs', NavTabs)
customElements.define('scroll-top', ScrollTop)
customElements.define('alert-message', Alert)
customElements.define('alert-floating', FloatingAlert)
customElements.define('modal-dialog', ModalDialog)
customElements.define('theme-switcher', ThemeSwitcher)

/* Modules */
import './modules/header.js'
import './modules/scrollreveal.js'
import './modules/modal.js'
import {ThemeSwitcher} from "./elements/ThemeSwitcher";

// start the Stimulus application
// import './bootstrap';

