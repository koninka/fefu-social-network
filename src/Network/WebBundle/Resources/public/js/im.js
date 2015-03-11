'use strict';

var currentThreadId;

var lastThreadId;

var openedThreads = [];
var lastOpenedThreadsPostDate = [];

var conferences = [];

var addedPostFile = [];
var addedImg = [];
var addedMp3 = [];
var addedVideo = [];

var uploader;

window.onbeforeunload = function () {
    for (var file in addedPostFile) {
        deleteFile(addedPostFile[file]);
    }
};

function deleteFile(fileId) {
    $.post(
        "/profile/json/file.delete",
        {
            file_id: fileId
        },
        function(resp, textStatus, jqXHR)
        {
            addedPostFile.splice(addedPostFile.indexOf(resp.fileId) - 1, 1);
            $('#send').removeAttr('disabled');
        }
    );
}

$(document).keypress(function(e) {
    if(e.which == 13) {
        $('#send').click();
        $('#post-text').val('');
    }
});

$(document).ready(function() {
    uploader = $("#add_file").uploadFile({
        url: "/profile/upload/file", //not good
        multiple: false,
        dragDrop: false,
        showDelete: true,
        showDone: false,
        showProgress : true,
        maxFileSize: 2097152,
        onSubmit:function(files)
        {
            $('#send').attr('disabled','disabled');
        },
        onSuccess: function(file, data, xhr) {
            $('#send').removeAttr('disabled');
            var status = data.status;
            switch (status) {
                case 'ok':
                    addedPostFile.push(data.metadata.file_id);
                    break;
                case 'badFile':
                    alert('Unable to upload a file!');
                    break;
                default:
                    break;
            }
        },
        deleteCallback: function(data, pd) {
            $('#send').attr('disabled','disabled');
            $.post(
                "/profile/json/file.delete",
                {
                    file_id: data.metadata.file_id
                },
                function(resp, textStatus, jqXHR)
                {
                    addedPostFile.splice(addedPostFile.indexOf(resp.fileId) - 1, 1);
                    $('#send').removeAttr('disabled');
                }
            );
            pd.statusbar.hide().remove();
        }
    });

    $("body").on('click', "button.edit-msg-btn", function () {
        editMessage($(this).val());
    });
});

function editMessage(id) {
    $.get(
        "api/get_message",
        {
            id: id,
            markdown: 0
        },
        function (data) {
            $('#post-text').val(data.text);
        });

    $('#send').val('save').text('Save').attr( "data", id);
    $('.ajax-file-upload').hide();
}

function updateMessage(msg, id) {
    $('.edit-msg-btn[value="' + id + '"]').prev().prev().html(msg.text);
}

function xhr(action, message) {
    message = message || {};
    var dfd = $.Deferred();
    $.post(action, message).done(function(data, textStatus, jqXHR){
        if (data.error) {
            console.log(data.error);
            dfd.reject();
            return;
        }
        dfd.resolve(data);
    }).fail(function(data, textStatus, jqXHR){
        document.write(data.responseText);
        dfd.reject();
    });
    return dfd;
}

function removeTab(threadId) {
    openedThreads[threadId].remove();

    lastOpenedThreadsPostDate = lastOpenedThreadsPostDate
        .filter(function (el) {
            return el.threadId !== threadId;
        });

    delete openedThreads[threadId];
    if (threadId == currentThreadId) {
        var existOpenedThread = false;
        for(var id in openedThreads) { // get any opened thread
            if (openedThreads[id] == null) continue;
            updateThreadView(id, null, false);
            existOpenedThread = true;
            break;
        }
        if (!existOpenedThread) {
            $('#show-opened-threads').hide();
            updateThreadList();
        }
    }
}

