import States from '../states.js';
import adBreak from '../adBreak.js';
import AdsError from '../consts/errors';

const AdState = States.getState('AdState');

class Midroll extends AdState {

  /*
   * Allows state name to be logged even after minification.
   */
  static _getName() {
    return 'Midroll';
  }

  /*
   * Midroll breaks happen when the ad plugin calls startLinearAdMode,
   * which can happen at any time during content playback.
   */
  init(player) {
    player.ads.adType = 'midroll';
    adBreak.start(player);
    player.addClass('vjs-ad-loading');
  }

  /*
   * An ad has actually started playing.
   * Remove the loading spinner.
   */
  onAdStarted(player) {
    player.removeClass('vjs-ad-loading');
  }

  /*
   * Midroll break is done.
   */
  endLinearAdMode() {
    const player = this.player;

    if (this.inAdBreak()) {
      this.contentResuming = true;
      player.addClass('vjs-ad-content-resuming');
      player.removeClass('vjs-ad-loading');
      adBreak.end(player);
    }
  }

  /*
   * End midroll break if there is an error.
   */
  onAdsError(player) {
    player.ads.error({
      errorType: AdsError.AdsMidrollError
    });

    // In the future, we may not want to do this automatically.
    // Ad plugins should be able to choose to continue the ad break
    // if there was an error.
    if (this.inAdBreak()) {
      player.ads.endLinearAdMode();
    }
  }

  /*
   * Cleanup CSS classes.
   */
  cleanup(player) {
    player.removeClass('vjs-ad-loading');
    player.removeClass('vjs-ad-content-resuming');
  }

}

States.registerState('Midroll', Midroll);

export default Midroll;
