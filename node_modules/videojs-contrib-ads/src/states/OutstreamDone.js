import videojs from 'video.js';
import States from '../states.js';

const AdState = States.getState('AdState');

/**
 * This is the final state for a player in outstream mode. There
 * should be no more ads playing once the player has transitioned
 * to this state.
 */
class OutstreamDone extends AdState {

  /**
   * Allows state name to be logged after minification
   */
  static _getName() {
    return 'OutstreamDone';
  }

  /*
   * For state transitions to work correctly, initialization should
   * happen here, not in a constructor.
   */
  init(player) {
    player.trigger('ended');
  }

  /*
   * No more ads should play after this state.
   */
  startLinearAdMode() {
    videojs.log.warn('Unexpected startLinearAdMode invocation (OutstreamDone)');
  }
}

States.registerState('OutstreamDone', OutstreamDone);

export default OutstreamDone;
