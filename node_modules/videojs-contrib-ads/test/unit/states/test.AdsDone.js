import QUnit from 'qunit';
import sinon from 'sinon';
import AdsDone from '../../../src/states/AdsDone.js';

/*
 * These tests are intended to be isolated unit tests for one state with all
 * other modules mocked.
 */
QUnit.module('AdsDone', {
  beforeEach() {
    this.events = [];
    this.player = {
      trigger: (event) => {
        this.events.push(event);
      },
      ads: {}
    };

    this.adsDone = new AdsDone(this.player);
  }
});

QUnit.test('sets _contentHasEnded on init', function(assert) {
  this.adsDone.init(this.player);
  assert.equal(this.player.ads._contentHasEnded, true, 'content has ended');
});

QUnit.test('ended event on init', function(assert) {
  this.adsDone.init(this.player);
  assert.equal(this.events[0], 'ended', 'content has ended');
});

QUnit.test('does not play midrolls', function(assert) {
  this.adsDone.transitionTo = sinon.spy();

  this.adsDone.init(this.player);
  this.adsDone.startLinearAdMode();
  assert.equal(this.adsDone.transitionTo.callCount, 0, 'no transition');
});
