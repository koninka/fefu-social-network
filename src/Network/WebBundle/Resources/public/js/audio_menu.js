'use strict';

var MenuBox = React.createClass({
  render: function() {
    return (
      <div className="menu-box">
        <ul>
          <li>Music<a href="#modal-upload" className="btn btn-upload btn-right">
            <b>&#8683;</b></a></li>
          <li><h2>Playlists</h2></li>
          <PlaylistList data={this.props.playlists}
                        currentPlaylistId={this.props.currentPlaylistId}/>
          <li><CreatePlaylist/></li>
        </ul>
      </div>
    );
  }
});

var CreatePlaylist = React.createClass({
  render: function() {
    return (
      <li>
        <input type="text" className="new-playlist-name"/>
        <a className="create-playlist btn btn-right"
           onClick={this._addPlaylistClick}>&#10010;</a>
      </li>
    );
  },
  _addPlaylistClick: function (e) {
    e.preventDefault();
    Actions.addPlaylist($('.new-playlist-name').val());
    $('.new-playlist-name').val('');
  }
});

var PlaylistList = React.createClass({
  render: function() {
    var playlistNodes = this.props.data.map(function (playlist) {
      return (
        <PlaylistListItem
        className="playlist-list-item"
        name={playlist.name}
        playlistId={playlist.id}
        key={playlist.id}
        currentPlaylistId={this.props.currentPlaylistId}>
          {playlist.items.length}
        </PlaylistListItem>
      );
    }.bind(this));
    return (
      <div className="playlist-list">
        <ul>
          {playlistNodes}
        </ul>
      </div>
    );
  }
});

var PlaylistListItem = React.createClass({
  render: function() {
    var classNameString = "playlist-name ";
    if (this.props.playlistId === this.props.currentPlaylistId) {
      classNameString += "playlist-current ";
    }
    return (
      <li className="playlist-list-item">
        <a className={classNameString}
           onClick={this._itemClilck}>
           {this.props.name} ({this.props.children})
        </a>
        <a className="btn btn-right"
           onClick={this._removePlaylistClick}>&#10006;
        </a>
      </li>
    );
  },
  _removePlaylistClick: function (e) {
    e.preventDefault();
    Actions.removePlaylist(this.props.playlistId);
  },
  _itemClilck: function (e) {
    e.preventDefault();
    if (this.props.playlistId !== this.props.currentPlaylistId) {
      Actions.setCurrentPlaylist(this.props.playlistId);
    }
  }
});
