// TODO: we have no module system, this globals will be "fixed out" with
// 'use strict';

AudioDispatcher = new Dispatcher();

ActionTypes = {
  RECEIVE_RAW_PLAYLISTS: 'RECEIVE_RAW_PLAYLISTS',
  REMOVE_PLAYLIST_ITEM: 'REMOVE_PLAYLIST_ITEM',
  PUSH_PLAYLIST_ITEM: 'PUSH_PLAYLIST_ITEM',
  REMOVE_PLAYLIST: 'REMOVE_PLAYLIST',
  SET_CURRENT_PLAYLIST: 'SET_CURRENT_PLAYLIST',
  ADD_PLAYLIST: 'ADD_PLAYLIST',
  PLAY: 'PLAY',
  PAUSE: 'PAUSE',
  HANDLE_PLAY_END: 'HANDLE_PLAY_END',
  SEARCH: 'SEARCH'
};

Actions = {
  receiveAllPlaylists: function (rawPlaylists) {
    AudioDispatcher.dispatch({
      type: ActionTypes.RECEIVE_RAW_PLAYLISTS,
      rawPlaylists: rawPlaylists
    });
  },
  removePlaylistItem: function (playlistId, itemId) {
    Promise.resolve($.post('/delete/audio/' + itemId + '/playlist/' + playlistId))
    .bind(this)
    .then(function(response) {
      if (response.status === 'ok') {
        AudioDispatcher.dispatch({
          type: ActionTypes.REMOVE_PLAYLIST_ITEM,
          playlistId: playlistId,
          itemId: itemId
        });
      }
    }).catch(function(e) {
      console.log(e);
    });
  },
  pushPlaylistItem: function (item) {
    Promise.resolve($.post('/playlist/' + _currentPlaylistId + '/push/' + item.id))
    .then(function(response) {
        if (response.status === 'ok') {
          item.rank = response.rank;
          AudioDispatcher.dispatch({
            type: ActionTypes.PUSH_PLAYLIST_ITEM,
            item: item
          });
        } else {
          console.log('error: ' + response);
        }
    }).catch(function(e) {
        console.log(e);
    });
  },
  removePlaylist: function (playlistId) {
    Promise.resolve($.post('/playlist/remove/' + playlistId))
    .then(function(response) {
      if (response.status === 'ok') {
        AudioDispatcher.dispatch({
          type: ActionTypes.REMOVE_PLAYLIST,
          playlistId: playlistId
        });
      }
    }).catch(function(e) {
      console.log(e);
    });
  },
  setCurrentPlaylist: function (playlistId) {
    AudioDispatcher.dispatch({
      type: ActionTypes.SET_CURRENT_PLAYLIST,
      playlistId: playlistId
    });
  },
  addPlaylist: function (name) {
    Promise.resolve($.post('/playlist/add/' + name))
    .then(function(response) {
      if (response.status === 'ok') {
        AudioDispatcher.dispatch({
          type: ActionTypes.ADD_PLAYLIST,
          name: name,
          playlistId: response.id
        });
      }
    }).catch(function(e) {
      console.log(e);
    });
  },
  play: function (item) {
    $('#jquery_jplayer_1').jPlayer("setMedia", item);
    $('#jquery_jplayer_1').jPlayer("play");
    AudioDispatcher.dispatch({
      type: ActionTypes.PLAY,
      item: item
    });
  },
  pause: function (item) {
    $('#jquery_jplayer_1').jPlayer("pause");
    AudioDispatcher.dispatch({
      type: ActionTypes.PAUSE,
      item: item
    });
  },
  handlePlayEnd: function() {
    AudioDispatcher.dispatch({
      type: ActionTypes.HANDLE_PLAY_END
    });
  },
  search: function(what, by) {
    if (what.length === 0) {
      AudioDispatcher.dispatch({
        type: ActionTypes.SEARCH,
        what: what,
        by: by,
        tracks: []
      });
      return;
    }
    Promise.resolve($.post('/search/audio/' + by + '/' + what))
    .then(function(response) {
      if (response.status === 'ok') {
        AudioDispatcher.dispatch({
          type: ActionTypes.SEARCH,
          what: what,
          by: by,
          tracks: response.tracks
        });
      }
    }).catch(function(e) {
      console.log(e);
    });
  }
};

