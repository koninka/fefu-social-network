$(function() {

    var currYear = (new Date).getFullYear();

    $.datepicker.setDefaults($.datepicker.regional['']);

    $('.date').datepicker({
        showAnim: 'slideDown',
        dateFormat: 'yy-mm-dd',
        changeMonth: true,
        changeYear: true,
        yearRange: String(currYear-120 + ':' + currYear)
    });

});
