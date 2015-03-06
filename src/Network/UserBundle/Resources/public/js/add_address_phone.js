var phoneCount = 0;
var addressCount = 0;
var edit_ = '';
var delete_ = '';
var save_ = '';
var cancel_ = '';

function addAddress(country, city, street, house, Div, editBtnName, deleteBtnName) {
    edit_ = editBtnName;
    delete_ = deleteBtnName;
    var addressList = $('#address-fields-list');
    var address = country + ', г.' + city + ', ул.' + street + ', ' + house;
    var newLi = $('<div></div>').html(address);
    var linkEdit = $('<button id="editAddressButton" class="btn btn-blue btn-small" type="button">'+edit_+'</button>');
    var linkdelete = $('<button id="deleteAddressButton" class="btn btn-red btn-small" type="button">'+delete_+'</button>');
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

function valAddress (address, val) {
    for(var i = 0; i < 4; i++) {
        address[i] = val[i].value;
    }
}

function addButton(newDiv, save) {
    var address =  $('input', newDiv);
    var address_val = [];
    valAddress(address_val, address);
    var saveAddress = save;
    var newLi;
    var error = $('<div>Complete all fields</div>');
    error.css("display", "none");
    newDiv.append(error);
    var linkSave = $('<button class="btn btn-green btn-small" type="button">'+save_+'</button>');
    newDiv.append(linkSave);
    $(linkSave).click(function(e) {
        var bool;
        valAddress(address_val, address);
        for(var i = 0; i < 4; i++) {
            bool = address_val[i] === "";
        }
        if (bool) {
            error.css("display", "block");
        } else {
            newLi = addAddress(address_val[0]
                             , address_val[1]
                             , address_val[2]
                             , address_val[3]
                             , newDiv
                             , edit_
                             , delete_);
            saveAddress = true;
            error.css("display", "none");
            newDiv.css("display", "none");
        }
    });
    var linkCancel = $('<button class="btn btn-red btn-small" type="button">'+cancel_+'</button>');
    newDiv.append(linkCancel);
    $(linkCancel).click(function(e) {
        e.preventDefault();
        if (!saveAddress) {
            newDiv.remove();
        } else {
            newDiv.css("display", "none");
            if (newLi) {
                newLi.css("display", "block");
                for(var i = 0; i < 4; i++) {
                    address[i].value = address_val[i];
                }
            } else {
                valAddress(address_val, address);
                newLi = addAddress(address_val[0], address_val[1],
                address_val[2], address_val[3], newDiv);
            }
        }
    });
}

function addPhone(phone, delete_) {
    var phoneList = $('#phone-fields-list');
    var newLi = $('<div id="phone"></div>').html(phone);
    var div = $('div', newLi);
    var linkdelete = $('<button class="btn btn-red btn-small btn-phone-delete" type="button">'+delete_+'</button>');
    linkdelete.appendTo(div[1]);
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
    addButton(newDiv, true);
    newDiv.css("display", "none");
    addressCount++;
    newDiv.appendTo(addressList);
    
    return newDiv;
}

$(document).ready(function() {
    $('#add-another-address').click(function(e) {
        save_ = $(this).attr('saveBtnName');
        cancel_ = $(this).attr('cancelBtnName');
        var addressList = $('#address-fields-list');
        var newWidget = addressList.attr('data-prototype');
        newWidget = newWidget.replace(/__name__/g, addressCount);
        var newDiv = $('<div id="address"></div>').html(newWidget);
        addButton(newDiv);
        addressCount++;
        newDiv.appendTo(addressList);
    });
    $('#add-another-phone').click(function(e) {
        var btnName = $(this).attr('deleteBtnName');
        e.preventDefault();
        var phoneList = $('#phone-fields-list');
        var newWidget = phoneList.attr('data-prototype');
        newWidget = newWidget.replace(/__name__/g, phoneCount);
        addPhone(newWidget, btnName);
    });
});
