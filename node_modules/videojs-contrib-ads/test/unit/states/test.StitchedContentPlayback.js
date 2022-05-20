import StitchedContentPlayback from '../../../src/states/StitchedContentPlayback.js';
import QUnit from 'qunit';

/*
 * These tests are intended to be isolated unit tests for one state with all
 * other modules mocked.
 */
QUnit.module('StitchedContentPlayback', {
  beforeEach() {
    this.events = [];
    this.playTriggered = false;

    this.player = {
      paused: () => false,
      play: () => {},
      trigger: (event) => {
        this.events.push(event);
      },
      ads: {
        debug: () => {},
        _contentHasEnded: false,
        _shouldBlockPlay: true
      }
    };

    this.stitchedContentPlayback = new StitchedContentPlayback(this.player);
    this.stitchedContentPlayback.transitionTo = (newState) => {
      this.newState = newState.name;
    };
  }
});

QUnit.test('transitions to StitchedAdRoll when startLinearAdMode is called', function(assert) {
  this.stitchedContentPlayback.init();
  this.stitchedContentPlayback.startLinearAdMode();
  assert.equal(this.newState, 'StitchedAdRoll', 'transitioned to StitchedAdRoll');
});

QUnit.test('sets _shouldBlockPlay to false on init', function(assert) {
  assert.equal(this.player.ads._shouldBlockPlay, true);

  this.stitchedContentPlayback.init();
  assert.equal(this.player.ads._shouldBlockPlay, false);
});
