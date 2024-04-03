import QUnit from 'qunit';
import sinon from 'sinon';
import OutstreamPending from '../../../src/states/OutstreamPending.js';

/*
 * These tests are intended to be isolated unit tests for one state with all
 * other modules mocked.
 */
QUnit.module('OutstreamPending', {
  beforeEach() {
    this.player = {
      addClass: () => {},
      removeClass: () => {},
      trigger: sinon.spy(),
      ads: {
        _inLinearAdMode: true,
        debug: () => {}
      }
    };

    this.outstreamPending = new OutstreamPending(this.player);
    this.outstreamPending.transitionTo = (newState, arg) => {
      this.newState = newState.name;
      this.transitionArg = arg;
    };
  }
});

QUnit.test('transitions to OutstreamPlayback on adsready', function(assert) {
  this.outstreamPending.init(this.player);
  assert.equal(this.outstreamPending.adsReady, false);
  this.outstreamPending.onAdsReady(this.player);
  assert.equal(this.outstreamPending.adsReady, true);
  this.outstreamPending.onPlay(this.player);
  assert.equal(this.newState, 'OutstreamPlayback');
});

QUnit.test('sets adsReady to false on adserror', function(assert) {
  this.outstreamPending.init(this.player);
  this.outstreamPending.onAdsError(this.player);
  assert.equal(this.outstreamPending.adsReady, false);
  this.outstreamPending.onPlay(this.player);
  assert.equal(this.newState, 'OutstreamPlayback');
});
