import QUnit from 'qunit';
import sinon from 'sinon';
import videojs from 'video.js';
import window from 'global/window';
import sharedModuleHooks from './lib/shared-module-hooks.js';
import _ from 'lodash';

const sharedHooks = sharedModuleHooks();

const timerExists = function(env, id) {
  return env.clock.timers.hasOwnProperty(id);
};

// Stub mobile browsers to force cancelContentPlay to be used
const fakeVideojs = function() {
  this.videojs = sinon.stub(videojs, 'browser').get(() => {
    return {
      IS_ANDROID: true,
      IS_IOS: true
    };
  });
};

// Restore original videojs behavior
const restoreVideojs = function() {
  this.videojs.restore();
};

// Run custom hooks before sharedModuleHooks, as videojs must be
// modified before setting up the player and videojs-contrib-ads
QUnit.module('Cancel Content Play', {
  beforeEach: _.flow(function() {
    this.adsOptions = {};
  }, fakeVideojs, sharedHooks.beforeEach),
  afterEach: _.flow(function() {
    this.adsOptions = null;
  }, restoreVideojs, sharedHooks.afterEach)
});

QUnit.test('pauses to wait for prerolls when the plugin loads BEFORE play', function(assert) {
  const spy = sinon.spy(this.player, 'pause');

  this.player.paused = function() {
    return false;
  };

  this.player.trigger('adsready');
  this.player.trigger('play');
  this.clock.tick(1);
  this.player.trigger('play');
  this.clock.tick(1);

  assert.strictEqual(spy.callCount, 2, 'play attempts are paused');
});

QUnit.test('pauses to wait for prerolls when the plugin loads AFTER play', function(assert) {
  const pauseSpy = sinon.spy(this.player, 'pause');

  this.player.paused = function() {
    return false;
  };

  this.player.trigger('play');
  this.clock.tick(1);
  this.player.trigger('play');
  this.clock.tick(1);
  assert.equal(pauseSpy.callCount, 2, 'play attempts are paused');
});

QUnit.test('stops canceling play events when an ad is playing', function(assert) {
  const setTimeoutSpy = sinon.spy(window, 'setTimeout');
  const pauseSpy = sinon.spy(this.player, 'pause');

  // Throughout this test, we check both that the expected timeout is
  // populated on the `clock` _and_ that `setTimeout` has been called the
  // expected number of times.
  assert.notOk(this.player.ads._cancelledPlay, 'we have not canceled a play event');

  this.player.paused = () => {
    return false;
  };
  this.player.trigger('play');
  assert.strictEqual(setTimeoutSpy.callCount, 1, 'one timer was created (`_prerollTimeout`)');
  assert.ok(timerExists(this, this.player.ads._state._timeout), 'preroll timeout exists after play');
  assert.equal(this.player.ads._cancelledPlay, true);

  this.clock.tick(1);
  assert.equal(pauseSpy.callCount, 1);

  this.player.trigger('adsready');
  assert.ok(timerExists(this, this.player.ads._state._timeout), 'preroll timeout exists after adsready');

  this.player.ads.startLinearAdMode();
  assert.notOk(timerExists(this, this.player.ads._state._timeout), 'preroll timeout no longer exists');

  this.player.trigger('play');
  assert.equal(pauseSpy.callCount, 1, 'pause is not called while in an ad break');

  window.setTimeout.restore();
});

QUnit.test("cancelContentPlay doesn\'t block play in content playback", function(assert) {
  const pauseSpy = sinon.spy(this.player, 'pause');

  this.player.trigger('loadstart');
  this.player.trigger('adscanceled');
  this.player.paused = () => {
    return false;
  };
  this.player.trigger('play');
  assert.strictEqual(pauseSpy.callCount, 1, 'pause should have been called');
  assert.strictEqual(
    this.player.ads._cancelledPlay, false,
    'cancelContentPlay is not called while resuming'
  );

  // enters ContentPlayback
  this.player.trigger('playing');
  this.player.trigger('play');

  assert.strictEqual(pauseSpy.callCount, 1, 'pause should not have been called again');
  assert.notOk(this.player.ads._cancelledPlay, 'cancelContentPlay does nothing in content playback');
});

QUnit.test('content is resumed after ads if a user initiated play event is canceled', function(assert) {
  const playSpy = sinon.spy(this.player, 'play');
  const setTimeoutSpy = sinon.spy(window, 'setTimeout');
  const pauseSpy = sinon.spy(this.player, 'pause');

  this.player.paused = () => {
    return false;
  };

  this.player.trigger('play');
  this.player.trigger('adsready');

  assert.strictEqual(setTimeoutSpy.callCount, 1, 'one timer was created (`_prerollTimeout`)');
  assert.ok(timerExists(this, this.player.ads._state._timeout), 'preroll timeout exists');
  assert.ok(this.player.ads._cancelledPlay, true, 'play has been canceled');
  assert.ok(pauseSpy.callCount, 1, 'pause was called');

  this.player.ads.startLinearAdMode();
  this.player.ads.endLinearAdMode();
  assert.strictEqual(playSpy.callCount, 1, 'play should be called by the snapshot restore');

  this.player.trigger('play');
  assert.ok(pauseSpy.callCount, 1, 'pause was not called again');
});

// Set up contrib-ads options and run custom hooks before sharedModuleHooks, as
// videojs must be modified before setting up the player and videojs-contrib-ads
QUnit.module('Cancel Content Play (w/ Stitched Ads)', {
  beforeEach: _.flow(function() {
    this.adsOptions = {
      stitchedAds: true
    };
  }, fakeVideojs, sharedHooks.beforeEach),
  afterEach: _.flow(function() {
    this.adsOptions = null;
  }, restoreVideojs, sharedHooks.afterEach)
});

QUnit.test('does not pause to wait for prerolls when the plugin loads BEFORE play', function(assert) {
  const spy = sinon.spy(this.player, 'pause');

  this.player.paused = function() {
    return false;
  };

  this.player.trigger('adsready');
  this.player.trigger('play');
  this.clock.tick(1);
  this.player.trigger('play');
  this.clock.tick(1);

  assert.strictEqual(spy.callCount, 0, 'play attempts are not paused');
});

QUnit.test('does not pause to wait for prerolls when the plugin loads AFTER play', function(assert) {
  const pauseSpy = sinon.spy(this.player, 'pause');

  this.player.paused = function() {
    return false;
  };

  this.player.trigger('play');
  this.clock.tick(1);
  this.player.trigger('play');
  this.clock.tick(1);
  assert.equal(pauseSpy.callCount, 0, 'play attempts are not paused');
});
