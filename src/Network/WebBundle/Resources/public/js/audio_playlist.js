'use strict';

var PlaylistItem = React.createClass({
  render: function() {
    var orUnknown = function (d) { return d.length === 0 ? "Unknown" : d; }
    var title = orUnknown(this.props.item.title);
    var artist = orUnknown(this.props.item.artist);
    var classNameString = 'playlist-item ';
    if (this.props.item.id === this.props.currentTrackId) {
      classNameString += 'track-current';
    }
    return (
      <li className={classNameString}>
        <a className="btn-item-play btn" onClick={this._pauseClick}>&#10074;&#10074;</a>
        <a className="btn-item-play btn" onClick={this._playClick}>&#9654;</a>
        <a>{artist}</a>
        <span> – </span>
        <span>{title}</span>
        <a className="btn btn-right" onClick={this._removeClick}>
          &#10006;
        </a>
        <a className="btn btn-right" onClick={this._addClick}>
          &#10010;
        </a>
        <a className="btn btn-right">
          <b>&#8801;</b>
        </a>
      </li>
    );
  },
  _addClick: function (e) {
    e.preventDefault(e);
    Actions.pushPlaylistItem(this.props.item);
  },
  _removeClick: function(e) {
    e.preventDefault();
    Actions.removePlaylistItem(this.props.playlistId, this.props.item.id);
  },
  _playClick: function (e) {
    e.preventDefault();
    Actions.play(this.props.item);
  },
  _pauseClick: function (e) {
    e.preventDefault();
    Actions.pause(this.props.item);
  }
});

var Playlist = React.createClass({
  render: function() {
    this.props.playlist = this.props.playlist || {};
    var playlistItems = this.props.playlist.items || [];
    var items = playlistItems.map(function (item) {
      return (
        <PlaylistItem item={item}
                      playlistId={this.props.playlist.id}
                      currentTrackId={this.props.currentTrackId}
                      key={item.id}>
        </PlaylistItem>
      );
    }.bind(this));
    return (
      <div className="playlist">
      <ul>
        {items}
      </ul>
      </div>
    );
  }
});
