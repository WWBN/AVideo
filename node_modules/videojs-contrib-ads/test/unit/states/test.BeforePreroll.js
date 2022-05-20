import QUnit from 'qunit';
import sinon from 'sinon';

import BeforePreroll from '../../../src/states/BeforePreroll.js';
import * as CancelContentPlay from '../../../src/cancelContentPlay.js';

/*
 * These tests are intended to be isolated unit tests for one state with all
 * other modules mocked.
 */
QUnit.module('BeforePreroll', {
  beforeEach() {
    this.events = [];

    this.player = {
      ads: {
        debug: () => {},
        _shouldBlockPlay: false,
        settings: {}
      },
      setTimeout: () => {},
      trigger: (event) => {
        this.events.push(event);
      }
    };

    this.beforePreroll = new BeforePreroll(this.player);
    this.beforePreroll.transitionTo = (newState, arg, arg2) => {
      this.newState = newState.name;
      this.transitionArg = arg;
      this.transitionArg2 = arg2;
    };

    this.cancelContentPlayStub = sinon.stub(CancelContentPlay, 'cancelContentPlay');
  },

  afterEach() {
    this.cancelContentPlayStub.restore();
  }
});

QUnit.test('transitions to Preroll (adsready first)', function(assert) {
  this.beforePreroll.init(this.player);
  assert.equal(this.beforePreroll.adsReady, false);
  this.beforePreroll.onAdsReady(this.player);
  assert.equal(this.beforePreroll.adsReady, true);
  this.beforePreroll.onPlay(this.player);
  assert.equal(this.newState, 'Preroll');
  assert.equal(this.transitionArg, true);
});

QUnit.test('transitions to Preroll (play first)', function(assert) {
  this.beforePreroll.init(this.player);
  assert.equal(this.beforePreroll.adsReady, false);
  this.beforePreroll.onPlay(this.player);
  assert.equal(this.newState, 'Preroll');
  assert.equal(this.transitionArg, false);
});

QUnit.test('cancels ads', function(assert) {
  this.beforePreroll.init(this.player);
  this.beforePreroll.onAdsCanceled(this.player);
  assert.equal(this.beforePreroll.shouldResumeToContent, true);
  this.beforePreroll.onPlay(this.player);
  assert.equal(this.newState, 'Preroll');
  assert.equal(this.transitionArg, false);
  assert.equal(this.transitionArg2, true);
});

QUnit.test('transitions to content resuming in preroll on error', function(assert) {
  this.beforePreroll.init(this.player);
  this.beforePreroll.onAdsError(this.player);
  assert.equal(this.beforePreroll.shouldResumeToContent, true);
  this.beforePreroll.onPlay(this.player);
  assert.equal(this.newState, 'Preroll');
  assert.equal(this.transitionArg, false);
  assert.equal(this.transitionArg2, true);
});

QUnit.test('has no preroll', function(assert) {
  this.beforePreroll.init(this.player);
  this.beforePreroll.onNoPreroll(this.player);
  assert.equal(this.beforePreroll.shouldResumeToContent, true);
  this.beforePreroll.onPlay(this.player);
  assert.equal(this.newState, 'Preroll');
  assert.equal(this.transitionArg, false);
  assert.equal(this.transitionArg2, true);
});

QUnit.test('skips the preroll', function(assert) {
  this.beforePreroll.init(this.player);
  this.beforePreroll.skipLinearAdMode();
  assert.equal(this.events[0], 'adskip');
  assert.equal(this.beforePreroll.shouldResumeToContent, true);
  this.beforePreroll.onPlay(this.player);
  assert.equal(this.newState, 'Preroll');
  assert.equal(this.transitionArg, false);
  assert.equal(this.transitionArg2, true);
});

QUnit.test('handles content change', function(assert) {
  sinon.spy(this.beforePreroll, 'init');
  this.beforePreroll.onContentChanged(this.player);
  assert.equal(this.beforePreroll.init.calledOnce, true);
});

QUnit.test('sets _shouldBlockPlay to true by default', function(assert) {
  this.beforePreroll.init(this.player);
  assert.equal(this.player.ads._shouldBlockPlay, true);
});

QUnit.test('sets _shouldBlockPlay to true if allowVjsAutoplay option is true and player.autoplay() is false', function(assert) {
  this.player.ads.settings.allowVjsAutoplay = true;

  this.player.autoplay = () => false;

  this.beforePreroll.init(this.player);
  assert.equal(this.player.ads._shouldBlockPlay, true);
});

QUnit.test('sets _shouldBlockPlay to false if allowVjsAutoplay option is true and player.autoplay() is truthy', function(assert) {
  this.player.ads.settings.allowVjsAutoplay = true;

  this.player.autoplay = () => 'play';

  this.beforePreroll.init(this.player);
  assert.equal(this.player.ads._shouldBlockPlay, false);
});

QUnit.test('updates `shouldResumeToContent` on `nopreroll`', function(assert) {
  this.beforePreroll.init(this.player);
  this.beforePreroll.onNoPreroll();
  assert.strictEqual(this.beforePreroll.shouldResumeToContent, true);
});

QUnit.test('updates `shouldResumeToContent` on `adserror`', function(assert) {
  this.beforePreroll.init(this.player);
  this.beforePreroll.onAdsError();
  assert.strictEqual(this.beforePreroll.shouldResumeToContent, true);
});

QUnit.test('updates `shouldResumeToContent` on `adscanceled`', function(assert) {
  this.beforePreroll.init(this.player);
  this.beforePreroll.onAdsCanceled(this.player);
  assert.strictEqual(this.beforePreroll.shouldResumeToContent, true);
});

QUnit.test('updates `shouldResumeToContent` on `skipLinearAdMode`', function(assert) {
  this.beforePreroll.init(this.player);
  this.beforePreroll.skipLinearAdMode();
  assert.strictEqual(this.beforePreroll.shouldResumeToContent, true);
});
