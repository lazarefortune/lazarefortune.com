export class TableWrapper extends HTMLElement {
    constructor() {
        super();
    }

    connectedCallback() {
        const child = this.firstElementChild
        if (!child) {
            return
        }

        const wrapper = document.createElement('div');
        wrapper.classList.add('flex', 'flex-col', 'mb-10');
        wrapper.innerHTML = `
            <div class="-m-1.5 overflow-x-auto">
                <div class="p-1.5 min-w-full inline-block align-middle">
                    <div class="overflow-hidden"></div>
                </div>
            </div>
        `;

        const innerWrapper = wrapper.querySelector('.overflow-hidden');
        innerWrapper.appendChild(child);

        this.innerHTML = '';
        this.appendChild(wrapper);
    }
}