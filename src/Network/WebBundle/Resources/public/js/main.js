$(document).ready(function() {
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
