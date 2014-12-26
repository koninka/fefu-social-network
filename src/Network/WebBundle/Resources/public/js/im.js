'use strict';

function xhr(action, message) {
  return new Promise(function (resolve, reject) {
    var oReq = new XMLHttpRequest();
    oReq.open('POST', '/' + action);
    oReq.responseType = 'json';

    oReq.onreadystatechange = function () {
      if (oReq.readyState === 4) {
        switch (oReq.status) {
          case 200:
            console.log(oReq.response);
            resolve(oReq.response);
          break;

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
        $('#recipient').val($(this).data().id);
        updateThreadView($(this).data().id);
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

$(function () {
  updateThreadList();
});
