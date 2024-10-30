var createAirPlayButton = require('./components/AirPlayButton'),
    createAirPlayPlugin = require('./enableAirPlay');

/**
 * @module index
 */

/**
 * Registers the AirPlay plugin and AirPlayButton Component with Video.js. See
 * {@link module:AirPlayButton} and {@link module:enableAirPlay} for more details about
 * how the plugin and button are registered and configured.
 *
 * @param {object} videojs
 * @see module:enableAirPlay
 * @see module:AirPlayButton
 */
module.exports = function(videojs) {
   videojs = videojs || window.videojs;
   createAirPlayButton(videojs);
   createAirPlayPlugin(videojs);
};
