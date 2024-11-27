import 'select2/dist/css/select2.min.css';
import 'select2/dist/js/select2.min.js';

import $ from 'jquery';

$(document).ready(function () {
    // search element with the attribute data-remote and call the url and use the result as the data for the select2
    $('.select2').select2({
        // width: '100%',
        // dropdownAutoWidth: true,
    });
});
