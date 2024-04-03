import QUnit from 'qunit';
import sinon from 'sinon';
import OutstreamDone from '../../../src/states/OutstreamDone.js';

/*
 * These tests are intended to be isolated unit tests for one state with all
 * other modules mocked.
 */
QUnit.module('OutstreamDone', {
  beforeEach() {
    this.events = [];
    this.player = {
      trigger: (event) => {
        this.events.push(event);
      },
      ads: {}
    };

    this.outstreamDone = new OutstreamDone(this.player);
  }
});

QUnit.test('ended event on init', function(assert) {
  this.outstreamDone.init(this.player);
  assert.equal(this.events[0], 'ended', 'content has ended');
});

QUnit.test('does not play more ads', function(assert) {
  this.outstreamDone.transitionTo = sinon.spy();

  this.outstreamDone.init(this.player);
  this.outstreamDone.startLinearAdMode();
  assert.equal(this.outstreamDone.transitionTo.callCount, 0, 'no transition');
});
