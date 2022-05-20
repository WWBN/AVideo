import QUnit from 'qunit';
import sinon from 'sinon';
import ContentState from '../../../../src/states/abstract/ContentState.js';

/*
 * These tests are intended to be isolated unit tests for one state with all
 * other modules mocked.
 */
QUnit.module('ContentState', {
  beforeEach() {
    this.player = {
      ads: {
        debug: () => {}
      }
    };

    this.contentState = new ContentState(this.player);
    this.contentState.transitionTo = (newState) => {
      this.newState = newState.name;
    };
  }
});

QUnit.test('is not an ad state', function(assert) {
  assert.equal(this.contentState.isAdState(), false);
});

QUnit.test('handles content changed when not playing', function(assert) {
  this.player.paused = () => true;
  this.player.pause = sinon.stub();

  this.contentState.onContentChanged(this.player);
  assert.equal(this.newState, 'BeforePreroll');
  assert.equal(this.player.pause.callCount, 0, 'did not pause player');
  assert.ok(!this.player.ads._pausedOnContentupdate, 'did not set _pausedOnContentupdate');
});

QUnit.test('handles content changed when playing', function(assert) {
  this.player.paused = () => false;
  this.player.pause = sinon.stub();

  this.contentState.onContentChanged(this.player);
  assert.equal(this.newState, 'Preroll');
  assert.equal(this.player.pause.callCount, 1, 'paused player');
  assert.equal(this.player.ads._pausedOnContentupdate, true, 'set _pausedOnContentupdate');
});
