import 'select2/dist/css/select2.min.css';
import 'select2/dist/js/select2.min.js';

import './select2.scss';

import $ from 'jquery';

$(document).ready(function () {
    $('.select2').select2();

    $('.select2-tags').select2({
        tags: true,
        tokenSeparators: [',', ' ']
    }).on('change', function (e) {
        let option = $(this).find("[data-select2-tag=true]:last-of-type")
        console.log(option.val());
        if (option.length && $.inArray(option.val(), $(this).val()) !== -1) {
            $.ajax({
                url: '/admin/api/tags/new/' + option.val(),
                method: 'POST',
            }).done(function (data) {
                console.log(data);
                option.replaceWith('<option value="' + data.id + '" selected>' + data.name + '</option>');
            });
        }
    });
});
