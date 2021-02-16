/**
 * JS for component: `list`
 */

// initial limit of table
$('data-table').data('limit', 10);

// initial data fetch
getData();

// !: initialize tablesorter
// needs to be here because of `https://stackoverflow.com/a/10252215`
$("table.data-table").tablesorter();

// sort btns
$('button.sort').click(function(e) {
    e.preventDefault();
    var limit = Number($(this).data('value'));
    // console.log('limit table: ' + limit);

    // persistent limit even after reload of table
    $('data-table').data('limit', limit);

    getData(limit);
});

function getData() {
    $.ajax({
        type: "GET",
        url: "data/api.php?action=get&limit=0", // limit=0 => equals all
        data: "",
        dataType: "json",
        success: function(data) {
            // template for new records
            var template = $('#record-template').html();
            // render html with data
            var html = Mustache.render(template, data);
            // assign to table
            $('#table-body').html(html);

            // !: Plugins for Table here
            // !: EITHER PAGINATION OR TABLESORTER

            // reset the existing pagination
            $('#ul-pagination').html('');

            // pagination
            $('#table-body').pageMe({
                pagerSelector: '#ul-pagination',
                activeColor: 'teal lighten-2',
                prevText: 'Previous',
                nextText: 'Next',
                showPrevNext: true,
                hidePageNumbers: false,
                perPage: Number($('data-table').data('limit'))
            });

            // tablesorter trigger update
            $("table.data-table").trigger('update');

            // show possible errors
            showResponseToasts(data);

            // !: After load add events
            $('.mode-edit').click(function(e) {
                e.preventDefault();
                let id = $(this).parent().data('id');
                // console.log('MODE: edit - ID: ' + id);

                showModal(id);
            });

            $('.mode-delete').click(function(e) {
                e.preventDefault();
                let id = $(this).parent().data('id');
                // console.log('MODE: delete - ID: ' + id);

                showDeleteModal(id);
            });
            // !: End event register

        }
    });

}