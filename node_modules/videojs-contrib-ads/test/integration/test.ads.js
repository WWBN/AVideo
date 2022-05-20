import QUnit from 'qunit';
import sinon from 'sinon';
import videojs from 'video.js';
import sharedModuleHooks from './lib/shared-module-hooks.js';

QUnit.module('Contrib Ads', sharedModuleHooks());

QUnit.test('begins in BeforePreroll', function(assert) {
  assert.equal(this.player.ads._state.constructor.name, 'BeforePreroll');
});

QUnit.test('adstart is fired before a preroll', function(assert) {
  const spy = sinon.spy();

  assert.expect(1);

  this.player.on('adstart', spy);
  this.player.trigger('adsready');
  this.player.trigger('play');
  this.player.ads.startLinearAdMode();
  assert.strictEqual(spy.callCount, 1, 'a preroll triggers adstart');
});

QUnit.test('player has the .vjs-has-started class once a preroll begins', function(assert) {
  assert.expect(1);
  this.player.trigger('adsready');

  // This is a bit of a hack in order to not need the test to be async.
  this.player.tech_.trigger('play');
  this.player.ads.startLinearAdMode();
  assert.ok(this.player.hasClass('vjs-has-started'), 'player has .vjs-has-started class');
});

QUnit.test('calls start immediately on play when ads are ready', function(assert) {
  const readyForPrerollSpy = sinon.spy();

  assert.expect(1);

  this.player.on('readyforpreroll', readyForPrerollSpy);
  this.player.trigger('adsready');
  this.player.trigger('loadstart');
  this.player.trigger('play');
  assert.strictEqual(readyForPrerollSpy.callCount, 1, 'readyforpreroll was fired');
});

QUnit.test('adds the ad-mode class when a preroll plays', function(assert) {
  assert.expect(1);

  this.player.trigger('adsready');
  this.player.trigger('play');
  this.player.ads.startLinearAdMode();
  const el = this.player.el();

  assert.ok(this.player.hasClass('vjs-ad-playing'), 'the ad class should be in "' + el.className + '"');
});

QUnit.test('removes the ad-mode class when a preroll finishes', function(assert) {

  this.player.trigger('adsready');
  this.player.trigger('play');
  this.player.ads.startLinearAdMode();
  this.player.ads.endLinearAdMode();
  const el = this.player.el();

  assert.notOk(this.player.hasClass('vjs-ad-playing'), 'the ad class should not be in "' + el.className + '"');

  this.player.trigger('playing');
});

QUnit.test('adds a class while waiting for an ad plugin to load', function(assert) {
  assert.expect(1);

  this.player.trigger('play');
  const el = this.player.el();

  assert.ok(this.player.hasClass('vjs-ad-loading'), 'the ad loading class should be in "' + el.className + '"');
});

QUnit.test('adds a class while waiting for a preroll', function(assert) {
  assert.expect(1);

  this.player.trigger('adsready');
  this.player.trigger('play');
  const el = this.player.el();

  assert.ok(this.player.hasClass('vjs-ad-loading'), 'the ad loading class should be in "' + el.className + '"');
});

QUnit.test('removes the loading class when the preroll begins', function(assert) {
  assert.expect(1);

  this.player.trigger('adsready');
  this.player.trigger('play');
  this.player.ads.startLinearAdMode();
  this.player.trigger('ads-ad-started');
  const el = this.player.el();

  assert.notOk(this.player.hasClass('vjs-ad-loading'), 'there should be no ad loading class present in "' + el.className + '"');
});

QUnit.test('removes the loading class when the preroll times out', function(assert) {
  this.player.trigger('loadstart');
  this.player.trigger('adsready');
  this.player.trigger('play');
  this.player.trigger('adtimeout');
  this.player.trigger('playing');
  const el = this.player.el();

  assert.notOk(this.player.hasClass('vjs-ad-loading'), 'there should be no ad loading class present in "' + el.className + '"');
});

QUnit.test('begins resuming to content if there is no preroll', function(assert) {
  this.player.trigger('loadstart');
  this.player.trigger('adsready');
  this.player.trigger('play');
  this.clock.tick(1);
  this.player.trigger('adtimeout');

  assert.strictEqual(this.player.ads.inAdBreak(), false, 'should not be in an ad break');
  assert.strictEqual(this.player.ads.isContentResuming(), true, 'should be resuming content');
  assert.strictEqual(this.player.ads.isInAdMode(), true, 'should be in ad mode');
});

