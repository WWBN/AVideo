import States from '../../states.js';
import State from './State.js';

/*
 * This class contains logic for all ads, be they prerolls, midrolls, or postrolls.
 * Primarily, this involves handling startLinearAdMode and endLinearAdMode.
 * It also handles content resuming.
 */
class AdState extends State {

  constructor(player) {
    super(player);
    this.contentResuming = false;
    this.waitingForAdBreak = false;
  }

  /*
   * Overrides State.isAdState
   */
  isAdState() {
    return true;
  }

  /*
   * We end the content-resuming process on the playing event because this is the exact
   * moment that content playback is no longer blocked by ads.
   */
  onPlaying() {
    const ContentPlayback = States.getState('ContentPlayback');

    if (this.contentResuming) {
      this.transitionTo(ContentPlayback);
    }
  }

  /*
   * If the ad plugin does not result in a playing event when resuming content after an
   * ad, they should instead trigger a contentresumed event to signal that content should
   * resume. The main use case for this is when ads are stitched into the content video.
   */
  onContentResumed() {
    const ContentPlayback = States.getState('ContentPlayback');

    if (this.contentResuming) {
      this.transitionTo(ContentPlayback);
    }
  }

  /*
   * Check if we are in an ad state waiting for the ad plugin to start
   * an ad break.
   */
  isWaitingForAdBreak() {
    return this.waitingForAdBreak;
  }

  /*
   * Allows you to check if content is currently resuming after an ad break.
   */
  isContentResuming() {
    return this.contentResuming;
  }

  /*
   * Allows you to check if an ad break is in progress.
   */
  inAdBreak() {
    return this.player.ads._inLinearAdMode === true;
  }

}

States.registerState('AdState', AdState);

export default AdState;
