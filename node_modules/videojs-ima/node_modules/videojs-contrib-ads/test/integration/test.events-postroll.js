/*
TODO:
* timeupdate, adtimeupdate, contenttimeupdate
* loadstart, adloadstart, contentloadstart
* play, adplay, contentplay
* contentended
* loadeddata, adloadeddata, contentloadeddata
* loadedmetadata, adloadedmetadata, contentloadedmetadata
*/

import videojs from 'video.js';
import '../../examples/basic-ad-plugin/example-plugin.js';
import document from 'global/document';
import QUnit from 'qunit';

QUnit.module('Events and Postrolls', {
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

QUnit.test('ended event and postrolls: 0 before postroll, 1 after', function(assert) {
  const done = assert.async();

  let beforePostroll = true;
  let endedBeforePostroll = 0;
  let endedAfterPostroll = 0;

  this.player.on('adend', () => {
    beforePostroll = false;
  });

  this.player.on('ended', () => {
    if (beforePostroll) {
      endedBeforePostroll++;
    } else {
      endedAfterPostroll++;
    }
  });

  this.player.on(['error', 'aderror'], () => {
    assert.ok(false, 'no errors');
    done();
  });

  this.player.one('ended', () => {
    if (beforePostroll) {
      assert.ok(false, 'ended before postroll!');
    }
    // Run checks after a pause in case there are multiple ended events.
    setTimeout(() => {
      assert.equal(endedBeforePostroll, 0, 'no ended before postroll');
      assert.equal(endedAfterPostroll, 1, 'exactly one ended after postroll');
      done();
    }, 1000);
  });

  this.player.ready(() => {
    this.player.play();
    this.player.currentTime(46);
  });

});

QUnit.test('Event prefixing and postrolls', function(assert) {
  const done = assert.async();

  let beforePostroll = true;
  const seenInAdMode = [];
  const seenInContentResuming = [];
  const seenOutsideAdModeBefore = [];
  const seenOutsideAdModeAfter = [];

  this.player.on('adend', () => {
    beforePostroll = false;
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
    if (e.type === 'contentended') {
      return;
    }
    const str = e.type;

    if (this.player.ads.isInAdMode()) {
      if (this.player.ads.isContentResuming()) {
        seenInContentResuming.push(str);
      } else {
        seenInAdMode.push(str);
      }
    } else if (beforePostroll) {
      seenOutsideAdModeBefore.push(str);
    } else {
      seenOutsideAdModeAfter.push(str);
    }
  });

  this.player.on(['error', 'aderror'], () => {
    assert.ok(false, 'no errors');
    done();
  });

  this.player.on('ended', () => {

    seenOutsideAdModeBefore.forEach((event) => {
      assert.ok(!/^ad/.test(event), event + ' has no ad prefix before postroll');
      assert.ok(!/^content/.test(event), event + ' has no content prefix before postroll');
    });

    seenInAdMode.forEach((event) => {
      assert.ok(/^ad/.test(event), event + ' has ad prefix during postroll');
    });

    seenInContentResuming.forEach((event) => {
      assert.ok(/^content/.test(event), event + ' has content prefix during postroll');
    });

    seenOutsideAdModeAfter.forEach((event) => {
      assert.ok(!/^ad/.test(event), event + ' has no ad prefix after postroll');
      assert.ok(!/^content/.test(event), event + ' has no content prefix after postroll');
    });

    done();
  });

  // Seek to end once we're ready so postroll can play quickly
  this.player.one('playing', () => {
    this.player.currentTime(46);
  });

  this.player.ready(this.player.play);

});
