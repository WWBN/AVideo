import videojs from 'video.js';
import adBreak from '../adBreak.js';

import States from '../states.js';
import AdsError from '../consts/errors.js';

const AdState = States.getState('AdState');

/*
 * This state encapsulates waiting for prerolls, preroll playback, and
 * content restoration after a preroll.
 */
class Preroll extends AdState {

  /*
   * Allows state name to be logged even after minification.
   */
  static _getName() {
    return 'Preroll';
  }

  /*
   * For state transitions to work correctly, initialization should
   * happen here, not in a constructor.
   */
  init(player, adsReady, shouldResumeToContent) {
    this.waitingForAdBreak = true;

    // Loading spinner from now until ad start or end of ad break.
    player.addClass('vjs-ad-loading');

    // If adserror, adscanceled, nopreroll or skipLinearAdMode already
    // ocurred, resume to content immediately
    if (shouldResumeToContent || player.ads.nopreroll_) {
      return this.resumeAfterNoPreroll(player);
    }

    // Determine preroll timeout based on plugin settings
    let timeout = player.ads.settings.timeout;

    if (typeof player.ads.settings.prerollTimeout === 'number') {
      timeout = player.ads.settings.prerollTimeout;
    }

    // Start the clock ticking for ad timeout
    this._timeout = player.setTimeout(function() {
      player.trigger('adtimeout');
    }, timeout);

    // If adsready already happened, lets get started. Otherwise,
    // wait until onAdsReady.
    if (adsReady) {
      this.handleAdsReady();

    } else {
      this.adsReady = false;
    }
  }

  /*
   * Adsready event after play event.
   */
  onAdsReady(player) {
    if (!player.ads.inAdBreak()) {
      player.ads.debug('Received adsready event (Preroll)');
      this.handleAdsReady();
    } else {
      videojs.log.warn('Unexpected adsready event (Preroll)');
    }
  }

  /*
   * Ad plugin is ready. Let's get started on this preroll.
   */
  handleAdsReady() {
    this.adsReady = true;
    this.readyForPreroll();
  }

  /*
   * Helper to call a callback only after a loadstart event.
   * If we start content or ads before loadstart, loadstart
   * will not be prefixed correctly.
   */
  afterLoadStart(callback) {
    const player = this.player;

    if (player.ads._hasThereBeenALoadStartDuringPlayerLife) {
      callback();
    } else {
      player.ads.debug('Waiting for loadstart...');
      player.one('loadstart', () => {
        player.ads.debug('Received loadstart event');
        callback();
      });
    }
  }

  /*
   * If there is no preroll, play content instead.
   */
  noPreroll() {
    this.afterLoadStart(() => {
      this.player.ads.debug('Skipping prerolls due to nopreroll event (Preroll)');
      this.resumeAfterNoPreroll(this.player);
    });
  }

  /*
   * Fire the readyforpreroll event. If loadstart hasn't happened yet,
   * wait until loadstart first.
   */
  readyForPreroll() {
    const player = this.player;

    this.afterLoadStart(() => {
      player.ads.debug('Triggered readyforpreroll event (Preroll)');
      player.trigger('readyforpreroll');
    });
  }

  /*
   * adscanceled cancels all ads for the source. Play content now.
   */
  onAdsCanceled(player) {
    player.ads.debug('adscanceled (Preroll)');

    this.afterLoadStart(() => {
      this.resumeAfterNoPreroll(player);
    });
  }