QUnit.test('removes the poster attribute so it does not flash between videos', function(assert) {
  this.video.poster = 'http://www.videojs.com/img/poster.jpg';
  assert.ok(this.video.poster, 'the poster is present initially');

  this.player.trigger('adsready');
  this.player.trigger('play');
  this.player.ads.startLinearAdMode();
  assert.strictEqual(this.video.poster, '', 'poster is removed');
});

QUnit.test('changing the src triggers "contentupdate"', function(assert) {
  const spy = sinon.spy();

  assert.expect(1);

  this.player.on('contentupdate', spy);

  // set src and trigger synthetic 'loadstart'
  this.player.currentSrc = () => 'AH HA!!! I AM NOT A REAL SOURCE';
  this.player.trigger('loadstart');
  assert.strictEqual(spy.callCount, 1, 'one contentupdate event fired');
});

QUnit.test('"contentupdate" should fire when src is changed after postroll', function(assert) {
  const contentupdateSpy = sinon.spy();

  this.player.on('contentupdate', contentupdateSpy);

  this.player.trigger('adsready');
  this.player.trigger('play');
  this.player.trigger('adtimeout');
  this.player.trigger('ended');
  this.player.trigger('adtimeout');

  // set src and trigger synthetic 'loadstart'
  this.player.src({
    src: 'http://vjs.zencdn.net/v/oceans.webm',
    type: 'video/webm'
  });
  this.player.trigger('loadstart');
  assert.strictEqual(contentupdateSpy.callCount, 1, 'one contentupdate event fired');
});

QUnit.test('"contentupdate" should fire when src is changed after postroll', function(assert) {
  const contentupdateSpy = sinon.spy();

  this.player.on('contentupdate', contentupdateSpy);
  this.player.trigger('adsready');
  this.player.trigger('play');
  this.player.trigger('adtimeout');
  this.player.trigger('ended');
  this.player.trigger('adtimeout');
  this.player.trigger('ended');

  // set src and trigger synthetic 'loadstart'
  this.player.src({
    src: 'http://vjs.zencdn.net/v/oceans.webm',
    type: 'video/webm'
  });
  this.player.trigger('loadstart');
  assert.strictEqual(contentupdateSpy.callCount, 1, 'one contentupdate event fired');
});

QUnit.test('changing src does not trigger "contentupdate" during ad playback', function(assert) {
  const spy = sinon.spy();

  this.player.on('contentupdate', spy);

  // enter ad playback mode
  this.player.trigger('adsready');
  this.player.trigger('play');
  this.player.ads.startLinearAdMode();

  // set src and trigger synthetic 'loadstart'
  this.player.src({
    src: 'http://vjs.zencdn.net/v/oceans.webm',
    type: 'video/webm'
  });
  this.player.trigger('loadstart');

  // finish playing ad
  this.player.ads.endLinearAdMode();
  assert.strictEqual(spy.callCount, 0, 'no contentupdate events fired');
});

QUnit.test('the `_playRequested` flag is set on the first play request', function(assert) {
  this.player.trigger('loadstart');
  this.player.trigger('adsready');
  assert.strictEqual(
    this.player.ads._playRequested, false,
    'initially set to false'
  );
  assert.strictEqual(
    this.player.ads.isInAdMode(), false,
    'starts in a content state'
  );

  this.player.trigger('play');
  assert.strictEqual(
    this.player.ads._playRequested, true,
    '_playRequested is now true'
  );
  assert.strictEqual(
    this.player.ads.isInAdMode(), true,
    'now in ad state'
  );
});

QUnit.test('ended event happens after postroll errors out', function(assert) {
  const endedSpy = sinon.spy();

  this.player.on('ended', endedSpy);

  this.player.trigger('loadstart');
  this.player.trigger('adsready');
  this.player.trigger('play');
  this.player.trigger('adtimeout');
  // Entered content
  this.player.trigger('playing');
  this.player.trigger('ended');
  this.player.trigger('adserror');

  this.clock.tick(1);
  assert.strictEqual(endedSpy.callCount, 1, 'ended event happened');
});

QUnit.test('ended event happens after postroll timed out', function(assert) {
  const endedSpy = sinon.spy();

  this.player.on('ended', endedSpy);

  this.player.trigger('loadstart');
  this.player.trigger('adsready');
  this.player.trigger('play');
  this.player.trigger('adtimeout');
  // Entered content
  this.player.trigger('playing');
  this.player.trigger('ended');
  this.player.trigger('adtimeout');

  this.clock.tick(1);
  assert.strictEqual(endedSpy.callCount, 1, 'ended event happened');
});

