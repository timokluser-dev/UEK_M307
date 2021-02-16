/**
 * JS for component: `form`
 */

// initialize modal 
$('.modal').modal();

// initialize components
$('select').formSelect();
$('*[data-length]').characterCounter();
M.Range.init($('*[type=range]'));

$('#modal-save').click(function(e) {
    e.preventDefault();

    // get id from input field
    var id = Number($('#field-id').val());

    // get form data
    var name = $('#field-name').val();
    var bauart = $('#field-bauart').val();
    var kraftstoff = $('#field-kraftstoff').val();
    var color = $('#field-farbe').val();
    var tank = $('#field-tank').val();
    var age = $('#field-age').val();
    var email = $('#field-email').val();

    // ! Validation
    var isValid = true;

    // requiredFieldIsValid(str: string, fieldname: string): boolean
    // -- OR --
    // fieldIsValid(str: string, fieldname: string): boolean

    if (!requiredFieldIsValid(name, 'Name')) isValid = false;
    if (!requiredFieldIsValid(email, 'Email')) isValid = false;
    if (!requiredFieldIsValid(kraftstoff, 'Kraftstoff')) isValid = false;
    if (!requiredFieldIsValid(bauart, 'Bauart')) isValid = false;

    // email validation
    if (!fieldEmailIsValid(email)) isValid = false;

    console.log('Form valid: ' + isValid);

    if (isValid) {
        // close modal here to avoid double submit
        var instance = M.Modal.getInstance($('#modal'));
        instance.close();

        // check for id and update & create
        if (id > 0) {
            // * Update
            $.ajax({
                type: "POST",
                url: "data/api.php?action=update&id=" + id,
                data: {
                    'name': name,
                    'bauart': bauart,
                    'kraftstoff': kraftstoff,
                    'color': color,
                    'tank': tank,
                    'email': email
                },
                dataType: "json",
                success: function(response) {
                    console.log(response);
                    showResponseToasts(response);
                    getData();
                }
            });
        } else {
            // * Create
            $.ajax({
                type: "POST",
                url: "data/api.php?action=create",
                data: {
                    'name': name,
                    'bauart': bauart,
                    'kraftstoff': kraftstoff,
                    'color': color,
                    'tank': tank,
                    'email': email
                },
                dataType: "json",
                success: function(response) {
                    console.log(response);
                    showResponseToasts(response);
                    getData();
                }
            });
        }
    }
});

// checks the field if it contains content
// also checks if overflow
function requiredFieldIsValid(str, fieldname) {
    str = String(str);
    str = str.trim(); // trim white spaces
    console.log(fieldname + ":" + str);
    if (!(str.length > 0 && str.length <= 255 && str != 'null')) {
        showToast('Please check the field ' + fieldname, 'error');
    }
    return (str.length > 0 && str.length <= 255 && str != 'null');
}

// only checks if overload
// for non required fields
function fieldIsValid(str, fieldname) {
    str = String(str);
    str = str.trim(); // trim white spaces
    console.log(fieldname + ":" + str);
    if (!(str.length <= 255)) {
        showToast('Please check the field ' + fieldname, 'error');
    }
    return (str.length <= 255);
}

// type email validation
// basic frontend email validation
function fieldEmailIsValid(str) {
    str = String(str);
    if (!(/^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/.test(str))) {
        showToast('Please check the E-Mail format', 'error');
    }
    return ((/^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/.test(str)));
}