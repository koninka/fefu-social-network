$(document).ready(function(){

var cssSelector = {
  jPlayer: "#jquery_jplayer_1",
  cssSelectorAncestor: "#jp_container_1"
};

var options = {
  swfPath: "/js/lib/jplayer/",
  supplied: "mp3",
  playlistOptions: {
    enableRemoveControls: true
  },
  useStateClassSkin: true,
  autoBlur: false,
  smoothPlayBar: true,
  keyEnabled: true,
  audioFullScreen: true
};

var playlist = [
  {% for mp3 in mp3s %}
    {
      title: "{{ mp3.getSong.getTitle }}",
      artist: "{{ mp3.getSong.getArtist }}",
      mp3: Routing.generate('file_mp3_get', {file_id: {{ mp3.getId }}}),
      id: {{ mp3.getId }},
      poster: ""
    },
  {% endfor %}
];

var myPlaylist = new jPlayerPlaylist(cssSelector, playlist, options);

$('#jquery_jplayer_1').jPlayer({
  swfPath: '/js/lib/jplayer/',
  solution: 'html, flash',
  supplied: 'mp3',
  preload: 'metadata',
  volume: 0.8,
  muted: false,
  backgroundColor: '#000000',
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

$("#mp3_upload").uploadFile({
  url: "{{ path('file_mp3_upload') }}",
  multiple: true,
  filename: "{{ filename }}",
  onSuccess: function(file, data, xhr) {
    var status = data['status'];

    switch (status) {
      case 'ok':
        var metadata = data['metadata'];
        var newMedia = {
          title: metadata['title'],
          artist: metadata['artist'],
          mp3: Routing.generate('file_mp3_get', { file_id: metadata['file_id'] }),
          id: metadata['file_id']
        };

        if (metadata['album_id'] !== undefined) {
          newMedia['poster'] = Routing.generate('file_mp3_poster', {
            album_id: metadata['album_id']
          });
        }

        myPlaylist.addToUserPlaylist(newMedia);
        break;

      case 'badFile':
        alert(
          'Mp3 file \'' + file +
          '\' is bad (doesn\'t content ID3-tags). ' +
          'Try to upload other files.'
        );
        break;

      default:
        break;
    }
  }
});

});