QUnit.test('ended event happens after postroll skipped', function(assert) {
  const endedSpy = sinon.spy();

  this.player.on('ended', endedSpy);

  this.player.trigger('loadstart');
  this.player.trigger('adsready');
  this.player.trigger('play');
  // preroll times out
  this.player.trigger('adtimeout');
  // Entered content
  this.player.trigger('playing');
  // content ends
  this.player.trigger('ended');
  this.player.ads.skipLinearAdMode();

  this.clock.tick(1);
  assert.strictEqual(endedSpy.callCount, 1, 'ended event happened');
});

QUnit.test('an "ended" event is fired after postroll if not fired naturally', function(assert) {
  const endedSpy = sinon.spy();

  this.player.on('ended', endedSpy);

  this.player.trigger('loadstart');
  this.player.trigger('adsready');
  this.player.trigger('play');
  // skip preroll
  this.player.trigger('adtimeout');
  // return to content
  this.player.trigger('playing');
  // handled by redispatch
  this.player.trigger('ended');

  assert.strictEqual(endedSpy.callCount, 0, 'no ended event before postroll');

  // start postroll
  this.player.ads.startLinearAdMode();
  this.player.ads.endLinearAdMode();
  this.player.trigger('resumeended');
  assert.strictEqual(endedSpy.callCount, 1, 'ended event happened');
});

QUnit.test('ended events when content ends first and second time', function(assert) {
  const endedSpy = sinon.spy();

  this.player.on('ended', endedSpy);

  this.player.trigger('loadstart');
  this.player.trigger('adsready');
  this.player.trigger('play');
  // Preroll times out
  this.player.trigger('adtimeout');
  // Entered content
  this.player.trigger('playing');
  // Triggers readyforpostroll
  this.player.trigger('ended');

  // Postroll starts
  this.player.ads.startLinearAdMode();
  this.player.ads.endLinearAdMode();
  this.player.trigger('resumeended');

  assert.strictEqual(endedSpy.callCount, 1, 'ended event after postroll');

  this.player.trigger('ended');
  assert.strictEqual(endedSpy.callCount, 2, 'ended event after ads done');
});

QUnit.test('endLinearAdMode during ad break triggers adend', function(assert) {
  const adendSpy = sinon.spy();

  this.player.on('adend', adendSpy);

  this.player.trigger('loadstart');
  this.player.trigger('adsready');
  this.player.trigger('play');
  this.player.ads.startLinearAdMode();

  assert.strictEqual(adendSpy.callCount, 0, 'no adend yet');

  this.player.ads.endLinearAdMode();

  assert.strictEqual(adendSpy.callCount, 1, 'adend happened');
});

QUnit.test('calling startLinearAdMode() when already in ad-playback does not trigger adstart', function(assert) {
  const spy = sinon.spy();

  this.player.on('adstart', spy);
  this.player.trigger('adsready');
  this.player.trigger('play');
  this.player.ads.startLinearAdMode();
  assert.strictEqual(spy.callCount, 1, 'adstart should have fired');

  // add an extraneous start call
  this.player.ads.startLinearAdMode();
  assert.strictEqual(spy.callCount, 1, 'adstart should not have fired');

  // make sure subsequent adstarts trigger again on exit/re-enter
  this.player.ads.endLinearAdMode();
  this.player.trigger('playing');

  this.player.ads.startLinearAdMode();
  assert.strictEqual(spy.callCount, 2, 'adstart should have fired');
});

QUnit.test('calling endLinearAdMode() outside of linear ad mode does not trigger adend', function(assert) {
  const adendSpy = sinon.spy();

  this.player.on('adend', adendSpy);

  this.player.ads.endLinearAdMode();
  assert.strictEqual(adendSpy.callCount, 0, 'adend should not have fired right away');

  this.player.trigger('adsready');

  this.player.ads.endLinearAdMode();
  assert.strictEqual(adendSpy.callCount, 0, 'adend should not have fired after adsready');

  this.player.trigger('play');

  this.player.ads.endLinearAdMode();
  assert.strictEqual(adendSpy.callCount, 0, 'adend should not have fired after play');

  this.player.trigger('adtimeout');

  this.player.ads.endLinearAdMode();
  assert.strictEqual(adendSpy.callCount, 0, 'adend should not have fired after adtimeout');

  this.player.ads.startLinearAdMode();

  this.player.ads.endLinearAdMode();
  assert.strictEqual(adendSpy.callCount, 1, 'adend should have fired after preroll');
});

