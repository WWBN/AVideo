import QUnit from 'qunit';
import sinon from 'sinon';
import StitchedAdRoll from '../../../src/states/StitchedAdRoll.js';
import adBreak from '../../../src/adBreak.js';

/*
 * These tests are intended to be isolated unit tests for one state with all
 * other modules mocked.
 */
QUnit.module('StitchedAdRoll', {
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

    this.adroll = new StitchedAdRoll(this.player);

    this.adBreakStartStub = sinon.stub(adBreak, 'start');
    this.adBreakEndStub = sinon.stub(adBreak, 'end');
  },

  afterEach() {
    this.adBreakStartStub.restore();
    this.adBreakEndStub.restore();
  }
});

QUnit.test('starts an ad break on init', function(assert) {
  this.adroll.init();
  assert.equal(this.player.ads.adType, 'stitched', 'ad type is stitched');
  assert.equal(this.adBreakStartStub.callCount, 1, 'ad break started');
});

QUnit.test('ends an ad break on endLinearAdMode', function(assert) {
  this.adroll.init();
  this.adroll.endLinearAdMode();
  assert.equal(this.adBreakEndStub.callCount, 1, 'ad break ended');
});

QUnit.test('adended during ad break leaves linear ad mode and re-triggers ended', function(assert) {
  sinon.spy(this.adroll, 'endLinearAdMode');

  this.adroll.init();
  this.adroll.onAdEnded();
  assert.ok(this.player.trigger.calledOnce, 'the player fired one event');
  assert.ok(this.player.trigger.calledWith('ended'), 'the event it fired was ended');
  assert.ok(this.adroll.endLinearAdMode.calledOnce, 'the ad roll called endLinearAdMode');
});
