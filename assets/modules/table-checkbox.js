document.addEventListener("DOMContentLoaded", function () {
    // Gestion des tables
    const tables = document.querySelectorAll('.table');

    tables.forEach(table => {
        // get input checkbox in thead and tbody and add event listener
        const theadCheckbox = table.querySelector('thead input[type="checkbox"]');
        const tbodyCheckboxes = table.querySelectorAll('tbody input[type="checkbox"]');
        const tbodyTrs = table.querySelectorAll('tbody tr');

        if (!theadCheckbox) {
            return;
        }

        if (tbodyCheckboxes.length === 0) {
            return;
        }
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

                theadCheckbox.checked = tbodyCheckboxes.length === tbodyCheckboxesChecked.length;
            });
        });
    });

});