var _currentPlaylistId = parseInt(localStorage.getItem('currentPlaylistId', true)) || null;;
var _currentTrackId = null;
var _playlists = [];
var _searchResults = [{items:[]}];

AudioStore = $.extend({}, EventEmitter.prototype, {
  init: function(rawPlaylists) {
    for (var i = rawPlaylists.length - 1; i >= 0; i--) {
      var items = rawPlaylists[i].items;
      for (var j = items.length - 1; j >= 0; j--) {
        var item = items[j];
        var t = $.extend({}, item.audio_track);
        t.rank = item.rank;
        t.mp3 = '/download/audio/' + t.id;
        items[j] = t;
      }
    }

    for (var i = rawPlaylists.length - 1; i >= 0; i--) {
      var ordered = rawPlaylists[i].items;
      ordered.sort(function(a, b) {
        if (a.rank > b.rank) {
          return -1;
        } else if (a.rank < b.rank) {
          return 1;
        }
        return 0;
      });
    };

    _playlists = rawPlaylists;
    if (_currentPlaylistId === null) {
      _currentPlaylistId = _playlists.length > 0 ? _playlists[0].id : null;
      if (_currentPlaylistId !== null) {
        localStorage.setItem('currentPlaylistId', _currentPlaylistId);
      }
    }
  },
  removePlaylistItem: function (playlistId, itemId) {
    var p = this.getPlaylist(playlistId).items;
    var index = null;
    for (var i = p.length - 1; i >= 0; i--) {
      if (p[i].id === itemId) {
        index = i;
        break;
      }
    };
    if (index !== null) {
      p.splice(index, 1);
    }
  },
  pushPlaylistItem: function (item) {
    this.getPlaylist(_currentPlaylistId).items.unshift(item);
  },
  removePlaylist: function (playlistId) {
    var index = null;
    for (var i = _playlists.length - 1; i >= 0; i--) {
      if (_playlists[i].id === playlistId) {
        index = i;
        break;
      }
    };
    if (index !== null) {
      _playlists.splice(index, 1);
    }

    if (playlistId === _currentPlaylistId && _playlists.length > 0) {
      this.setCurrentPlaylist(_playlists[0].id);
    }
  },
  setCurrentPlaylist: function (playlistId) {
    _currentPlaylistId = playlistId;
    localStorage.setItem('currentPlaylistId', _currentPlaylistId);
  },
  addPlaylist: function (playlistId, name) {
    var p = {
      name: name,
      id: playlistId,
      items: [],
    };
    _playlists.push(p);
  },
  play: function (item) {
    _currentTrackId = item.id;
  },
  pause: function (item) {

  },
  emitChange: function() {
    this.emit('change');
  },
  getCurrentPlaylistId: function() {
    return _currentPlaylistId;
  },
  getCurrentTrackId: function() {
    return _currentTrackId;
  },
  getPlaylists: function() {
    return _playlists;
  },
  getPlaylist: function (id) {
    for (var i = _playlists.length - 1; i >= 0; i--) {
      if (_playlists[i].id === id) {
        return _playlists[i];
      }
    };
    return [];
  },
  getTrack: function (id) {
    for (var i = _playlists.length - 1; i >= 0; i--) {
      for (var j = _playlists[i].items.length - 1; j >= 0; j--) {
        var t = _playlists[i].items[j];
        if (t.id == id) {
          return t;
        }
      };
    };
    return null;
  },
  getSearchResults: function () {
    return _searchResults;
  },
  handlePlayEnd: function() {
    var p = this.getPlaylist(_currentPlaylistId).items;
    var index = null;
    for (var i = p.length - 1; i >= 0; i--) {
      if (p[i].id === _currentTrackId) {
        index = i;
        break;
      }
    }
    if (index !== null) {
      index = (index + 1) % p.length;
      var t = p[index];
      _currentTrackId = t.id;
      $('#jquery_jplayer_1').jPlayer("setMedia", t);
      $('#jquery_jplayer_1').jPlayer("play");
    }
  },
  handleSearchResult: function(what, by, tracks) {
    _searchResults = {items:[]};
    for (var i = tracks.length - 1; i >= 0; i--) {
      tracks[i].mp3 = '/download/audio/' + tracks[i].id;
      _searchResults.items.push(tracks[i]);
    };
  }
});

