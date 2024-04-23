import {NavTabs, ScrollTop, ModalDialog} from 'headless-elements'
import {Spotlight} from './admin/Spotlight'
import {Alert, FloatingAlert} from "./Alert";
import {AccordionGroup} from "./Accordion";
import {DropdownButton} from "./Dropdown";

customElements.define('spotlight-bar', Spotlight)
customElements.define('nav-tabs', NavTabs)
customElements.define('scroll-top', ScrollTop)
customElements.define('alert-message', Alert)
customElements.define('alert-floating', FloatingAlert)
customElements.define('modal-dialog', ModalDialog)
// customElements.define('accordion-group', AccordionGroup)
customElements.define('dropdown-button', DropdownButton)
