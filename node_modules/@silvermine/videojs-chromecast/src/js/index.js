/* eslint-disable global-require */
var preloadWebComponents = require('./preloadWebComponents'),
    createChromecastButton = require('./components/ChromecastButton'),
    createChromecastTech = require('./tech/ChromecastTech'),
    enableChromecast = require('./enableChromecast');

/**
 * @module index
 */

/**
 * Registers the Chromecast plugin and ChromecastButton Component with Video.js. See
 * {@link module:ChromecastButton} and {@link module:enableChromecast} for more details
 * about how the plugin and button are registered and configured.
 *
 * @param videojs {object} the videojs library. If `undefined`, this plugin
 * will look to `window.videojs`.
 * @param userOpts {object} the options to use for configuration
 * @see module:enableChromecast
 * @see module:ChromecastButton
 */
module.exports = function(videojs, userOpts) {
   var options = Object.assign({ preloadWebComponents: false }, userOpts);

   if (options.preloadWebComponents) {
      preloadWebComponents();
   }

   videojs = videojs || window.videojs;
   createChromecastButton(videojs);
   createChromecastTech(videojs);
   enableChromecast(videojs);
};
