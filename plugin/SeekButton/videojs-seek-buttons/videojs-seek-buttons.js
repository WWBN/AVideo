/**
 * videojs-seek-buttons
 * @version 1.2.4
 * @copyright 2018 Ben Clifford
 * @license Apache-2.0
 */
(function (global, factory) {
	typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory(require('video.js')) :
	typeof define === 'function' && define.amd ? define(['video.js'], factory) :
	(global.videojsSeekButtons = factory(global.videojs));
}(this, (function (videojs) { 'use strict';

videojs = 'default' in videojs ? videojs['default'] : videojs;

var version = "1.2.4";

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

var Button = videojs.getComponent('Button');
var Component = videojs.getComponent('Component');

// Default options for the plugin.
var defaults$$1 = {};

// Cross-compatibility for Video.js 5 and 6.
var registerPlugin = videojs.registerPlugin || videojs.plugin;
// const dom = videojs.dom || videojs;

/**
 * Function to invoke when the player is ready.
 *
 * This is a great place for your plugin to initialize itself. When this
 * function is called, the player will have its DOM and child components
 * in place.
 *
 * @function onPlayerReady
 * @param    {Player} player
 *           A Video.js player object.
 *
 * @param    {Object} [options={}]
 *           A plain object containing options for the plugin.
 */
var onPlayerReady = function onPlayerReady(player, options) {

  player.addClass('vjs-seek-buttons');

  if (options.forward && options.forward > 0) {
    player.controlBar.seekForward = player.controlBar.addChild('seekButton', {
      direction: 'forward',
      seconds: options.forward
    });
    player.controlBar.el().insertBefore(player.controlBar.seekForward.el(), player.controlBar.el().firstChild.nextSibling);
  }

  if (options.back && options.back > 0) {
    player.controlBar.seekBack = player.controlBar.addChild('seekButton', {
      direction: 'back',
      seconds: options.back
    });
    player.controlBar.el().insertBefore(player.controlBar.seekBack.el(), player.controlBar.el().firstChild.nextSibling);
  }
};

/**
 * A video.js plugin.
 *
 * In the plugin function, the value of `this` is a video.js `Player`
 * instance. You cannot rely on the player being in a "ready" state here,
 * depending on how the plugin is invoked. This may or may not be important
 * to you; if not, remove the wait for "ready"!
 *
 * @function seekButtons
 * @param    {Object} [options={}]
 *           An object of options left to the plugin author to define.
 */
var seekButtons = function seekButtons(options) {
  var _this = this;

  this.ready(function () {
    onPlayerReady(_this, videojs.mergeOptions(defaults$$1, options));
  });
};

/**
 * Button to seek forward/back
 *
 * @param {Player|Object} player
 * @param {Object=} options
 * @extends Button
 * @class SeekToggle
 */

var SeekButton = function (_Button) {
  inherits(SeekButton, _Button);

  function SeekButton(player, options) {
    classCallCheck(this, SeekButton);

    var _this2 = possibleConstructorReturn(this, _Button.call(this, player, options));

    if (_this2.options_.direction === 'forward') {
      _this2.controlText(_this2.localize('Seek forward {{seconds}} seconds').replace('{{seconds}}', _this2.options_.seconds));
    } else if (_this2.options_.direction === 'back') {
      _this2.controlText(_this2.localize('Seek back {{seconds}} seconds').replace('{{seconds}}', _this2.options_.seconds));
    }
    return _this2;
  }

  SeekButton.prototype.buildCSSClass = function buildCSSClass() {
    /* Each button will have the classes:
       `vjs-seek-button`
       `skip-forward` or `skip-back`
       `skip-n` where `n` is the number of seconds
       So you could have a generic icon for "skip back" and a more
       specific one for "skip back 30 seconds"
    */
    return 'vjs-seek-button skip-' + this.options_.direction + ' ' + ('skip-' + this.options_.seconds + ' ' + _Button.prototype.buildCSSClass.call(this));
  };

  SeekButton.prototype.handleClick = function handleClick() {
    var now = this.player_.currentTime();

    if (this.options_.direction === 'forward') {
      this.player_.currentTime(now + this.options_.seconds);
    } else if (this.options_.direction === 'back') {
      this.player_.currentTime(now - this.options_.seconds);
    }
  };

  return SeekButton;
}(Button);

Component.registerComponent('SeekButton', SeekButton);

// Register the plugin with video.js.
registerPlugin('seekButtons', seekButtons);

// Include the version number.
seekButtons.VERSION = version;

return seekButtons;

})));
