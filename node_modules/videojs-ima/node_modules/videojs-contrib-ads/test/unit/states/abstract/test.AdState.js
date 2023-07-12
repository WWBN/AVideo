import QUnit from 'qunit';
import AdState from '../../../../src/states/abstract/AdState.js';

/*
 * These tests are intended to be isolated unit tests for one state with all
 * other modules mocked.
 */
QUnit.module('AdState', {
  beforeEach() {
    this.player = {
      ads: {}
    };

    this.adState = new AdState(this.player);
    this.adState.transitionTo = (newState) => {
      this.newState = newState.name;
    };
  }
});

QUnit.test('does not start out with content resuming', function(assert) {
  assert.equal(this.adState.contentResuming, false);
});

QUnit.test('is an ad state', function(assert) {
  assert.equal(this.adState.isAdState(), true);
});

QUnit.test('transitions to ContentPlayback on playing if content resuming', function(assert) {
  this.adState.contentResuming = true;
  this.adState.onPlaying();
  assert.equal(this.newState, 'ContentPlayback');
});

QUnit.test('doesn\'t transition on playing if content not resuming', function(assert) {
  this.adState.onPlaying();
  assert.equal(this.newState, undefined, 'no transition');
});

QUnit.test('transitions to ContentPlayback on contentresumed if content resuming', function(assert) {
  this.adState.contentResuming = true;
  this.adState.onContentResumed();
  assert.equal(this.newState, 'ContentPlayback');
});

QUnit.test('doesn\'t transition on contentresumed if content not resuming', function(assert) {
  this.adState.onContentResumed();
  assert.equal(this.newState, undefined, 'no transition');
});

QUnit.test('can check if content is resuming', function(assert) {
  assert.equal(this.adState.isContentResuming(), false, 'not resuming');
  this.adState.contentResuming = true;
  assert.equal(this.adState.isContentResuming(), true, 'resuming');
});

QUnit.test('can check if in ad break', function(assert) {
  assert.equal(this.adState.inAdBreak(), false, 'not in ad break');
  this.player.ads._inLinearAdMode = true;
  assert.equal(this.adState.inAdBreak(), true, 'in ad break');
});
