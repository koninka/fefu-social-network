
function createNotice(msg){
    new jBox('Notice', {
        content: msg
    });

}

function handleSendFriendResponse(data, textStatus, jqXHR){
    if(data['status'] == 'friendship_request_sent'){
        $('#send_request').hide();
        $('#unsubscribe').show();

        createNotice(msgSent);
    } else {
        if(data['status'] == 'friendship_accepted'){
            $('#send_request').hide();
            $('#delete_request').show();

            createNotice(msgAccepted);

        } else {
            createNotice(msgFail);
        }
    }
}

function handleDeleteFriendResponse(data, textStatus, jqXHR){
    if(data['status'] == 'friendship_deleted'){
        $('#delete_request').hide();
        $('#send_request').show();

        createNotice(msgFriendshipDeleted);
    } else {
        createNotice(msgNotAFriend);
    }
}

function handleUnsubscribeFriendResponse(data, textStatus, jqXHR){
    if(data['status'] == 'friendship_request_deleted') {
        $('#unsubscribe').hide();
        $('#send_request').show();

        createNotice(msgUnsubscribed);
    } else {
        createNotice(msgFail);
    }
}

$(document).ready(function() {

    switch(relStatus){
        case 'relationship_none':
        case 'friendship_subscribed_by_user':
            $('#send_request').show();
            break;
        case 'friendship_accepted':
            $('#delete_request').show();
            break;
        case 'friendship_subscribed_by_me':
            $('#unsubscribe').show();
    }

    $('#send_request').on('click', function(e){
        e.preventDefault();

        $.post(
            Routing.generate(
                'send_friendship_request',
                {
                    id: user
                }
            ),
            null,
            handleSendFriendResponse
        )
    });

    $('#delete_request').on('click', function(e){
        e.preventDefault();

        $.post(
            Routing.generate(
                'delete_friendship_request',
                {
                    id: user
                }
            ),
            null,
            handleDeleteFriendResponse
        )
    });

    $('#unsubscribe').on('click', function(e){
        e.preventDefault();

        $.post(
            Routing.generate(
                'unsubscribe_friendship_request',
                {
                    id: user
                }
            ),
            null,
            handleUnsubscribeFriendResponse
        )
    });

});
