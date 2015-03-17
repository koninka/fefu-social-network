'use strict';

function getStateFromStores() {
  return {
    playlists: AudioStore.getPlaylists(),
    currentPlaylistId: AudioStore.getCurrentPlaylistId(),
    currentTrackId: AudioStore.getCurrentTrackId(),
    searchResults: AudioStore.getSearchResults()
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
      items: []
    };
    for (var i = this.state.playlists.length - 1; i >= 0; i--) {
      if (this.state.playlists[i].id === this.state.currentPlaylistId) {
        playlist = this.state.playlists[i];
      }
    };
    var searchResults = this.state.searchResults;
    searchResults.id = 0;
    // TODO: move above id matching elsewhere
    return (
      <div className="audioWrap">
        <SearchBox playlist={searchResults}
                   currentTrackId={this.state.currentTrackId}/>
        <Playlist playlist={playlist}
                  currentTrackId={this.state.currentTrackId}/>
        <MenuBox playlists={this.state.playlists}
                 currentPlaylistId={this.state.currentPlaylistId}/>
      </div>
    );
  },
  _onChange: function () {
    this.setState(getStateFromStores());
  }
});
