/**
 * JS for component: `form-delete`
 */

// initialize modal
$('.modal').modal();

$('#modal-remove').click(function(e) {
    e.preventDefault();

    // get id to reference
    var id = Number($('#modal-delete').data('id'));

    $.ajax({
        type: "GET",
        url: "data/api.php?action=delete&id=" + id,
        data: "",
        dataType: "json",
        success: function(response) {
            console.log(response);

            // close modal
            var instance = M.Modal.getInstance($('#modal-delete'));
            instance.close();

            showResponseToasts(response);
            getData();
        }
    });
});