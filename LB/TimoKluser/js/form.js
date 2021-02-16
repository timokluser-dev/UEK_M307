/**
 * JS for component: `form`
 */

// initialize modal 
$('.modal').modal();

// initialize components
$('select').formSelect();
$('*[data-length]').characterCounter();
// date picker => Already in previous step
// $('.datepicker').datepicker({
//     format: 'yyyy-mm-dd'
// });

$('#modal-save').click(function(e) {
    e.preventDefault();

    // get id from input field
    var id = Number($('#field-id').val());

    // get form data
    var name = $('#field-name').val();
    var kaufdatum = $('#field-kaufdatum').val();
    var kaufpreis = $('#field-kaufpreis').val();
    var kategorie = $('#field-kategorie').val();
    var rating = $('#field-rating').val();

    // ! Validation
    var isValid = true;

    // requiredFieldIsValid(str: string, fieldname: string): boolean
    // -- OR --
    // fieldIsValid(str: string, fieldname: string): boolean

    if (!requiredFieldIsValid(name, 'Name')) isValid = false;
    if (!requiredFieldIsValid(kaufdatum, 'Kaufdatum')) isValid = false;
    if (!requiredFieldIsValid(kategorie, 'Kategorie')) isValid = false;

    // kaufpreis
    if (kaufpreis != '') {
        if (!(kaufpreis >= 0.0 && kaufpreis <= 999.99)) {
            isValid = false;
            showToast('Kaufpreis cannot has more that 3 CHF cells', 'error');
        }
    }


    // rating only full numbers
    // and <= 1 & >= 6
    if (rating != '') {
        if (rating <= 6 && rating >= 1) {
            // if decimal
            if (rating % 1 != 0) {
                isValid = false;
                showToast('Rating must be number without comma', 'error');
            }
        } else {
            isValid = false;
            showToast('Rating must be between 1 & 6', 'error');
        }
    }

    // date validation
    // user should first fill out field
    if (kaufdatum != '') {
        if (!(/^\d{4}-\d{2}-\d{2}$/.test(kaufdatum))) {
            isValid = false;
            showToast('Please check the Date format', 'error');
        }
    }

    // email validation
    // if (!fieldEmailIsValid(email)) isValid = false;

    // console.log('Form valid: ' + isValid);

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
                    'app_name': name,
                    'app_kaufdatum': kaufdatum,
                    'app_kaufpreis': kaufpreis,
                    'app_kategorie': kategorie,
                    'app_rating': rating
                },
                dataType: "json",
                success: function(response) {
                    // console.log(response);
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
                    'app_name': name,
                    'app_kaufdatum': kaufdatum,
                    'app_kaufpreis': kaufpreis,
                    'app_kategorie': kategorie,
                    'app_rating': rating
                },
                dataType: "json",
                success: function(response) {
                    // console.log(response);
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
    // console.log(fieldname + ":" + str);
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
    // console.log(fieldname + ":" + str);
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