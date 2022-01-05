import States from '../states.js';

const ContentState = States.getState('ContentState');

/*
 * This state represents content playback when stitched ads are in play.
 */
class StitchedContentPlayback extends ContentState {

  /*
   * Allows state name to be logged even after minification.
   */
  static _getName() {
    return 'StitchedContentPlayback';
  }

  /*
   * For state transitions to work correctly, initialization should
   * happen here, not in a constructor.
   */
  init() {

    // Don't block calls to play in stitched ad players, ever.
    this.player.ads._shouldBlockPlay = false;
  }

  /*
   * Source change does not do anything for stitched ad players.
   * contentchanged does not fire during ad breaks, so we don't need to
   * worry about that.
   */
  onContentChanged() {
    this.player.ads.debug(`Received contentchanged event (${this.constructor._getName()})`);
  }

  /*
   * This is how stitched ads start.
   */
  startLinearAdMode() {
    const StitchedAdRoll = States.getState('StitchedAdRoll');

    this.transitionTo(StitchedAdRoll);
  }
}

States.registerState('StitchedContentPlayback', StitchedContentPlayback);

export default StitchedContentPlayback;
