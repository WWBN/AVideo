import QUnit from 'qunit';
import sinon from 'sinon';
import Midroll from '../../../src/states/Midroll.js';
import adBreak from '../../../src/adBreak.js';

/*
 * These tests are intended to be isolated unit tests for one state with all
 * other modules mocked.
 */
QUnit.module('Midroll', {
  beforeEach() {
    this.player = {
      addClass: () => {},
      removeClass: () => {},
      ads: {
        _inLinearAdMode: true,
        endLinearAdMode: () => {
          this.calledEndLinearAdMode = true;
        }
      }
    };

    this.midroll = new Midroll(this.player);

    this.adBreakStartStub = sinon.stub(adBreak, 'start');
    this.adBreakEndStub = sinon.stub(adBreak, 'end');
  },

  afterEach() {
    this.adBreakStartStub.restore();
    this.adBreakEndStub.restore();
  }
});

QUnit.test('starts an ad break on init', function(assert) {
  this.midroll.init(this.player);
  assert.equal(this.player.ads.adType, 'midroll', 'ad type is midroll');
  assert.equal(this.adBreakStartStub.callCount, 1, 'ad break started');
});

QUnit.test('ends an ad break on endLinearAdMode', function(assert) {
  this.midroll.init(this.player);
  this.midroll.endLinearAdMode();
  assert.equal(this.adBreakEndStub.callCount, 1, 'ad break ended');
});

QUnit.test('adserror during ad break ends ad break', function(assert) {
  this.midroll.init(this.player);
  this.midroll.onAdsError(this.player);
  assert.equal(this.calledEndLinearAdMode, true, 'linear ad mode ended');
});
