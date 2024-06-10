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

import {ThemeSwitcher} from "./elements/ThemeSwitcher";

customElements.define('nav-tabs', NavTabs)
customElements.define('scroll-top', ScrollTop)
customElements.define('alert-message', Alert)
customElements.define('alert-floating', FloatingAlert)
customElements.define('modal-dialog', ModalDialog)
customElements.define('theme-switcher', ThemeSwitcher)
customElements.define('youtube-player', YoutubePlayer)
customElements.define('time-countdown', TimeCountdown)
customElements.define('time-ago', TimeAgo)
customElements.define('play-button', PlayButton)
customElements.define('auto-scroll', AutoScroll, { extends: 'div' })
customElements.define('puzzle-challenge', PuzzleCaptcha)
/* Modules */
import './modules/header.js'
import './modules/scrollreveal.js'
import './modules/modal.js'
import './modules/modal.js'
import './modules/header.js'
import './modules/hamburger.js'
import './modules/dropdown.js'
import {registerHeaderBehavior} from "./modules/header";
import {YoutubePlayer} from "./elements/player/YoutubePlayer";
import {TimeCountdown} from "./elements/TimeCountdown";
import {PlayButton} from "./elements/PlayButton";
import {AutoScroll} from "./elements/AutoScroll";
import {TimeAgo} from "./elements/TimeAgo";
import {PuzzleCaptcha} from "./elements/Captcha";

registerHeaderBehavior()

// start the Stimulus application
// import './bootstrap';