function setTab(threadId, threadName) {
    if (!threadName) return;
    if (openedThreads[threadId]) {
        openedThreads[threadId].find('a').text(threadName);
        return;
    }
    openedThreads[threadId] =
        $('<li/>').append($('<a/>', {
            text: threadName
        })).append($('<span/>', {
                class: 'ui-icon ui-icon-close',
                role: 'presentation',
                click: function(){
                    removeTab(threadId);
                }
            })).append($('<span/>', {
            class: 'unread-post-counter',
            click: function(){
                removeTab(threadId);
            }
        })).appendTo('#tabs-list').click(function () {
            updateThreadView(threadId, null, false);
        });
    $('#show-opened-threads').show();
}

function selectTab(threadId) {
    $('#tabs-list li').removeClass('selected-thread');
    openedThreads[threadId].addClass('selected-thread');
    openedThreads[threadId].find('.unread-post-counter').text('');
}

function closeActions() {
    $('#add-user-wrapper, #kick-user-wrapper, #new-topic-wrapper').hide();
}

function updateThreadList() {
    xhr('thread_list').then(function (data) {
        $('#thread-list').empty();
        var items = data.items;
        var helpMap = data.helpMap;
        for (var i in items) {
            var thread = items[i];
            var threadBlock = $('#thread-preview').clone();
            threadBlock.show();
            var threadButton = threadBlock.find('#open-thread');
            if (thread.unreadPosts > 0)
                threadBlock.find('#unreadPosts').html(thread.unreadPosts);
            var helpItem = helpMap[thread.id];
            var threadName = '';
            if (helpItem) {
                threadName = helpItem.userName;
                threadBlock
                    .find('#user')
                    .attr('href', '/id' + helpItem.userId)
                    .html(helpItem.userName)
            }
            else {
                conferences[thread.id] = true;
                threadName = thread.topic;
                threadBlock.find('#topic').html(thread.topic);
            }
            $('#thread-list').append(threadBlock);
            threadButton
                .click({id: thread.id, name: threadName},function (e) {
                    var threadId = e.data.id;
                    var threadName = e.data.name;
                    updateThreadView(threadId, threadName, true);
                    e.preventDefault();
                });
        }
    });
    $('#thread-list-wrapper').show();
    $('#posts-wrapper').hide();
    $('#conference-topic-div').hide();
    $('#thread-tabs').hide();
    $('#im-menu-actions').hide();
    closeActions();
}

function updateThreadView(threadId, topic, scroll) {
    // Currently reloads all posts from thread
    setTab(threadId, topic);
    if (removeMessageRequest(threadId)) {
        redrawMessageRequest();
    }
    selectTab(threadId);
    closeActions();
    $('#thread-tabs').show();
    $('#thread-list-wrapper').hide();
    $('#posts-wrapper').show();
    $('#posts').show();
    $('#conference-topic-div').hide();
    lastThreadId = currentThreadId = threadId;
    var lastAuthor = 0;
    var lastDate = null;
    xhr('thread', {id: threadId})
    .then(function (data) {
        var selfId = data.selfId;
        var posts = data.posts;
        var unreadPosts = data.unreadPosts || 0;
        $('#post-form>#custom-recipient').hide();
        var postsBlock = $('#posts');
        postsBlock.show();
        var postsWidth = postsBlock.width();
        postsBlock.empty();
        for (var i = posts.length - 1; unreadPosts > 0 && i >= 0; --i) {
            if (posts[i].userId != selfId) {
                posts[i].unread = true;
                --unreadPosts;
            }
        }
        var l = posts.length;
        for (var j in posts) {
            var post = posts[j];
            var unread = post.unread;
            var wtf = moment(post.ts.date);
            var ts = new Date(wtf.format('YYYY/MM/DD HH:mm:ss') + ' UTC');
            var tsString;
            var now = new Date;
            if (diff_less_than(now, ts, 60 * 24)) {
                tsString = ts.toLocaleTimeString();
            } else {
                tsString = ts.toLocaleDateString();
            }
            var with_header = lastAuthor !== post.userId || !diff_less_than(ts, lastDate, 1);
            postsBlock.append(createPostDiv(post, tsString, with_header, unread));
            lastAuthor = post.userId;
            lastDate = ts;
        }
        if ( ($.grep(lastOpenedThreadsPostDate, function(e) { return e.threadId == threadId; })).length === 0) {
            lastOpenedThreadsPostDate.push({ 'threadId': threadId, 'ts': ts, 'author' : lastAuthor});
        }
        postsBlock.width(postsWidth);
        if (scroll) {
            scrollToBottom();
        }
        postsBlock.trigger('slimscrolling');
    });
    if (conferences[threadId])
        $('#im-menu-actions').show();
    else
        $('#im-menu-actions').hide();
}

