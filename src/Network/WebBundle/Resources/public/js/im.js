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
      var threadButton = $('<input id="thread-button" type="button" '
      + 'class="blue_button" value="' + thread.id + ' ' + thread.topic + '"/>');
      $('#thread-list').append(threadButton);
      threadButton.data('id', thread.id);
      threadButton.click(function (e) {
        $('#thread-list-wrapper').hide();
        $('#posts').show();
        $('#post-form').show();
        var thread_id = $(this).data().id;
        $('#recipient').val(thread_id);
        currentThreadId = thread_id;
        updateThreadView(thread_id);
        e.preventDefault();
      });
    }
  });
}

function updateThreadView(threadId) {
  // Currently reloads all posts from thread
  xhr('thread', {id: threadId})
  .then(function (data) {
    var postsBlock = $('#posts');
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
      var postBody = $('#post-template').clone();
      postBody.html(post.text);
      postBody.show();
      postsBlock.append(postBody);
    }
  });
}

$(function () {
  updateThreadList();

  $('#send').click(function (e) {
    xhr('post', {
      recipientId: $('#recipient').val(),
      threadId: currentThreadId,
      text: $('#post-text').val()
    })
    .then(function (data) {
      updateThreadList();
      updateThreadView(data.threadId);
    });
    e.preventDefault();
  });
});
