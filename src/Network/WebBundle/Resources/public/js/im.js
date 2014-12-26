'use strict';

function xhr(action, message) {
  return new Promise(function (resolve, reject) {
    var oReq = new XMLHttpRequest();
    oReq.open('POST', '/' + action);
    oReq.responseType = 'text';

    oReq.onreadystatechange = function () {
      if (oReq.readyState === 4) {
        switch (oReq.status) {
          case 200:
            console.log(oReq.response);
            resolve(JSON.parse(oReq.response));
          break;

          case 500:
            // it's an internal server error so we're assuming for response text
            // to be a html document describing the error
            // TODO: ensure this only happens in dev mode
            document.write(oReq.responseText);
            reject();

          default:
            console.log('readyState is 4 and status = ', oReq.status);
            reject(oReq.response);
          break;
        }
      }
    };

    var strMessage = JSON.stringify(message);
    console.log('sending: ', strMessage);
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
        var thread_id = $(this).data().id;
        $('#recipient').val(thread_id);
        updateThreadView(thread_id);
        e.preventDefault();
      });
    }
  });
}

function updateThreadView(threadId) {
  xhr('thread', {id: threadId})
  .then(function (data) {
    $('#posts').empty();
    for (var j in data) {
      var post = data[j];
      // TODO: translate time to user timezone and format it
      var postDiv = $('<div>' + post.id + ' ' + post.from + ': ' + post.ts.date
        + '\n' + post.text + '</div>');
      $("#posts").append(postDiv);
    }
  });
}

$(function () {
  updateThreadList();

  $('#send').click(function (e) {
    xhr('post', {
      id: $('#recipient').val(),
      text: $('#post-text').val()
    })
    .then(function (data) {
      updateThreadList();
      updateThreadView(data.threadId);
    });
    e.preventDefault();
  });
});