QUnit.test('skipLinearAdMode during ad playback does not trigger adskip', function(assert) {
  const adskipSpy = sinon.spy();

  this.player.on('adskip', adskipSpy);

  this.player.trigger('adsready');
  this.player.trigger('play');
  this.player.ads.startLinearAdMode();

  this.player.ads.skipLinearAdMode();
  assert.strictEqual(
    adskipSpy.callCount, 0,
    'adskip event should not trigger when skipLinearAdMode is called during an ad'
  );
});

QUnit.test('adsready in content-playback triggers readyforpreroll', function(assert) {
  const spy = sinon.spy();

  this.player.on('readyforpreroll', spy);
  this.player.trigger('loadstart');
  this.player.trigger('play');
  this.player.trigger('adtimeout');
  // entered ContentPlayback
  this.player.trigger('playing');
  this.player.trigger('adsready');
  assert.strictEqual(spy.callCount, 1, 'readyforpreroll should have been triggered.');
});

QUnit.test('adsready while preroll content resuming triggers readyforpreroll', function(assert) {
  const spy = sinon.spy();

  this.player.on('readyforpreroll', spy);
  this.player.trigger('loadstart');
  this.player.trigger('play');
  this.player.trigger('adtimeout');
  assert.strictEqual(this.player.ads.isContentResuming(), true, 'should be resuming to content');
  this.player.trigger('adsready');
  assert.strictEqual(spy.callCount, 1, 'readyforpreroll should have been triggered.');
});

// ----------------------------------
// Event prefixing during ad playback
// ----------------------------------

QUnit.test('player events during prerolls are prefixed if tech is reused for ad', function(assert) {
  const sawLoadstart = sinon.spy();
  const sawPlaying = sinon.spy();
  const sawPause = sinon.spy();
  const sawEnded = sinon.spy();
  const sawFirstplay = sinon.spy();
  const sawLoadedalldata = sinon.spy();
  const sawAdloadstart = sinon.spy();
  const sawAdpause = sinon.spy();
  const sawAdended = sinon.spy();
  const sawAdfirstplay = sinon.spy();
  const sawAdloadedalldata = sinon.spy();

  // play a preroll
  this.player.on('readyforpreroll', function() {
    this.ads.startLinearAdMode();
  });

  this.player.trigger('play');
  this.player.trigger('loadstart');
  this.player.trigger('adsready');

  this.player.ads.snapshot = {
    currentSrc: 'something'
  };

  // simulate video events that should be prefixed
  this.player.on('loadstart', sawLoadstart);
  this.player.on('playing', sawPlaying);
  this.player.on('pause', sawPause);
  this.player.on('ended', sawEnded);
  this.player.on('firstplay', sawFirstplay);
  this.player.on('loadedalldata', sawLoadedalldata);
  this.player.on('adloadstart', sawAdloadstart);
  this.player.on('adpause', sawAdpause);
  this.player.on('adended', sawAdended);
  this.player.on('adfirstplay', sawAdfirstplay);
  this.player.on('adloadedalldata', sawAdloadedalldata);
  this.player.trigger('firstplay');
  this.player.trigger('loadstart');
  this.player.trigger('playing');
  this.player.trigger('loadedalldata');
  this.player.trigger('pause');
  this.player.trigger('ended');
  assert.strictEqual(sawLoadstart.callCount, 0, 'no loadstart fired');
  assert.strictEqual(sawPlaying.callCount, 0, 'no playing fired');
  assert.strictEqual(sawPause.callCount, 0, 'no pause fired');
  assert.strictEqual(sawEnded.callCount, 0, 'no ended fired');
  assert.strictEqual(sawFirstplay.callCount, 0, 'no firstplay fired');
  assert.strictEqual(sawLoadedalldata.callCount, 0, 'no loadedalldata fired');
  assert.strictEqual(sawAdloadstart.callCount, 1, 'adloadstart fired');
  assert.strictEqual(sawAdpause.callCount, 1, 'adpause fired');
  assert.strictEqual(sawAdended.callCount, 1, 'adended fired');
  assert.strictEqual(sawAdfirstplay.callCount, 1, 'adfirstplay fired');
  assert.strictEqual(sawAdloadedalldata.callCount, 1, 'adloadedalldata fired');
});

