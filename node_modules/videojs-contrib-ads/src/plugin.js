/*
This main plugin file is responsible for the public API and enabling the features
that live in in separate files.
*/

import videojs from 'video.js';

import {version as adsVersion} from '../package.json';

import getAds from './ads.js';
import redispatch from './redispatch.js';
import initializeContentupdate from './contentupdate.js';
import adMacroReplacement from './macros.js';
import cueTextTracks from './cueTextTracks.js';
import initCancelContentPlay from './cancelContentPlay.js';
import playMiddlewareFeature from './playMiddleware.js';
import register from './register.js';
import {listenToTcf} from './tcf.js';
import {obtainUsPrivacyString} from './usPrivacy.js';
import {OUTSTREAM_VIDEO} from './constants.js';
import AdsError from './consts/errors';

import States from './states.js';
import './states/abstract/State.js';
import './states/abstract/AdState.js';
import './states/abstract/ContentState.js';
import './states/AdsDone.js';
import './states/Preroll.js';
import './states/BeforePreroll.js';
import './states/Midroll.js';
import './states/Postroll.js';
import './states/ContentPlayback.js';
import './states/StitchedContentPlayback.js';
import './states/StitchedAdRoll.js';
import './states/OutstreamPending.js';
import './states/OutstreamPlayback.js';
import './states/OutstreamDone.js';

const { isMiddlewareMediatorSupported } = playMiddlewareFeature;
const VIDEO_EVENTS = videojs.getTech('Html5').Events;

// Default settings
const defaults = {
  // Maximum amount of time in ms to wait to receive `adsready` from the ad
  // implementation after play has been requested. Ad implementations are
  // expected to load any dynamic libraries and make any requests to determine
  // ad policies for a video during this time.
  timeout: 5000,

  // Maximum amount of time in ms to wait for the ad implementation to start
  // linear ad mode after `readyforpreroll` has fired. This is in addition to
  // the standard timeout.
  prerollTimeout: undefined,

  // Maximum amount of time in ms to wait for the ad implementation to start
  // linear ad mode after `readyforpostroll` has fired.
  postrollTimeout: undefined,

  // When truthy, instructs the plugin to output additional information about
  // plugin state to the video.js log. On most devices, the video.js log is
  // the same as the developer console.
  debug: false,

  // Set this to true when using ads that are part of the content video
  stitchedAds: false,

  // Force content to be treated as live or not live
  // if not defined, the code will try to infer if content is live,
  // which can have limitations.
  contentIsLive: undefined,

  // If set to true, content will play muted behind ads on supported platforms. This is
  // to support ads on video metadata cuepoints during a live stream. It also results in
  // more precise resumes after ads during a live stream.
  liveCuePoints: true,

  // If set to true, callPlay middleware will not terminate the first play request in
  // BeforePreroll if the player intends to autoplay. This allows the manual autoplay
  // attempt made by video.js to resolve/reject naturally and trigger an 'autoplay-success'
  // or 'autoplay-failure' event with which other plugins can interface.
  allowVjsAutoplay: videojs.options.normalizeAutoplay || false
};

