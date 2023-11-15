import {createIcons, icons} from 'lucide';

createIcons({icons});

import '../components/_modal';
import '../components/_alert';

/* Libs */
import * as toast from '../../libs/toast-notifications/toast';

window.toast = toast;

document.addEventListener("DOMContentLoaded", function () {

    // Modales
    const modalButtons = document.querySelectorAll('.modal-button');
    const modalCloses = document.querySelectorAll('.modal-close');


    modalButtons.forEach(button => {
        button.addEventListener('click', function () {
            const modalID = button.getAttribute('data-modal-id');
            const modal = document.getElementById(modalID);
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        });
    });

    modalCloses.forEach(close => {
        close.addEventListener('click', function () {
            const modal = close.closest('.modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        });
    });

    const switchesBox = document.querySelectorAll('.form-switch');

    switchesBox.forEach(switchElementBox => {
        // get input element switchElementBox
        const switchElement = switchElementBox.querySelector('.form-switch-input');

        // get label element switchElementBox
        const label = switchElementBox.querySelector('.form-switch-label');
        const labelOn = switchElement.getAttribute('data-label-on');
        const labelOff = switchElement.getAttribute('data-label-off');

        // Initialisation du label au chargement de la page
        label.textContent = switchElement.checked ? labelOn : labelOff;

        // change check state
        switchElement.value = switchElement.checked ? 1 : 0;

        switchElement.addEventListener('change', function () {
                label.textContent = this.checked ? labelOn : labelOff;
                this.value = this.checked ? 1 : 0;
            }
        );
    });

    // Gestion des tables
    const tables = document.querySelectorAll('.table');

    tables.forEach(table => {
        // get input checkbox in thead and tbody and add event listener
        const theadCheckbox = table.querySelector('thead input[type="checkbox"]');
        const tbodyCheckboxes = table.querySelectorAll('tbody input[type="checkbox"]');
        const tbodyTrs = table.querySelectorAll('tbody tr');

        // foreach checkbox checked, add class table-active on tr
        tbodyCheckboxes.forEach(checkbox => {
            if (checkbox.checked) {
                checkbox.closest('tr').classList.add('table-active');
            }
        });

        // add event listener on thead checkbox
        theadCheckbox.addEventListener('change', function () {
            tbodyCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });

            tbodyTrs.forEach(tr => {
                if (this.checked) {
                    tr.classList.add('table-active');
                } else {
                    tr.classList.remove('table-active');
                }
            });
        });

        // add event listener on tbody checkbox
        tbodyCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function () {
                if (this.checked) {
                    this.closest('tr').classList.add('table-active');
                } else {
                    this.closest('tr').classList.remove('table-active');
                }
            });

            // active head checkbox if all tbody checkboxes are checked
            checkbox.addEventListener('change', function () {
                const tbodyCheckboxes = table.querySelectorAll('tbody input[type="checkbox"]');
                const tbodyCheckboxesChecked = table.querySelectorAll('tbody input[type="checkbox"]:checked');

                if (tbodyCheckboxes.length === tbodyCheckboxesChecked.length) {
                    theadCheckbox.checked = true;
                } else {
                    theadCheckbox.checked = false;
                }
            });
        });
    });


    //----  Gestion de l'affichage des prix enfants ---- //
    const priceChildrenSwitchBox = document.querySelector('#prestation_considerChildrenForPrice');
    const priceChildrenBox = document.querySelector('#form-children-price');

    if (priceChildrenSwitchBox && priceChildrenBox) {

        if (priceChildrenSwitchBox.checked) {
            priceChildrenBox.classList.remove('hidden');
        }

        priceChildrenSwitchBox.addEventListener('change', function () {
            if (this.checked) {
                priceChildrenBox.classList.remove('hidden');
            } else {
                priceChildrenBox.classList.add('hidden');
            }
        });

    }


});