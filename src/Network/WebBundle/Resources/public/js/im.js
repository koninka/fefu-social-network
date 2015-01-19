'use strict';

var currentThreadId;

function xhr(action, message) {
  return new Promise(function (resolve, reject) {
    var oReq = new XMLHttpRequest();
    oReq.open('POST', '/' + action);
    oReq.responseType = 'text';

    oReq.onreadystatechange = function () {
      if (oReq.readyState === 4) {
        switch (oReq.status) {
          case 200:
            resolve(JSON.parse(oReq.response));
          break;

          case 500:
            // it's an internal server error so we're assuming for response text
            // to be a html document describing the error
            // TODO: ensure this only happens in dev mode
            document.write(oReq.responseText);
            reject();

          default:
            reject(oReq.response);
          break;
        }
      }
    };

    var strMessage = JSON.stringify(message);
    oReq.send(strMessage);
  });
}

function updateThreadList() {
  xhr('thread_list', {

  })
  .then(function (data) {
    $('#thread-list').empty();
    for (var i in data) {
      var thread = data[i];
      var threadBlock = $('#thread-preview').clone();
      threadBlock.show();
      var threadButton = threadBlock.find('#open-thread');
        if (thread.unreadPosts > 0)
        threadBlock.find('#unreadPosts').html(thread.unreadPosts);
      if (thread.userId)
        threadBlock.find('#user')
            .attr('href', '/id' + thread.userId)
            .html(thread.userName);
      $('#thread-list').append(threadBlock);
      threadButton.data('id', thread.id);
      threadButton.click(function (e) {
        $('#thread-list-wrapper').hide();
          $('#posts-wrapper').show();
        $('#posts').show();
        $('#post-form').show();
        var threadId = $(this).data().id;
        updateThreadView(threadId);
        e.preventDefault();
      });
    }
  });
}

function updateThreadView(threadId) {
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
          var posts = data.posts;
          var unreadPosts = data.unreadPosts || 0;
    currentThreadId = threadId;
    $('#post-form>#custom-recipient').hide();
    var postsBlock = $('#posts');
    postsBlock.show();
          var postsWidth = postsBlock.width();
    postsBlock.empty();
    var l = posts.length;
    for (var j in posts) {
        var unread = l - j <= unreadPosts;
      var post = posts[j];
        var ts = new Date(post.ts.date + ' UTC');
        var tsString;
        var now = new Date;
        if (diff_less_than(now, ts, 60 * 24)) {
            tsString = ts.toLocaleTimeString();
        } else {
            tsString = ts.toLocaleDateString();
        }
        var with_header = lastAuthor !== post.author || !diff_less_than(ts, lastDate, 5);
        postsBlock.append(new_post(post, tsString, with_header, unread));
        lastAuthor = post.author;
        lastDate = ts;
    }
          postsBlock.width(postsWidth);
  });
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
            if (data.error){
                console.log(data.error);
                posts = null;
                return ;
            }
            posts.removeClass('unread-post');
            posts = null;
            $posts.trigger('slimscrolling');
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
    });;
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
    });
    $('#send').click(function (e) {
        var recipient = $('#recipient').select2('data');
        var recipientId = null;
        if (recipient != null)
            recipientId = recipient.id;
        xhr('post', {
            recipientId: recipientId,
            threadId: currentThreadId,
            text: $('#post-text').val()
        })
            .then(function (data) {
                if(data.error){
                    console.log(data.error);
                    return;
                }
                $('#post-text').val('');
                updateThreadView(data.threadId);
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
    if (partnerId == null) {
        updateThreadList();
    } else {
        $('#compose-post').click();
        $('#recipient').select2('data', {id: partnerId, text: partnerName});
    }
}
