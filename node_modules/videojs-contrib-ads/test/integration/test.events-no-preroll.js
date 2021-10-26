import videojs from 'video.js';
import '../../examples/basic-ad-plugin/example-plugin.js';
import QUnit from 'qunit';
import document from 'global/document';

QUnit.module('Initial Events With No Preroll', {
  beforeEach() {
    this.video = document.createElement('video');

    this.fixture = document.querySelector('#qunit-fixture');
    this.fixture.appendChild(this.video);

    this.player = videojs(this.video);

    this.player.exampleAds({
      adServerUrl: '/test/integration/lib/inventory.json',
      playPreroll: false,
      playMidroll: false
    });

    this.player.src({
      src: 'http://vjs.zencdn.net/v/oceans.webm',
      type: 'video/webm'
    });

    // Mute the player to allow playback without user interaction
    this.player.muted(true);
  },

  afterEach() {
    this.player.dispose();
  }
});

QUnit.test('initial play event with no preroll: one please', function(assert) {
  const done = assert.async();

  let playEvents = 0;

  this.player.on('play', () => {
    playEvents++;
  });

  this.player.on(['error', 'aderror'], () => {
    assert.ok(false, 'no errors');
    done();
  });

  this.player.on('timeupdate', () => {
    if (this.player.currentTime() > 1) {
      assert.equal(playEvents, 1, '1 play event');
      done();
    }
  });

  this.player.ready(this.player.play);

});

QUnit.test('initial playing event with no preroll: 1+', function(assert) {
  const done = assert.async();

  let playingEvents = 0;

  this.player.on('playing', () => {
    playingEvents++;
  });

  this.player.on(['error', 'aderror'], () => {
    assert.ok(false, 'no errors');
    done();
  });

  this.player.on('timeupdate', () => {
    if (this.player.currentTime() > 1) {
      assert.ok(playingEvents >= 1, '1+ playing events');
      done();
    }
  });

  this.player.ready(this.player.play);

});

QUnit.test('no ended event at start if video with no preroll', function(assert) {
  const done = assert.async();

  let endedEvents = 0;

  this.player.on('ended', () => {
    endedEvents++;
  });

  this.player.on(['error', 'aderror'], () => {
    assert.ok(false, 'no errors');
    done();
  });

  this.player.on('timeupdate', () => {
    if (this.player.currentTime() > 1) {
      assert.equal(endedEvents, 0, 'no ended events');
      done();
    }
  });

  this.player.ready(this.player.play);

});

QUnit.test('initial loadstart event with no preroll: one please', function(assert) {
  const done = assert.async();

  let loadstartEvents = 0;

  this.player.on('loadstart', () => {
    loadstartEvents++;
  });

  this.player.on(['error', 'aderror'], () => {
    assert.ok(false, 'no errors');
    done();
  });

  this.player.on('timeupdate', () => {
    if (this.player.currentTime() > 1) {
      assert.equal(loadstartEvents, 1, '1 loadstart event');
      done();
    }
  });

  this.player.ready(this.player.play);

});
