'use strict';

/**
 * The ChromecastButton module contains both the ChromecastButton class definition and
 * the function used to register the button as a Video.js Component.
 *
 * @module ChromecastButton
 */

var ChromecastButton;

/**
* The Video.js Button class is the base class for UI button components.
*
* @external Button
* @see {@link http://docs.videojs.com/Button.html|Button}
*/

/** @lends ChromecastButton.prototype */
ChromecastButton = {

   /**
    * This class is a button component designed to be displayed in the player UI's control
    * bar. It opens the Chromecast menu when clicked.
    *
    * @constructs
    * @extends external:Button
    * @param player {Player} the video.js player instance
    */
   constructor: function(player) {
      this.constructor.super_.apply(this, arguments);

      player.on('chromecastConnected', this._onChromecastConnected.bind(this));
      player.on('chromecastDisconnected', this._onChromecastDisconnected.bind(this));
      player.on('chromecastDevicesAvailable', this._onChromecastDevicesAvailable.bind(this));
      player.on('chromecastDevicesUnavailable', this._onChromecastDevicesUnavailable.bind(this));

      this.controlText('Open Chromecast menu');

      // Use the initial state of `hasAvailableDevices` to call the corresponding event
      // handlers because the corresponding events may have already been emitted before
      // binding the listeners above.
      if (player.chromecastSessionManager && player.chromecastSessionManager.hasAvailableDevices()) {
         this._onChromecastDevicesAvailable();
      } else {
         this._onChromecastDevicesUnavailable();
      }
   },

   /**
    * Overrides Button#buildCSSClass to return the classes used on the button element.
    *
    * @param el {DOMElement}
    * @see {@link http://docs.videojs.com/Button.html#buildCSSClass|Button#buildCSSClass}
    */
   buildCSSClass: function() {
      return 'vjs-chromecast-button ' + (this._isChromecastConnected ? 'vjs-chromecast-casting-state ' : '') +
         this.constructor.super_.prototype.buildCSSClass();
   },

   /**
    * Overrides Button#handleClick to handle button click events. Chromecast functionality
    * is handled outside of this class, which should be limited to UI related logic. This
    * function simply triggers an event on the player.
    *
    * @fires ChromecastButton#chromecastRequested
    * @param el {DOMElement}
    * @see {@link http://docs.videojs.com/Button.html#handleClick|Button#handleClick}
    */
   handleClick: function() {
      this.player().trigger('chromecastRequested');
   },

   /**
    * Handles `chromecastConnected` player events.
    *
    * @private
    */
   _onChromecastConnected: function() {
      this._isChromecastConnected = true;
      this._reloadCSSClasses();
   },

   /**
    * Handles `chromecastDisconnected` player events.
    *
    * @private
    */
   _onChromecastDisconnected: function() {
      this._isChromecastConnected = false;
      this._reloadCSSClasses();
   },

   /**
    * Handles `chromecastDevicesAvailable` player events.
    *
    * @private
    */
   _onChromecastDevicesAvailable: function() {
      this.show();
   },

   /**
    * Handles `chromecastDevicesUnavailable` player events.
    *
    * @private
    */
   _onChromecastDevicesUnavailable: function() {
      this.hide();
   },

   /**
    * Re-calculates which CSS classes the button needs and sets them on the buttons'
    * DOMElement.
    *
    * @private
    */
   _reloadCSSClasses: function() {
      if (!this.el_) {
         return;
      }
      this.el_.className = this.buildCSSClass();
   },
};

/**
 * Registers the ChromecastButton Component with Video.js. Calls
 * {@link http://docs.videojs.com/Component.html#.registerComponent}, which will add a
 * component called `chromecastButton` to the list of globally registered Video.js
 * components. The `chromecastButton` is added to the player's control bar UI
 * automatically once {@link module:enableChromecast} has been called. If you would like
 * to specify the order of the buttons that appear in the control bar, including this
 * button, you can do so in the options that you pass to the `videojs` function when
 * creating a player:
 *
 * ```
 * videojs('playerID', {
 *    controlBar: {
 *       children: [
 *          'playToggle',
 *          'progressControl',
 *          'volumePanel',
 *          'fullscreenToggle',
 *          'chromecastButton',
 *       ],
 *    }
 * });
 * ```
 *
 * @param videojs {object} A reference to {@link http://docs.videojs.com/module-videojs.html|Video.js}
 * @see http://docs.videojs.com/module-videojs.html#~registerPlugin
 */
module.exports = function(videojs) {
   var ChromecastButtonImpl;

   ChromecastButtonImpl = videojs.extend(videojs.getComponent('Button'), ChromecastButton);
   videojs.registerComponent('chromecastButton', ChromecastButtonImpl);
};
