import './bootstrap.js';
import './scss/app.scss';

import { createIcons, icons } from 'lucide';

createIcons({ icons });

/* Elements */
import { NavTabs, ScrollTop, ModalDialog } from 'headless-elements'
import { Alert, FloatingAlert } from './elements/Alert'
// import {AccordionGroup} from './elements/Accordion'
/* Libs */
import './libs/flatpickr'

import { ThemeSwitcher } from "./elements/ThemeSwitcher";

customElements.define('nav-tabs', NavTabs)
customElements.define('con-fetti', Confetti)
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
customElements.define('progress-tracker', ProgressTracker)
customElements.define('ajax-delete', AjaxDelete)
customElements.define('loader-overlay', LoaderOverlay)
customElements.define('auto-submit-form', AutoSubmitForm);
customElements.define('auto-submit', AutoSubmit, { extends: 'form' })
customElements.define('markdown-editor', MarkdownEditor, { extends: 'textarea' })

/* Modules */
import './modules/app/chapters'
import './modules/app/share-button'
import './modules/highlight.js'
import './modules/scrollreveal.js'
import './modules/modal.js'
import './modules/dropdown.js'
import './modules/password-toggle.js'
import './modules/carousel.js'
import { registerBadgeAlert } from "./modules/badges";

import { registerHeaderBehavior } from "./modules/app/header";
import { YoutubePlayer } from "./elements/player/YoutubePlayer";
import { TimeCountdown } from "./elements/TimeCountdown";
import { PlayButton } from "./elements/PlayButton";
import { AutoScroll } from "./elements/AutoScroll";
import { TimeAgo } from "./elements/TimeAgo";
import { ProgressTracker } from "./elements/player/ProgressTracker";
import { AjaxDelete } from "./elements/AjaxDelete";
import LoaderOverlay from "./elements/LoaderOverlay";
import { AutoSubmit } from "./elements/AutoSubmit";
import { AutoSubmitForm } from "./elements/AutoSubmitForm";
import { MarkdownEditor } from "./elements/editor";
import { Confetti } from "./elements/Confetti";

registerHeaderBehavior()
registerBadgeAlert()

// start the Stimulus application
// import './bootstrap';

import React from "react"
import ReactDOM from "react-dom/client"
import reactToWebComponent from "react-to-webcomponent"
import Comments from "./components/Comments";
import Quiz from "./components/Quiz/Quiz";
import PuzzleCaptcha from "./components/Captcha";
import { Notifications } from "./components/Notifications";
import { Search } from "./components/Search";
import { BadgeUnlock } from "./components/BadgeUnlock";
import { PremiumButton } from "./components/premium/PremiumButton";

const CommentsElement = reactToWebComponent(Comments, React, ReactDOM, {
    shadow: false, // pour ne pas utiliser le Shadow DOM
    props: ['contentId'] // Liste des propriétés que le Custom Element doit observer
});

const QuizElement = reactToWebComponent(Quiz, React, ReactDOM, {
    shadow: false,
    props: ['contentId', 'isUserLoggedIn']
});

const PuzzleCaptchaElement = reactToWebComponent(PuzzleCaptcha, React, ReactDOM, {
    shadow: false,
    props: ['width', 'height', 'pieceWidth', 'pieceHeight', 'src', 'answerInputName', 'challengeInputName']
});

const SearchElement = reactToWebComponent(Search, React, ReactDOM, {
    shadow: false,
    props: ['searchUrl', 'searchApi']
});

const NotificationsElement = reactToWebComponent(Notifications, React, ReactDOM, {
    shadow: false,
});

const BadgeUnlockElement = reactToWebComponent(BadgeUnlock, React, ReactDOM, {
    shadow: false,
    props: ['name', 'description', 'theme', 'image']
});


const PremiumButtonElement = reactToWebComponent(PremiumButton, React, ReactDOM, {
    shadow: false,
    props: ['plan', 'price', 'duration', 'stripeKey', 'paypalId', 'children'],
});

customElements.define('puzzle-challenge', PuzzleCaptchaElement)
customElements.define('comments-area', CommentsElement)
customElements.define('quiz-area', QuizElement)
customElements.define('search-button', SearchElement)
customElements.define('notifications-area', NotificationsElement)
customElements.define('badge-unlock', BadgeUnlockElement)
customElements.define('premium-button', PremiumButtonElement)
