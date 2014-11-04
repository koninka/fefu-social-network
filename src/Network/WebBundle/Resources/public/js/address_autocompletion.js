var autocomplete = function() {

    var token = '51dfe5d42fb2b43e3300006e';
    var key   = '86a2c2a06f1b2451a87d05512cc2c3edfdf41969';

    var city     = $('[id$="city"]');
    var street   = $('[id$="street"]');
    var building = $('[id$="house"]');

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

$(document).ready(function() {

    autocomplete();

    $('span[id$="idAddress"]').click(function(event) {
        setTimeout(function() {
            autocomplete();
        }, 2000)
    });

});