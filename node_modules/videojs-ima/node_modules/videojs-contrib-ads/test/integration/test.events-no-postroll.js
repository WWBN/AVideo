import videojs from 'video.js';
import '../../examples/basic-ad-plugin/example-plugin.js';
import QUnit from 'qunit';
import document from 'global/document';

QUnit.module('Final Events With No Postroll', {
  beforeEach() {
    this.video = document.createElement('video');

    this.fixture = document.querySelector('#qunit-fixture');
    this.fixture.appendChild(this.video);

    this.player = videojs(this.video);

    this.player.exampleAds({
      adServerUrl: '/test/integration/lib/inventory.json',
      playPreroll: false,
      playMidroll: false,
      playPostroll: false
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

QUnit.test('final ended event with no postroll: just 1', function(assert) {
  const done = assert.async();
  let endedEvents = 0;

  // Prevent the test from timing out by making it run faster
  this.player.ads.settings.postrollTimeout = 1;

  this.player.on('ended', () => {
    endedEvents++;
  });

  this.player.on(['error', 'aderror'], () => {
    assert.ok(false, 'no errors');
    done();
  });

  this.player.one('ended', () => {
    // Run checks after a pause in case there are multiple ended events.
    setTimeout(() => {
      assert.equal(endedEvents, 1, 'exactly one ended with no postroll');
      done();
    }, 1000);
  });

  // Seek to end once we're ready so postroll can play quickly
  this.player.one('playing', () => {
    this.player.currentTime(46);
  });

  this.player.ready(this.player.play);

});
