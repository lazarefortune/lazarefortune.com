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
import "./admin/QuestionEditor";

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

