function initDatePickers(root) {

    var currYear = (new Date).getFullYear();

    $.datepicker.setDefaults($.datepicker.regional['']);

    var datepickerOptions = {
        showAnim: 'slideDown',
        dateFormat: 'yy-mm-dd',
        changeMonth: true,
        changeYear: true,
        yearRange: String(currYear-120 + ':' + currYear)
    };

    $(root)
        .find('input[class~="datepicker"]')
        .each(function(){
            $(this).datepicker(datepickerOptions);
        });
}

$(document).on('ready', function() {
    initDatePickers(this);
});
