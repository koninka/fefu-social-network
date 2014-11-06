$(function() {
    $.datepicker.setDefaults($.datepicker.regional['']);
    $('.date').datepicker({showAnim: 'slideDown', dateFormat: 'dd-mm-yy'});
});