const contribAdsPlugin = function(options) {

  const player = this; // eslint-disable-line consistent-this

  const settings = videojs.obj.merge(defaults, options);

  // Prefix all video element events during ad playback
  // if the video element emits ad-related events directly,
  // plugins that aren't ad-aware will break. prefixing allows
  // plugins that wish to handle ad events to do so while
  // avoiding the complexity for common usage
  const videoEvents = [];

  // dedupe event names
  VIDEO_EVENTS.concat(['firstplay', 'loadedalldata']).forEach(function(eventName) {
    if (videoEvents.indexOf(eventName) === -1) {
      videoEvents.push(eventName);
    }
  });

  // Set up redispatching of player events
  player.on(videoEvents, redispatch);

  // Set up features to block content playback while waiting for ads.
  // Play middleware is only supported on later versions of video.js
  // and on desktop currently(as the user-gesture requirement on mobile
  // will disallow calling play once play blocking is lifted)
  // The middleware must also be registered outside of the plugin,
  // to avoid a middleware factory being created for each player
  if (!isMiddlewareMediatorSupported()) {
    initCancelContentPlay(player, settings.debug);
  }

  // If we haven't seen a loadstart after 5 seconds, the plugin was not initialized
  // correctly.
  player.setTimeout(() => {
    if (!player.ads._hasThereBeenALoadStartDuringPlayerLife && player.src() !== '') {
      videojs.log.error('videojs-contrib-ads has not seen a loadstart event 5 seconds ' +
        'after being initialized, but a source is present. This indicates that ' +
        'videojs-contrib-ads was initialized too late. It must be initialized ' +
        'immediately after video.js in the same tick. As a result, some ads will not ' +
        'play and some media events will be incorrect. For more information, see ' +
        'http://videojs.github.io/videojs-contrib-ads/integrator/getting-started.html');
    }
  }, 5000);

  // "vjs-has-started" should be present at the end of a video. This makes sure it's
  // always there.
  player.on('ended', function() {
    if (!player.hasClass('vjs-has-started')) {
      player.addClass('vjs-has-started');
    }
  });

  // video.js removes the vjs-waiting class on timeupdate. We want
  // to make sure this still happens during content restoration.
  player.on('contenttimeupdate', function() {
    player.removeClass('vjs-waiting');
  });

  // We now auto-play when an ad gets loaded if we're playing ads in the same video
  // element as the content.
  // The problem is that in IE11, we cannot play in addurationchange but in iOS8, we
  // cannot play from adcanplay.
  // This will prevent ad plugins from needing to do this themselves.
  player.on(['addurationchange', 'adcanplay'], function() {
    // We don't need to handle this for stitched ads because
    // linear ads in such cases are stitched into the content.
    if (player.ads.settings.stitchedAds) {
      return;
    }
    // Some techs may retrigger canplay after playback has begun.
    // So we want to procceed only if playback hasn't started.
    if (player.hasStarted()) {
      return;
    }

    if (player.ads.snapshot && player.currentSrc() === player.ads.snapshot.currentSrc) {
      return;
    }

    // If an ad isn't playing, don't try to play an ad. This could result from prefixed
    // events when the player is blocked by a preroll check, but there is no preroll.
    if (!player.ads.inAdBreak()) {
      return;
    }

    const playPromise = player.play();

    if (playPromise && playPromise.catch) {
      playPromise.catch((error) => {
        videojs.log.warn('Play promise rejected when playing ad', error);
      });
    }
  });

  player.on('nopreroll', function() {
    player.ads.debug('Received nopreroll event');
    player.ads.nopreroll_ = true;
  });

  player.on('nopostroll', function() {
    player.ads.debug('Received nopostroll event');
    player.ads.nopostroll_ = true;
  });

  // Restart the cancelContentPlay process.
  player.on('playing', () => {
    player.ads._cancelledPlay = false;
    player.ads._pausedOnContentupdate = false;
  });

  // Keep track of whether a play event has happened
  player.on('play', () => {
    player.ads._playRequested = true;
  });

  player.one('loadstart', () => {
    player.ads._hasThereBeenALoadStartDuringPlayerLife = true;
  });

  player.on('loadeddata', () => {
    player.ads._hasThereBeenALoadedData = true;
  });

  player.on('loadedmetadata', () => {
    player.ads._hasThereBeenALoadedMetaData = true;
  });

  // Replace the plugin constructor with the ad namespace
  player.ads = getAds(player);

  player.ads.settings = settings;

  // Set the stitched ads state. This needs to happen before the `_state` is
  // initialized below - BeforePreroll needs to know whether contrib-ads is
  // playing stitched ads or not.
  // The setter is deprecated, so this does not use it.
  // But first, cast to boolean.
  settings.stitchedAds = !!settings.stitchedAds;

  if (settings.playerMode === 'outstream') {
    // Set a 0s mp4 file to enable ads to play
    player.src(OUTSTREAM_VIDEO);
    player.ads._state = new (States.getState('OutstreamPending'))(player);
  } else if (settings.stitchedAds) {
    player.ads._state = new (States.getState('StitchedContentPlayback'))(player);
  } else {
    player.ads._state = new (States.getState('BeforePreroll'))(player);
  }

  player.ads._state.init(player);

  player.ads.cueTextTracks = cueTextTracks;
  player.ads.adMacroReplacement = adMacroReplacement.bind(player);

  // Start sending contentupdate and contentchanged events for this player
  initializeContentupdate(player);

  // Global contentchanged handler for resetting plugin state
  player.on('contentchanged', player.ads.reset);

  // A utility method for textTrackChangeHandler to define the conditions
  // when text tracks should be disabled.
  // Currently this includes:
  //  - on iOS with native text tracks, during an ad playing
  const shouldDisableTracks = function() {
    // If the platform matches iOS with native text tracks
    // and this occurs during ad playback, we should disable tracks again.
    // If shouldPlayContentBehindAd, no special handling is needed.
    return !player.ads.shouldPlayContentBehindAd(player) &&
            player.ads.inAdBreak() &&
            player.tech_.featuresNativeTextTracks &&
            videojs.browser.IS_IOS &&
            // older versions of video.js did not use an emulated textTrackList
            !Array.isArray(player.textTracks());
  };

  /*
   * iOS Safari will change caption mode to 'showing' if a user previously
   * turned captions on manually for that video source, so this TextTrackList
   * 'change' event handler will re-disable them in case that occurs during ad playback
   */
  const textTrackChangeHandler = function() {
    const textTrackList = player.textTracks();

    if (shouldDisableTracks()) {
      // We must double check all tracks
      for (let i = 0; i < textTrackList.length; i++) {
        const track = textTrackList[i];

        if (track.mode === 'showing') {
          track.mode = 'disabled';
        }
      }
    }
  };

  // Add the listener to the text track list
  player.ready(function() {
    player.textTracks().addEventListener('change', textTrackChangeHandler);
  });

  // Event handling for the current state.
  player.on([
    'play', 'playing', 'ended',
    'adsready', 'adscanceled', 'adskip', 'adserror', 'adtimeout', 'adended',
    'ads-ad-started', 'ads-ad-skipped',
    'contentchanged', 'dispose', 'contentresumed', 'readyforpostroll',
    'nopreroll', 'nopostroll'
  ], (e) => {
    player.ads._state.handleEvent(e.type);
  });

  // Clear timeouts and handlers when player is disposed
  player.on('dispose', function() {
    player.ads.reset();
    player.textTracks().removeEventListener('change', textTrackChangeHandler);
  });

  // Listen to TCF changes
  listenToTcf();

  // Initialize the US Privacy string
  obtainUsPrivacyString(() => {});

  // Can be called for testing, or if the TCF CMP has loaded late
  player.ads.listenToTcf = listenToTcf;

  // Expose so the US privacy string can be updated as needed
  player.ads.updateUsPrivacyString = (callback) => obtainUsPrivacyString(callback);
};

// contrib-ads specific error const
contribAdsPlugin.Error = AdsError;

// Expose the contrib-ads version before it is initialized. Will be replaced
// after initialization in ads.js
contribAdsPlugin.VERSION = adsVersion;

// Attempt to register the plugin, if we can.
register(contribAdsPlugin);

export default contribAdsPlugin;
