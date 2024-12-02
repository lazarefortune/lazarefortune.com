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
customElements.define('progress-tracker', ProgressTracker)
customElements.define('ajax-delete', AjaxDelete)
customElements.define('loader-overlay', LoaderOverlay)
customElements.define('auto-submit', AutoSubmit, { extends: 'form' })
customElements.define('markdown-editor', MarkdownEditor, { extends: 'textarea' })

/* Modules */
import './modules/highlight.js'
import './modules/scrollreveal.js'
import './modules/modal.js'
import './modules/dropdown.js'
import './modules/password-toggle.js'

import { registerHeaderBehavior } from "./modules/app/header";
import { YoutubePlayer } from "./elements/player/YoutubePlayer";
import { TimeCountdown } from "./elements/TimeCountdown";
import { PlayButton } from "./elements/PlayButton";
import { AutoScroll } from "./elements/AutoScroll";
import { TimeAgo } from "./elements/TimeAgo";
import { PuzzleCaptcha } from "./elements/Captcha";
import { ProgressTracker } from "./elements/player/ProgressTracker";
import { AjaxDelete } from "./elements/AjaxDelete";
import LoaderOverlay from "./elements/LoaderOverlay";
import { AutoSubmit } from "./elements/AutoSubmit";
import { MarkdownEditor } from "./elements/editor";

registerHeaderBehavior()

// start the Stimulus application
// import './bootstrap';

import React from "react"
import ReactDOM from "react-dom/client"
import reactToWebComponent from "react-to-webcomponent"
import Comments from "./components/Comments";
import Quiz from "./components/Quiz/Quiz";

// const CommentElement = reactToWebComponent(Comment, React, ReactDOM)
const CommentsElement = reactToWebComponent(Comments, React, ReactDOM, {
    shadow: false, // Si vous ne voulez pas utiliser le Shadow DOM
    props: ['contentId'] // Liste des propriétés que le Custom Element doit observer
});

const QuizElement = reactToWebComponent(Quiz, React, ReactDOM, {
    shadow: false,
    props: ['contentId']
});

customElements.define('comments-area', CommentsElement)
customElements.define('quiz-area', QuizElement)