  /*
   * An ad error occured. Play content instead.
   */
  onAdsError(player) {
    videojs.log('adserror (Preroll)');

    player.ads.error({
      errorType: AdsError.AdsPrerollError
    });

    // In the future, we may not want to do this automatically.
    // Ad plugins should be able to choose to continue the ad break
    // if there was an error.
    if (this.inAdBreak()) {
      player.ads.endLinearAdMode();

    } else {
      this.afterLoadStart(() => {
        this.resumeAfterNoPreroll(player);
      });
    }
  }
  /*
   * Ad plugin invoked startLinearAdMode, the ad break starts now.
   */
  startLinearAdMode() {
    const player = this.player;

    if (this.adsReady && !player.ads.inAdBreak() && !this.isContentResuming()) {
      this.clearTimeout(player);
      player.ads.adType = 'preroll';
      this.waitingForAdBreak = false;
      adBreak.start(player);

      // We don't need to block play calls anymore
      player.ads._shouldBlockPlay = false;
    } else {
      videojs.log.warn('Unexpected startLinearAdMode invocation (Preroll)');
    }
  }

  /*
   * An ad has actually started playing.
   * Remove the loading spinner.
   */
  onAdStarted(player) {
    player.removeClass('vjs-ad-loading');
  }

  /*
   * Ad plugin invoked endLinearAdMode, the ad break ends now.
   */
  endLinearAdMode() {
    const player = this.player;

    if (this.inAdBreak()) {
      player.removeClass('vjs-ad-loading');
      player.addClass('vjs-ad-content-resuming');
      this.contentResuming = true;
      adBreak.end(player);
    }
  }

  /*
   * Ad skipped by ad plugin. Play content instead.
   */
  skipLinearAdMode() {
    const player = this.player;

    if (player.ads.inAdBreak() || this.isContentResuming()) {
      videojs.log.warn('Unexpected skipLinearAdMode invocation');
    } else {
      this.afterLoadStart(() => {
        player.trigger('adskip');
        player.ads.debug('skipLinearAdMode (Preroll)');
        this.resumeAfterNoPreroll(player);
      });
    }
  }

  /*
   * Prerolls took too long! Play content instead.
   */
  onAdTimeout(player) {
    this.afterLoadStart(() => {
      player.ads.debug('adtimeout (Preroll)');
      this.resumeAfterNoPreroll(player);
    });
  }

  /*
   * Check if nopreroll event was too late before handling it.
   */
  onNoPreroll(player) {
    if (player.ads.inAdBreak() || this.isContentResuming()) {
      videojs.log.warn('Unexpected nopreroll event (Preroll)');
    } else {
      this.noPreroll();
    }
  }

  resumeAfterNoPreroll(player) {

    // Resume to content and unblock play as there is no preroll ad
    this.contentResuming = true;
    player.ads._shouldBlockPlay = false;
    this.cleanupPartial(player);

    // Play the content if we had requested play or we paused on 'contentupdate'
    // and we haven't played yet. This happens if there was no preroll or if it
    // errored, timed out, etc. Otherwise snapshot restore would play.
    if (player.ads._playRequested || player.ads._pausedOnContentupdate) {
      if (player.paused()) {
        player.ads.debug('resumeAfterNoPreroll: attempting to resume playback (Preroll)');

        const playPromise = player.play();

        if (playPromise && playPromise.then) {
          playPromise.then(null, function(e) { });
        }

      } else {
        player.ads.debug('resumeAfterNoPreroll: already playing (Preroll)');
        player.trigger('play');
        player.trigger('playing');
      }
    }

  }

  /*
   * Cleanup timeouts and spinner.
   */
  cleanup(player) {
    if (!player.ads._hasThereBeenALoadStartDuringPlayerLife) {
      videojs.log.warn('Leaving Preroll state before loadstart event can cause issues.');
    }
    this.cleanupPartial(player);
  }

  /*
   * Performs cleanup tasks without depending on a state transition. This is
   * used mainly in cases where a preroll failed.
   */
  cleanupPartial(player) {
    player.removeClass('vjs-ad-loading');
    player.removeClass('vjs-ad-content-resuming');
    this.clearTimeout(player);
  }

  /*
   * Clear the preroll timeout and nulls out the pointer.
   */
  clearTimeout(player) {
    player.clearTimeout(this._timeout);
    this._timeout = null;
  }
}

States.registerState('Preroll', Preroll);

export default Preroll;