QUnit.test('player events during midrolls are prefixed if tech is reused for ad', function(assert) {
  assert.expect(2);

  const prefixed = sinon.spy();
  const unprefixed = sinon.spy();

  // play a midroll
  this.player.trigger('play');
  this.player.trigger('loadstart');
  this.player.trigger('adsready');
  this.player.trigger('adtimeout');
  // return to content
  this.player.trigger('playing');
  this.player.ads.startLinearAdMode();

  this.player.ads.snapshot = {
    currentSrc: 'something'
  };

  // simulate video events that should be prefixed
  this.player.on(['loadstart', 'playing', 'pause', 'ended', 'firstplay', 'loadedalldata'], unprefixed);
  this.player.on(['adloadstart', 'adpause', 'adended', 'adfirstplay', 'adloadedalldata'], prefixed);
  this.player.trigger('firstplay');
  this.player.trigger('loadstart');
  this.player.trigger('playing');
  this.player.trigger('loadedalldata');
  this.player.trigger('pause');
  this.player.trigger('ended');
  assert.strictEqual(unprefixed.callCount, 0, 'no unprefixed events fired');
  assert.strictEqual(prefixed.callCount, 5, 'prefixed events fired');
});

QUnit.test('player events during postrolls are prefixed if tech is reused for ad', function(assert) {
  assert.expect(2);

  const prefixed = sinon.spy();
  const unprefixed = sinon.spy();

  // play a postroll
  this.player.trigger('play');
  this.player.trigger('loadstart');
  this.player.trigger('adsready');
  this.player.trigger('adtimeout');
  // return to content
  this.player.trigger('playing');
  this.player.trigger('ended');
  this.player.ads.startLinearAdMode();

  this.player.ads.snapshot = {
    currentSrc: 'something'
  };

  // simulate video events that should be prefixed
  this.player.on(['loadstart', 'playing', 'pause', 'ended', 'firstplay', 'loadedalldata'], unprefixed);
  this.player.on(['adloadstart', 'adpause', 'adended', 'adfirstplay', 'adloadedalldata'], prefixed);
  this.player.trigger('firstplay');
  this.player.trigger('loadstart');
  this.player.trigger('playing');
  this.player.trigger('loadedalldata');
  this.player.trigger('pause');
  this.player.trigger('ended');
  assert.strictEqual(unprefixed.callCount, 0, 'no unprefixed events fired');
  assert.strictEqual(prefixed.callCount, 5, 'prefixed events fired');
});

QUnit.test('player events during stitched ads are prefixed', function(assert) {
  assert.expect(2);

  const prefixed = sinon.spy();
  const unprefixed = sinon.spy();

  this.player.ads.stitchedAds(true);

  // play a midroll
  this.player.trigger('play');
  this.player.trigger('loadstart');
  this.player.trigger('adsready');
  this.player.trigger('adtimeout');
  this.player.trigger('playing');
  this.player.ads.startLinearAdMode();

  // simulate video events that should be prefixed
  this.player.on(['loadstart', 'playing', 'pause', 'ended', 'firstplay', 'loadedalldata'], unprefixed);
  this.player.on(['adloadstart', 'adplaying', 'adpause', 'adended', 'adfirstplay', 'adloadedalldata'], prefixed);
  this.player.trigger('firstplay');
  this.player.trigger('loadstart');
  this.player.trigger('playing');
  this.player.trigger('loadedalldata');
  this.player.trigger('pause');
  this.player.trigger('ended');
  assert.strictEqual(unprefixed.callCount, 0, 'no unprefixed events fired');
  assert.strictEqual(prefixed.callCount, 6, 'prefixed events fired');
});

QUnit.test('player events during content playback are not prefixed', function(assert) {
  assert.expect(3);

  const prefixed = sinon.spy();
  const unprefixed = sinon.spy();

  // play content
  this.player.trigger('loadstart');
  this.player.trigger('play');
  this.player.trigger('adsready');
  this.player.trigger('adtimeout');
  this.player.trigger('playing');
  this.player.trigger('loadedalldata');

  // simulate video events that should not be prefixed
  this.player.on(['seeked', 'playing', 'pause', 'ended', 'firstplay', 'loadedalldata'], unprefixed);
  this.player.on(['adseeked', 'adplaying', 'adpause', 'adended', 'contentended', 'adfirstplay', 'adloadedalldata'], prefixed);
  this.player.trigger('firstplay');
  this.player.trigger('seeked');
  this.player.trigger('playing');
  this.player.trigger('loadedalldata');
  this.player.trigger('pause');
  this.player.trigger('ended');
  assert.strictEqual(unprefixed.callCount, 5, 'unprefixed events fired');
  assert.strictEqual(prefixed.callCount, 1, 'prefixed events fired');
  assert.strictEqual(prefixed.getCall(0).args[0].type, 'contentended', 'prefixed the ended event');
});

