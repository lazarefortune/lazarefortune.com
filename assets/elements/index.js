import {NavTabs, ScrollTop, ModalDialog} from 'headless-elements'
import {Spotlight} from './admin/Spotlight'
import {Alert, FloatingAlert} from "./Alert";
import {AccordionGroup} from "./Accordion";
import {DropdownButton} from "./Dropdown";
import InputAttachment from "./admin/InputAttachment";
import {ChaptersEditor} from "./admin/ChaptersEditor";
import {ThemeSwitcher} from "./ThemeSwitcher";
import {FileManager} from 'filemanager-element'
import 'filemanager-element/FileManager.css'
import {YoutubePlayer} from "./player/YoutubePlayer";
FileManager.register();

customElements.define('spotlight-bar', Spotlight)
customElements.define('nav-tabs', NavTabs)
customElements.define('scroll-top', ScrollTop)
customElements.define('alert-message', Alert)
customElements.define('alert-floating', FloatingAlert)
customElements.define('modal-dialog', ModalDialog)
customElements.define('chapters-editor', ChaptersEditor, { extends: 'textarea' })
// customElements.define('accordion-group', AccordionGroup)
customElements.define('dropdown-button', DropdownButton)
customElements.define('input-attachment', InputAttachment, { extends: 'input' })
customElements.define('youtube-player', YoutubePlayer)
customElements.define('theme-switcher', ThemeSwitcher)

document.addEventListener('DOMContentLoaded', () => {
    const filemanager = document.querySelector("file-manager");
    if (!filemanager) return;
    filemanager.addEventListener("close", () => {
        console.log("close");
    });

    filemanager.addEventListener("selectfile", e => {
        console.log("fileSelected", e.detail);
    });
})


