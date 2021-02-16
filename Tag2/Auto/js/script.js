$(document).ready(function() {
    // modal 
    $('.modal').modal();

    // onclick events
    $('.data-table').load("sites/list.html", function() {
        console.log('INFO: data table loaded');
        $.getScript("js/list.js", function() {
            console.log('INFO: data table JS loaded');
        });
    });

    $('.mode-new').click(function(e) {
        e.preventDefault();
        $('#modal-title').html('New');

        $('form-data').load("sites/form.html", function() {
            console.log('INFO: form loaded')
            $.getScript("js/form.js", function() {
                console.log('INFO: form JS loaded');
                // open modal after all
                $('.modal').modal('open');
            });
        });

    });

});