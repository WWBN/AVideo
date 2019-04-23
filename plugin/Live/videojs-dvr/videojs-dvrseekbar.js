/*! @name videojs-dvrseekbar @version 0.0.1 @license Apache-2.0 */
(function (global, factory) {
  typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory(require('video.js')) :
  typeof define === 'function' && define.amd ? define(['video.js'], factory) :
  (global.videojsDvrseekbar = factory(global.videojs));
}(this, (function (videojs) { 'use strict';

  videojs = videojs && videojs.hasOwnProperty('default') ? videojs['default'] : videojs;

  var version = "0.0.1";

  var classCallCheck = function (instance, Constructor) {
    if (!(instance instanceof Constructor)) {
      throw new TypeError("Cannot call a class as a function");
    }
  };

  var inherits = function (subClass, superClass) {
    if (typeof superClass !== "function" && superClass !== null) {
      throw new TypeError("Super expression must either be null or a function, not " + typeof superClass);
    }

    subClass.prototype = Object.create(superClass && superClass.prototype, {
      constructor: {
        value: subClass,
        enumerable: false,
        writable: true,
        configurable: true
      }
    });
    if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass;
  };

  var possibleConstructorReturn = function (self, call) {
    if (!self) {
      throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
    }

    return call && (typeof call === "object" || typeof call === "function") ? call : self;
  };

  var DVRSeekBar = function () {
    function DVRSeekBar(player, options) {
      classCallCheck(this, DVRSeekBar);

      if (!options) {
        options = {};
      }

      this.vjsPlayer_ = player;
      this.options_ = options;

      if (this.vjsPlayer_.dash && this.vjsPlayer_.dash.shakaPlayer) {
        this.player_ = this.vjsPlayer_.dash.shakaPlayer;
        this.player_.addEventListener('buffering', this.onBufferingStateChange_.bind(this));
        // window.setInterval(this.updateTimeAndSeekRange_.bind(this), 125);
      } else {
        this.player_ = this.vjsPlayer_;
      }

      window.setInterval(this.updateTimeAndSeekRange_.bind(this), 125);

      /** @private {HTMLMediaElement} */
      this.video_ = this.vjsPlayer_.tech_.el_;
      /** @private {boolean} */
      this.enabled_ = true;
      /** @private {?number} */
      this.seekTimeoutId_ = null;

      var seekBarEl = document.createElement('input');

      seekBarEl.setAttribute('type', 'range');
      seekBarEl.setAttribute('step', 'any');
      seekBarEl.setAttribute('min', '0');
      seekBarEl.setAttribute('max', '1');
      seekBarEl.setAttribute('value', '0');
      seekBarEl.setAttribute('id', 'seekBar');

      seekBarEl.addEventListener('mousedown', this.onSeekStart_.bind(this));
      seekBarEl.addEventListener('touchstart', this.onSeekStart_.bind(this), {
        passive: true
      });
      seekBarEl.addEventListener('input', this.onSeekInput_.bind(this));
      seekBarEl.addEventListener('touchend', this.onSeekEnd_.bind(this));
      seekBarEl.addEventListener('mouseup', this.onSeekEnd_.bind(this));

      this.dvrSeekBar_ = seekBarEl;

      this.currentTime_ = document.getElementById('dvr-current-time');
      this.currentTime_.addEventListener('click', this.onCurrentTimeClick_.bind(this));

      this.firstSeekRangeStart = null;

      if (options.flowMode) ;
    }

    DVRSeekBar.prototype.getEl = function getEl() {
      return this.dvrSeekBar_;
    };

    /** @private */


    DVRSeekBar.prototype.onSeekStart_ = function onSeekStart_() {
      if (!this.enabled_) return;

      this.isSeeking_ = true;
      this.video_.pause();
    };

    /** @private */


    DVRSeekBar.prototype.onSeekInput_ = function onSeekInput_() {
      if (!this.enabled_) return;

      if (!this.video_.duration) {
        // Can't seek yet.  Ignore.
        return;
      }

      // Update the UI right away.
      this.updateTimeAndSeekRange_();

      // Collect input events and seek when things have been stable for 125ms.
      if (this.seekTimeoutId_ != null) {
        window.clearTimeout(this.seekTimeoutId_);
      }
      this.seekTimeoutId_ = window.setTimeout(this.onSeekInputTimeout_.bind(this), 125);
    };

    /** @private */


    DVRSeekBar.prototype.onSeekInputTimeout_ = function onSeekInputTimeout_() {
      var seekVal = parseFloat(this.dvrSeekBar_.value);
      var lastStartPoint = this.getSeekRange().start;

      this.dvrSeekBar_.min = lastStartPoint;
      this.seekTimeoutId_ = null;
      //TODO: Hack para evitar que se cuelgue al cambiarse el starter point mientras es en vivo:
      this.video_.currentTime = seekVal <= lastStartPoint ? lastStartPoint + 30 : seekVal;
    };

    /** @private */


    DVRSeekBar.prototype.onSeekEnd_ = function onSeekEnd_() {
      if (!this.enabled_) return;

      if (this.seekTimeoutId_ != null) {
        // They just let go of the seek bar, so end the timer early.
        window.clearTimeout(this.seekTimeoutId_);
        this.onSeekInputTimeout_();
      }

      this.isSeeking_ = false;
      this.video_.play();
    };

    /** @private */


    DVRSeekBar.prototype.onCurrentTimeClick_ = function onCurrentTimeClick_() {
      if (!this.enabled_) return;

      // Jump to LIVE if the user clicks on the current time.
      if (this.player_.isLive && this.player_.isLive()) {
        this.video_.currentTime = this.dvrSeekBar_.max;
      }
    };

    /**
      * Iniciar desde el comienzo el contenido live
      * con FlowMode activado.
      *
      * @memberof DVRSeekBar
      */


    DVRSeekBar.prototype.onFlowModePlaying_ = function onFlowModePlaying_(e) {
      if (this.player_.isLive()) {
        this.video_.currentTime = this.getSeekRange().start + 30;
      }
    };

    /**
     * @param {Event} event
     * @private
     */


    DVRSeekBar.prototype.onBufferingStateChange_ = function onBufferingStateChange_(event) {}
    //this.bufferingSpinner_.style.display =
    //    event.buffering ? 'inherit' : 'none';


    /**
     * @return {boolean}
     * @private
     */
    ;

    DVRSeekBar.prototype.isOpaque_ = function isOpaque_() {
      if (!this.enabled_) return false;

      return this.vjsPlayer_.userActive();
    };

    /**
     * @return {number}
     * @private
     */


    DVRSeekBar.prototype.getMediaSeekRangeSize_ = function getMediaSeekRangeSize_() {
      return this.getSeekRange().end - this.getSeekRange().start;
    };

    /**
     * Builds a time string, e.g., 01:04:23, from |displayTime|.
     *
     * @param {number} displayTime
     * @param {boolean} showHour
     * @return {string}
     * @private
     */


    DVRSeekBar.prototype.buildTimeString_ = function buildTimeString_(displayTime, showHour) {
      var h = Math.floor(displayTime / 3600);
      var m = Math.floor(displayTime / 60 % 60);
      var s = Math.floor(displayTime % 60);
      if (s < 10) s = '0' + s;
      var text = m + ':' + s;
      if (showHour) {
        if (m < 10) text = '0' + text;
        text = h + ':' + text;
      }
      return text;
    };

    /**
     * Called when the seek range or current time need to be updated.
     * @private
     * @memberof DVRSeekBar
     */


    DVRSeekBar.prototype.updateTimeAndSeekRange_ = function updateTimeAndSeekRange_() {
      // Suppress updates if the controls are hidden.
      if (!this.isOpaque_()) {
        return;
      }

      var seekRange = this.getSeekRange();
      // Suppress updates if seekable range are not loaded.
      if (seekRange.end === 0 && seekRange.start === seekRange.end) {
        return;
      }

      this.dvrSeekBar_.min = seekRange.start;
      this.dvrSeekBar_.max = seekRange.end;

      var seekRangeSize = this.getMediaSeekRangeSize_();
      var displayTime = this.isSeeking_ ? this.dvrSeekBar_.value : this.video_.currentTime;
      var duration = this.video_.duration;
      var bufferedLength = this.video_.buffered.length;
      var bufferedStart = bufferedLength ? this.video_.buffered.start(0) : 0;
      var bufferedEnd = bufferedLength ? this.video_.buffered.end(bufferedLength - 1) : 0;

      if (this.player_.isLive && this.player_.isLive()) {
        // The amount of time we are behind the live edge.
        var behindLive = Math.floor(seekRange.end - displayTime);
        displayTime = Math.max(0, behindLive);

        var _showHour = seekRangeSize >= 3600;

        // Consider "LIVE" when less than 1 second behind the live-edge.  Always
        // show the full time string when seeking, including the leading '-';
        // otherwise, the time string "flickers" near the live-edge.
        if (displayTime >= 15 || this.isSeeking_) {
          // Si es con experiencia Flow:
          if (this.options_.flowMode) {
            if (!this.firstSeekRangeStart) ;
            //player.vjsPlayer.currentTime(seekRange.start);

            // Fill firstSeekRangeStart
            if (this.isSeeking_ || !this.firstSeekRangeStart) {
              this.firstSeekRangeStart = seekRange.start;
            }

            this.currentTime_.textContent = this.buildTimeString_(this.video_.currentTime - this.firstSeekRangeStart, _showHour);
            console.log('SeekRangeStart: ' + seekRange.start + ' | SeekRangeEnd: ' + seekRange.end + ' | CurrentTime: ' + this.video_.currentTime + ' | DisplayTime: ' + displayTime + ' | SeekSize: ' + seekRangeSize + ' | Time: ' + this.currentTime_.textContent);
          } else {
            this.currentTime_.textContent = '- ' + this.buildTimeString_(displayTime, _showHour);
          }

          this.currentTime_.style.cursor = 'pointer';
        } else {
          this.currentTime_.textContent = 'LIVE';
          this.currentTime_.style.cursor = '';
        }

        if (!this.isSeeking_) {
          this.dvrSeekBar_.value = seekRange.end - displayTime;
        }
      } else {
        var showHour = duration >= 3600;

        this.currentTime_.textContent = this.buildTimeString_(displayTime, showHour);

        if (!this.isSeeking_) {
          this.dvrSeekBar_.value = displayTime;
        }

        this.currentTime_.style.cursor = '';
      }
      if (bufferedLength == 0) ; else {
        var clampedBufferStart = Math.max(bufferedStart, seekRange.start);
        var clampedBufferEnd = Math.min(bufferedEnd, seekRange.end);

        var bufferStartDistance = clampedBufferStart - seekRange.start;
        var bufferEndDistance = clampedBufferEnd - seekRange.start;
        var playheadDistance = displayTime - seekRange.start;
      }
      /* this.dvrSeekBar_.style.background =
      	'linear-gradient(' + gradient.join(',') + ')'; */
    };

    DVRSeekBar.prototype.getSeekRange = function getSeekRange() {
      if (this.player_.seekRange) {
        return this.player_.seekRange();
      }
      return {
        start: this.player_.seekable().start(0),
        end: this.player_.seekable().end(0)
      };
    };

    return DVRSeekBar;
  }();

  var Plugin = videojs.getPlugin('plugin');

  // Default options for the plugin.
  var defaults$1 = {
    startTime: 0,
    externalSeekable: null
  };

  /**
   * An advanced Video.js plugin. For more information on the API
   *
   * See: https://blog.videojs.com/feature-spotlight-advanced-plugins/
   */

  var Dvrseekbar = function (_Plugin) {
    inherits(Dvrseekbar, _Plugin);

    /**
     * Create a Dvrseekbar plugin instance.
     *
     * @param  {Player} player
     *         A Video.js Player instance.
     *
     * @param  {Object} [options]
     *         An optional options object.
     *
     *         While not a core part of the Video.js plugin architecture, a
     *         second argument of options is a convenient way to accept inputs
     *         from your plugin's caller.
     */
    function Dvrseekbar(player, options) {
      classCallCheck(this, Dvrseekbar);

      var _this = possibleConstructorReturn(this, _Plugin.call(this, player));
      // the parent class will add player under this.player


      _this.options = videojs.mergeOptions(defaults$1, options);

      _this.player.ready(function () {
        _this.player.addClass('vjs-dvrseekbar');
      });

      _this.player.on('loadeddata', function () {
        // if (this.dash && this.dash.shakaPlayer) {
        _this.ifShakaPlayer();
        /* } else {
          this.on('timeupdate', e => {
            onTimeUpdate(this, e);
          });
           this.on('pause', e => {
            let btnLiveEl = document.getElementById('liveButton');
            btnLiveEl.className = 'vjs-live-label';
          });
           iDontKnowWhatThisDoes(this, videojs.mergeOptions(defaults, options));
        } */
      });

      _this.one('playing', function (e) {
        var sourceHandler = _this.tech_.sourceHandler_;

        if (options.flowMode) {
          var startTime = 0;

          if (sourceHandler.constructor.name === 'ShakaHandler') {
            startTime = sourceHandler.shakaPlayer.seekRange().start + 30;
          }

          _this.currentTime(startTime);
        }
      });
      return _this;
    }

    Dvrseekbar.prototype.onTimeUpdate = function onTimeUpdate(player, e) {
      var time = player.seekableFromShaka && player.seekableFromShaka() || player.seekable();
      var btnLiveEl = document.getElementById('liveButton');

      // When any tech is disposed videojs will trigger a 'timeupdate' event
      // when calling stopTrackingCurrentTime(). If the tech does not have
      // a seekable() method, time will be undefined
      if (!time || !time.length) {
        return;
      }

      if (time.end(0) - player.currentTime() < 30) {
        btnLiveEl.className = 'label onair';
      } else {
        btnLiveEl.className = 'label';
      }

      player.duration(time.end(0));
    };

    Dvrseekbar.prototype.ifShakaPlayer = function ifShakaPlayer() {
      var dvrCurrentTime = document.createElement('div');

      dvrCurrentTime.setAttribute('id', 'dvr-current-time');
      dvrCurrentTime.innerHTML = '0:00';
      dvrCurrentTime.className = 'vjs-current-time-display';

      this.player.controlBar.progressControl.seekBar.hide();
      this.player.controlBar.progressControl.disable();

      var currentSrc = this.player.tech_ && this.player.tech_.currentSource_ || {};

      // TODO: delete this
      currentSrc.hasCatchUp = true;
      //////////////////////

      if (currentSrc.hasCatchUp) {
        this.player.controlBar.el_.insertBefore(dvrCurrentTime, this.player.controlBar.progressControl.el_.nextSibling);

        var dvrSeekBar = new DVRSeekBar(this.player, this.options);
        this.player.controlBar.progressControl.el_.appendChild(dvrSeekBar.getEl());
      }
    };

    Dvrseekbar.prototype.iDontKnowWhatThisDoes = function iDontKnowWhatThisDoes(player, options) {
      player.addClass('vjs-dvrseekbar');
      player.controlBar.addClass('vjs-dvrseekbar-control-bar');

      if (player.controlBar.progressControl) {
        player.controlBar.progressControl.addClass('vjs-dvrseekbar-progress-control');
      }

      // ADD Live Button:
      var btnLiveEl = document.createElement('div');
      var newLink = document.createElement('a');

      btnLiveEl.className = 'vjs-live-button vjs-control';

      newLink.innerHTML = document.getElementsByClassName('vjs-live-display')[0].innerHTML;
      newLink.id = 'liveButton';

      if (!player.paused()) {
        newLink.className = 'vjs-live-label onair';
      }

      var clickHandler = function clickHandler(e) {
        var livePosition = player.seekableFromShaka && player.seekableFromShaka().end() || player.seekable().end(0);

        player.currentTime(livePosition - 1);
        player.play();
        e.target.className += ' onair';
      };

      if (newLink.addEventListener) {
        // DOM method
        newLink.addEventListener('click', clickHandler, false);
      } else if (newLink.attachEvent) {
        // this is for IE, because it doesn't support addEventListener
        newLink.attachEvent('onclick', function () {
          return clickHandler.apply(newLink, [window.event]);
        });
      }

      btnLiveEl.appendChild(newLink);

      var controlBar = document.getElementsByClassName('vjs-control-bar')[0];
      var insertBeforeNode = document.getElementsByClassName('vjs-progress-control')[0];

      controlBar.insertBefore(btnLiveEl, insertBeforeNode);

      videojs.log('dvrSeekbar Plugin ENABLED!', options);
    };

    return Dvrseekbar;
  }(Plugin);

  // Define default values for the plugin's `state` object here.


  Dvrseekbar.defaultState = {};

  // Include the version number.
  Dvrseekbar.VERSION = version;

  // Register the plugin with video.js.
  videojs.registerPlugin('dvrseekbar', Dvrseekbar);

  return Dvrseekbar;

})));