QUnit.test('startLinearAdMode should only trigger adstart from correct states', function(assert) {

  const adstart = sinon.spy();

  this.player.on('adstart', adstart);

  this.player.ads.startLinearAdMode();
  assert.strictEqual(adstart.callCount, 0, 'Before play');

  this.player.trigger('play');

  this.player.ads.startLinearAdMode();
  assert.strictEqual(adstart.callCount, 0, 'Before adsready');

  this.player.trigger('adsready');
  this.player.ads.startLinearAdMode();
  assert.strictEqual(adstart.callCount, 1, 'Preroll');

  this.player.ads.startLinearAdMode();
  assert.strictEqual(adstart.callCount, 1, 'During preroll playback');

  this.player.ads.endLinearAdMode();
  this.player.trigger('playing');

  this.player.ads.startLinearAdMode();
  assert.strictEqual(adstart.callCount, 2, 'Midroll');

  this.player.ads.startLinearAdMode();
  assert.strictEqual(adstart.callCount, 2, 'During midroll playback');

  this.player.ads.endLinearAdMode();
  this.player.trigger('playing');

  this.player.trigger('ended');
  this.player.ads.startLinearAdMode();
  assert.strictEqual(adstart.callCount, 3, 'Postroll');

  this.player.ads.startLinearAdMode();
  assert.strictEqual(adstart.callCount, 3, 'During postroll playback');

  this.player.ads.endLinearAdMode();
  assert.strictEqual(adstart.callCount, 3, 'Ads done');

});

QUnit.test('ad impl can notify contrib-ads there is no preroll', function(assert) {
  this.player.trigger('loadstart');
  this.player.trigger('nopreroll');
  this.player.trigger('play');
  this.player.trigger('adsready');

  assert.strictEqual(this.player.ads.isInAdMode(), true, 'in ad mode');
  assert.strictEqual(this.player.ads.isContentResuming(), true, 'content is resuming');
});

// Same test as above with different event order because this used to be broken.
QUnit.test('ad impl can notify contrib-ads there is no preroll 2', function(assert) {
  this.player.trigger('loadstart');
  this.player.trigger('nopreroll');
  this.player.trigger('adsready');
  this.player.trigger('play');

  assert.strictEqual(this.player.ads.isInAdMode(), true, 'not in ad mode');
  assert.strictEqual(this.player.ads.isContentResuming(), true, 'content is resuming');
});

QUnit.test('ad impl can notify contrib-ads there is no preroll 3', function(assert) {
  this.player.trigger('loadstart');
  this.player.trigger('play');
  this.player.trigger('nopreroll');
  this.player.trigger('adsready');

  assert.strictEqual(this.player.ads.isInAdMode(), true, 'not in ad mode');
  assert.strictEqual(this.player.ads.isContentResuming(), true, 'content is resuming');
});

QUnit.test('ad impl can notify contrib-ads there is no preroll 4', function(assert) {
  this.player.trigger('loadstart');
  this.player.trigger('adsready');
  this.player.trigger('nopreroll');
  this.player.trigger('play');

  assert.strictEqual(this.player.ads.isInAdMode(), true, 'not in ad mode');
  assert.strictEqual(this.player.ads.isContentResuming(), true, 'content is resuming');
});

QUnit.test('ended event is sent after nopostroll', function(assert) {

  const ended = sinon.spy();

  this.player.on('ended', ended);

  this.player.trigger('loadstart');
  this.player.trigger('nopostroll');
  this.player.trigger('play');
  this.player.trigger('adsready');
  this.player.ads.skipLinearAdMode();
  this.player.trigger('playing');
  this.player.trigger('readyforpostroll');
  this.clock.tick(1);
  assert.ok(ended.calledOnce, 'Ended triggered');

});

QUnit.test('ended event is sent with postroll', function(assert) {

  this.player.trigger('loadstart');
  this.player.trigger('adsready');
  this.player.trigger('play');
  this.player.ads.skipLinearAdMode();
  this.player.trigger('playing');

  const ended = sinon.spy();

  this.player.on('ended', ended);

  this.player.trigger('readyforpostroll');

  this.clock.tick(10000);
  assert.ok(ended.calledOnce, 'Ended triggered');

});

