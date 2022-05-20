import videojs from 'video.js';
import '../../examples/basic-ad-plugin/example-plugin.js';
import QUnit from 'qunit';
import document from 'global/document';
import window from 'global/window';
import sinon from 'sinon';

QUnit.module('Integration: play middleware', {
  beforeEach() {
    this.video = document.createElement('video');

    this.fixture = document.querySelector('#qunit-fixture');
    this.fixture.appendChild(this.video);

    this.player = videojs(this.video);

    this.player.exampleAds({
      adServerUrl: '/test/integration/lib/inventory.json'
    });

    // Mute the player to allow playback without user interaction
    this.player.muted(true);
  },

  afterEach() {
    this.player.dispose();
  }
});

QUnit.test('the `_playRequested` flag is set on the first play request', function(assert) {
  const done = assert.async();
  let finish;

  this.player.src({
    src: 'http://vjs.zencdn.net/v/oceans.webm',
    type: 'video/webm'
  });

  const onAdstart = () => {
    assert.strictEqual(
      this.player.ads._playRequested, true,
      '_playRequested is true when the play method is used'
    );
    finish();
  };

  const onTimeupdate = () => {
    if (this.player.currentTime() > 0) {
      assert.strictEqual(
        this.player.ads._playRequested, true,
        '_playRequested is true when the play method is used'
      );
      finish();
    }
  };

  finish = () => {
    this.player.off('adstart', onAdstart);
    this.player.off('timeupdate', onTimeupdate);
    done();
  };

  // When the preroll starts
  this.player.on('adstart', onAdstart);

  // If there wasn't an ad
  this.player.on('timeupdate', onTimeupdate);

  this.player.ready(this.player.play);
});

QUnit.test('blocks calls to play to wait for prerolls if adsready BEFORE play', function(assert) {
  const done = assert.async();
  const techPlaySpy = sinon.spy(this.video, 'play');
  const playEventSpy = sinon.spy();
  let seenAdsReady = false;
  let finish;

  this.player.on('play', playEventSpy);
  this.player.on('adsready', () => {
    seenAdsReady = true;
  });

  const onTimeupdate = () => {
    if (this.player.currentTime() > 0) {
      assert.strictEqual(
        techPlaySpy.callCount, 0,
        "tech play shouldn't be called while waiting for prerolls"
      );
      assert.strictEqual(
        playEventSpy.callCount, 1,
        'play event should be triggered'
      );
      finish();
    }
  };

  const onAdstart = () => {
    // sometimes play will happen just after adstart
    window.setTimeout(() => {
      assert.strictEqual(
        techPlaySpy.callCount, 0,
        "tech play shouldn't be called while waiting for prerolls"
      );
      assert.strictEqual(
        playEventSpy.callCount, 1,
        'play event should be triggered'
      );
      finish();
    }, 1);
  };

  const onError = function() {
    assert.ok(false, 'no errors, or adtimeout');
    finish();
  };

  finish = () => {
    this.player.off('adstart', onAdstart);
    this.player.off('timeupdate', onTimeupdate);
    this.player.off(['error', 'aderror', 'adtimeout'], onError);
    done();
  };

  // When the preroll starts
  this.player.on('adstart', onAdstart);

  // Once we are in content
  this.player.on('timeupdate', onTimeupdate);
  this.player.on(['error', 'aderror', 'adtimeout'], onError);

  this.player.src({
    src: 'http://vjs.zencdn.net/v/oceans.webm',
    type: 'video/webm'
  });

  this.player.ready(() => {
    if (seenAdsReady) {
      this.player.play();

    } else {
      this.player.on('adsready', this.player.play);
    }
  });
});

QUnit.test('stops blocking play when an ad is playing', function(assert) {
  const done = assert.async();

  this.player.on('adstart', () => {
    assert.strictEqual(this.player.ads._shouldBlockPlay, true);
  });

  // Wait for the ad to start playing
  this.player.on('ads-ad-started', () => {
    assert.strictEqual(
      this.player.ads._shouldBlockPlay, false,
      'should stop blocking once in an adbreak'
    );
    done();
  });

  this.player.src({
    src: 'http://vjs.zencdn.net/v/oceans.webm',
    type: 'video/webm'
  });

  this.player.ready(this.player.play);
});

QUnit.test("playMiddleware doesn\'t block play in content playback", function(assert) {
  const done = assert.async();

  this.player.on('adstart', () => {
    assert.strictEqual(this.player.ads._shouldBlockPlay, true);
  });

  const onTimeupdate = () => {
    if (this.player.currentTime() > 0) {
      assert.strictEqual(
        this.player.ads._shouldBlockPlay, false,
        'should stop blocking in content'
      );
      this.player.off('timeupdate', onTimeupdate);
      done();
    }
  };

  // Wait for the ad to start playing
  this.player.on('timeupdate', onTimeupdate);

  this.player.src({
    src: 'http://vjs.zencdn.net/v/oceans.webm',
    type: 'video/webm'
  });

  this.player.ready(this.player.play);
});

QUnit.test("don't trigger play event if another middleware terminates", function(assert) {
  const done = assert.async();
  const fixture = document.querySelector('#qunit-fixture');
  const vid = document.createElement('video');
  const playSpy = sinon.spy();

  let shouldTerminate = true;
  const anotherMiddleware = function(player) {
    return {
      setSource(srcObj, next) {
        next(null, srcObj);
      },
      callPlay() {
        if (shouldTerminate === true) {
          shouldTerminate = false;
          return videojs.middleware.TERMINATOR;
        }
      },
      play(terminated, value) {
      }
    };
  };

  // Register the other middleware
  videojs.use('video/webm', anotherMiddleware);

  // Don't use the shared player, setup new localPlayer
  fixture.appendChild(vid);
  const localPlayer = videojs(vid);

  // Setup before example integration
  localPlayer.on('nopreroll', () => {
    localPlayer.ready(localPlayer.play);
  });

  // Don't play preroll ads
  localPlayer.exampleAds({
    adServerUrl: '/test/integration/lib/inventory.json',
    playPreroll: false
  });

  localPlayer.on('play', playSpy);

  // Wait for the middleware to run
  localPlayer.setTimeout(() => {
    assert.strictEqual(
      localPlayer.ads._playBlocked, false,
      'play should not have been blocked'
    );
    assert.strictEqual(
      playSpy.callCount, 0,
      'play event should not be triggered'
    );
    done();
  }, 1);

  localPlayer.src({
    src: 'http://vjs.zencdn.net/v/oceans.webm',
    type: 'video/webm'
  });
});