function InitActions() {
    $('#show-thread-list').click(function(){
        updateThreadList();
    });
    $('#show-opened-threads').click(function(){
        if (lastThreadId)
            updateThreadView(lastThreadId, null, false);
    });
    (function(){ //add friend to conference
        var $friendList = $('#add-user-list').select2({
            width:'resolve',
            ajax: {
                url: "/api/friends",
                dataType: 'json',
                quietMillis: 250,
                data: function (term, page) {
                    return {
                        query:    term,
                        page:     page,
                        threadId: currentThreadId
                    };
                },
                results: function (data, page) {
                    return { results: data.items, more: data.more };
                }
            }
        });
        $('#add-user-apply').click(function(){
            var friend = $friendList.select2('data');
            if (friend == null) return;
            var friendId = friend.id;
            xhr('thread/add_user', {
                conferenceId: currentThreadId,
                userId: friendId
            }).then(function(data) {
                $('#add-user-cancel').click();
            });
        });
        $('#add-user-cancel').click(function(){
            $friendList.select2("val", "");
            $('#add-user-wrapper').hide();
        });
        $('#add-user-action').click(function(e){
            $('#add-user-wrapper').show();
        });
    })();
    (function(){
        var initValues = function() {
            xhr('thread/users', {
                threadId: currentThreadId
            }).then(function (data) {
                var items = [];
                var users = data.users;
                var canBeKicked = data.canBeKicked;
                var k = {};
                for (var i = 0; i < canBeKicked.length; ++i) {
                    k[canBeKicked[i]] = true;
                }
                for (var i in users) {
                    var user = users[i];
                    if (user['id'] == data['userId'] || !k[user['id']]) continue;
                    items.push({id: user['id'], text: user['firstName'] + ' ' + user['lastName']});
                }
                $('#kick-user-list').select2({
                    data: items
                })
            });
        };
        $('#kick-user-cancel').click(function(){
            $('#kick-user-wrapper').hide();
        });
        $('#kick-user-apply').click(function(){
            var user = $('#kick-user-list').select2('data');
            if (user == null) return;
            var userId = user.id;
            xhr('thread/kick_user', {
               conferenceId: currentThreadId,
               userId: userId
            }).then(function(data){
                $('#kick-user-cancel').click();
                $('#kick-user-list').select2({data: []});
            })
        });
        $('#kick-user-action').click(function(){
            initValues();
            $('#kick-user-wrapper').show();
        });
        $('#kick-user-list').select2({data: []});
    })();
    (function(){ //change topic
        $('#new-topic-cancel').click(function(){
            $('#new-topic-wrapper').hide();
        });
        $('#new-topic-apply').click(function(){
            xhr('thread/change_topic', {
                topic: $('#new-topic-field').val(),
                conferenceId: currentThreadId
            }).then(function(data) {
                $('#new-topic-cancel').click();
                setTab(data.conferenceId, data.topic);
            });
        });
        $('#new-topic-action').click(function(e){
            $('#new-topic-wrapper').show();
        });
    })();
    (function(){
        var $confirm = $('#dialog-leave-conference-confirm');
        var yes_button = $confirm.attr('yes');
        var no_button = $confirm.attr('no');
        var buttons = {};
        buttons[yes_button] = function(){
            $confirm.dialog('close');
            xhr('thread/leave', {
                conferenceId: currentThreadId
            }).then(function(data){
                removeTab(data.conferenceId);
            });
        };
        buttons[no_button] = function(){
            $confirm.dialog('close');
        };
        $confirm.dialog({
            resizable: true,
            modal: true,
            width: 700,
            autoOpen: false,
            buttons: buttons
        });
        $('#leave-conference-action').click(function(){
            $confirm.dialog('open');
        });
    })();
}