QUnit.test('isLive', function(assert) {

  // Make videojs.browser writeable
  videojs.browser = Object.assign({}, videojs.browser);

  this.player.duration = function() {
    return 0;
  };
  videojs.browser.IOS_VERSION = '8';
  assert.strictEqual(this.player.ads.isLive(this.player), true);

  this.player.duration = function() {
    return 5;
  };
  videojs.browser.IOS_VERSION = '8';
  assert.strictEqual(this.player.ads.isLive(this.player), false);

  this.player.duration = function() {
    return Infinity;
  };
  videojs.browser.IOS_VERSION = '8';
  assert.strictEqual(this.player.ads.isLive(this.player), true);

  this.player.duration = function() {
    return 0;
  };
  videojs.browser.IOS_VERSION = undefined;
  assert.strictEqual(this.player.ads.isLive(this.player), false);

  this.player.duration = function() {
    return 5;
  };
  videojs.browser.IOS_VERSION = undefined;
  assert.strictEqual(this.player.ads.isLive(this.player), false);

  this.player.duration = function() {
    return Infinity;
  };
  videojs.browser.IOS_VERSION = undefined;
  assert.strictEqual(this.player.ads.isLive(this.player), true);

});

QUnit.test('shouldPlayContentBehindAd', function(assert) {

  // Make videojs.browser writeable
  videojs.browser = Object.assign({}, videojs.browser);

  this.player.duration = function() {
    return Infinity;
  };
  videojs.browser.IS_IOS = true;
  videojs.browser.IS_ANDROID = true;
  assert.strictEqual(this.player.ads.shouldPlayContentBehindAd(this.player), false);

  this.player.duration = function() {
    return Infinity;
  };
  videojs.browser.IS_IOS = true;
  videojs.browser.IS_ANDROID = false;
  assert.strictEqual(this.player.ads.shouldPlayContentBehindAd(this.player), false);

  this.player.duration = function() {
    return Infinity;
  };
  videojs.browser.IS_IOS = false;
  videojs.browser.IS_ANDROID = true;
  assert.strictEqual(this.player.ads.shouldPlayContentBehindAd(this.player), false);

  this.player.duration = function() {
    return Infinity;
  };
  videojs.browser.IS_IOS = false;
  videojs.browser.IS_ANDROID = false;
  assert.strictEqual(this.player.ads.shouldPlayContentBehindAd(this.player), true);

  this.player.duration = function() {
    return 5;
  };
  videojs.browser.IS_IOS = true;
  videojs.browser.IS_ANDROID = true;
  assert.strictEqual(this.player.ads.shouldPlayContentBehindAd(this.player), false);

  this.player.duration = function() {
    return 5;
  };
  videojs.browser.IS_IOS = true;
  videojs.browser.IS_ANDROID = false;
  assert.strictEqual(this.player.ads.shouldPlayContentBehindAd(this.player), false);

  this.player.duration = function() {
    return 5;
  };
  videojs.browser.IS_IOS = false;
  videojs.browser.IS_ANDROID = true;
  assert.strictEqual(this.player.ads.shouldPlayContentBehindAd(this.player), false);

  this.player.duration = function() {
    return 5;
  };
  videojs.browser.IS_IOS = false;
  videojs.browser.IS_ANDROID = false;
  assert.strictEqual(this.player.ads.shouldPlayContentBehindAd(this.player), false);

  this.player.duration = function() {
    return Infinity;
  };
  videojs.browser.IS_IOS = false;
  videojs.browser.IS_ANDROID = false;
  try {
    this.player.ads.shouldPlayContentBehindAd();
  } catch (error) {
    assert.strictEqual(
      error.message,
      'shouldPlayContentBehindAd requires a player as a param'
    );
  }

  this.player.duration = function() {
    return Infinity;
  };
  videojs.browser.IS_IOS = false;
  videojs.browser.IS_ANDROID = false;
  this.player.ads.settings.liveCuePoints = false;
  assert.strictEqual(this.player.ads.shouldPlayContentBehindAd(this.player), false);
});

QUnit.test('Check incorrect addition of vjs-live during ad-playback', function(assert) {
  this.player.trigger('play');
  this.player.ads.startLinearAdMode();
  assert.strictEqual(this.player.hasClass('vjs-live'), false, 'We have the correct class');

});

