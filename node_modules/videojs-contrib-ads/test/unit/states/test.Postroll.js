import QUnit from 'qunit';
import sinon from 'sinon';
import Postroll from '../../../src/states/Postroll.js';
import adBreak from '../../../src/adBreak.js';

/*
 * These tests are intended to be isolated unit tests for one state with all
 * other modules mocked.
 */
QUnit.module('Postroll', {
  beforeEach() {
    this.events = [];

    this.player = {
      ads: {
        settings: {},
        debug: () => {},
        error: () => {},
        inAdBreak: () => false
      },
      addClass: () => {},
      removeClass: () => {},
      setTimeout: () => {},
      trigger: (event) => {
        this.events.push(event);
      },
      clearTimeout: () => {}
    };

    this.postroll = new Postroll(this.player);

    this.postroll.transitionTo = (newState) => {
      this.newState = newState.name;
    };

    this.adBreakStartStub = sinon.stub(adBreak, 'start');
    this.adBreakEndStub = sinon.stub(adBreak, 'end');
  },

  afterEach() {
    this.adBreakStartStub.restore();
    this.adBreakEndStub.restore();
  }
});

QUnit.test('sets _contentEnding on init', function(assert) {
  this.postroll.init(this.player);
  assert.equal(this.player.ads._contentEnding, true, 'content is ending');
});

QUnit.test('startLinearAdMode starts ad break', function(assert) {
  this.postroll.init(this.player);
  this.postroll.startLinearAdMode();
  assert.equal(this.adBreakStartStub.callCount, 1, 'ad break started');
  assert.equal(this.player.ads.adType, 'postroll', 'ad type is postroll');
});

QUnit.test('removes ad loading class on ad started', function(assert) {
  this.player.removeClass = sinon.spy();
  this.postroll.init(this.player);
  this.postroll.onAdStarted(this.player);
  assert.ok(this.player.removeClass.calledWith('vjs-ad-loading'));
});

QUnit.test('ends linear ad mode & ended event on ads error', function(assert) {
  this.player.ads.endLinearAdMode = sinon.spy();

  this.postroll.init(this.player);
  this.player.ads.inAdBreak = () => true;
  this.postroll.onAdsError(this.player);
  assert.equal(this.player.ads.endLinearAdMode.callCount, 1, 'linear ad mode ended');
});

QUnit.test('no endLinearAdMode on adserror if not in ad break', function(assert) {
  this.player.ads.endLinearAdMode = sinon.spy();

  this.postroll.init(this.player);
  this.player.ads.inAdBreak = () => false;
  this.postroll.onAdsError(this.player);
  assert.equal(this.player.ads.endLinearAdMode.callCount, 0, 'linear ad mode ended');
});

QUnit.test('does not transition to AdsDone unless content resuming', function(assert) {
  this.postroll.init(this.player);
  this.postroll.onEnded(this.player);
  assert.equal(this.newState, undefined, 'no transition');
});

QUnit.test('transitions to BeforePreroll on content changed after ad break', function(assert) {
  this.postroll.isContentResuming = () => true;
  this.postroll.init(this.player);
  this.postroll.onContentChanged(this.player);
  assert.equal(this.newState, 'BeforePreroll');
});

QUnit.test('transitions to Preroll on content changed before ad break', function(assert) {
  this.postroll.init(this.player);
  this.postroll.onContentChanged(this.player);
  assert.equal(this.newState, 'Preroll');
});

QUnit.test('doesn\'t transition on content changed during ad break', function(assert) {
  this.postroll.inAdBreak = () => true;
  this.postroll.init(this.player);
  this.postroll.onContentChanged(this.player);
  assert.equal(this.newState, undefined, 'no transition');
});

QUnit.test('transitions to AdsDone on nopostroll before ad break', function(assert) {
  this.postroll.init(this.player);
  this.postroll.onNoPostroll(this.player);
  assert.equal(this.newState, 'AdsDone');
});

QUnit.test('no transition on nopostroll during ad break', function(assert) {
  this.postroll.inAdBreak = () => true;
  this.postroll.init(this.player);
  this.postroll.onNoPostroll(this.player);
  assert.equal(this.newState, undefined, 'no transition');
});

QUnit.test('no transition on nopostroll after ad break', function(assert) {
  this.postroll.isContentResuming = () => true;
  this.postroll.init(this.player);
  this.postroll.onNoPostroll(this.player);
  assert.equal(this.newState, undefined, 'no transition');
});

QUnit.test('can abort', function(assert) {
  const removeClassSpy = sinon.spy(this.player, 'removeClass');

  this.postroll.init(this.player);
  this.postroll.abort(this.player);
  assert.equal(this.postroll.contentResuming, true, 'contentResuming');
  assert.ok(removeClassSpy.calledWith('vjs-ad-loading'), 'loading class removed');
});

QUnit.test('can clean up', function(assert) {
  const clearSpy = sinon.spy(this.player, 'clearTimeout');

  this.postroll.init(this.player);
  this.postroll.cleanup(this.player);
  assert.equal(this.player.ads._contentEnding, false, '_contentEnding');
  assert.ok(clearSpy.calledWith(this.postroll._postrollTimeout), 'cleared timeout');
});

QUnit.test('can tell if waiting for ad break', function(assert) {
  this.postroll.init(this.player);
  assert.equal(this.postroll.isWaitingForAdBreak(), true, 'waiting for ad break');
  this.postroll.startLinearAdMode();
  assert.equal(this.postroll.isWaitingForAdBreak(), false, 'not waiting for ad break');
});
