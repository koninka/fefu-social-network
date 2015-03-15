$(document).ready(function(){

var cssSelector = {
  jPlayer: "#jquery_jplayer_1",
  cssSelectorAncestor: "#jp_container_1"
};

var options = {
  swfPath: "/js/lib/jplayer/",
  supplied: "mp3",
  playlistOptions: {
    enableRemoveControls: true,
    shuffleOnLoop: false
  },
  useStateClassSkin: true,
  autoBlur: false,
  autoPlay: false,
  smoothPlayBar: true,
  keyEnabled: false,
  audioFullScreen: false
};

var myPlaylist = new jPlayerPlaylist(cssSelector, [], options);
var playlists = undefined;

Promise.resolve($.post("/playlist/all")).then(function(response) {
    if (response.status === 'ok') {
      playlists = response.playlists;
      for (var i = playlists.length - 1; i >= 0; i--) {
        playlists[i] = playlists[i].items;
        for (var j = playlists[i].length - 1; j >= 0; j--) {
          playlists[i][j] = playlists[i][j].audio_track;
          playlists[i][j].mp3 = '/download/audio/' + playlists[i][j].id;
        };
      };
      myPlaylist.addToUserPlaylist(playlists[0]);
      console.log(response);
    } else {
      console.log('error, status not ok: ' + response.status);
    }

}).catch(function(e) {
    console.log(e);
    //jQuery doesn't throw real errors so use catch-all
    console.log(e.statusText);
});

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
  videoPlay: '.jp-video-play',
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
  noSolution: '.jp-no-solution'},
  errorAlerts: false,
  warningAlerts: false
});

$("#audio_file_upload").uploadFile({
  url: '/upload/audio',
  multiple: true,
  filename: 'mp3',
  onSuccess: function(file, data, xhr) {
    var status = data['status'];
    console.log(data);

    switch (status) {
      case 'ok':
        var newMedia = {
          title: data.title,
          artist: data.artist,
          mp3: Routing.generate('audio_track_download', { id: data.id }),
          id: data.id
        };
        // playlists[0].id
        var url = '/playlist/' + 1 + '/push/' + data.id;
        console.log(url);
        Promise.resolve($.post(url)).then(function(response) {
            if (response.status === 'ok') {
              myPlaylist.addToUserPlaylist(newMedia);
              console.log(response);
            } else {
              console.log('error, status not ok: ' + response.status);
            }

        }).catch(function(e) {
            console.log(e.statusText);
        });

        break;

      default:
        console.log("won't upload, status: " + status);
        break;
    }
  }
});

});