QUnit.test(
  'Check for existence of vjs-live after ad-end for LIVE videos',
  function(assert) {
    this.player.trigger('loadstart');
    this.player.trigger('adsready');
    this.player.trigger('play');
    this.player.ads.startLinearAdMode();
    this.player.duration = function() {
      return Infinity;
    };
    this.player.ads.endLinearAdMode();
    this.player.trigger('playing');
    assert.strictEqual(this.player.ads.isLive(this.player), true, 'Content is LIVE');
    assert.ok(this.player.hasClass('vjs-live'), 'We should be having vjs-live class here');
  }
);

QUnit.test('Plugin state resets after contentchanged', function(assert) {

  assert.equal(this.player.ads.disableNextSnapshotRestore, false);
  assert.equal(this.player.ads._contentHasEnded, false);
  assert.equal(this.player.ads.snapshot, null);
  assert.equal(this.player.ads.snapshot, null);
  assert.equal(this.player.ads.nopreroll_, null);
  assert.equal(this.player.ads.nopostroll_, null);

  this.player.ads.disableNextSnapshotRestore = true;
  this.player.ads._contentHasEnded = true;
  this.player.ads.snapshot = {};
  this.player.ads.nopreroll_ = true;
  this.player.ads.nopostroll_ = true;

  this.player.trigger('contentchanged');

  assert.equal(this.player.ads.disableNextSnapshotRestore, false);
  assert.equal(this.player.ads._contentHasEnded, false);
  assert.equal(this.player.ads.snapshot, null);
  assert.equal(this.player.ads.nopreroll_, false);
  assert.equal(this.player.ads.nopostroll_, false);

});

QUnit.test('Plugin sets adType as expected', function(assert) {

  // adType is unset originally
  assert.strictEqual(this.player.ads.adType, null);

  // before preroll
  this.player.trigger('loadstart');
  this.player.trigger('adsready');
  assert.strictEqual(this.player.ads.adType, null);
  this.player.trigger('play');
  assert.strictEqual(this.player.ads.adType, null);

  // preroll starts and finishes
  this.player.ads.startLinearAdMode();
  assert.strictEqual(this.player.ads.adType, 'preroll');
  this.player.ads.endLinearAdMode();
  assert.strictEqual(this.player.ads.adType, null);

  // content is playing, midroll starts
  this.player.trigger('playing');
  this.player.ads.startLinearAdMode();
  assert.strictEqual(this.player.ads.adType, 'midroll');

  // midroll ends, content is playing
  this.player.ads.endLinearAdMode();
  assert.strictEqual(this.player.ads.adType, null);
  this.player.trigger('playing');

  // postroll starts
  this.player.trigger('readyforpostroll');
  this.player.ads.startLinearAdMode();
  assert.strictEqual(this.player.ads.adType, 'postroll');

  // postroll ends
  this.player.ads.endLinearAdMode();
  assert.strictEqual(this.player.ads.adType, null);

  // reset values
  this.player.trigger('contentchanged');
  assert.strictEqual(this.player.ads.adType, null);

  // check preroll case where play is observed
  this.player.trigger('play');
  assert.strictEqual(this.player.ads.adType, null);
  this.player.trigger('adsready');
  assert.strictEqual(this.player.ads.adType, null);
  this.player.ads.startLinearAdMode();
  assert.strictEqual(this.player.ads.adType, 'preroll');
});

if (videojs.browser.IS_IOS) {
  QUnit.test('Check the textTrackChangeHandler takes effect on iOS', function(assert) {
    const tracks = this.player.textTracks();

    // Since addTextTrack is async, wait for the addtrack event
    tracks.on('addtrack', function() {

      // Confirm the track is added, set the mode to showing
      assert.equal(tracks.length, 1);
      tracks[0].mode = 'showing';
      assert.equal(tracks[0].mode, 'showing', 'Initial state is showing');

      // Start the ad, confirm the track is disabled
      this.player.ads.startLinearAdMode();
      assert.equal(tracks[0].mode, 'disabled', 'Snapshot sets tracks to disabled');

      // Force the mode to showing
      tracks[0].mode = 'showing';

    }.bind(this));

    // The mode should go back to disabled when the change event happens as
    // during ad playback we do not want the content captions to be visible on iOS
    tracks.on('change', function() {
      assert.equal(tracks[0].mode, 'disabled', 'Mode is reset to disabled');

      // End the ad, check the track mode is showing again
      this.player.ads.endLinearAdMode();
      assert.equal(tracks[0].mode, 'showing', 'Mode is restored after ad');
    }.bind(this));

    this.player.trigger('play');
    this.player.trigger('adsready');
    this.player.addTextTrack('captions', 'English', 'en');
  });
}
