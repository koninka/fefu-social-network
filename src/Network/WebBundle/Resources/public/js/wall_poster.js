function addPost(user_id, thread_id, post_id, username, msg, ts)
{
    var postContainer = $('<div class="post" id="thread_' + thread_id + '"></div>');
    var usernameContainer = $('<div class="username"></div>');
    var tsContainer = $('<div class="timestamp"></div>');
    var msgContainer = $('<div class="msg"></div>');
    var controlsContainer = $('<div class="controls"></div>');
    var commentContainer = $('<div class="to_comment"><form><textarea class="comment_text"></textarea>' +
        '<button class="comment_btn" id="comment_' + thread_id + '">Comment!</button></form></div>');

    usernameContainer.append($(
        '<a href="' + Routing.generate('user_profile', {id: user_id}) + '">' + username + '</a>'
    ));

    tsContainer.text(ts);

    if (user_id === userId) {
        controlsContainer.append($(
            '<a href="' + post_id + '" class="edit_control">Edit</a>'
        ));
    }

    if (user_id === userId || userId === objectId) {
        controlsContainer.append($(
            '<a href="' + post_id + '" class="delete_control">Delete</a>'
        ));
    }

    msgContainer.text(msg);

    $([usernameContainer, tsContainer, controlsContainer, msgContainer, commentContainer])
        .each(function (_, element) {
            postContainer.append(element);
        });

    $('#posts').prepend(postContainer);
}

function addComment(user_id, thread_id, post_id, username, msg, ts) {
    var commentContainer = $('<div class="comment"></div>');
    var usernameContainer = $('<div class="username"></div>');
    var tsContainer = $('<div class="timestamp"></div>');
    var controlsContainer = $('<div class="controls"></div>');
    var msgContainer = $('<div class="msg"></div>');

    usernameContainer.append($(
        '<a href="' + Routing.generate('user_profile', {id: user_id}) + '">' + username + '</a>'
    ));

    tsContainer.text(ts);

    if (user_id === userId || userId === objectId) {
        controlsContainer.append($(
            '<a href="' + post_id + '" class="delete_control">Delete</a>'
        ));
    }

    msgContainer.text(msg);

    $([usernameContainer, tsContainer, controlsContainer, msgContainer])
        .each(function (_, element) {
            commentContainer.append(element);
        });

    var threadContainer = $('#thread_' + thread_id);
    var commentsContainer = threadContainer.find('.comments');

    if (commentsContainer.length == 0) {
        commentsContainer = $('<div class="comments"></div>');
        threadContainer.find('.msg').after(commentsContainer);
    }

    commentsContainer.append(commentContainer);
}

function createEditInterface(e)
{
    // place for dahin's code
}

function diff_less_than(date1, date2, min){
    return date1 - date2 < min * 60 * 1000;
}

function handleWriteResponse(data, textStatus, jqXHR)
{
    if (data['status'] === 'ok') {
        var msg = data['msg'];
        var user_id = data['user_id'];
        var username = data['username'];
        var post_id = data['post_id'];
        var thread_id = data['thread_id'];

        var ts = new Date(moment(data['ts'].date).format('YYYY/MM/DD HH:mm:ss') + ' UTC');
        var tsString;
        var now = new Date;
        if (diff_less_than(now, ts, 60 * 24)) {
            tsString = ts.toLocaleTimeString();
        } else {
            tsString = ts.toLocaleDateString();
        }

        if (data['new_thread']) {
            addPost(user_id, thread_id, post_id, username, msg, tsString);
        } else {
            addComment(user_id, thread_id, post_id, username, msg, tsString);
        }
    }
}

function handleDeleteResponse(data, textStatus, jqXHR)
{
    if (data['status'] === 'ok') {
        $('a.delete_control[href=' + data['id'] + ']')
            .parent()
            .parent()
            .remove();
    }
}

$(document).on('ready', function () {

    var wallContainer = $('#posts');

    wallContainer.on('click', 'a.edit_control', createEditInterface);

    wallContainer.on('click', 'a.delete_control', function (e) {
        e.preventDefault();

        var postId = +$(this).attr('href');

        $.post(
            Routing.generate(
                'wall_delete',
                {
                    id: objectId,
                    type: objectType,
                    post_id: postId
                }
            ),
            null,
            handleDeleteResponse
        )
    });

    wallContainer.on('click', '.comment_btn', function (e) {
        e.preventDefault();

        var commentText= $(this).parent().find('.comment_text');

        var msg = commentText.val();
        var threadId = +$(this).attr('id').match(/\d+$/)[0];

        commentText.val('');

        $.post(
            Routing.generate('wall_write', {
                id: objectId,
                type: objectType
            }),
            JSON.stringify({
                msg: msg,
                threadId: threadId
            }),
            handleWriteResponse
        );
    });

    $('#tell_btn').on('click', function (e) {
        e.preventDefault();
        
        var msg = $('#msg_input').val();

        if (msg.length === 0) {
            return;
        }

        $.post(
            Routing.generate(
                'wall_write',
                {
                    id: objectId,
                    type: objectType
                }
            ),
            JSON.stringify({
                msg: msg
            }),
            handleWriteResponse
        );
    });

});
