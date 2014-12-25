var phoneCount = 0;
var addressCount = 0;

function addAddress(country, city, street, house, Div) {
    var addressList = $('#address-fields-list');
    var address = country + ', г.' + city + ', ул.' + street + ', ' + house;
    var newLi = $('<div></div>').html(address);
    var linkEdit = $('<button type="button">edit</button>');
    var linkdelete = $('<button type="button">X</button>');
    newLi.append(linkEdit);
    newLi.append(linkdelete);
    $(linkEdit).click(function(e) {
        Div.css("display", "block");
        newLi.css("display", "none");
    });
    $(linkdelete).click(function(e) {
        e.preventDefault();
        newLi.remove();
        Div.remove();
    });
    newLi.appendTo(addressList);
    
    return newLi;
}

function addButton(newDiv, save) {
    var div =  $('*', newDiv);
    var address = [];
    var j = 1;
    for(var i = 3; i < 16; i += 4) {
        address[j++] = div[i];
    }
    var saveAddress = save;
    var newLi;
    var error = $('<div>Complete all fields</div>');
    error.css("display", "none");
    newDiv.append(error);
    var linkSave = $('<button type="button">save</button>');
    newDiv.append(linkSave);
    $(linkSave).click(function(e) {
        var bool;
        for(var i = 1; i < 5; i++) {
            bool = address[i].value ==="";
        }
        if (bool) {
            error.css("display", "block");
        } else {
            newLi = addAddress(address[1].value, address[2].value, 
            address[3].value, address[4].value, newDiv);
            saveAddress = true;
            error.css("display", "none");
            newDiv.css("display", "none");
        }
    });
    var linkCancel = $('<button type="button">cancel</button>');
    newDiv.append(linkCancel);
    $(linkCancel).click(function(e) {
        e.preventDefault();
        if (!saveAddress) {
            newDiv.remove();
        } else {
            newDiv.css("display", "none");
            if (newLi) {
                newLi.css("display", "block");
            } else {
                newLi = addAddress(address[1].value, address[2].value, 
                address[3].value, address[4].value, newDiv);
            }
        }
    });
}

function addPhone(phone) {
    var phoneList = $('#phone-fields-list');
    var newLi = $('<div id = "phone"></div>').html(phone);
    var linkdelete = $('<button type="button">X</button>');
    newLi.append(linkdelete);
    $(linkdelete).click(function(e) {
        e.preventDefault();
        newLi.remove();
    });
    phoneCount++;
    newLi.appendTo(phoneList);
}

function createDiv(address) {
    var addressList = $('#address-fields-list');
    var newDiv = $('<div id="address"></div>').html(address);
    addButton (newDiv, true);
    newDiv.css("display", "none");
    addressCount++;
    newDiv.appendTo(addressList);
    
    return newDiv;
} 

$(document).ready(function() {
    $('#add-another-address').click(function(e) {
        var addressList = $('#address-fields-list');
        var newWidget = addressList.attr('data-prototype');
        newWidget = newWidget.replace(/__name__/g, addressCount);
        var newDiv = $('<div id="address"></div>').html(newWidget);
        addButton (newDiv);
        addressCount++;
        newDiv.appendTo(addressList);
    });
    $('#add-another-phone').click(function(e) {
        e.preventDefault();
        var phoneList = $('#phone-fields-list');
        var newWidget = phoneList.attr('data-prototype');
        newWidget = newWidget.replace(/__name__/g, phoneCount);
        addPhone(newWidget);
    }); 
});
