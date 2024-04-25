import 'select2/dist/css/select2.min.css';
import 'select2/dist/js/select2.min.js';

import $ from 'jquery';

$(document).ready(function () {
    // search element with the attribute data-remote and call the url and use the result as the data for the select2
    $('.select2').select2({
        // theme: 'bootstrap4',
        width: '100%',
        dropdownAutoWidth: true,
        // ajax: {
        //     url: function () {
        //         return $(this).data('remote');
        //     },
        //     dataType: 'json',
        //     delay: 250,
        //     data: function (params) {
        //         return {
        //             q: params.term,
        //         };
        //     },
        //     processResults: function (data) {
        //         console.log(data)
        //         return {
        //             results: data.map(item => ({
        //                 id: item.slug,
        //                 name: item.name
        //             }))
        //         };
        //     },
        // },
    });

    $('.select2-tags').select2({
        tags: true,
        tokenSeparators: [',', ' ']
    }).on('change', function (e) {
        let option = $(this).find("[data-select2-tag=true]:last-of-type")
        if (option.length && $.inArray(option.val(), $(this).val()) !== -1) {
            $.ajax({
                headers: {
                    'Accept': 'application/json',
                },
                contentType: 'application/json',
                url: ' /api/admin/tags/new',
                data: JSON.stringify({
                    name: option.val()
                }),
                method: 'POST',
            }).done(function (data) {
                option.replaceWith('<option value="' + data.id + '" selected>' + data.name + '</option>');
            });
        }
    });
});
