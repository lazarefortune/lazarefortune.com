import {NavTabs, ScrollTop, ModalDialog} from 'headless-elements'
import {Spotlight} from './admin/Spotlight'
import InputAttachment from "./admin/InputAttachment";
import {ChaptersEditor} from "./admin/ChaptersEditor";
import {Alert, FloatingAlert} from "./Alert";
import {DropdownButton} from "./Dropdown";
import {ThemeSwitcher} from "./ThemeSwitcher";
import {YoutubePlayer} from "./player/YoutubePlayer";
import {AjaxDelete} from "./AjaxDelete";
import LoaderOverlay from "./LoaderOverlay";
import SpinningDots from "@grafikart/spinning-dots-element";
import { LineChart } from "./admin/LineChart";
import {TimeAgo} from "./TimeAgo";
import { MarkdownEditor } from "./editor";
import { AutosaveBlur } from "./AutosaveBlur";
import { FileManager } from "./admin/FileManager";
import { TimeCountdown } from "./TimeCountdown";
import { Notifications } from "../components/Notifications";
import "./admin/QuestionEditor";
import reactToWebComponent from "react-to-webcomponent";
import React from "react";
import ReactDOM from "react-dom/client";
import { registerBadgeAlert } from "../modules/badges";
import { AutoSubmitForm } from "./AutoSubmitForm";
import { AutoSubmit } from "./AutoSubmit";
import { Confetti } from "./Confetti";
import { BadgeUnlock } from "../components/BadgeUnlock";
import { SlugGenerator } from "./SlugGenerator";
import DropZone from "../components/DropZone";
import { PhoneInput } from "./PhoneInput";

const NotificationsElement = reactToWebComponent(Notifications, React, ReactDOM, {
    shadow: false,
});

const BadgeUnlockElement = reactToWebComponent(BadgeUnlock, React, ReactDOM, {
    shadow: false,
    props: ['name', 'description', 'theme', 'image']
});

const DropZoneElement = reactToWebComponent(DropZone, React, ReactDOM, {
    shadow: false,
    props: ['name'],
});

customElements.define('drop-zone', DropZoneElement);


registerBadgeAlert()

customElements.define('badge-unlock', BadgeUnlockElement)
customElements.define('con-fetti', Confetti)
customElements.define('spotlight-bar', Spotlight)
customElements.define('nav-tabs', NavTabs)
customElements.define('scroll-top', ScrollTop)
customElements.define('alert-message', Alert)
customElements.define('alert-floating', FloatingAlert)
customElements.define('modal-dialog', ModalDialog)
customElements.define('chapters-editor', ChaptersEditor)
customElements.define('dropdown-button', DropdownButton)
customElements.define('input-attachment', InputAttachment, { extends: 'input' })
customElements.define('youtube-player', YoutubePlayer)
customElements.define('theme-switcher', ThemeSwitcher)
customElements.define('ajax-delete', AjaxDelete)
customElements.define('loader-overlay', LoaderOverlay)
customElements.define('spinning-dots', SpinningDots)
customElements.define('time-countdown', TimeCountdown)
customElements.define('line-chart', LineChart)
customElements.define('autosave-blur', AutosaveBlur, {extends: 'form'})
customElements.define('time-ago', TimeAgo)
customElements.define('markdown-editor', MarkdownEditor, { extends: 'textarea' })
customElements.define('file-manager', FileManager);
customElements.define('notifications-area', NotificationsElement)
customElements.define('auto-submit-form', AutoSubmitForm);
customElements.define('auto-submit', AutoSubmit, { extends: 'form' })
customElements.define("slug-generator", SlugGenerator);
customElements.define('phone-input', PhoneInput);
