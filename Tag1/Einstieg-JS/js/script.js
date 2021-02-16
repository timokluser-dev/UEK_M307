// function myNewFunction(txt) {
//     console.log('hello: ' + txt);
// }

// myNewFunction('timo');

// arr = ['a', 'b', 'c'];

// arr.forEach(element => {
//     console.log(element);
// });

// ! shortcut = jqDocReady
$(document).ready(function() {

    // ! shortcut = jqClick
    $('#add-btn').click(function(e) {
        e.preventDefault();

        $('#boxed-html').html('<h1>HELLO WORLD</h1>');

        $(this).removeClass('teal lighten-3');
        $(this).addClass('yellow accent-2');
    });

    // ! shortcut = jqLoad
    $('main').load("sites/list.html", function() {
        console.log('content loaded');

        // ! shortcut = jqScript
        $.getScript("sites/list.js", function() {});

    });

});