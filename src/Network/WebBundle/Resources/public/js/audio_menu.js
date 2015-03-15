var data = [
  {
    name: 'music',
    count: 193
  },
  {
    name: 'cute',
    count: 20
  }
]

var MenuBox = React.createClass({
  handleAddNewPlaylist: function() {
    Promise.resolve($.post('/playlist/add/pes')).then(function(response) {
      if (response.status === 'ok') {
        this.loadPlaylistsFromServer();
      }
    }.bind(this)).catch(function(e) {
      console.log(e);
    }.bind(this));
  },
  loadPlaylistsFromServer: function() {
    Promise.resolve($.post('/playlist/all')).then(function(response) {
      if (response.status === 'ok') {
        this.setState({data: response.playlists});
      }
    }.bind(this)).catch(function(e) {
      console.log(e);
    }.bind(this));
  },
  handleRemovePlaylist: function (playlist_id) {
    Promise.resolve($.post('/playlist/remove/' + playlist_id)).then(function(response) {
      if (response.status === 'ok') {
        this.loadPlaylistsFromServer();
      }
    }.bind(this)).catch(function(e) {
      console.log(e);
    }.bind(this));
  },
  getInitialState: function() {
    return {data: []};
  },
  componentDidMount: function() {
    this.loadPlaylistsFromServer();
    setInterval(this.loadPlaylistsFromServer, 32000);
  },
  render: function() {
    return (
      <div className="menuBox" style={{float: 'right'}}>
        <h1>Playlists</h1>
        <PlaylistList data={this.state.data} onRemovePlaylist={this.handleRemovePlaylist}/>
        <CreatePlaylist onAddPlaylist={this.handleAddNewPlaylist}/>
      </div>
    );
  }
});

var CreatePlaylist = React.createClass({
  getInitialState: function() {
    return {name: false};
  },
  handleClick: function (e) {
    e.preventDefault();
    this.props.onAddPlaylist();
  },
  render: function() {
    var text = this.state.liked ? 'like' : 'haven\'t liked';
    return (
      <p onClick={this.handleClick}>
        Add
      </p>
    );
  }
});

var PlaylistList = React.createClass({
  render: function() {
    var playlistNodes = this.props.data.map(function (playlist) {
      return (
        <PlaylistItem name={playlist.name} playlistId={playlist.id} onRemovePlaylist={this.props.onRemovePlaylist}>
          {playlist.count}
        </PlaylistItem>
      );
    }.bind(this));
    return (
      <div className="playlistList">
        {playlistNodes}
      </div>
    );
  }
});

var PlaylistItem = React.createClass({
  handleClick: function (e) {
    e.preventDefault();
    console.log(this.props);
    this.props.onRemovePlaylist(this.props.playlistId);
  },
  render: function() {
    return (
      <div className="playlistItem">
        <p className="playlistName">
          {this.props.name}
          <span onClick={this.handleClick}> [X]</span>
        </p>
      </div>
    );
  }
});

React.render(
  <MenuBox />,
  document.getElementById('audio_menu')
);