function InitIM(partnerId, partnerName) {
    var $posts = $('#posts');
    var posts = null;
    var checkUnreadPosts = function () {
        if (posts == null) return;
        xhr('api/read_posts', {
            count : posts.length,
            threadId : currentThreadId
        }).then(function(data){
            posts.removeClass('unread-post');
            posts = null;
            $posts.trigger('slimscrolling');
        }, function () {
            posts = null;
        })
    };
    $posts.slimScroll().bind('slimscrolling', function(e, pos){
        if (posts != null) return;
        posts = $("#post.unread-post").filter(function(){
            var $this = $(this);
            return $this.position().top < $posts.height();
        });
        if (posts.length == 0) {posts = null; return;}
        checkUnreadPosts();
    });
    var $sl = $('.slimScrollDiv:has(#posts)');

    $('#posts-wrapper').resizable({
        alsoResize: ['#posts'],
        minWidth:  500,
        minHeight: 300,
        resize: function( event, ui ) {
            $sl.height($posts.height()); //hack to resize scrollbar view
        }
    });
    $('#recipient').select2({
        width:'resolve',
        multiple: true,
        ajax: {
            url: "/api/friends",
            dataType: 'json',
            quietMillis: 250,
            data: function (term, page) {
                return {
                    query:    term,
                    page:     page
                };
            },
            results: function (data, page) {
                return { results: data.items, more: data.more };
            }
        }
    }).on("change", function(e) {
        var data = $('#recipient').select2('data');
        if (data.length > 1)
            $('#conference-topic-div').show();
        else
            $('#conference-topic-div').hide();
    });

    $('#send').click(function (e) {
        if (!$.trim($("#post-text").val())) {
            return;
        }
        $('#send').attr('disabled','disabled');
        var files = {
            postFile: addedPostFile,
            imgFile: addedImg,
            mp3File: addedMp3,
            videoFile: addedVideo
        };

        $('#conference-topic-div').hide();
        var recipient = $('#recipient').select2('data');
        var recipientIds = [];
        if (recipient != null) {
            for (var i in recipient) {
                recipientIds.push(recipient[i].id);
            }
        }
        $('.ajax-file-upload').show();
        if ($(this).val() == 'send') {
            xhr('post', {
                recipientId: recipientIds,
                threadId: currentThreadId,
                text: $('#post-text').val(),
                topic: $('#conference-topic').val(),
                files: files
            }).then(function (data) {
                if (recipientIds.length > 1)
                    conferences[data.threadId] = true;
                clearEditor();
                addMessage(data.threadId, data.post);
            });
        } else if ($(this).val() == 'save') {
            xhr('api/update_message/', {
                id: $('#send').attr('data'),
                text: $('#post-text').val()
            }).then(function (data) {
                updateMessage(data, $('#send').attr('data'));
                clearEditor();
            });
        }
        e.preventDefault();
    });

    $('#compose-post').click(function (e) {
        $('#thread-list-wrapper').hide();
        $('#posts').hide();
        $('#post-form>#custom-recipient').show();
        $('#posts-wrapper').show();
        $('#post-text').val('');
        currentThreadId = null;
        e.preventDefault();
    });
    InitActions();
    if (partnerId == null) {
        updateThreadList();
    } else {
        $('#compose-post').click();
        $('#recipient').select2('data', {id: partnerId, text: partnerName});
    }
}

