/**
 * @module hasAirPlayAPISupport
 */

/**
 * Returns whether or not the current browser environment supports AirPlay.
 *
 * @private
 * @returns {boolean} true if AirPlay support is available
 */
module.exports = function() {
   return !!window.WebKitPlaybackTargetAvailabilityEvent;
};
