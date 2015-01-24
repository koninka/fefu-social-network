'use strict';

var currentThreadId;

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
            if (helpItem)
                threadBlock
                    .find('#user')
                    .attr('href', '/id' + helpItem.userId)
                    .html(helpItem.userName);
            else
                threadBlock.find('#topic').html(thread.topic);
            $('#thread-list').append(threadBlock);
            threadButton
                .data('id', thread.id)
                .click(function (e) {
                    $('#thread-list-wrapper').hide();
                    $('#posts-wrapper').show();
                    $('#posts').show();
                    $('#post-form').show();
                    $('#conference-topic-div').hide();
                    var threadId = $(this).data().id;
                    updateThreadView(threadId);
                    e.preventDefault();
                });
        }
    });
}

function updateThreadView(threadId, scroll) {
    // Currently reloads all posts from thread
    var lastAuthor = "";
    var lastDate = null;
    var diff_less_than = function (date1, date2, min){
        return date1 - date2 < min * 60 * 1000;
    };
    var new_post = function(post, tsString, with_header, unread){
        var postDiv = $('#post').clone();
        if (with_header) {
            var postHeader = postDiv.find('#post-header');
            var pAuthor = postHeader.find('#author');
            pAuthor.attr('href', '/id' + post.userId);
            pAuthor.html(post.author);
            postHeader.find('#ts').html(tsString);
            postHeader.show();
        }
        if (unread)
            postDiv.addClass('unread-post');
        var postBody = postDiv.find('#post-body');
        postBody.html(post.text);
        postBody.show();
        postDiv.show();
        return postDiv;
    };
    xhr('thread', {id: threadId})
    .then(function (data) {
        var selfId = data.selfId;
        var posts = data.posts;
        var unreadPosts = data.unreadPosts || 0;
        currentThreadId = threadId;
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
            postsBlock.append(new_post(post, tsString, with_header, unread));
            lastAuthor = post.userId;
            lastDate = ts;
        }
        postsBlock.width(postsWidth);
        if (scroll) {
            var scrollTo_int = postsBlock.prop('scrollHeight') + 'px';
            postsBlock.slimScroll({
                scrollTo: scrollTo_int,
                start: 'bottom'
            });
        }
        postsBlock.trigger('slimscrolling');
        $('#im-menu').show();
    });
}

function InitActions() {
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
                for (var i = 0; i < data['users'].length; ++i) {
                    var user = data['users'][i];
                    if (user['id'] == data['userId']) continue;
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
            });
        });
        $('#new-topic-action').click(function(e){
            $('#new-topic-wrapper').show();
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
        $('#conference-topic-div').hide();
        var recipient = $('#recipient').select2('data');
        var recipientIds = [];
        if (recipient != null) {
            for (var i in recipient) {
                recipientIds.push(recipient[i].id);
            }
        }
        xhr('post', {
            recipientId: recipientIds,
            threadId: currentThreadId,
            text: $('#post-text').val(),
            topic: $('#conference-topic').val()
        }).then(function (data) {
            $('#post-text').val('');
            updateThreadView(data.threadId, true);
        });
        e.preventDefault();
    });
    $('#compose-post').click(function (e) {
        $('#thread-list-wrapper').hide();
        $('#post-form').show();
        $('#post-form>#custom-recipient').show();
        $('#posts-wrapper').show();
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
