/*! @name videojs-playlist-ui @version 3.5.2 @license Apache-2.0 */
(function (global, factory) {
  typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory(require('global/document'), require('video.js')) :
  typeof define === 'function' && define.amd ? define(['global/document', 'video.js'], factory) :
  (global.videojsPlaylistUi = factory(global.document,global.videojs));
}(this, (function (document,videojs) { 'use strict';

  document = document && document.hasOwnProperty('default') ? document['default'] : document;
  videojs = videojs && videojs.hasOwnProperty('default') ? videojs['default'] : videojs;

  function _inheritsLoose(subClass, superClass) {
    subClass.prototype = Object.create(superClass.prototype);
    subClass.prototype.constructor = subClass;
    subClass.__proto__ = superClass;
  }

  var version = "3.5.2";

  var dom = videojs.dom || videojs;
  var registerPlugin = videojs.registerPlugin || videojs.plugin; // Array#indexOf analog for IE8

  var indexOf = function indexOf(array, target) {
    for (var i = 0, length = array.length; i < length; i++) {
      if (array[i] === target) {
        return i;
      }
    }

    return -1;
  }; // see https://github.com/Modernizr/Modernizr/blob/master/feature-detects/css/pointerevents.js


  var supportsCssPointerEvents = function () {
    var element = document.createElement('x');
    element.style.cssText = 'pointer-events:auto';
    return element.style.pointerEvents === 'auto';
  }();

  var defaults = {
    className: 'vjs-playlist',
    playOnSelect: false,
    supportsCssPointerEvents: supportsCssPointerEvents
  }; // we don't add `vjs-playlist-now-playing` in addSelectedClass
  // so it won't conflict with `vjs-icon-play
  // since it'll get added when we mouse out

  var addSelectedClass = function addSelectedClass(el) {
    el.addClass('vjs-selected');
  };

  var removeSelectedClass = function removeSelectedClass(el) {
    el.removeClass('vjs-selected');

    if (el.thumbnail) {
      dom.removeClass(el.thumbnail, 'vjs-playlist-now-playing');
    }
  };

  var upNext = function upNext(el) {
    el.addClass('vjs-up-next');
  };

  var notUpNext = function notUpNext(el) {
    el.removeClass('vjs-up-next');
  };

  var createThumbnail = function createThumbnail(thumbnail) {
    if (!thumbnail) {
      var placeholder = document.createElement('div');
      placeholder.className = 'vjs-playlist-thumbnail vjs-playlist-thumbnail-placeholder';
      return placeholder;
    }

    var picture = document.createElement('picture');
    picture.className = 'vjs-playlist-thumbnail';

    if (typeof thumbnail === 'string') {
      // simple thumbnails
      var img = document.createElement('img');
      img.src = thumbnail;
      img.alt = '';
      picture.appendChild(img);
    } else {
      // responsive thumbnails
      // additional variations of a <picture> are specified as
      // <source> elements
      for (var i = 0; i < thumbnail.length - 1; i++) {
        var _variant = thumbnail[i];
        var source = document.createElement('source'); // transfer the properties of each variant onto a <source>

        for (var prop in _variant) {
          source[prop] = _variant[prop];
        }

        picture.appendChild(source);
      } // the default version of a <picture> is specified by an <img>


      var variant = thumbnail[thumbnail.length - 1];

      var _img = document.createElement('img');

      _img.alt = '';

      for (var _prop in variant) {
        _img[_prop] = variant[_prop];
      }

      picture.appendChild(_img);
    }

    return picture;
  };

  var Component = videojs.getComponent('Component');

  var PlaylistMenuItem =
  /*#__PURE__*/
  function (_Component) {
    _inheritsLoose(PlaylistMenuItem, _Component);

    function PlaylistMenuItem(player, playlistItem, settings) {
      var _this;

      if (!playlistItem.item) {
        throw new Error('Cannot construct a PlaylistMenuItem without an item option');
      }

      _this = _Component.call(this, player, playlistItem) || this;
      _this.item = playlistItem.item;
      _this.playOnSelect = settings.playOnSelect;

      _this.emitTapEvents();

      _this.on(['click', 'tap'], _this.switchPlaylistItem_);

      _this.on('keydown', _this.handleKeyDown_);

      return _this;
    }

    var _proto = PlaylistMenuItem.prototype;

    _proto.handleKeyDown_ = function handleKeyDown_(event) {
      // keycode 13 is <Enter>
      // keycode 32 is <Space>
      if (event.which === 13 || event.which === 32) {
        this.switchPlaylistItem_();
      }
    };

    _proto.switchPlaylistItem_ = function switchPlaylistItem_(event) {
      this.player_.playlist.currentItem(indexOf(this.player_.playlist(), this.item));

      if (this.playOnSelect) {
        this.player_.play();
      }
    };

    _proto.createEl = function createEl() {
      var li = document.createElement('li');
      var item = this.options_.item;

      if (typeof item.data === 'object') {
        var dataKeys = Object.keys(item.data);
        dataKeys.forEach(function (key) {
          var value = item.data[key];
          li.dataset[key] = value;
        });
      }

      li.className = 'vjs-playlist-item';
      li.setAttribute('tabIndex', 0); // Thumbnail image

      this.thumbnail = createThumbnail(item.thumbnail);
      li.appendChild(this.thumbnail); // Duration

      if (item.duration) {
        var duration = document.createElement('time');
        var time = videojs.formatTime(item.duration);
        duration.className = 'vjs-playlist-duration';
        duration.setAttribute('datetime', 'PT0H0M' + item.duration + 'S');
        duration.appendChild(document.createTextNode(time));
        li.appendChild(duration);
      } // Now playing


      var nowPlayingEl = document.createElement('span');
      var nowPlayingText = this.localize('Now Playing');
      nowPlayingEl.className = 'vjs-playlist-now-playing-text';
      nowPlayingEl.appendChild(document.createTextNode(nowPlayingText));
      nowPlayingEl.setAttribute('title', nowPlayingText);
      this.thumbnail.appendChild(nowPlayingEl); // Title container contains title and "up next"

      var titleContainerEl = document.createElement('div');
      titleContainerEl.className = 'vjs-playlist-title-container';
      this.thumbnail.appendChild(titleContainerEl); // Up next

      var upNextEl = document.createElement('span');
      var upNextText = this.localize('Up Next');
      upNextEl.className = 'vjs-up-next-text';
      upNextEl.appendChild(document.createTextNode(upNextText));
      upNextEl.setAttribute('title', upNextText);
      titleContainerEl.appendChild(upNextEl); // Video title

      var titleEl = document.createElement('cite');
      var titleText = item.name || this.localize('Untitled Video');
      titleEl.className = 'vjs-playlist-name';
      titleEl.appendChild(document.createTextNode(titleText));
      titleEl.setAttribute('title', titleText);
      titleContainerEl.appendChild(titleEl);
      return li;
    };

    return PlaylistMenuItem;
  }(Component);

  var PlaylistMenu =
  /*#__PURE__*/
  function (_Component2) {
    _inheritsLoose(PlaylistMenu, _Component2);

    function PlaylistMenu(player, options) {
      var _this2;

      if (!player.playlist) {
        throw new Error('videojs-playlist is required for the playlist component');
      }

      _this2 = _Component2.call(this, player, options) || this;
      _this2.items = [];

      if (options.horizontal) {
        _this2.addClass('vjs-playlist-horizontal');
      } else {
        _this2.addClass('vjs-playlist-vertical');
      } // If CSS pointer events aren't supported, we have to prevent
      // clicking on playlist items during ads with slightly more
      // invasive techniques. Details in the stylesheet.


      if (options.supportsCssPointerEvents) {
        _this2.addClass('vjs-csspointerevents');
      }

      _this2.createPlaylist_();

      if (!videojs.browser.TOUCH_ENABLED) {
        _this2.addClass('vjs-mouse');
      }

      _this2.on(player, ['loadstart', 'playlistchange', 'playlistsorted'], function (event) {
        _this2.update();
      }); // Keep track of whether an ad is playing so that the menu
      // appearance can be adapted appropriately


      _this2.on(player, 'adstart', function () {
        _this2.addClass('vjs-ad-playing');
      });

      _this2.on(player, 'adend', function () {
        _this2.removeClass('vjs-ad-playing');
      });

      _this2.on('dispose', function () {
        _this2.empty_();

        player.playlistMenu = null;
      });

      _this2.on(player, 'dispose', function () {
        _this2.dispose();
      });

      return _this2;
    }

    var _proto2 = PlaylistMenu.prototype;

    _proto2.createEl = function createEl() {
      return dom.createEl('div', {
        className: this.options_.className
      });
    };

    _proto2.empty_ = function empty_() {
      if (this.items && this.items.length) {
        this.items.forEach(function (i) {
          return i.dispose();
        });
        this.items.length = 0;
      }
    };

    _proto2.createPlaylist_ = function createPlaylist_() {
      var playlist = this.player_.playlist() || [];
      var list = this.el_.querySelector('.vjs-playlist-item-list');
      var overlay = this.el_.querySelector('.vjs-playlist-ad-overlay');

      if (!list) {
        list = document.createElement('ol');
        list.className = 'vjs-playlist-item-list';
        this.el_.appendChild(list);
      }

      this.empty_(); // create new items

      for (var i = 0; i < playlist.length; i++) {
        var item = new PlaylistMenuItem(this.player_, {
          item: playlist[i]
        }, this.options_);
        this.items.push(item);
        list.appendChild(item.el_);
      } // Inject the ad overlay. IE<11 doesn't support "pointer-events:
      // none" so we use this element to block clicks during ad
      // playback.


      if (!overlay) {
        overlay = document.createElement('li');
        overlay.className = 'vjs-playlist-ad-overlay';
        list.appendChild(overlay);
      } else {
        // Move overlay to end of list
        list.appendChild(overlay);
      } // select the current playlist item


      var selectedIndex = this.player_.playlist.currentItem();

      if (this.items.length && selectedIndex >= 0) {
        addSelectedClass(this.items[selectedIndex]);
        var thumbnail = this.items[selectedIndex].$('.vjs-playlist-thumbnail');

        if (thumbnail) {
          dom.addClass(thumbnail, 'vjs-playlist-now-playing');
        }
      }
    };

    _proto2.update = function update() {
      // replace the playlist items being displayed, if necessary
      var playlist = this.player_.playlist();

      if (this.items.length !== playlist.length) {
        // if the menu is currently empty or the state is obviously out
        // of date, rebuild everything.
        this.createPlaylist_();
        return;
      }

      for (var i = 0; i < this.items.length; i++) {
        if (this.items[i].item !== playlist[i]) {
          // if any of the playlist items have changed, rebuild the
          // entire playlist
          this.createPlaylist_();
          return;
        }
      } // the playlist itself is unchanged so just update the selection


      var currentItem = this.player_.playlist.currentItem();

      for (var _i = 0; _i < this.items.length; _i++) {
        var item = this.items[_i];

        if (_i === currentItem) {
          addSelectedClass(item);

          if (document.activeElement !== item.el()) {
            dom.addClass(item.thumbnail, 'vjs-playlist-now-playing');
          }

          notUpNext(item);
        } else if (_i === currentItem + 1) {
          removeSelectedClass(item);
          upNext(item);
        } else {
          removeSelectedClass(item);
          notUpNext(item);
        }
      }
    };

    return PlaylistMenu;
  }(Component);
  /**
   * Returns a boolean indicating whether an element has child elements.
   *
   * Note that this is distinct from whether it has child _nodes_.
   *
   * @param  {HTMLElement} el
   *         A DOM element.
   *
   * @return {boolean}
   *         Whether the element has child elements.
   */


  var hasChildEls = function hasChildEls(el) {
    for (var i = 0; i < el.childNodes.length; i++) {
      if (dom.isEl(el.childNodes[i])) {
        return true;
      }
    }

    return false;
  };
  /**
   * Finds the first empty root element.
   *
   * @param  {string} className
   *         An HTML class name to search for.
   *
   * @return {HTMLElement}
   *         A DOM element to use as the root for a playlist.
   */


  var findRoot = function findRoot(className) {
    var all = document.querySelectorAll('.' + className);
    var el;

    for (var i = 0; i < all.length; i++) {
      if (!hasChildEls(all[i])) {
        el = all[i];
        break;
      }
    }

    return el;
  };
  /**
   * Initialize the plugin on a player.
   *
   * @param  {Object} [options]
   *         An options object.
   *
   * @param  {HTMLElement} [options.el]
   *         A DOM element to use as a root node for the playlist.
   *
   * @param  {string} [options.className]
   *         An HTML class name to use to find a root node for the playlist.
   *
   * @param  {boolean} [options.playOnSelect = false]
   *         If true, will attempt to begin playback upon selecting a new
   *         playlist item in the UI.
   */


  var playlistUi = function playlistUi(options) {
    var player = this;

    if (!player.playlist) {
      throw new Error('videojs-playlist plugin is required by the videojs-playlist-ui plugin');
    }

    if (dom.isEl(options)) {
      videojs.log.warn('videojs-playlist-ui: Passing an element directly to playlistUi() is deprecated, use the "el" option instead!');
      options = {
        el: options
      };
    }

    options = videojs.mergeOptions(defaults, options); // If the player is already using this plugin, remove the pre-existing
    // PlaylistMenu, but retain the element and its location in the DOM because
    // it will be re-used.

    if (player.playlistMenu) {
      var el = player.playlistMenu.el(); // Catch cases where the menu may have been disposed elsewhere or the
      // element removed from the DOM.

      if (el) {
        var parentNode = el.parentNode;
        var nextSibling = el.nextSibling; // Disposing the menu will remove `el` from the DOM, but we need to
        // empty it ourselves to be sure.

        player.playlistMenu.dispose();
        dom.emptyEl(el); // Put the element back in its place.

        if (nextSibling) {
          parentNode.insertBefore(el, nextSibling);
        } else {
          parentNode.appendChild(el);
        }

        options.el = el;
      }
    }

    if (!dom.isEl(options.el)) {
      options.el = findRoot(options.className);
    }

    player.playlistMenu = new PlaylistMenu(player, options);
  }; // register components


  videojs.registerComponent('PlaylistMenu', PlaylistMenu);
  videojs.registerComponent('PlaylistMenuItem', PlaylistMenuItem); // register the plugin

  registerPlugin('playlistUi', playlistUi);
  playlistUi.VERSION = version;

  return playlistUi;

})));
