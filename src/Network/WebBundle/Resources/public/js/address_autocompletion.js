var kladrOff = function() {
            var city = $('input[id$="city"]');
            var street = $('input[id$="street"]');
            var building = $('input[id$="house"]');

            var new_city = $("<input/>", {
                id: city.attr('id'),
                class: city.attr('class'),
                type: city.attr('type'),
                maxlength: city.attr('maxlength'),
                required: city.attr('required'),
                name: city.attr('name'),
            }).val(city.val());

            var new_street = $("<input/>", {
                id: street.attr('id'),
                class: street.attr('class'),
                type: street.attr('type'),
                maxlength: street.attr('maxlength'),
                required: street.attr('required'),
                name: street.attr('name'),
            }).val(street.val());

            var new_building = $("<input/>", {
                id: building.attr('id'),
                class: building.attr('class'),
                type: building.attr('type'),
                maxlength: building.attr('maxlength'),
                required: building.attr('required'),
                name: building.attr('name'),
            }).val(building.val());

            city.replaceWith(new_city);
            street.replaceWith(new_street);
            building.replaceWith(new_building);
}
var autocomplete = function() {

    var token = '51dfe5d42fb2b43e3300006e';
    var key = '86a2c2a06f1b2451a87d05512cc2c3edfdf41969';

    var city = $('input[id$="city"]');
    var street = $('input[id$="street"]');
    var building = $('input[id$="house"]');

    city.kladr({
        token: token,
        key: key,
        type: $.kladr.type.city,
        select: function(obj) {
            street.kladr('parentType', $.kladr.type.city);
            street.kladr('parentId', obj.id);
            building.kladr('parentType', $.kladr.type.city);
            building.kladr('parentId', obj.id);
        }
    });

    street.kladr({
        token: token,
        key: key,
        type: $.kladr.type.street,
        select: function(obj) {
            building.kladr('parentType', $.kladr.type.street);
            building.kladr('parentId', obj.id);
        }
    });

    building.kladr({
        token: token,
        key: key,
        type: $.kladr.type.building
    });

};
$(document).on(
    'change',
    function() {
        var country = $('input[id$="country"]').val().toLowerCase();

        if (country === 'россия') {
            autocomplete();

        } else {
            kladrOff();
        }
    }
);
