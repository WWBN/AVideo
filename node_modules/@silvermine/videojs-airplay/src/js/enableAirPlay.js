/**
 * @module enableAirPlay
 */

var hasAirPlayAPISupport = require('./lib/hasAirPlayAPISupport');

/**
 * @private
 * @param {object} the Video.js Player instance
 * @returns {AirPlayButton} or `undefined` if it does not exist
 */
function getExistingAirPlayButton(player) {
   return player.controlBar.getChild('airPlayButton');
}

/**
 * Adds the AirPlayButton Component to the player's ControlBar component, if the
 * AirPlayButton does not already exist in the ControlBar.
 * @private
 * @param player {object} the Video.js Player instance
 * @param options {object}
 */
function ensureAirPlayButtonExists(player, options) {
   var existingAirPlayButton = getExistingAirPlayButton(player),
       indexOpt;

   if (options.addButtonToControlBar && !existingAirPlayButton) {
      // Figure out AirPlay button's index
      indexOpt = player.controlBar.children().length;
      if (typeof options.buttonPositionIndex !== 'undefined') {
         indexOpt = options.buttonPositionIndex >= 0
            ? options.buttonPositionIndex
            : player.controlBar.children().length + options.buttonPositionIndex;
      }

      player.controlBar.addChild('airPlayButton', options, indexOpt);
   }
}

/**
 * Handles requests for AirPlay triggered by the AirPlayButton Component.
 *
 * @private
 * @param player {object} the Video.js Player instance
 */
function onAirPlayRequested(player) {
   var mediaEl = player.el().querySelector('video, audio');

   if (mediaEl && mediaEl.webkitShowPlaybackTargetPicker) {
      mediaEl.webkitShowPlaybackTargetPicker();
   }
}

/**
 * Adds an event listener for the `airPlayRequested` event triggered by the AirPlayButton
 * Component.
 *
 * @private
 * @param player {object} the Video.js Player instance
 */
function listenForAirPlayEvents(player) {
   // Respond to requests for AirPlay. The AirPlayButton component triggers this event
   // when the user clicks the AirPlay button.
   player.on('airPlayRequested', onAirPlayRequested.bind(null, player));
}

/**
 * Sets up the AirPlay plugin.
 *
 * @private
 * @param player {object} the Video.js player
 * @param options {object} the plugin options
 */
function enableAirPlay(player, options) {
   if (!player.controlBar) {
      return;
   }

   if (hasAirPlayAPISupport()) {
      listenForAirPlayEvents(player);
      ensureAirPlayButtonExists(player, options);
   }
}

/**
 * Registers the AirPlay plugin with Video.js. Calls
 * {@link http://docs.videojs.com/module-videojs.html#~registerPlugin|videojs#registerPlugin},
 * which will add a plugin function called `airPlay` to any instance of a Video.js player
 * that is created after calling this function. Call `player.airPlay(options)`, passing in
 * configuration options, to enable the AirPlay plugin on your Player instance.
 *
 * Currently, the only configuration option is:
 *
 *    * **buttonText** - the text to display inside of the button component. By default,
 *    this text is hidden and is used for accessibility purposes.
 *
 * @param {object} videojs
 * @see http://docs.videojs.com/module-videojs.html#~registerPlugin
 */
module.exports = function(videojs) {
   videojs.registerPlugin('airPlay', function(options) {
      var pluginOptions = Object.assign({ addButtonToControlBar: true }, options || {});

      // `this` is an instance of a Video.js Player.
      // Wait until the player is "ready" so that the player's control bar component has
      // been created.
      this.ready(enableAirPlay.bind(this, this, pluginOptions));
   });
};
