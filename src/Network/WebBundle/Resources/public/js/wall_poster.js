var postsCount = 0;
var allPostsLoaded = false;
var lazyLoadRequestSent = false;
var onlyMyDisplayed = false;

function createPost(user_id, thread_id, post_id, username, msg, ts)
{
    var postContainer = $('<div class="post" id="thread_' + thread_id + '"></div>');
    if (user_id == userId) {
        $(postContainer).addClass('my');
    }

    var usernameContainer = $('<div class="username"></div>');
    var tsContainer = $('<div class="timestamp"></div>');
    var msgContainer = $('<div class="msg"></div>');
    var controlsContainer = $('<div class="controls"></div>');
    var commentsContainer = $('<div class="comments"></div>');
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

    $([
        usernameContainer,
        tsContainer,
        controlsContainer,
        msgContainer,
        commentsContainer,
        commentContainer
    ]).each(function (_, element) {
        postContainer.append(element);
    });

    return postContainer;
}

function addPost(user_id, thread_id, post_id, username, msg, ts)
{
    $('#posts').prepend(createPost(user_id, thread_id, post_id, username, msg, ts));
}

function createComment(user_id, thread_id, post_id, username, msg, ts)
{
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

    return commentContainer;
}

function addComment(user_id, thread_id, post_id, username, msg, ts)
{
    var threadContainer = $('#thread_' + thread_id);
    var commentsContainer = threadContainer.find('.comments');

    commentsContainer.append(createComment(user_id, thread_id, post_id, username, msg, ts));
}

function createEditInterface(e)
{
    // place for dahin's code
}

function diff_less_than(date1, date2, min){
    return date1 - date2 < min * 60 * 1000;
}

function fixDate(timestamp)
{
    var ts = new Date(moment(timestamp.date).format('YYYY/MM/DD HH:mm:ss') + ' UTC');
    var tsString;
    var now = new Date;
    if (diff_less_than(now, ts, 60 * 24)) {
        tsString = ts.toLocaleTimeString();
    } else {
        tsString = ts.toLocaleDateString();
    }

    return tsString;
}

function handleWriteResponse(data, textStatus, jqXHR)
{
    if (data['status'] === 'ok') {
        var msg = data['msg'];
        var user_id = data['user_id'];
        var username = data['username'];
        var post_id = data['post_id'];
        var thread_id = data['thread_id'];
        var tsString = fixDate(data['ts']);

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

function handleLoadPostsResponse(data, textStatus, jqXHR)
{
    lazyLoadRequestSent = false;

    if (allPostsLoaded) {
        return ;
    }

    if (data['status'] === 'ok') {
        for (var j = 0; j < data['threads'].length; ++j) {
            var thread = data['threads'][j];
            var postData = thread.posts[0];
            var post = createPost(
                postData['user_id'],
                thread.id,
                postData['post_id'],
                postData['username'],
                postData['msg'],
                fixDate(postData['ts'])
            );

            var commentsContainer = post.find('.comments');

            for (var i = 1; i < thread.posts.length; ++i) {
                var comment = thread.posts[i];

                commentsContainer.append(
                    createComment(
                        comment['user_id'],
                        thread.id,
                        comment['post_id'],
                        comment['username'],
                        comment['msg'],
                        fixDate(comment['ts'])
                    )
                );
            }

            $('#posts').append(post);
        }

        postsCount += data['threads'].length;
    } else if (data['status'] === 'nothingMore') {
        allPostsLoaded = true;
    }
}

$(document).on('ready', function () {

    postsCount = $('.post').length;

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

        var input = $('#msg_input');

        var msg = input.val();

        input.val('');

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

    $(window).scroll(function(e) {
        if($(window).scrollTop() + $(window).height() >= $(document).height() - 10) {
            if (!allPostsLoaded && !lazyLoadRequestSent) {
                $.post(
                    Routing.generate(
                        'wall_load_posts',
                        {
                            id: objectId,
                            type: objectType,
                            start: postsCount
                        }
                    ),
                    null,
                    handleLoadPostsResponse
                );

                lazyLoadRequestSent = true;
            }
        }
    });

    $('#show_my_btn').click(function (e) {
        e.preventDefault();

        onlyMyDisplayed = !onlyMyDisplayed;

        $('.post').each(function (_, el) {
            if (!$(this).hasClass('my')) {
                if (onlyMyDisplayed) {
                    $(this).hide();
                } else {
                    $(this).show()
                }
            }
        });

        if (onlyMyDisplayed) {
            $(this).text('All');
        } else {
            $(this).text('Only my');
        }
    });

});
