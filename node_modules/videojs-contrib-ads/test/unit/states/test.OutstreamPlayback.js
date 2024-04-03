import QUnit from 'qunit';
import sinon from 'sinon';
import OutstreamPlayback from '../../../src/states/OutstreamPlayback.js';
import adBreak from '../../../src/adBreak.js';

/*
 * These tests are intended to be isolated unit tests for one state with all
 * other modules mocked.
 */
QUnit.module('OutstreamPlayback', {
  beforeEach() {
    this.events = [];
    this.playTriggered = false;
    this.classes = [];

    this.player = {
      ads: {
        debug: () => {},
        settings: {},
        inAdBreak: () => false,
        isContentResuming: () => false,
        _shouldBlockPlay: true
      },
      setTimeout: () => {},
      clearTimeout: () => {},
      addClass: (name) => this.classes.push(name),
      removeClass: (name) => this.classes.splice(this.classes.indexOf(name), 1),
      hasClass: (name) => this.classes.indexOf(name) !== -1,
      one: () => {},
      trigger: (event) => {
        this.events.push(event);
      },
      paused: () => {},
      play: () => {
        this.playTriggered = true;
      }
    };
    this.outstreamPlayback = new OutstreamPlayback(this.player);
    this.outstreamPlayback.transitionTo = (newState) => {
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

QUnit.test('transitions to OutstreamDone on ad end', function(assert) {
  this.outstreamPlayback.init(this.player);
  this.outstreamPlayback.endLinearAdMode();
  assert.equal(this.newState, 'OutstreamDone');
});

QUnit.test('transition to OutstreamDone on ad error', function(assert) {
  this.outstreamPlayback.init(this.player);
  this.outstreamPlayback.onAdsError(this.player);
  assert.equal(this.newState, 'OutstreamDone');
});

QUnit.test('transition to OutstreamDone on ad timeout', function(assert) {
  this.outstreamPlayback.init(this.player);
  this.outstreamPlayback.onAdTimeout(this.player);
  assert.equal(this.newState, 'OutstreamDone');
});

QUnit.test('transition to OutstreamDone on ad cancelled', function(assert) {
  this.outstreamPlayback.init(this.player);
  this.outstreamPlayback.onAdsCanceled(this.player);
  assert.equal(this.newState, 'OutstreamDone');
});

QUnit.test('transition to OutstreamDone on ad skipped', function(assert) {
  this.outstreamPlayback.init(this.player);
  this.outstreamPlayback.skipLinearAdMode();
  assert.equal(this.newState, 'OutstreamDone');
});
