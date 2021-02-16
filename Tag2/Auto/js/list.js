/**
 * JS for component: `list table`
 */

// !: shortcut = jqGetJson
$.getJSON('data/data.json',
    function(data) {
        console.log(data);

        // *: Message handling
        if (data['error'].length > 0) {
            data['error'].forEach(element => {
                // show toast
                M.toast({ html: element['message'], classes: 'red' });
            });
        }
        if (data['success'].length > 0) {
            data['success'].forEach(element => {
                // show toast
                M.toast({ html: element['message'], classes: 'green' });
            });
        }
        // *: END Message handling


        // mustache template
        let template = $('#row-template').html();
        // mustache render the html
        let html = Mustache.render(template, data);

        $('tbody').html(html); // full replace of content

        $('tbody').append(html); // add to existing content

        // !: REGISTER ALL EVENTS
        $('.mode-tanken').click(function(e) {
            e.preventDefault();
            let id = $(this).parent().data('id');
            console.log('MODE: tanken - ID: ' + id);
        });

        $('.mode-edit').click(function(e) {
            e.preventDefault();
            let id = $(this).parent().data('id');
            console.log('MODE: edit - ID: ' + id);

            // set modal html
            $('#modal-title').html('Edit - ID: ' + $(this).parent().data('id')); // * Get parent and read attr `data-id` => data('id')

            $('form-data').load("sites/form.html", function() {
                console.log('INFO: form loaded')
                $.getScript("js/form.js", function() {
                    console.log('INFO: form JS loaded');
                    // open modal after all
                    $('.modal').modal('open');
                });
            });
        });

        $('.mode-delete').click(function(e) {
            e.preventDefault();
            let id = $(this).parent().data('id');
            console.log('MODE: delete - ID: ' + id);

            // set modal html
            $('#modal-title').html('Delete - ID: ' + $(this).parent().data('id')); // * Get parent and read attr `data-id` => data('id')

            $('form-data').load("sites/form.html", function() {
                console.log('INFO: form loaded')
                $.getScript("js/form.js", function() {
                    console.log('INFO: form JS loaded');
                    // open modal after all
                    $('.modal').modal('open');
                });
            });
        });
        // !: END REGISTER EVENTS
    }
);