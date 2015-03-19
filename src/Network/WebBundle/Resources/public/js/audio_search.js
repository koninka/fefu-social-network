var SearchHeader = React.createClass({
  render: function () {
    return (
      <div className="jp-playlist">
        <div className="jp-search-container">
          <label htmlFor="search_input">Search</label>
          <input type="text" className="jp-search-what" id="search_input"
                 onChange={this._handleSearch}/>
          <select className="jp-search-by">
            <option value="title">by title</option>
            <option value="artist">by artist</option>
          </select>
        </div>
        <ul className="jp-tracks-container">
          <li>&nbsp;</li>
        </ul>
        <div className="jp-found-tracks">
          <span className="jp-found-tracks-title"></span>
          <ul className="jp-found-tracks-container">
            <li>&nbsp;</li>
          </ul>
        </div>
      </div>
    );
  },
  _handleSearch: function () {
    var what = $('.jp-search-what').val();
    var by = $('.jp-search-by').val();
    Actions.search(what, by);
  }
});

var SearchBox = React.createClass({
  render: function () {
    return (
      <SearchHeader/>
    );
  }
});
