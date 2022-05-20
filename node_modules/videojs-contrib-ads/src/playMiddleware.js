import videojs from 'video.js';

const obj = {};
// This reference allows videojs to be mocked in unit tests
// while still using the available videojs import in the source code
// @see obj.testHook
let videojsReference = videojs;

/**
 * Checks if middleware mediators are available and
 * can be used on this platform.
 * Currently we can only use mediators on desktop platforms.
 */
obj.isMiddlewareMediatorSupported = function() {

  if (videojsReference.browser.IS_IOS || videojsReference.browser.IS_ANDROID) {
    return false;

  } else if (
    // added when middleware was introduced in video.js
    videojsReference.use &&
    // added when mediators were introduced in video.js
    videojsReference.middleware &&
    videojsReference.middleware.TERMINATOR) {
    return true;

  }

  return false;
};

obj.playMiddleware = function(player) {
  return {
    setSource(srcObj, next) {
      next(null, srcObj);
    },
    callPlay() {
      // Block play calls while waiting for an ad, only if this is an
      // ad supported player
      if (player.ads && player.ads._shouldBlockPlay === true) {
        player.ads.debug('Using playMiddleware to block content playback');
        player.ads._playBlocked = true;
        return videojsReference.middleware.TERMINATOR;
      }
    },
    play(terminated, playPromise) {
      if (player.ads && player.ads._playBlocked && terminated) {
        player.ads.debug('Play call to Tech was terminated.');
        // Trigger play event to match the user's intent to play.
        // The call to play on the Tech has been blocked, so triggering
        // the event on the Player will not affect the Tech's playback state.
        player.trigger('play');
        // At this point the player has technically started
        player.addClass('vjs-has-started');
        // Reset playBlocked
        player.ads._playBlocked = false;

      // Safari issues a pause event when autoplay is blocked but other browsers
      // do not, so we send a pause for consistency in those cases. This keeps the
      // play button in the correct state if play is rejected.
      } else if (playPromise && playPromise.catch) {
        playPromise.catch((e) => {
          if (e.name === 'NotAllowedError' && !videojs.browser.IS_SAFARI) {
            player.trigger('pause');
          }
        });
      }
    }
  };
};

obj.testHook = function(testVjs) {
  videojsReference = testVjs;
};

export default obj;
