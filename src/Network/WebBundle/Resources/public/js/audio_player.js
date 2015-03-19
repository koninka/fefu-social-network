'use strict';

function getStateFromStores() {
  return {
    playlists: AudioStore.getPlaylists(),
    currentPlaylistId: AudioStore.getCurrentPlaylistId(),
    currentTrackId: AudioStore.getCurrentTrackId(),
    searchResults: AudioStore.getSearchResults(),
    playState: AudioStore.getPlayState(),
    thisUserId: AudioStore.getThisUserId()
  };
}

var AudioWrap = React.createClass({
  getInitialState: function() {
    return getStateFromStores();
  },
  componentDidMount: function() {
    AudioStore.on('change', this._onChange);
  },
  componentWillUnmount: function() {
    AudioStore.removeListener('change', this._onChange);
  },
  render: function() {
    var playlist = {
      items: [],
      userId: null
    };
    for (var i = this.state.playlists.length - 1; i >= 0; i--) {
      if (this.state.playlists[i].id === this.state.currentPlaylistId) {
        playlist = this.state.playlists[i];
      }
    };
    var searchResults = this.state.searchResults;
    searchResults.id = 0;
    searchResults.userId = null;
    // TODO: move above id matching elsewhere
    return (
      <div className="audio-wrap">
        <table className="audio-table" cellSpacing="0" cellPading="0"><tbody><tr>
          <td>
            <SearchBox/>
            <Playlist
            thisUserId={this.state.thisUserId}
            playlist={playlist}
            currentTrackId={this.state.currentTrackId}
            playState={this.state.playState}/>
            <hr/>
            <Playlist
            thisUserId={this.state.thisUserId}
            playlist={searchResults}
            currentTrackId={this.state.currentTrackId}
            playState={this.state.playState}/>
          </td>
          <td>
            <MenuBox
            playlists={this.state.playlists}
            currentPlaylistId={this.state.currentPlaylistId}/>
          </td>
        </tr></tbody></table>
      </div>
    );
  },
  _onChange: function () {
    this.setState(getStateFromStores());
  }
});
