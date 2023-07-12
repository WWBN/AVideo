/*
 * Implements the public API available in `player.ads` as well as application state.
 */

import videojs from 'video.js';

import {version as adsVersion} from '../package.json';

export default function getAds(player) {
  return {

    disableNextSnapshotRestore: false,

    // This is true if we have finished actual content playback but haven't
    // dealt with postrolls and officially ended yet
    _contentEnding: false,

    // This is set to true if the content has officially ended at least once.
    // After that, the user can seek backwards and replay content, but _contentHasEnded
    // remains true.
    _contentHasEnded: false,

    // Tracks if loadstart has happened yet for the initial source. It is not reset
    // on source changes because loadstart is the event that signals to the ad plugin
    // that the source has changed. Therefore, no special signaling is needed to know
    // that there has been one for subsequent sources.
    _hasThereBeenALoadStartDuringPlayerLife: false,

    // Tracks if loadeddata has happened yet for the current source.
    _hasThereBeenALoadedData: false,

    // Tracks if loadedmetadata has happened yet for the current source.
    _hasThereBeenALoadedMetaData: false,

    // Are we after startLinearAdMode and before endLinearAdMode?
    _inLinearAdMode: false,

    // Should we block calls to play on the content player?
    _shouldBlockPlay: false,

    // Was play blocked by the plugin's playMiddleware feature?
    _playBlocked: false,

    // Tracks whether play has been requested for this source,
    // either by the play method or user interaction
    _playRequested: false,

    // This is an estimation of the current ad type being played
    // This is experimental currently. Do not rely on its presence or behavior!
    adType: null,

    VERSION: adsVersion,

    reset() {
      player.ads.disableNextSnapshotRestore = false;
      player.ads._contentEnding = false;
      player.ads._contentHasEnded = false;
      player.ads.snapshot = null;
      player.ads.adType = null;
      player.ads._hasThereBeenALoadedData = false;
      player.ads._hasThereBeenALoadedMetaData = false;
      player.ads._cancelledPlay = false;
      player.ads._shouldBlockPlay = false;
      player.ads._playBlocked = false;
      player.ads.nopreroll_ = false;
      player.ads.nopostroll_ = false;
      player.ads._playRequested = false;
    },

    // Call this when an ad response has been received and there are
    // linear ads ready to be played.
    startLinearAdMode() {
      player.ads._state.startLinearAdMode();
    },

    // Call this when a linear ad pod has finished playing.
    endLinearAdMode() {
      player.ads._state.endLinearAdMode();
    },

    // Call this when an ad response has been received but there are no
    // linear ads to be played (i.e. no ads available, or overlays).
    // This has no effect if we are already in an ad break.  Always
    // use endLinearAdMode() to exit from linear ad-playback state.
    skipLinearAdMode() {
      player.ads._state.skipLinearAdMode();
    },

    // With no arguments, returns a boolean value indicating whether or not
    // contrib-ads is set to treat ads as stitched with content in a single
    // stream. With arguments, treated as a setter, but this behavior is
    // deprecated.
    stitchedAds(arg) {
      if (arg !== undefined) {
        videojs.log.warn('Using player.ads.stitchedAds() as a setter is deprecated, ' +
          'it should be set as an option upon initialization of contrib-ads.');

        // Keep the private property and the settings in sync. When this
        // setter is removed, we can probably stop using the private property.
        this.settings.stitchedAds = !!arg;
      }

      return this.settings.stitchedAds;
    },

    // Returns whether the video element has been modified since the
    // snapshot was taken.
    // We test both src and currentSrc because changing the src attribute to a URL that
    // AdBlocker is intercepting doesn't update currentSrc.
    videoElementRecycled() {
      if (player.ads.shouldPlayContentBehindAd(player)) {
        return false;
      }

      if (!this.snapshot) {
        throw new Error('You cannot use videoElementRecycled while there is no snapshot.');
      }

      const srcChanged = player.tech_.src() !== this.snapshot.src;
      const currentSrcChanged = player.currentSrc() !== this.snapshot.currentSrc;

      return srcChanged || currentSrcChanged;
    },

    // Returns a boolean indicating if given player is in live mode.
    // One reason for this: https://github.com/videojs/video.js/issues/3262
    // Also, some live content can have a duration.
    isLive(somePlayer = player) {
      if (typeof somePlayer.ads.settings.contentIsLive === 'boolean') {
        return somePlayer.ads.settings.contentIsLive;
      } else if (somePlayer.duration() === Infinity) {
        return true;
      } else if (videojs.browser.IOS_VERSION === '8' && somePlayer.duration() === 0) {
        return true;
      }
      return false;
    },

    // Return true if content playback should mute and continue during ad breaks.
    // This is only done during live streams on platforms where it's supported.
    // This improves speed and accuracy when returning from an ad break.
    shouldPlayContentBehindAd(somePlayer = player) {
      if (!somePlayer) {
        throw new Error('shouldPlayContentBehindAd requires a player as a param');
      } else if (!somePlayer.ads.settings.liveCuePoints) {
        return false;
      } else {
        return !videojs.browser.IS_IOS &&
               !videojs.browser.IS_ANDROID &&
               somePlayer.duration() === Infinity;
      }
    },

    // Return true if the ads plugin should save and restore snapshots of the
    // player state when moving into and out of ad mode.
    shouldTakeSnapshots(somePlayer = player) {
      return !this.shouldPlayContentBehindAd(somePlayer) && !this.stitchedAds();
    },

    // Returns true if player is in ad mode.
    //
    // Ad mode definition:
    // If content playback is blocked by the ad plugin.
    //
    // Examples of ad mode:
    //
    // * Waiting to find out if an ad is going to play while content would normally be
    //   playing.
    // * Waiting for an ad to start playing while content would normally be playing.
    // * An ad is playing (even if content is also playing)
    // * An ad has completed and content is about to resume, but content has not resumed
    //   yet.
    //
    // Examples of not ad mode:
    //
    // * Content playback has not been requested
    // * Content playback is paused
    // * An asynchronous ad request is ongoing while content is playing
    // * A non-linear ad is active
    isInAdMode() {
      return this._state.isAdState();
    },

    // Returns true if in ad mode but an ad break hasn't started yet.
    isWaitingForAdBreak() {
      return this._state.isWaitingForAdBreak();
    },

    // Returns true if content is resuming after an ad. This is part of ad mode.
    isContentResuming() {
      return this._state.isContentResuming();
    },

    // Deprecated because the name was misleading. Use inAdBreak instead.
    isAdPlaying() {
      return this._state.inAdBreak();
    },

    // Returns true if an ad break is ongoing. This is part of ad mode.
    // An ad break is the time between startLinearAdMode and endLinearAdMode.
    inAdBreak() {
      return this._state.inAdBreak();
    },

    /*
     * Remove the poster attribute from the video element tech, if present. When
     * reusing a video element for multiple videos, the poster image will briefly
     * reappear while the new source loads. Removing the attribute ahead of time
     * prevents the poster from showing up between videos.
     *
     * @param {Object} player The videojs player object
     */
    removeNativePoster() {
      const tech = player.$('.vjs-tech');

      if (tech) {
        tech.removeAttribute('poster');
      }
    },

    debug(...args) {
      if (this.settings.debug) {
        if (args.length === 1 && typeof args[0] === 'string') {
          videojs.log('ADS: ' + args[0]);
        } else {
          videojs.log('ADS:', ...args);
        }
      }
    }

  };

}
