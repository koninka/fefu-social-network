/*
 * Playlist Object for the jPlayer Plugin
 * http://www.jplayer.org
 *
 * Copyright (c) 2009 - 2014 Happyworm Ltd
 * Licensed under the MIT license.
 * http://www.opensource.org/licenses/MIT
 *
 * Author: Mark J Panaghiston
 * Version: 2.4.1
 * Date: 19th November 2014
 *
 * Requires:
 *  - jQuery 1.7.0+
 *  - jPlayer 2.8.2+
 */

/*global jPlayerPlaylist:true */

(function($, undefined) {

  jPlayerPlaylist = function(cssSelector, playlist, options) {
    var self = this;

    this.current = 0;
    this.loop = false; // Flag used with the jPlayer repeat event
    this.shuffled = false;
    this.removing = false; // Flag is true during remove animation, disabling the remove() method until complete.

    this.cssSelector = $.extend({}, this._cssSelector, cssSelector); // Object: Containing the css selectors for jPlayer and its cssSelectorAncestor
    this.options = $.extend(true, {
      keyBindings: {
        next: {
          key: 221, // ]
          fn: function() {
            self.next();
          }
        },
        previous: {
          key: 219, // [
          fn: function() {
            self.previous();
          }
        },
        shuffle: {
          key: 83, // s
          fn: function() {
            self.shuffle();
          }
        }
      },
      stateClass: {
        shuffled: "jp-state-shuffled"
      }
    }, this._options, options); // Object: The jPlayer constructor options for this playlist and the playlist options

    this.playlist = []; // Array of Objects: The current playlist displayed (Un-shuffled or Shuffled)
    this.original = []; // Array of Objects: The original playlist
    this.userPlaylist = []; // Array of Objects: The user's playlist
    this.foundPlaylist = []; // Array of Objects: The search query result

    this.notOriginalDisplayed = false;
    this.playingFound = false;

    this.currentEditingMp3 = null;

    this._initPlaylist(playlist); // Copies playlist to this.original. Then mirrors this.original to this.playlist. Creating two arrays, where the element pointers match. (Enables pointer comparison.)

    // Setup the css selectors for the extra interface items used by the playlist.
    this.cssSelector.details = this.cssSelector.cssSelectorAncestor + " .jp-details"; // Note that jPlayer controls the text in the title element.
    this.cssSelector.playlist = this.cssSelector.cssSelectorAncestor + " .jp-playlist";
    this.cssSelector.next = this.cssSelector.cssSelectorAncestor + " .jp-next";
    this.cssSelector.previous = this.cssSelector.cssSelectorAncestor + " .jp-previous";
    this.cssSelector.shuffle = this.cssSelector.cssSelectorAncestor + " .jp-shuffle";
    this.cssSelector.shuffleOff = this.cssSelector.cssSelectorAncestor + " .jp-shuffle-off";

    this.cssSelector.tracksContainer = this.cssSelector.cssSelectorAncestor + " .jp-tracks-container";

    this.cssSelector.foundTracks = this.cssSelector.cssSelectorAncestor + " .jp-found-tracks";
    this.cssSelector.foundTracksContainer = this.cssSelector.cssSelectorAncestor + " .jp-found-tracks-container";
    this.cssSelector.foundTracksTitle = this.cssSelector.cssSelectorAncestor + " .jp-found-tracks-title";

    this.cssSelector.editDialog = {};

    this.cssSelector.editDialog.titleInput = '#title_input';
    this.cssSelector.editDialog.artistInput = '#artist_input';

    // Setup the DOM-elements for my own search extension
    this.searchContainer = $(this.cssSelector.cssSelectorAncestor + " .jp-search-container");
    this.searchWhatInput = $(this.searchContainer).find('.jp-search-what');
    this.searchByCombobox = $(this.searchContainer).find('.jp-search-by');
    this.editDialog = $('#edit-dialog');

    // Override the cssSelectorAncestor given in options
    this.options.cssSelectorAncestor = this.cssSelector.cssSelectorAncestor;

    // Override the default repeat event handler
    this.options.repeat = function(event) {
      self.loop = event.jPlayer.options.loop;
    };

    // Create a ready event handler to initialize the playlist
    $(this.cssSelector.jPlayer).bind($.jPlayer.event.ready, function() {
      self._init();
    });

    // Create an ended event handler to move to the next item
    $(this.cssSelector.jPlayer).bind($.jPlayer.event.ended, function() {
      self.next();
    });

    // Create a play event handler to pause other instances
    $(this.cssSelector.jPlayer).bind($.jPlayer.event.play, function() {
      $(this).jPlayer("pauseOthers");
    });

    // Create a resize event handler to show the title in full screen mode.
    $(this.cssSelector.jPlayer).bind($.jPlayer.event.resize, function(event) {
      if(event.jPlayer.options.fullScreen) {
        $(self.cssSelector.details).show();
      } else {
        $(self.cssSelector.details).hide();
      }
    });

    // Create click handlers for the extra buttons that do playlist functions.
    $(this.cssSelector.previous).click(function(e) {
      e.preventDefault();
      self.previous();
      self.blur(this);
    });

    $(this.cssSelector.next).click(function(e) {
      e.preventDefault();
      self.next();
      self.blur(this);
    });

    $(this.cssSelector.shuffle).click(function(e) {
      e.preventDefault();
      if(self.shuffled && $(self.cssSelector.jPlayer).jPlayer("option", "useStateClassSkin")) {
        self.shuffle(false);
      } else {
        self.shuffle(true);
      }
      self.blur(this);
    });
    $(this.cssSelector.shuffleOff).click(function(e) {
      e.preventDefault();
      self.shuffle(false);
      self.blur(this);
    }).hide();

    // Put the title in its initial display state
    if(!this.options.fullScreen) {
      $(this.cssSelector.details).hide();
    }

    // Remove the empty <li> from the page HTML.
    // Allows page to be valid HTML, while not interfereing with display animations
    $(this.cssSelector.playlist + " ul").empty();

    // Create .on() handlers for the playlist items along with the free media
    // and remove controls.
    this._createItemHandlers();

    // Instance jPlayer
    $(this.cssSelector.jPlayer).jPlayer(this.options);

    $(this.searchWhatInput).change(this._handleSearchInputEvent.bind(this));
  };

  jPlayerPlaylist.prototype = {
    _cssSelector: { // static object, instanced in constructor
      jPlayer: "#jquery_jplayer_1",
      cssSelectorAncestor: "#jp_container_1"
    },
    _options: { // static object, instanced in constructor
      playlistOptions: {
        autoPlay: false,
        loopOnPrevious: true,
        shuffleOnLoop: true,
        enableRemoveControls: false,
        displayTime: 'slow',
        addTime: 'fast',
        removeTime: 'fast',
        shuffleTime: 'slow',
        itemClass: "jp-playlist-item",
        freeGroupClass: "jp-free-media",
        freeItemClass: "jp-playlist-item-free",
        removeItemClass: "jp-playlist-item-remove",
        addItemClass: "jp-playlist-item-add",
        editItemClass: "jp-playlist-item-edit"
      }
    },
    _findMediaByTrackId: function (id) {
      for (var i = 0; i < this.userPlaylist.length; ++i) {
        if (this.userPlaylist[i]['id'] === id) {
          return this.userPlaylist[i];
        }
      }

      return null;
    },
    _clearPlaylist: function () {
      this.playlist = [];
      this.original = [];

      $(this.cssSelector.tracksContainer).empty();
    },
    _clearFoundPlaylist: function () {
      this.foundPlaylist = [];

      $(this.cssSelector.foundTracks).hide();
      $(this.cssSelector.foundTracksContainer).empty();
    },
    _fillPlaylist: function () {
      for (var i = 0; i < this.userPlaylist.length; ++i) {
        this.add(this.userPlaylist[i]);
      }

      this.notOriginalDisplayed = false;
    },
    _filterPlaylist: function (what, by) {
      this._clearPlaylist();

      var what_ = what.replace(/([.*+?^=!:${}()|\[\]\/\\])/g, "\\$1")
              .replace(/\s+$/, '')
              .replace(/\s+/g, '\\s+');

      for (var i = 0; i < this.userPlaylist.length; ++i) {
        var item = this.userPlaylist[i];
        var searchOption = by === 'title' ? item['title'] : item['artist'];

        if (searchOption.match(new RegExp('^' + what_, 'i'))) {
          this.add(item);
        }
      }

      this.notOriginalDisplayed = true;
    },
    _sendSearchRequest: function (what, by) {
      if (what.length === 0) {
        return;
      }
      $.post('/search/audio/' + by + '/' + what,
        null,
        this._handleSearchRequest.bind(this)
      );
    },
    _handleSearchInputEvent: function (e) {
      var what = $(this.searchWhatInput).val();
      var by = $(this.searchByCombobox).val();

      if (0 === what.length && this.notOriginalDisplayed) {
        this._clearPlaylist();
        this._fillPlaylist();
        this._clearFoundPlaylist();
      } else {
        this._filterPlaylist(what, by);
        this._sendSearchRequest(what, by);
      }
    },
    _handleSearchRequest: function (data, textStatus, jqXHR) {
      if (data.status === 'ok') {
        var medias = data.tracks;
        this._clearFoundPlaylist();

        $(this.cssSelector.foundTracksTitle).text(
          'The search returned ' + medias.length + ' audio tracks'
        );

        var ul = $(this.cssSelector.foundTracksContainer);

        ul.empty();

        for (var i = 0; i < medias.length; ++i) {
          medias[i].mp3 = '/download/audio/' + medias[i].id;
          ul.append(this._createFoundListItem(medias[i]));
          this.foundPlaylist.push(medias[i]);
        }

        $(this.cssSelector.foundTracks).show();
      }
    },
    _handleAddResponse: function (data, textStatus, jqXHR) {
      if (data.status === 'ok') {
        var media = {
          id: data.id,
          mp3: '/download/audio/' + data.id,
          artist: data.artist,
          title: data.title
        };

        this.userPlaylist.push(media);
        alert('Audio ' + media.artist + ' - ' + media.title
          + ' was successfully added to your playlist.');
      }
    },
    _sendEditRequest: function (id) {
      $.post(
        '/audio/edit/' + id,
        JSON.stringify({
          title: this.editDialog.find(this.cssSelector.editDialog.titleInput).val(),
          artist: this.editDialog.find(this.cssSelector.editDialog.artistInput).val()
        }),
        this._handleEditResponse.bind(this)
      );
    },
    _handleEditResponse: function (data, textStatus, jqXHR) {
      if (data['status'] === 'ok') {
        var index = -1;
        var media = data['metadata'];

        media['mp3'] = Routing.generate('file_mp3_get', { file_id: media['id'] });

        for (var i = 0; i < this.userPlaylist.length; ++i) {
          if (this.userPlaylist[i]['id'] == media['old_id']) {
            $.extend(this.userPlaylist[i], media);

            index = i;
          }
        }

        this.playlist = $.extend([], this.userPlaylist);
        this.original = $.extend([], this.userPlaylist);

        if (!this.notOriginalDisplayed) {
          $(this.cssSelector.tracksContainer)
            .find('li:nth-child(' + (index + 1) + ')')
            .remove();
          if (index > 0) {
            $(this.cssSelector.tracksContainer)
              .find('li:nth-child(' + index + ')')
              .after(this._createListItem(media));
          } else {
            $(this.cssSelector.tracksContainer)
              .append(this._createListItem(media));
          }

        }
      }
    },
    _handleDeleteResponse: function (data, textStatus, jqXHR) {
      if (data['status'] === 'ok') {
        var id = data['id'];

        for (var i = 0; i < this.userPlaylist.length; ++i) {
          if (id === this.userPlaylist[i]['id']) {
            if (this.userPlaylist.length > 1) {
              this.userPlaylist.splice(i, 1);
            } else {
              this.userPlaylist = [];
            }
          }
        }

        this._clearPlaylist();
        this._fillPlaylist();
      }
    },
    _createFoundListItem: function(media) {
      var self = this;

      var listItem = "<li><div>";

      listItem += "<a href='javascript:;' class='" + this.options.playlistOptions.itemClass + "' tabindex='0'>" + media.title + (media.artist ? " <span class='jp-artist'>by " + media.artist + "</span>" : "") + "</a>";
      listItem += "<a href='javasript:;' class='" + this.options.playlistOptions.addItemClass + "' tabindex='0'>+</a>";
      listItem += "</div></li>";

      var jqueryListItem = $(listItem);

      jqueryListItem
        .find('a.' + this.options.playlistOptions.itemClass)
        .on('click', function (e) {
          e.preventDefault();

          var index = $(this).parent().parent().index();

          self.pause();
          self.play(index, true);
          self.playingFound = true;
          self.blur(this);
        });

      jqueryListItem
        .find('a.' + this.options.playlistOptions.addItemClass)
        .on('click', function (e) {
          e.preventDefault();

          var index = $(this).parent().parent().index();

          // TODO: 1 <-> currentPlaylistId
          $.post('/playlist/1/push/' + self.foundPlaylist[index].id,
            null,
            self._handleAddResponse.bind(self)
          )
        });

      return jqueryListItem;
    },
    option: function(option, value) { // For changing playlist options only
      if(value === undefined) {
        return this.options.playlistOptions[option];
      }

      this.options.playlistOptions[option] = value;

      switch(option) {
        case "enableRemoveControls":
          this._updateControls();
          break;
        case "itemClass":
        case "freeGroupClass":
        case "freeItemClass":
        case "removeItemClass":
          this._refresh(true); // Instant
          this._createItemHandlers();
          break;
      }
      return this;
    },
    _init: function() {
      var self = this;
      this._refresh(function() {
        if(self.options.playlistOptions.autoPlay) {
          self.play(self.current);
        } else {
          self.select(self.current);
        }
      });
    },
    _initPlaylist: function(playlist) {
      this.current = 0;
      this.shuffled = false;
      this.removing = false;
      this.original = $.extend(true, [], playlist); // Copy the Array of Objects
      this.userPlaylist = $.extend(true, [], playlist);
      this._originalPlaylist();
    },
    _originalPlaylist: function() {
      var self = this;
      this.playlist = [];
      // Make both arrays point to the same object elements. Gives us 2 different arrays, each pointing to the same actual object. ie., Not copies of the object.
      $.each(this.original, function(i) {
        self.playlist[i] = self.original[i];
      });
    },
    _refresh: function(instant) {
      /* instant: Can be undefined, true or a function.
       *  undefined -> use animation timings
       *  true -> no animation
       *  function -> use animation timings and execute function at half way point.
       */
      var self = this;

      if(instant && !$.isFunction(instant)) {
        $(this.cssSelector.tracksContainer).empty();
        $.each(this.playlist, function(i) {
          $(self.cssSelector.tracksContainer).append(self._createListItem(self.playlist[i]));
        });
        this._updateControls();
      } else {
        var displayTime = $(this.cssSelector.tracksContainer).children().length ? this.options.playlistOptions.displayTime : 0;

        $(this.cssSelector.tracksContainer).slideUp(displayTime, function() {
          var $this = $(this);
          $(this).empty();

          $.each(self.playlist, function(i) {
            $this.append(self._createListItem(self.playlist[i]));
          });
          self._updateControls();
          if($.isFunction(instant)) {
            instant();
          }
          if(self.playlist.length) {
            $(this).slideDown(self.options.playlistOptions.displayTime);
          } else {
            $(this).show();
          }
        });
      }
    },
    _createListItem: function(media) {
      var self = this;

      // Wrap the <li> contents in a <div>
      var listItem = "<li><div>";

      // Create remove control
      listItem += "<a href='javascript:;' class='" + this.options.playlistOptions.removeItemClass + "'>&times;</a>";
      listItem += "<a href='javascript:;' class='" + this.options.playlistOptions.editItemClass + "'>edit</a>";

      // Create links to free media
      if(media.free) {
        var first = true;
        listItem += "<span class='" + this.options.playlistOptions.freeGroupClass + "'>(";
        $.each(media, function(property,value) {
          if($.jPlayer.prototype.format[property]) { // Check property is a media format.
            if(first) {
              first = false;
            } else {
              listItem += " | ";
            }
            listItem += "<a class='" + self.options.playlistOptions.freeItemClass + "' href='" + value + "' tabindex='-1'>" + property + "</a>";
          }
        });
        listItem += ")</span>";
      }

      // The title is given next in the HTML otherwise the float:right on the free media corrupts in IE6/7
      listItem += "<a href='javascript:;' class='" + this.options.playlistOptions.itemClass + "' tabindex='0'>" + media.title + (media.artist ? " <span class='jp-artist'>by " + media.artist + "</span>" : "") + "</a>";
      listItem += "</div></li>";

      return listItem;
    },
    addToUserPlaylist: function (media) {
      if (media.constructor === Array) {
        for (var i = media.length - 1; i >= 0; i--) {
          this.userPlaylist.push(media[i]);
        };
      } else {
        this.userPlaylist.push(media);
      }
      this._clearPlaylist();
      this._fillPlaylist();
    },
    _createItemHandlers: function() {
      var self = this;
      // Create live handlers for the playlist items
      $(this.cssSelector.tracksContainer).off("click", "a." + this.options.playlistOptions.itemClass).on("click", "a." + this.options.playlistOptions.itemClass, function(e) {
        e.preventDefault();
        self.playingFound = false;
        var index = $(this).parent().parent().index();
        if(self.current !== index) {
          self.play(index);
        } else {
          $(self.cssSelector.jPlayer).jPlayer("play");
        }
        self.blur(this);
      });

      // Create live handlers that disable free media links to force access via right click
      $(this.cssSelector.playlist).off("click", "a." + this.options.playlistOptions.freeItemClass).on("click", "a." + this.options.playlistOptions.freeItemClass, function(e) {
        e.preventDefault();
        $(this).parent().parent().find("." + self.options.playlistOptions.itemClass).click();
        self.blur(this);
      });

      // Create live handlers for the remove controls
      $(this.cssSelector.playlist)
        .off("click", "a." + this.options.playlistOptions.removeItemClass)
        .on("click", "a." + this.options.playlistOptions.removeItemClass,
          function (e) {
            e.preventDefault();

            var index = $(this).parent().parent().index();
            var id = +self.playlist[index]['mp3'].match(/[0-9]+$/)[0];

            // TODO: /delete/audio/id/playlist/id <- current playlist id
            $.post('/delete/audio/' + id + '/playlist/1',
              self._handleDeleteResponse.bind(self)
            );
        });

      $(this.cssSelector.playlist)
        .off('click', 'a.' + this.options.playlistOptions.editItemClass)
        .on('click', 'a.' + this.options.playlistOptions.editItemClass,
          function (e) {
            e.preventDefault();

            var index = $(this).parent().parent().index();
            var id = +self.playlist[index]['mp3'].match(/[0-9]+$/)[0];

            self.editDialog.dialog({
              modal: true,
              draggable: false,
              resizable: false,
              position: ['center', 'center'],
              show: 'blind',
              hide: 'blind',
              width: 400,
              open: function () {
                var media = self.playlist[index];

                self.editDialog
                  .find(self.cssSelector.editDialog.titleInput)
                  .val(media['title']);

                self.editDialog
                  .find(self.cssSelector.editDialog.artistInput)
                  .val(media['artist']);

              },
              buttons: {
                "Edit": function() {
                  self._sendEditRequest(id);
                  $(this).dialog("close");
                }
              }
            });
        });
    },
    _updateControls: function() {
      if(this.options.playlistOptions.enableRemoveControls) {
        $(this.cssSelector.playlist + " ." + this.options.playlistOptions.removeItemClass).show();
      } else {
        $(this.cssSelector.playlist + " ." + this.options.playlistOptions.removeItemClass).hide();
      }

      if(this.shuffled) {
        $(this.cssSelector.jPlayer).jPlayer("addStateClass", "shuffled");
      } else {
        $(this.cssSelector.jPlayer).jPlayer("removeStateClass", "shuffled");
      }
      if($(this.cssSelector.shuffle).length && $(this.cssSelector.shuffleOff).length) {
        if(this.shuffled) {
          $(this.cssSelector.shuffleOff).show();
          $(this.cssSelector.shuffle).hide();
        } else {
          $(this.cssSelector.shuffleOff).hide();
          $(this.cssSelector.shuffle).show();
        }
      }
    },
    _highlight: function(index, found) {
      if(this.playlist.length && index !== undefined) {
        $(this.cssSelector.playlist + " .jp-playlist-current").removeClass("jp-playlist-current");
        $(
          (found ? this.cssSelector.foundTracksContainer : this.cssSelector.tracksContainer)
          + " li:nth-child(" + (index + 1) + ")"
        )
          .addClass("jp-playlist-current")
          .find(".jp-playlist-item")
          .addClass("jp-playlist-current");
      }
    },
    setPlaylist: function(playlist) {
      this._initPlaylist(playlist);
      this._init();
    },
    add: function(media, playNow) {
      $(this.cssSelector.tracksContainer).append(this._createListItem(media));
      this._updateControls();
      this.original.push(media);
      this.playlist.push(media); // Both array elements share the same object pointer. Comforms with _initPlaylist(p) system.

      if(playNow) {
        this.play(this.playlist.length - 1);
      } else {
        if(this.original.length === 1 &&
          $('#jquery_jplayer_1').data().jPlayer.status.paused) {
          this.select(0);
        }
      }
    },
    remove: function(index) {
      var self = this;

      if(index === undefined) {
        this._initPlaylist([]);
        this._refresh(function() {
          $(self.cssSelector.jPlayer).jPlayer("clearMedia");
        });
        return true;
      } else {

        if(this.removing) {
          return false;
        } else {
          index = (index < 0) ? self.original.length + index : index; // Negative index relates to end of array.
          if(0 <= index && index < this.playlist.length) {
            this.removing = true;

            $(this.cssSelector.playlist + " li:nth-child(" + (index + 1) + ")").each(function() {
              $(this).remove();

              if(self.shuffled) {
                var item = self.playlist[index];
                $.each(self.original, function(i) {
                  if(self.original[i] === item) {
                    self.original.splice(i, 1);
                    return false; // Exit $.each
                  }
                });
                self.playlist.splice(index, 1);
              } else {
                self.original.splice(index, 1);
                self.playlist.splice(index, 1);
              }

              if(self.original.length) {
                if(index === self.current) {
                  self.current = (index < self.original.length) ? self.current : self.original.length - 1; // To cope when last element being selected when it was removed
                  self.select(self.current);
                } else if(index < self.current) {
                  self.current--;
                }
              } else {
                $(self.cssSelector.jPlayer).jPlayer("clearMedia");
                self.current = 0;
                self.shuffled = false;
                self._updateControls();
              }

              self.removing = false;
            });
          }
          return true;
        }
      }
    },
    select: function(index, found) {
      var list = found ? this.foundPlaylist : this.playlist;

      index = (index < 0) ? list.length + index : index; // Negative index relates to end of array.

      if (0 <= index && index < list.length) {
        this.current = index;
        this._highlight(index, found);
        $(this.cssSelector.jPlayer).jPlayer("setMedia", list[index]);
      } else {
        this.current = 0;
      }
    },
    play: function(index, found) {
      var list = found ? this.foundPlaylist : this.playlist;

      index = (index < 0) ? list.length + index : index; // Negative index relates to end of array.

      if(0 <= index && index < list.length) {
        if (list.length) {
          this.select(index, found);
          $(this.cssSelector.jPlayer).jPlayer("play");
        }
      } else if(index === undefined) {
        $(this.cssSelector.jPlayer).jPlayer("play");
      }
    },
    pause: function() {
      $(this.cssSelector.jPlayer).jPlayer("pause");
    },
    next: function() {
      if (this.playingFound) {
        var index = (this.current + 1) % this.foundPlaylist.length;

        if (this.loop || index > 0) {
          this.play(index, true);
        }
      } else {
        var index = (this.current + 1) % this.playlist.length;

        if(this.loop) {
          // See if we need to shuffle before looping to start, and only shuffle if more than 1 item.
          if(index === 0
            && this.shuffled
            && this.options.playlistOptions.shuffleOnLoop
            && this.playlist.length > 1) {

            this.shuffle(true, true); // playNow
          } else {

            this.play(index);
          }
        } else {
          // The index will be zero if it just looped round
          if(index > 0) {
            this.play(index);
          }
        }
      }
    },
    previous: function() {
      if (this.playingFound) {
        var index = (this.current - 1 >= 0) ? this.current - 1 : this.foundPlaylist.length - 1;

        if (this.loop || index < this.foundPlaylist.length - 1) {
          this.play(index, true);
        }
      } else {
        var index = (this.current - 1 >= 0) ? this.current - 1 : this.playlist.length - 1;

        if(this.loop && this.options.playlistOptions.loopOnPrevious || index < this.playlist.length - 1) {
          this.play(index);
        }
      }
    },
    shuffle: function(shuffled, playNow) {
      var self = this;

      if(shuffled === undefined) {
        shuffled = !this.shuffled;
      }

      if(shuffled || shuffled !== this.shuffled) {

        $(this.cssSelector.tracksContainer).slideUp(this.options.playlistOptions.shuffleTime, function() {
          self.shuffled = shuffled;
          if(shuffled) {
            self.playlist.sort(function() {
              return 0.5 - Math.random();
            });
          } else {
            self._originalPlaylist();
          }
          self._refresh(true); // Instant

          if(playNow || !$(self.cssSelector.jPlayer).data("jPlayer").status.paused) {
            self.play(0);
          } else {
            self.select(0);
          }

          $(this).slideDown(self.options.playlistOptions.shuffleTime);
        });
      }
    },
    blur: function(that) {
      if($(this.cssSelector.jPlayer).jPlayer("option", "autoBlur")) {
        $(that).blur();
      }
    }
  };
})(jQuery);
