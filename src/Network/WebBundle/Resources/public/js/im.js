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
      var threadUser = threadBlock.find('#user');
      threadUser.attr('href', '/id' + thread.userId);
      threadUser.html(thread.userName);
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
  xhr('thread', {id: threadId})
  .then(function (data) {
    currentThreadId = threadId;
    $('#post-form>#custom-recipient').hide();
    var postsBlock = $('#posts');
    postsBlock.show();
    postsBlock.empty();
    var lastAuthor = "";
    for (var j in data) {
      var post = data[j];
      if (lastAuthor !== post.from) {
        var postHeader = $('#post-header').clone();
        var pAuthor = postHeader.find('#author');
        pAuthor.attr('href', '/id' + post.userId);
        pAuthor.html(post.from);

        var ts = new Date(post.ts.date + ' UTC');
        var tsString;
        if (((new Date) - ts) < 60 * 60 * 1000 * 24) {
          tsString = ts.toLocaleTimeString();
        } else {
          tsString = ts.toLocaleDateString();
        }

        postHeader.find('#ts').html(tsString);
        postHeader.show();
        postsBlock.append(postHeader);
        lastAuthor = post.from;
      }
      var postBody = $('#post').clone();
      postBody.html(post.text);
      postBody.show();
      postsBlock.append(postBody);
    }
  });
}



function InitIM(partnerId, partnerName) {
    var $posts = $('#posts');
    $posts.slimScroll().bind('slimscrolling', function(e, pos){
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