function createPostDiv(post, tsString, with_header, unread){
    var postDiv = $('#post').clone();
    if (with_header) {
        var postHeader = postDiv.find('#post-header');
        var pAuthor = postHeader.find('#author');

        pAuthor.attr('href', '/id' + post.userId);
        pAuthor.html(post.author);
        postHeader.find('#ts').html(tsString);
        postHeader.show();
    }
    var pFiles = postDiv.find('#post-files-wrap');
    for (var i in post.postFiles) {
        pFiles.append('<a style="font-size: 11px;" target="_blank" href="/download/' +
            post.postFiles[i].id + '?h='+post.postFiles[i].hash+'">' + post.postFiles[i].name + '</a></br>');
    }
    if (unread)
        postDiv.addClass('unread-post');
    var postBody = postDiv.find('#post-body');
    postBody.html(post.text);
    var editBtn = '';
    if(post.editable) {

        editBtn = '<button style="top: ' + with_header * 25 + 'px" value="' + post.id + '" class="edit-msg-btn"></button>';
    }
    postBody.show();
    postDiv.show();
    postDiv.append(editBtn);
    return postDiv;
}

function addMessage(threadId, post) {
    var postsBlock = $('#posts');
    var unread = post.unread;
    var wtf = moment(post.ts.date);
    var ts = new Date(wtf.format('YYYY/MM/DD HH:mm:ss') + ' UTC');
    var tsString;
    var now = new Date;
    if (diff_less_than(now, ts, 60 * 24)) {
        tsString = ts.toLocaleTimeString();
    } else {
        tsString = ts.toLocaleDateString();
    }
    var ta = getLastAuthorAndTsWithThreadId(threadId);
    var with_header = ta.author !== post.userId || !diff_less_than(ts,  ta.ts, 1);
    postsBlock.append(createPostDiv(post, tsString, with_header, unread));
    updateLastThreadPostData(threadId, ts, post.userId);
    scrollToBottom();
}

function imOnMessage(evt) {
    var data = JSON.parse(evt.data);
    var action = data.action;

    if (action == "notify") {
        new jBox('Notice', {
            content: data.text,
            color:  data.type,
            autoClose: false
        });
    } else if (action == "deliver") {
        var post = data.post;
        if (currentThreadId == data.thread_id) {
            post.unread = true;
            addMessage(data.thread_id, post);
            xhr('api/read_posts', {threadId: data.thread_id, count:1})
                .then(function (data) {
                    console.log('User with id %d read post %d from thread %d', post.userId, post.id, data.thread_id);
                });
        } else if (getLastAuthorAndTsWithThreadId(data.thread_id) !== false){
            var tabSpan =  openedThreads[data.thread_id].find('.unread-post-counter');
            if (tabSpan.text() == '') {
                tabSpan.text(1)
            } else {
                tabSpan.text(parseInt(tabSpan.text()) + 1);
            }
        }
    }
}

function scrollToBottom() {
    var postsBlock = $('#posts');
    var scrollTo_int = postsBlock.prop('scrollHeight') + 'px';
    postsBlock.slimScroll({
        scrollTo: scrollTo_int,
        start: 'bottom'
    });
}

function updateLastThreadPostData(threadId, newTs, author) {
    $.each(lastOpenedThreadsPostDate, function( index, value ) {
        if (value.threadId === threadId) {
            value.ts = newTs;
            value.author = author;
        }
    });
}

function diff_less_than(date1, date2, min){
    return date1 - date2 < min * 60 * 1000;
}

function getLastAuthorAndTsWithThreadId(threadId) {
    var res = false;
    $.each(lastOpenedThreadsPostDate, function( index, value ) {
        if (value.threadId === threadId) {
            res = value;
        }
    });
    return res;
}

function clearEditor() {
    $('.ajax-file-upload-statusbar').each(function () {
        $(this).hide().remove();
    });
    $.each(addedPostFile, function (i, val) {
        deleteFile(val);
    });
    addedPostFile = [];
    $('#send').removeAttr('data').val('send').text('Send');
    $('#post-text').val('');
    $('#send').removeAttr('disabled');
}
