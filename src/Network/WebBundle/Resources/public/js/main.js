$(document).ready(function() {

    loadNotification();

    $.post('http://' + window.location.hostname + "/api/get_request", function(data) {
        userRequests = data;
        redrawMessageRequest();
    });
});

function addMessageRequest(newThreadId) {
    console.log('add ', newThreadId, 'to', userRequests);
    if (userRequests.threadsId.indexOf(newThreadId) > -1) {
        return false;
    }
    userRequests.threadsId.push(newThreadId);
    return true;
}

function removeMessageRequest(newThreadId) {
    var index =  userRequests.threadsId.indexOf(newThreadId);
    if (index < 0) {
        return false;
    }
    userRequests.threadsId.splice(index, 1);
    return true;
}

function redrawMessageRequest() {
    var unreadCountLabel = $('#msg-unread-count');
    var length = userRequests.threadsId.length;
    unreadCountLabel.text(length);
    if (length > 0) {
        unreadCountLabel.show();
    } else {
        unreadCountLabel.hide();
    }
}


function printNotification(item, i, arr){

    var myRef = $('<a/>', {
        href: '/' + 'id' + item['id'],
        text: 	item['name']
    });
    $('.not_msg').append(myRef);
    $('.not_msg').append('<br>');
}

function handleNotificationResponse(data, status) {
    if(status == 'success'){
        localStorage.setItem('notification', JSON.stringify(data));
        localStorage.setItem('time', JSON.stringify(new Date()));
    }

    $('.today').hide();
    $('.tomorrow').hide();
    if(data['today'].length > 0){
        $('#notification').show();
        $('.have_birth').show();
        $('.today').show();
        var users = data['today'];
        users.forEach(printNotification);
    } else {
        if(data['tomorrow'] > 0){
            $('#notification').show()
            $('.have_birth').show();
            $('.tomorrow').show();
            var users = data['tomorrow'];
            users.forEach(printNotification);
        } else {
            $('#notification').hide();
        }
    }
}

function loadNotification(){
    var not = localStorage.getItem('notification');
    if(not != null) {
        var time = JSON.parse(localStorage.getItem('time'));
        var lastTime = new Date(time).getHours();
        var now = new Date().getHours();
        if(!(now < lastTime || (now - lastTime) > 1)){
            handleNotificationResponse(JSON.parse(not));
            return;
        }
    }

    $.post(
        Routing.generate(
             'notification',
             {}
        ),
        null,
        handleNotificationResponse
    );

}
