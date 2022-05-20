import States from '../../states.js';
import State from './State.js';

class ContentState extends State {

  /*
   * Overrides State.isAdState
   */
  isAdState() {
    return false;
  }

  /*
   * Source change sends you back to preroll checks. contentchanged does not
   * fire during ad breaks, so we don't need to worry about that.
   */
  onContentChanged(player) {
    const BeforePreroll = States.getState('BeforePreroll');
    const Preroll = States.getState('Preroll');

    player.ads.debug('Received contentchanged event (ContentState)');
    if (player.paused()) {
      this.transitionTo(BeforePreroll);
    } else {
      this.transitionTo(Preroll, false);
      player.pause();
      player.ads._pausedOnContentupdate = true;
    }
  }

}

States.registerState('ContentState', ContentState);

export default ContentState;
