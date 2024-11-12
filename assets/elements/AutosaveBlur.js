import SpinningDots from "@grafikart/spinning-dots-element";
import { jsonFetchOrFlash } from "../functions/api";

export class AutosaveBlur extends HTMLFormElement {
    connectedCallback() {
        this.querySelectorAll('input, textarea').forEach(input => {
            input.addEventListener('blur', this.save.bind(this))
        })
    }

    save() {
        const loader = new SpinningDots()
        this.style.position = 'relative'
        loader.style.position = 'absolute'
        loader.style.top = '8px'
        loader.style.right = '8px'
        loader.style.height = '16px'
        loader.style.width = '16px'
        loader.classList.add('text-primary-600')
        loader.classList.add('dark:text-primary-200')
        this.appendChild(loader)

        jsonFetchOrFlash(this.getAttribute('action') || '', {
            method: this.getAttribute('method'),
            body: new FormData(this)
        })
            .catch(console.error)
            .finally(() => {
                this.removeChild(loader)
            })
    }

    disconnectedCallback () {}
}