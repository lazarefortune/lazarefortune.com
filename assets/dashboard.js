import './bootstrap.js';
import './scss/dashboard.scss'

import {createIcons, icons} from 'lucide';

createIcons({icons});
/* Elements */
import './elements/index'
import '@grafikart/drop-files-element'
/* Libs */
import './libs/flatpickr'
import './libs/select2'
/* Modules */
import './modules/highlight.js'
import './modules/modal.js'
import './modules/dropdown.js'
import './modules/scrollreveal.js'
import './modules/dashboard/sidebar.js'
import './modules/dashboard/sidebar-dropdown.js'
/* ===== Pages ===== */
import './pages/index.js'

// start the Stimulus application
import './bootstrap'
