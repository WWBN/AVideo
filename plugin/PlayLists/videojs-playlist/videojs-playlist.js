/*! @name videojs-playlist @version 4.2.6 @license Apache-2.0 */
(function (global, factory) {
  typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory(require('video.js')) :
  typeof define === 'function' && define.amd ? define(['video.js'], factory) :
  (global.videojsPlaylist = factory(global.videojs));
}(this, (function (videojs) { 'use strict';

  videojs = videojs && videojs.hasOwnProperty('default') ? videojs['default'] : videojs;

  /**
   * Validates a number of seconds to use as the auto-advance delay.
   *
   * @private
   * @param   {number} s
   *          The number to check
   *
   * @return  {boolean}
   *          Whether this is a valid second or not
   */
  var validSeconds = function validSeconds(s) {
    return typeof s === 'number' && !isNaN(s) && s >= 0 && s < Infinity;
  };
  /**
   * Resets the auto-advance behavior of a player.
   *
   * @param {Player} player
   *        The player to reset the behavior on
   */


  var reset = function reset(player) {
    var aa = player.playlist.autoadvance_;

    if (aa.timeout) {
      player.clearTimeout(aa.timeout);
    }

    if (aa.trigger) {
      player.off('ended', aa.trigger);
    }

    aa.timeout = null;
    aa.trigger = null;
  };
  /**
   * Sets up auto-advance behavior on a player.
   *
   * @param  {Player} player
   *         the current player
   *
   * @param  {number} delay
   *         The number of seconds to wait before each auto-advance.
   *
   * @return {undefined}
   *         Used to short circuit function logic
   */


  var setup = function setup(player, delay) {
    reset(player); // Before queuing up new auto-advance behavior, check if `seconds` was
    // called with a valid value.

    if (!validSeconds(delay)) {
      player.playlist.autoadvance_.delay = null;
      return;
    }

    player.playlist.autoadvance_.delay = delay;

    player.playlist.autoadvance_.trigger = function () {
      // This calls setup again, which will reset the existing auto-advance and
      // set up another auto-advance for the next "ended" event.
      var cancelOnPlay = function cancelOnPlay() {
        return setup(player, delay);
      }; // If there is a "play" event while we're waiting for an auto-advance,
      // we need to cancel the auto-advance. This could mean the user seeked
      // back into the content or restarted the content. This is reproducible
      // with an auto-advance > 0.


      player.one('play', cancelOnPlay);
      player.playlist.autoadvance_.timeout = player.setTimeout(function () {
        reset(player);
        player.off('play', cancelOnPlay);
        player.playlist.next();
      }, delay * 1000);
    };

    player.one('ended', player.playlist.autoadvance_.trigger);
  };

  /**
   * Removes all remote text tracks from a player.
   *
   * @param  {Player} player
   *         The player to clear tracks on
   */

  var clearTracks = function clearTracks(player) {
    var tracks = player.remoteTextTracks();
    var i = tracks && tracks.length || 0; // This uses a `while` loop rather than `forEach` because the
    // `TextTrackList` object is a live DOM list (not an array).

    while (i--) {
      player.removeRemoteTextTrack(tracks[i]);
    }
  };
  /**
   * Plays an item on a player's playlist.
   *
   * @param  {Player} player
   *         The player to play the item on
   *
   * @param  {Object} item
   *         A source from the playlist.
   *
   * @return {Player}
   *         The player that is now playing the item
   */


  var playItem = function playItem(player, item) {
    var replay = !player.paused() || player.ended();
    player.trigger('beforeplaylistitem', item);
    player.poster(item.poster || '');
    player.src(item.sources);
    clearTracks(player);
    player.ready(function () {
      (item.textTracks || []).forEach(player.addRemoteTextTrack.bind(player));
      player.trigger('playlistitem', item);

      if (replay) {
        var playPromise = player.play(); // silence error when a pause interrupts a play request
        // on browsers which return a promise

        if (typeof playPromise !== 'undefined' && typeof playPromise.then === 'function') {
          playPromise.then(null, function (e) {});
        }
      }

      setup(player, player.playlist.autoadvance_.delay);
    });
    return player;
  };

  /**
   * Given two sources, check to see whether the two sources are equal.
   * If both source urls have a protocol, the protocols must match, otherwise, protocols
   * are ignored.
   *
   * @private
   * @param {string|Object} source1
   *        The first source
   *
   * @param {string|Object} source2
   *        The second source
   *
   * @return {boolean}
   *         The result
   */

  var sourceEquals = function sourceEquals(source1, source2) {
    var src1 = source1;
    var src2 = source2;

    if (typeof source1 === 'object') {
      src1 = source1.src;
    }

    if (typeof source2 === 'object') {
      src2 = source2.src;
    }

    if (/^\/\//.test(src1)) {
      src2 = src2.slice(src2.indexOf('//'));
    }

    if (/^\/\//.test(src2)) {
      src1 = src1.slice(src1.indexOf('//'));
    }

    return src1 === src2;
  };
  /**
   * Look through an array of playlist items for a specific `source`;
   * checking both the value of elements and the value of their `src`
   * property.
   *
   * @private
   * @param   {Array} arr
   *          An array of playlist items to look through
   *
   * @param   {string} src
   *          The source to look for
   *
   * @return  {number}
   *          The index of that source or -1
   */


  var indexInSources = function indexInSources(arr, src) {
    for (var i = 0; i < arr.length; i++) {
      var sources = arr[i].sources;

      if (Array.isArray(sources)) {
        for (var j = 0; j < sources.length; j++) {
          var source = sources[j];

          if (source && sourceEquals(source, src)) {
            return i;
          }
        }
      }
    }

    return -1;
  };
  /**
   * Randomize the contents of an array.
   *
   * @private
   * @param  {Array} arr
   *         An array.
   *
   * @return {Array}
   *         The same array that was passed in.
   */


  var randomize = function randomize(arr) {
    var index = -1;
    var lastIndex = arr.length - 1;

    while (++index < arr.length) {
      var rand = index + Math.floor(Math.random() * (lastIndex - index + 1));
      var value = arr[rand];
      arr[rand] = arr[index];
      arr[index] = value;
    }

    return arr;
  };
  /**
   * Factory function for creating new playlist implementation on the given player.
   *
   * API summary:
   *
   * playlist(['a', 'b', 'c']) // setter
   * playlist() // getter
   * playlist.currentItem() // getter, 0
   * playlist.currentItem(1) // setter, 1
   * playlist.next() // 'c'
   * playlist.previous() // 'b'
   * playlist.first() // 'a'
   * playlist.last() // 'c'
   * playlist.autoadvance(5) // 5 second delay
   * playlist.autoadvance() // cancel autoadvance
   *
   * @param  {Player} player
   *         The current player
   *
   * @param  {Array=} initialList
   *         If given, an initial list of sources with which to populate
   *         the playlist.
   *
   * @param  {number=}  initialIndex
   *         If given, the index of the item in the list that should
   *         be loaded first. If -1, no video is loaded. If omitted, The
   *         the first video is loaded.
   *
   * @return {Function}
   *         Returns the playlist function specific to the given player.
   */


  function factory(player, initialList, initialIndex) {
    if (initialIndex === void 0) {
      initialIndex = 0;
    }

    var list = null;
    var changing = false;
    /**
     * Get/set the playlist for a player.
     *
     * This function is added as an own property of the player and has its
     * own methods which can be called to manipulate the internal state.
     *
     * @param  {Array} [newList]
     *         If given, a new list of sources with which to populate the
     *         playlist. Without this, the function acts as a getter.
     *
     * @param  {number}  [newIndex]
     *         If given, the index of the item in the list that should
     *         be loaded first. If -1, no video is loaded. If omitted, The
     *         the first video is loaded.
     *
     * @return {Array}
     *         The playlist
     */

    var playlist = player.playlist = function (newList, newIndex) {
      if (newIndex === void 0) {
        newIndex = 0;
      }

      if (changing) {
        throw new Error('do not call playlist() during a playlist change');
      }

      if (Array.isArray(newList)) {
        // @todo - Simplify this to `list.slice()` for v5.
        var previousPlaylist = Array.isArray(list) ? list.slice() : null;
        list = newList.slice(); // Mark the playlist as changing during the duringplaylistchange lifecycle.

        changing = true;
        player.trigger({
          type: 'duringplaylistchange',
          nextIndex: newIndex,
          nextPlaylist: list,
          previousIndex: playlist.currentIndex_,
          // @todo - Simplify this to simply pass along `previousPlaylist` for v5.
          previousPlaylist: previousPlaylist || []
        });
        changing = false;

        if (newIndex !== -1) {
          playlist.currentItem(newIndex);
        } // The only time the previous playlist is null is the first call to this
        // function. This allows us to fire the `duringplaylistchange` event
        // every time the playlist is populated and to maintain backward
        // compatibility by not firing the `playlistchange` event on the initial
        // population of the list.
        //
        // @todo - Remove this condition in preparation for v5.


        if (previousPlaylist) {
          player.setTimeout(function () {
            player.trigger('playlistchange');
          }, 0);
        }
      } // Always return a shallow clone of the playlist list.


      return list.slice();
    }; // On a new source, if there is no current item, disable auto-advance.


    player.on('loadstart', function () {
      if (playlist.currentItem() === -1) {
        reset(player);
      }
    });
    playlist.currentIndex_ = -1;
    playlist.player_ = player;
    playlist.autoadvance_ = {};
    playlist.repeat_ = false;
    /**
     * Get or set the current item in the playlist.
     *
     * During the duringplaylistchange event, acts only as a getter.
     *
     * @param  {number} [index]
     *         If given as a valid value, plays the playlist item at that index.
     *
     * @return {number}
     *         The current item index.
     */

    playlist.currentItem = function (index) {
      // If the playlist is changing, only act as a getter.
      if (changing) {
        return playlist.currentIndex_;
      }

      if (typeof index === 'number' && playlist.currentIndex_ !== index && index >= 0 && index < list.length) {
        playlist.currentIndex_ = index;
        playItem(playlist.player_, list[playlist.currentIndex_]);
      } else {
        playlist.currentIndex_ = playlist.indexOf(playlist.player_.currentSrc() || '');
      }

      return playlist.currentIndex_;
    };
    /**
     * Checks if the playlist contains a value.
     *
     * @param  {string|Object|Array} value
     *         The value to check
     *
     * @return {boolean}
     *         The result
     */


    playlist.contains = function (value) {
      return playlist.indexOf(value) !== -1;
    };
    /**
     * Gets the index of a value in the playlist or -1 if not found.
     *
     * @param  {string|Object|Array} value
     *         The value to find the index of
     *
     * @return {number}
     *         The index or -1
     */


    playlist.indexOf = function (value) {
      if (typeof value === 'string') {
        return indexInSources(list, value);
      }

      var sources = Array.isArray(value) ? value : value.sources;

      for (var i = 0; i < sources.length; i++) {
        var source = sources[i];

        if (typeof source === 'string') {
          return indexInSources(list, source);
        } else if (source.src) {
          return indexInSources(list, source.src);
        }
      }

      return -1;
    };
    /**
     * Get the index of the current item in the playlist. This is identical to
     * calling `currentItem()` with no arguments.
     *
     * @return {number}
     *         The current item index.
     */


    playlist.currentIndex = function () {
      return playlist.currentItem();
    };
    /**
     * Get the index of the last item in the playlist.
     *
     * @return {number}
     *         The index of the last item in the playlist or -1 if there are no
     *         items.
     */


    playlist.lastIndex = function () {
      return list.length - 1;
    };
    /**
     * Get the index of the next item in the playlist.
     *
     * @return {number}
     *         The index of the next item in the playlist or -1 if there is no
     *         current item.
     */


    playlist.nextIndex = function () {
      var current = playlist.currentItem();

      if (current === -1) {
        return -1;
      }

      var lastIndex = playlist.lastIndex(); // When repeating, loop back to the beginning on the last item.

      if (playlist.repeat_ && current === lastIndex) {
        return 0;
      } // Don't go past the end of the playlist.


      return Math.min(current + 1, lastIndex);
    };
    /**
     * Get the index of the previous item in the playlist.
     *
     * @return {number}
     *         The index of the previous item in the playlist or -1 if there is
     *         no current item.
     */


    playlist.previousIndex = function () {
      var current = playlist.currentItem();

      if (current === -1) {
        return -1;
      } // When repeating, loop back to the end of the playlist.


      if (playlist.repeat_ && current === 0) {
        return playlist.lastIndex();
      } // Don't go past the beginning of the playlist.


      return Math.max(current - 1, 0);
    };
    /**
     * Plays the first item in the playlist.
     *
     * @return {Object|undefined}
     *         Returns undefined and has no side effects if the list is empty.
     */


    playlist.first = function () {
      if (changing) {
        return;
      }

      if (list.length) {
        return list[playlist.currentItem(0)];
      }

      playlist.currentIndex_ = -1;
    };
    /**
     * Plays the last item in the playlist.
     *
     * @return {Object|undefined}
     *         Returns undefined and has no side effects if the list is empty.
     */


    playlist.last = function () {
      if (changing) {
        return;
      }

      if (list.length) {
        return list[playlist.currentItem(playlist.lastIndex())];
      }

      playlist.currentIndex_ = -1;
    };
    /**
     * Plays the next item in the playlist.
     *
     * @return {Object|undefined}
     *         Returns undefined and has no side effects if on last item.
     */


    playlist.next = function () {
      if (changing) {
        return;
      }

      var index = playlist.nextIndex();

      if (index !== playlist.currentIndex_) {
        return list[playlist.currentItem(index)];
      }
    };
    /**
     * Plays the previous item in the playlist.
     *
     * @return {Object|undefined}
     *         Returns undefined and has no side effects if on first item.
     */


    playlist.previous = function () {
      if (changing) {
        return;
      }

      var index = playlist.previousIndex();

      if (index !== playlist.currentIndex_) {
        return list[playlist.currentItem(index)];
      }
    };
    /**
     * Set up auto-advance on the playlist.
     *
     * @param  {number} [delay]
     *         The number of seconds to wait before each auto-advance.
     */


    playlist.autoadvance = function (delay) {
      setup(playlist.player_, delay);
    };
    /**
     * Sets `repeat` option, which makes the "next" video of the last video in
     * the playlist be the first video in the playlist.
     *
     * @param  {boolean} [val]
     *         The value to set repeat to
     *
     * @return {boolean}
     *         The current value of repeat
     */


    playlist.repeat = function (val) {
      if (val === undefined) {
        return playlist.repeat_;
      }

      if (typeof val !== 'boolean') {
        videojs.log.error('videojs-playlist: Invalid value for repeat', val);
        return;
      }

      playlist.repeat_ = !!val;
      return playlist.repeat_;
    };
    /**
     * Sorts the playlist array.
     *
     * @see {@link https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/sort}
     * @fires playlistsorted
     *
     * @param {Function} compare
     *        A comparator function as per the native Array method.
     */


    playlist.sort = function (compare) {
      // Bail if the array is empty.
      if (!list.length) {
        return;
      }

      list.sort(compare); // If the playlist is changing, don't trigger events.

      if (changing) {
        return;
      }
      /**
       * Triggered after the playlist is sorted internally.
       *
       * @event playlistsorted
       * @type {Object}
       */


      player.trigger('playlistsorted');
    };
    /**
     * Reverses the playlist array.
     *
     * @see {@link https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/reverse}
     * @fires playlistsorted
     */


    playlist.reverse = function () {
      // Bail if the array is empty.
      if (!list.length) {
        return;
      }

      list.reverse(); // If the playlist is changing, don't trigger events.

      if (changing) {
        return;
      }
      /**
       * Triggered after the playlist is sorted internally.
       *
       * @event playlistsorted
       * @type {Object}
       */


      player.trigger('playlistsorted');
    };
    /**
     * Shuffle the contents of the list randomly.
     *
     * @see   {@link https://github.com/lodash/lodash/blob/40e096b6d5291a025e365a0f4c010d9a0efb9a69/shuffle.js}
     * @fires playlistsorted
     * @todo  Make the `rest` option default to `true` in v5.0.0.
     * @param {Object} [options]
     *        An object containing shuffle options.
     *
     * @param {boolean} [options.rest = false]
     *        By default, the entire playlist is randomized. However, this may
     *        not be desirable in all cases, such as when a user is already
     *        watching a video.
     *
     *        When `true` is passed for this option, it will only shuffle
     *        playlist items after the current item. For example, when on the
     *        first item, will shuffle the second item and beyond.
     */


    playlist.shuffle = function (_temp) {
      var _ref = _temp === void 0 ? {} : _temp,
          rest = _ref.rest;

      var index = 0;
      var arr = list; // When options.rest is true, start randomization at the item after the
      // current item.

      if (rest) {
        index = playlist.currentIndex_ + 1;
        arr = list.slice(index);
      } // Bail if the array is empty or too short to shuffle.


      if (arr.length <= 1) {
        return;
      }

      randomize(arr); // When options.rest is true, splice the randomized sub-array back into
      // the original array.

      if (rest) {
        var _list;

        (_list = list).splice.apply(_list, [index, arr.length].concat(arr));
      } // If the playlist is changing, don't trigger events.


      if (changing) {
        return;
      }
      /**
       * Triggered after the playlist is sorted internally.
       *
       * @event playlistsorted
       * @type {Object}
       */


      player.trigger('playlistsorted');
    }; // If an initial list was given, populate the playlist with it.


    if (Array.isArray(initialList)) {
      playlist(initialList.slice(), initialIndex); // If there is no initial list given, silently set an empty array.
    } else {
      list = [];
    }

    return playlist;
  }

  var version = "4.2.6";

  var registerPlugin = videojs.registerPlugin || videojs.plugin;
  /**
   * The video.js playlist plugin. Invokes the playlist-maker to create a
   * playlist function on the specific player.
   *
   * @param {Array} list
   *        a list of sources
   *
   * @param {number} item
   *        The index to start at
   */

  var plugin = function plugin(list, item) {
    factory(this, list, item);
  };

  registerPlugin('playlist', plugin);
  plugin.VERSION = version;

  return plugin;

})));