AudioStore.dispatchToken = AudioDispatcher.register(function(action) {
  switch(action.type) {
    case ActionTypes.RECEIVE_RAW_PLAYLISTS:
      AudioStore.init(action.rawPlaylists);
      AudioStore.emitChange();
      break;
    case ActionTypes.REMOVE_PLAYLIST_ITEM:
      AudioStore.removePlaylistItem(action.playlistId, action.itemId);
      AudioStore.emitChange();
      break;
    case ActionTypes.PUSH_PLAYLIST_ITEM:
      AudioStore.pushPlaylistItem(action.item);
      AudioStore.emitChange();
      break;
    case ActionTypes.REMOVE_PLAYLIST:
      AudioStore.removePlaylist(action.playlistId);
      AudioStore.emitChange();
      break;
    case ActionTypes.SET_CURRENT_PLAYLIST:
      AudioStore.setCurrentPlaylist(action.playlistId);
      AudioStore.emitChange();
      break;
    case ActionTypes.ADD_PLAYLIST:
      AudioStore.addPlaylist(action.playlistId, action.name);
      AudioStore.emitChange();
      break;
    case ActionTypes.PLAY:
      AudioStore.play(action.item);
      AudioStore.emitChange();
      break;
    case ActionTypes.PAUSE:
      AudioStore.pause(action.item);
      AudioStore.emitChange();
      break;
    case ActionTypes.HANDLE_PLAY_END:
      AudioStore.handlePlayEnd();
      AudioStore.emitChange();
      break;
    case ActionTypes.SEARCH:
      AudioStore.handleSearchResult(action.what, action.by, action.tracks);
      AudioStore.emitChange();
      break;
    default:
  }
});

$(document).ready(function(){

  var cssSelector = {
    jPlayer: "#jquery_jplayer_1",
    cssSelectorAncestor: "#jp_container_1"
  };

  $('#jquery_jplayer_1').jPlayer({
    swfPath: '/js/lib/jplayer/',
    solution: 'html',
    supplied: 'mp3, ogg',
    preload: 'metadata',
    volume: 0.8,
    muted: false,
    backgroundColor: '#FF0000',
    cssSelectorAncestor: '#jp_container_1',
    cssSelector: {
      play: '.jp-play',
      pause: '.jp-pause',
      stop: '.jp-stop',
      seekBar: '.jp-seek-bar',
      playBar: '.jp-play-bar',
      mute: '.jp-mute',
      unmute: '.jp-unmute',
      volumeBar: '.jp-volume-bar',
      volumeBarValue: '.jp-volume-bar-value',
      volumeMax: '.jp-volume-max',
      playbackRateBar: '.jp-playback-rate-bar',
      playbackRateBarValue: '.jp-playback-rate-bar-value',
      currentTime: '.jp-current-time',
      duration: '.jp-duration',
      title: '.jp-title',
      fullScreen: '.jp-full-screen',
      restoreScreen: '.jp-restore-screen',
      repeat: '.jp-repeat',
      repeatOff: '.jp-repeat-off',
      gui: '.jp-gui',
      noSolution: '.jp-no-solution'
    },
    errorAlerts: true,
    warningAlerts: false
  });

  $('#jquery_jplayer_1').bind($.jPlayer.event.ready, function() {

  });

  $('#jquery_jplayer_1').bind($.jPlayer.event.ended, function() {
    Actions.handlePlayEnd();
  });

  $('#jquery_jplayer_1').bind($.jPlayer.event.play, function() {
    $(this).jPlayer("pauseOthers");
  });

  $("#audio_file_upload").uploadFile({
    url: '/upload/audio',
    multiple: true,
    filename: 'mp3',
    onSuccess: function(file, data, xhr) {
      var status = data.status;
      switch (status) {
        case 'ok':
          // emulating server record
          var item = {
            title: data.title,
            artist: data.artist,
            mp3: Routing.generate('audio_track_download', { id: data.id }),
            id: data.id
          };
          Actions.pushPlaylistItem(item);
          break;
        default:
          console.log("upload error, status: " + data);
          break;
      }
    }
  });

  // get all playlists
  Promise.resolve($.post('/playlist/all'))
  .then(function(response) {
    if (response.status === 'ok') {
      // postprocessing
      Actions.receiveAllPlaylists(response.playlists);
    }
  }).catch(function(e) {
    console.log(e);
  });

});

React.render(
  <AudioWrap />,
  document.getElementById('audio_wrap')
);
