Element.prototype._setAttribute = Element.prototype.setAttribute;

Element.prototype.setAttribute = function(attrName, newValue) {
    var event = document.createEvent("MutationEvents");
    var prevValue = this.getAttribute(attrName);
    this._setAttribute(attrName, newValue);
    event.initMutationEvent("DOMAttrModified", true, true, null, prevValue, newValue, attrName, 1);
    this.dispatchEvent(event);
}

var flag = false;
var input = '';

var objs = [
    {name: 'country', names: {'ru': 'Страна ', 'en': 'Country '}},
    {name: 'city', names: {'ru': 'Город ', 'en': 'City '}},
    {name: 'street', names: {'ru': 'Улица ', 'en': 'Street '}},
    {name: 'house', names: {'ru': 'Дом ', 'en': 'House '}}
];

var setNames = function(local) {
    for (var i = 0; i < objs.length; ++i) {
        var obj = $('input[id$="_'+objs[i].name+'"]');
        obj.parent().parent().find('label').text(objs[i].names[local]);
    }
}

var tryKladrOff = function() {
    if (input.value.toLowerCase() !== 'россия') {
        return false;
    }
}

var autocomplete = function() {
    var token = '51dfe5d42fb2b43e3300006e';
    var key = '86a2c2a06f1b2451a87d05512cc2c3edfdf41969';

    var city = $('input[id$="_city"]');
    var street = $('input[id$="_street"]');
    var building = $('input[id$="_house"]');

    city.kladr({
        token: token,
        key: key,
        type: $.kladr.type.city,
        openBefore: tryKladrOff,
        select: function(obj) {
            city.parent().parent().find('label').text(obj.type+' ');
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
        openBefore: tryKladrOff,
        select: function(obj) {
            street.parent().parent().find('label').text(obj.type+' ');
            building.kladr('parentType', $.kladr.type.street);
            building.kladr('parentId', obj.id);
        }
    });

    building.kladr({
        token: token,
        key: key,
        type: $.kladr.type.building,
        openBefore: tryKladrOff,
        select: function(obj) {
            building.parent().parent().find('label').text('Дом ');
        }
    });

};

$(document).on(
    'DOMAttrModified',
    'div[class$="modal fade in"]',
    function() {
        flag = false;
    }
);

$(document).on(
    'change',
    'input[id$="_country"]',
    function() {
        var pattern = /country$/;
        if (pattern.test(this.id)) {
            input = this;
        }
        if (input && input.value.toLowerCase() === 'россия') {
            if (!flag) {
                flag = true;
                setNames('ru');
            }
        } else {
            flag = false;
            setNames('en');
        }
        autocomplete();
    }
);
