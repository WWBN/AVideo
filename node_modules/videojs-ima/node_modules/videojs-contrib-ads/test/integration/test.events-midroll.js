/*
TODO:
* timeupdate, adtimeupdate, contenttimeupdate
* loadstart, adloadstart, contentloadstart
* play, adplay, contentplay
* loadeddata, adloadeddata, contentloadeddata
* loadedmetadata, adloadedmetadata, contentloadedmetadata
*/

import videojs from 'video.js';
import '../../examples/basic-ad-plugin/example-plugin.js';
import document from 'global/document';
import QUnit from 'qunit';

QUnit.module('Events and Midrolls', {
  beforeEach() {
    this.video = document.createElement('video');

    this.fixture = document.querySelector('#qunit-fixture');
    this.fixture.appendChild(this.video);

    this.player = videojs(this.video);

    this.player.exampleAds({
      adServerUrl: '/test/integration/lib/inventory.json',
      playPreroll: false,
      midrollPoint: 1
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

QUnit.test('Midrolls', function(assert) {
  const done = assert.async();

  let beforeMidroll = true;
  const seenInAdMode = [];
  const seenInContentResuming = [];
  const seenOutsideAdModeBefore = [];
  const seenOutsideAdModeAfter = [];

  this.player.on('adend', () => {
    beforeMidroll = false;
  });

  let events = [
    'suspend',
    'abort',
    'error',
    'emptied',
    'stalled',
    'canplay',
    'canplaythrough',
    'waiting',
    'seeking',
    'durationchange',
    'progress',
    'pause',
    'ratechange',
    'volumechange',
    'firstplay',
    'suspend',
    'playing',
    'ended'
  ];

  events = events.concat(events.map(function(e) {
    return 'ad' + e;
  }));

  events = events.concat(events.map(function(e) {
    return 'content' + e;
  }));

  this.player.on(events, (e) => {
    const str = e.type;

    if (this.player.ads.isInAdMode()) {
      if (this.player.ads.isContentResuming()) {
        seenInContentResuming.push(str);
      } else {
        seenInAdMode.push(str);
      }
    } else if (beforeMidroll) {
      seenOutsideAdModeBefore.push(str);
    } else {
      seenOutsideAdModeAfter.push(str);
    }
  });

  this.player.on(['error', 'aderror'], () => {
    assert.ok(false, 'no errors');
    done();
  });

  this.player.on('timeupdate', () => {
    videojs.log(this.player.currentTime(), this.player.currentSrc());
    if (this.player.currentTime() > 1.1) {

      seenOutsideAdModeBefore.forEach((event) => {
        assert.ok(!/^ad/.test(event), event + ' has no ad prefix before midroll');
        assert.ok(!/^content/.test(event), event + ' has no content prefix before midroll');
      });

      seenInAdMode.forEach((event) => {
        assert.ok(/^ad/.test(event), event + ' has ad prefix during midroll');
      });

      seenInContentResuming.forEach((event) => {
        assert.ok(/^content/.test(event), event + ' has content prefix during midroll');
      });

      seenOutsideAdModeAfter.forEach((event) => {
        assert.ok(!/^ad/.test(event), event + ' has no ad prefix after midroll');
        assert.ok(!/^content/.test(event), event + ' has no content prefix after midroll');
      });

      done();
    }
  });

  // Seek to right before the midroll
  this.player.one('playing', () => {
    this.player.currentTime(0.9);
  });

  this.player.ready(this.player.play);

});
