import videojs from 'video.js';
import States from '../states.js';

const ContentState = States.getState('ContentState');

class AdsDone extends ContentState {

  /*
   * Allows state name to be logged even after minification.
   */
  static _getName() {
    return 'AdsDone';
  }

  /*
   * For state transitions to work correctly, initialization should
   * happen here, not in a constructor.
   */
  init(player) {
    // From now on, `ended` events won't be redispatched
    player.ads._contentHasEnded = true;
    player.trigger('ended');
  }

  /*
   * Midrolls do not play after ads are done.
   */
  startLinearAdMode() {
    videojs.log.warn('Unexpected startLinearAdMode invocation (AdsDone)');
  }

}

States.registerState('AdsDone', AdsDone);

export default AdsDone;
