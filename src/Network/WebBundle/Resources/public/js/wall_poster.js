var postsCount = 0;
var allPostsLoaded = false;
var lazyLoadRequestSent = false;
var onlyMyDisplayed = false;


function createPost(user_id, thread_id, post_id, username, msg, ts, is_poll)
{
    var postContainer = $('<div class="post" id="thread_' + thread_id + '"></div>');
    if (user_id == userId) {
        $(postContainer).addClass('my');
    }

    var usernameContainer = $('<div class="username"></div>');
    var tsContainer = $('<div class="timestamp"></div>');
    var msgContainer = $('<div id="msg" class="msg"></div>');
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
    msgContainer.append(msg);
    if (is_poll) {
        getPoll(JSON.stringify({
            postId: post_id}))
    }

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

function addPost(user_id, thread_id, post_id, username, msg, ts, is_poll)
{
    $('#posts').prepend(createPost(user_id, thread_id, post_id, username, msg, ts, is_poll));
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
        $('#msg_input').val('');
        var msg = data['msg'];
        var user_id = data['user_id'];
        var username = data['username'];
        var post_id = data['post_id'];
        var thread_id = data['thread_id'];
        var tsString = fixDate(data['ts']);

        var is_poll = data['is_poll'];

        if (data['new_thread']) {
            addPost(user_id, thread_id, post_id, username, msg, tsString, is_poll);
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
                fixDate(postData['ts']), 
                postData['is_poll']
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

function handleWritePoll(data)
{
    if (data['status'] === 'ok') {
        return addPoll(data);
    }
    
}

function writePoll(pollId) 
{
    var msg = $('#msg_input').val();
    if (msg.length === 0) {
        msg = JSON.stringify({
            poll: pollId,
        });
    } else {
        msg = JSON.stringify({
            poll: pollId,
            msg: msg
        });
    }
    $.post(
        Routing.generate(
            'wall_write',
            {
                id: objectId,
                type: objectType
            }
        ),
        msg,
        handleWriteResponse
    );
}

function getPoll(msg) 
{
    return $.post(
        Routing.generate('user_poll', {
            id: userId,
        }),
        msg,
        handleWritePoll
    );
}

function addPoll(data) {
    var isOwner = data['isOwner'];
    var sum = data['sum'];
    var answer = data['answer'];
    var percent = data['percent'];
    var isAnswer = data['isAnswer'];
    var thread_id = data['thread_id'];
    var isAnonymously = data['isAnonymously'];
    var id = data['id'];
    var threadContainer = $('#thread_' + thread_id);
    var msgContainer = threadContainer.find('.msg');
    var pollDiv = $('#poll_post').clone();
    msgContainer.find('#poll_post').hide(); 
    pollDiv.html('');
        if (isOwner) {
            if (sum == 0) {
                pollDiv.append($('<div></div>').append($('<a>poll.edit</a>').attr('href', '/poll' + id + '/edit')));
            }
            pollDiv.append($('<div></div>').append($('<a>poll.delete</a>').attr('href', '/poll' + id + '/delete')));
        }
        pollDiv.append(data['question']);
        pollDiv.append('<br>');
        if (isAnonymously) {
            pollDiv.append('poll.anonymously');
        } else {
            pollDiv.append('poll.open');
        }
        pollDiv.append('<div>poll.sum: ' + sum + '</div>');
        pollDiv.append('<hr>');
    if (!isAnswer) {
        var form = $('<form class="form" method="post"></form>');
        for (var i = 0; i < answer.length; i++ ) {
            var ans = $('<div></div>').append('<input type="radio" name="answer"  value="' + answer[i][0] + '"/>\n\
                <label for="'+ answer[i][0]+'">'+ answer[i][1]+'</label>');
                form.append(ans);
            }
        var submit = $('<input class="blue_button" type="button" value="poll.submit"></input>');
        form.append(submit);
        $(submit).on('click', function (e) {
            var value = $("input[name=answer]:checked").val();
            $.post(
                    Routing.generate('user_poll', {
                        id: id,
                    }), 
                    JSON.stringify({
                         answer: value,
                         pollId: id,
                         threadId: thread_id
                    }),
                    handleWritePoll
                );
            });
            pollDiv.append(form);
    } else {
        msgContainer.find('.form').hide();
        var table = $('<table></table>');
        for (var i = 0; i < answer.length; i++ ) {
            var tr = $('<tr></tr>').html(answer[i][1]);
                table.append(tr);
                var per = $("<tr class='bar-container' style='background-color:#cc4400; height: 5%;width:150px;'></tr>");
                var user =$("<div class='poll_open_result' name ='" + answer[i][0] + "' style='width:" + percent[answer[i][0]] + "%;background-color:#0066cc;'></div>")
                        .append("<strong >" + percent[answer[i][0]] + "%</strong>");
                per.append(user);
                if (!isAnonymously ) { 
                    var divModal = $('<div id="modal_form_' + answer[i][0]+ '" style="display:none"></div>');
                    pollDiv.append(divModal);
                $(user).click(function(e) {
                    e.preventDefault();
                    $.post(
                        Routing.generate(
                        'users_poll'),
                        JSON.stringify({
                            answer: this.getAttribute("name")
                        }),
                        AddModalForm
                    );
                });
                }
                table.append(per);
                pollDiv.append(table);
            }
    }
    pollDiv.show();
    msgContainer.append($('<div></div>').append(pollDiv));
}

function AddModalForm(data)
{
    var id = data['id'];
    var user = data['users'];
    var name = "#modal_form_"+id;
    $( name ).display = "block";
    $(name).html('');
    $( name ).append($('<div></div>').append($('<div>poll.voters:'+ user.length + '</div>')));
    if (user.length > 0) {
        for(i = 0; i < user.length; i++) {
            $( name ).append($('<p></p>').append(
                $('<a href="' + Routing.generate('user_profile', {id: user[i]['id']}) + '">' + user[i]['text'] + '</a>')));
        }
    } else {
       $(name).append('<p> For this answer, no one voted </p>'); 
    }
    dialog = $(name).dialog({
        autoOpen: false,
         modal: true,
    });
    dialog.dialog( "open" );
}
function clickForm(data)
{
    var error = $('<p id="error">You do not correctly filled out a form to create a poll</p>');
    if (data['status'] === 'ok') {
        writePoll(data['pollId']); 
        if ($("#poll_form").find('#error').is(":visible"))
            $("#poll_form").find('#error').hide();
        $('#poll_form').hide();
        $($('#poll_form').find('form'))[0].reset();

     } else {
        $('#poll_form').show();
        if (!$("#poll_form").find('#error').is(":visible"))
            $('#poll_form').append(error); 
    }
}

$(document).on('ready', function () {
    $('#poll_form').hide();
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
    $.fn.serializeObject = function(){
        var o = {};
        var a = this.serializeArray();
        $.each(a, function() {
            if (o[this.name]) {
            if (!o[this.name].push) {
                 o[this.name] = [o[this.name]];
              }
                o[this.name].push(this.value || '');
              } else {
                o[this.name] = this.value || '';
                }
        });
        return o;
    };    
    $('#tell_btn').on('click', function (e) {
        e.preventDefault();
        var input = $('#msg_input');

         if ($("#poll_form").is(":visible")) {
            var form = $($('#poll_form').find('form')).serializeObject();
            $.post(
                Routing.generate(
                'user_poll_create'),
                form,
                clickForm
            );
        } else {
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
        } 
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

    $('#create_poll').on('click', function (e) {
        e.preventDefault();
        $('#poll_form').show();
    });
 });
