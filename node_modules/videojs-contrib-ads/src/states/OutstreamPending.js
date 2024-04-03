import States from '../states.js';

const AdState = States.getState('AdState');

/**
 * This is the initial state for a player in outstream mode. Once a 'play' event
 * is seen, we enter the OutstreamPlayback state. If any errors occur, we go
 * straight from OutstreamPlayback to OutstreamDone.
 */
class OutstreamPending extends AdState {
  /**
   * Allows state name to be logged after minification
   */
  static _getName() {
    return 'OutstreamPending';
  }

  /*
   * For state transitions to work correctly, initialization should
   * happen here, not in a constructor.
   */
  init(player) {
    this.adsReady = false;
  }

  onPlay(player) {
    const OutstreamPlayback = States.getState('OutstreamPlayback');

    player.ads.debug('Received play event (OutstreamPending)');
    this.transitionTo(OutstreamPlayback, this.adsReady);
  }

  onAdsReady(player) {
    player.ads.debug('Received adsready event (OutstreamPending)');
    this.adsReady = true;
  }

  onAdsError() {
    this.player.ads.debug('adserror (OutstreamPending)');
    this.adsReady = false;
  }
}

States.registerState('OutstreamPending', OutstreamPending);

export default OutstreamPending;
