'use strict';

var PlaylistItem = React.createClass({
  render: function() {
    var orUnknown = function (d) { return d.length === 0 ? "Unknown" : d; }
    var title = orUnknown(this.props.item.title);
    var artist = orUnknown(this.props.item.artist);
    var itemClassNameString = 'playlist-item ';
    var playClassNameString = 'btn-item-play btn ';
    var pauseClassNameString = 'btn-item-pause btn ';
    var curId = this.props.item.id === this.props.currentTrackId;
    var paused = this.props.playState === 'paused';
    if (curId) {
      itemClassNameString += 'track-current';
    }
    if (curId && !paused) {
      playClassNameString = 'invisible ' + playClassNameString;
    } else {
      pauseClassNameString = 'invisible ' + playClassNameString;
    }
    var addClassNameString = 'btn btn-right ';
    var removeClassNameString = 'btn btn-right ';
    var editClassNameString = 'btn btn-right ';
    if (this.props.currentUserId === this.props.playlistUserId) {
      addClassNameString += 'invisible ';
    } else {
      removeClassNameString += 'invisible ';
      editClassNameString += 'invisible ';
    }
    return (
      <li className={itemClassNameString}
        onClick={this._itemClick}>
        <a className={pauseClassNameString}
        onClick={this._pauseClick}>
          &#10074;&#10074;
        </a>
        <a className={playClassNameString}
        onClick={this._playClick}>
          &#9654;
        </a>
        <a className='artist-name'>
          {artist}
        </a>
        <span> – </span>
        <span className='title-name'>
          {title}
        </span>
        <a className={removeClassNameString}
        onClick={this._removeClick}>
          &#10006;
        </a>
        <a className={addClassNameString}
        onClick={this._addClick}>
          &#10010;
        </a>
        <a className={editClassNameString}
        onClick={this._editClick}>
          <b>&#8801;</b>
        </a>
      </li>
    );
  },
  _addClick: function (e) {
    e.stopPropagation();
    e.preventDefault(e);
    Actions.pushPlaylistItem(this.props.item);
  },
  _removeClick: function(e) {
    e.stopPropagation();
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
  },
  _editClick: function (e) {
    e.stopPropagation();
    e.preventDefault();
    Actions.edit(this.props.item);
  },
  _itemClick: function (e) {
    e.preventDefault();
    if (this.props.playState === 'paused' ||
        this.props.item.id !== this.props.currentTrackId) {
      Actions.play(this.props.item);
    } else if (this.props.playState === 'playing') {
      Actions.pause(this.props.item);
    }
  }
});

var Playlist = React.createClass({
  render: function() {
    this.props.playlist = this.props.playlist || {};
    var playlistItems = this.props.playlist.items || [];
    var items = playlistItems.map(function (item) {
      return (
        <PlaylistItem item={item}
                      currentUserId={this.props.thisUserId}
                      playlistUserId={this.props.playlist.userId}
                      playlistId={this.props.playlist.id}
                      currentTrackId={this.props.currentTrackId}
                      playState={this.props.playState}
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
