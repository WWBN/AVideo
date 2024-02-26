import document from 'global/document';

import QUnit from 'qunit';
import sinon from 'sinon';
import videojs from 'video.js';

import plugin from '../src/plugin';

const Player = videojs.getComponent('Player');

QUnit.test('the environment is sane', function(assert) {
  assert.strictEqual(typeof Array.isArray, 'function', 'es5 exists');
  assert.strictEqual(typeof sinon, 'object', 'sinon exists');
  assert.strictEqual(typeof videojs, 'function', 'videojs exists');
  assert.strictEqual(typeof plugin, 'function', 'plugin is a function');
});

QUnit.module('videojs-seek-buttons', {

  beforeEach() {

    // Mock the environment's timers because certain things - particularly
    // player readiness - are asynchronous in video.js 5. This MUST come
    // before any player is created; otherwise, timers could get created
    // with the actual timer methods!
    this.clock = sinon.useFakeTimers();

    this.fixture = document.getElementById('qunit-fixture');
    this.video = document.createElement('video');
    this.fixture.appendChild(this.video);
    this.player = videojs(this.video);
  },

  afterEach() {
    this.player.dispose();
    this.clock.restore();
  }
});

QUnit.test('registers itself with video.js', function(assert) {
  assert.expect(2);

  assert.strictEqual(
    typeof Player.prototype.seekButtons,
    'function',
    'videojs-seek-buttons plugin was registered'
  );

  this.player.seekButtons();

  // Tick the clock forward enough to trigger the player to be "ready".
  this.clock.tick(1);

  assert.ok(
    this.player.hasClass('vjs-seek-buttons'),
    'the plugin adds a class to the player'
  );
});

QUnit.test('adds buttons with classes', function(assert) {
  this.player.seekButtons({
    forward: 30,
    back: 10
  });

  // Tick the clock forward enough to trigger the player to be "ready".
  this.clock.tick(1);

  assert.ok(
    this.player.controlBar.seekBack,
    'the plugin adds a back button to the player'
  );

  assert.ok(
    this.player.controlBar.seekForward,
    'the plugin adds a forward button to the player'
  );

  assert.ok(
    this.player.controlBar.seekBack.hasClass('skip-10'),
    'the plugin adds a seconds class to the button'
  );

  assert.ok(
    this.player.controlBar.seekBack.hasClass('skip-back'),
    'the plugin adds a direction class to the button'
  );
});

QUnit.test('calls currentTime with the correct time', function(assert) {
  this.player.duration(100);

  this.player.seekButtons({
    forward: 30,
    back: 10
  });

  // Tick the clock forward enough to trigger the player to be "ready".
  this.clock.tick(1);

  const time = this.player.currentTime();

  const spy = sinon.spy(this.player, 'currentTime');

  this.player.controlBar.seekForward.trigger('click');

  assert.ok(
    spy.withArgs(time + 30).calledOnce,
    'forward button triggers seek 30 seconds'
  );

  // Fake that the seek happened - it won't have as the test player has no source.
  this.player.tech_.currentTime = () => 30;

  this.player.controlBar.seekBack.trigger('click');

  assert.ok(
    spy.withArgs(20).calledOnce,
    'back button triggers seek back 10 seconds'
  );

});
