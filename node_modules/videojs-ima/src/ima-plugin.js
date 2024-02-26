/**
 * Copyright 2017 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * IMA SDK integration plugin for Video.js. For more information see
 * https://www.github.com/googleads/videojs-ima
 */

import Controller from './client-side/controller.js';
import DaiController from './dai/dai-controller.js';
import videojs from 'video.js';

/**
 * Exposes the ImaPlugin to a publisher implementation.
 *
 * @param {Object} player Instance of the video.js player to which this plugin
 *     will be added.
 * @param {Object} options Options provided by the implementation.
 * @constructor
 * @struct
 * @final
 */
const ImaPlugin = function(player, options) {
  this.controller = new Controller(player, options);

  /**
   * Listener JSDoc for ESLint. This listener can be passed to
   * addContent(AndAds)EndedListener.
   * @callback listener
   */

  /**
   * Adds a listener that will be called when content and all ads have
   * finished playing.
   * @param {listener} listener The listener to be called when content and ads
   *     complete.
   */
  this.addContentAndAdsEndedListener = function(listener) {
    this.controller.addContentAndAdsEndedListener(listener);
  }.bind(this);


  /**
   * Adds a listener for the 'contentended' event of the video player. This
   * should be used instead of setting an 'contentended' listener directly to
   * ensure that the ima can do proper cleanup of the SDK before other event
   * listeners are called.
   * @param {listener} listener The listener to be called when content
   *     completes.
   */
  this.addContentEndedListener = function(listener) {
    this.controller.addContentEndedListener(listener);
  }.bind(this);


  /**
   * Callback JSDoc for ESLint. This callback can be passed to addEventListener.
   * @callback callback
   */


  /**
   * Ads an EventListener to the AdsManager. For a list of available events,
   * see
   * https://developers.google.com/interactive-media-ads/docs/sdks/html5/client-side/reference/js/google.ima.AdEvent#.Type
   * @param {google.ima.AdEvent.Type} event The AdEvent.Type for which to
   *     listen.
   * @param {callback} callback The method to call when the event is fired.
   */
  this.addEventListener = function(event, callback) {
    this.controller.addEventListener(event, callback);
  }.bind(this);


  /**
   * Changes the ad tag. You will need to call requestAds after this method
   * for the new ads to be requested.
   * @param {?string} adTag The ad tag to be requested the next time requestAds
   *     is called.
   */
  this.changeAdTag = function(adTag) {
    this.controller.changeAdTag(adTag);
  }.bind(this);


  /**
   * Returns the instance of the AdsManager.
   * @return {google.ima.AdsManager} The AdsManager being used by the plugin.
   */
  this.getAdsManager = function() {
    return this.controller.getAdsManager();
  }.bind(this);


  /**
   * Initializes the AdDisplayContainer. On mobile, this must be done as a
   * result of user action.
   */
  this.initializeAdDisplayContainer = function() {
    this.controller.initializeAdDisplayContainer();
  }.bind(this);


  /**
   * Pauses the ad.
   */
  this.pauseAd = function() {
    this.controller.pauseAd();
  }.bind(this);


  /**
   * Called by publishers in manual ad break playback mode to start an ad
   * break.
   */
  this.playAdBreak = function() {
    this.controller.playAdBreak();
  }.bind(this);


  /**
   * Creates the AdsRequest and request ads through the AdsLoader.
   */
  this.requestAds = function() {
    this.controller.requestAds();
  }.bind(this);


  /**
   * Resumes the ad.
   */
  this.resumeAd = function() {
    this.controller.resumeAd();
  }.bind(this);


  /**
   * Sets the listener to be called to trigger manual ad break playback.
   * @param {listener} listener The listener to be called to trigger manual ad
   *     break playback.
   */
  this.setAdBreakReadyListener = function(listener) {
    this.controller.setAdBreakReadyListener(listener);
  }.bind(this);


  /**
   * Sets the content of the video player. You should use this method instead
   * of setting the content src directly to ensure the proper ad tag is
   * requested when the video content is loaded.
   * @param {?string} contentSrc The URI for the content to be played. Leave
   *     blank to use the existing content.
   * @param {?string} adTag The ad tag to be requested when the content loads.
   *     Leave blank to use the existing ad tag.
   */
  this.setContentWithAdTag = function(contentSrc, adTag) {
    this.controller.setContentWithAdTag(contentSrc, adTag);
  }.bind(this);


  /**
   * Sets the content of the video player. You should use this method instead
   * of setting the content src directly to ensure the proper ads response is
   * used when the video content is loaded.
   * @param {?string} contentSrc The URI for the content to be played. Leave
   *     blank to use the existing content.
   * @param {?string} adsResponse The ads response to be requested when the
   *     content loads. Leave blank to use the existing ads response.
   */
  this.setContentWithAdsResponse =
      function(contentSrc, adsResponse) {
    this.controller.setContentWithAdsResponse(
        contentSrc, adsResponse);
  }.bind(this);

  /**
   * Sets the content of the video player. You should use this method instead
   * of setting the content src directly to ensure the proper ads request is
   * used when the video content is loaded.
   * @param {?string} contentSrc The URI for the content to be played. Leave
   *     blank to use the existing content.
   * @param {?Object} adsRequest The ads request to be requested when the
   *     content loads. Leave blank to use the existing ads request.
   */
  this.setContentWithAdsRequest =
      function(contentSrc, adsRequest) {
    this.controller.setContentWithAdsRequest(
        contentSrc, adsRequest);
  }.bind(this);


  /**
   * Changes the flag to show or hide the ad countdown timer.
   *
   * @param {boolean} showCountdownIn Show or hide the countdown timer.
   */
  this.setShowCountdown = function(showCountdownIn) {
    this.controller.setShowCountdown(showCountdownIn);
  }.bind(this);
};

/**
 * Exposes the ImaDaiPlugin to a publisher implementation.
 *
 * @param {Object} player Instance of the video.js player to which this plugin
 *     will be added.
 * @param {Object} options Options provided by the implementation.
 * @constructor
 * @struct
 * @final
 */
const ImaDaiPlugin = function(player, options) {
  this.controller = new DaiController(player, options);

  /**
   * Adds a listener that will be called when content and all ads in the
   * stream have finished playing. VOD stream only.
   * @param {listener} listener The listener to be called when content and ads
   *     complete.
   */
   this.streamEndedListener = function(listener) {
    this.controller.addStreamEndedListener(listener);
  }.bind(this);

  /**
   * Adds an EventListener to the StreamManager.
   * @param {google.ima.StreamEvent.Type} event The StreamEvent.Type for which
   * to listen.
   * @param {callback} callback The method to call when the event is fired.
   */
   this.addEventListener = function(event, callback) {
    this.controller.addEventListener(event, callback);
  }.bind(this);

  /**
   * Returns the instance of the StreamManager.
   * @return {google.ima.StreamManager} The StreamManager being used by the
   * plugin.
   */
   this.getStreamManager = function() {
    return this.controller.getStreamManager();
  }.bind(this);
};

/**
 * Initializes the plugin for client-side ads.
 * @param {Object} options Plugin option set on initiation.
 */
const init = function(options) {
  /* eslint no-invalid-this: 'off' */
  this.ima = new ImaPlugin(this, options);
};

/**
 * LiveStream class used for DAI live streams.
 */
class LiveStream {
  /**
   * LiveStream class constructor used for DAI live streams.
   * @param {string} streamFormat stream format, plugin currently supports only
   * 'hls' streams.
   * @param {string} assetKey live stream's asset key.
   */
  constructor(streamFormat, assetKey) {
    streamFormat = streamFormat.toLowerCase();
    if (streamFormat !== 'hls' && streamFormat !== 'dash') {
      window.console.error('VodStream error: incorrect streamFormat.');
      return;
    } else if (streamFormat === 'dash') {
      window.console.error('streamFormat error: DASH streams are not' +
                           'currently supported by this plugin.');
      return;
    } else if (typeof assetKey !== 'string') {
      window.console.error('assetKey error: value must be string.');
      return;
    }
    this.streamFormat = streamFormat;
    this.assetKey = assetKey;
  }
}

/**
 * VodStream class used for DAI VOD streams.
 */
class VodStream {
  /**
   * VodStream class constructor used for DAI VOD streams.
   * @param {string} streamFormat stream format, plugin currently supports only
   * 'hls' streams.
   * @param {string} cmsId VOD stream's CMS ID.
   * @param {string} videoId VOD stream's video ID.
   */
  constructor(streamFormat, cmsId, videoId) {
    streamFormat = streamFormat.toLowerCase();
    if (streamFormat !== 'hls' && streamFormat !== 'dash') {
      window.console.error('VodStream error: incorrect streamFormat.');
      return;
    } else if (streamFormat === 'dash') {
      window.console.error('streamFormat error: DASH streams are not' +
                           'currently supported by this plugin.');
      return;
    } else if (typeof cmsId !== 'string') {
      window.console.error('cmsId error: value must be string.');
      return;
    } else if (typeof videoId !== 'string') {
      window.console.error('videoId error: value must be string.');
      return;
    }

    this.streamFormat = streamFormat;
    this.cmsId = cmsId;
    this.videoId = videoId;
  }
}

/**
 * Initializes the plugin for DAI ads.
 * @param {Object} stream Accepts either an instance of the LiveStream or
 * VodStream classes.
 * @param {Object} options Plugin option set on initiation.
 */
const initDai = function(stream, options) {
  if (stream instanceof LiveStream) {
    options.streamType = 'live';
    options.assetKey = stream.assetKey;
  } else if (stream instanceof VodStream) {
    options.streamType = 'vod';
    options.cmsId = stream.cmsId;
    options.videoId = stream.videoId;
  } else {
    window.console.error(
      'initDai() first parameter must be an instance of LiveStream or ' +
      'VodStream.');
    return;
  }

  options.streamFormat = stream.streamFormat;
  /* eslint no-invalid-this: 'off' */
  this.imaDai = new ImaDaiPlugin(this, options);
};

const registerPlugin = videojs.registerPlugin || videojs.plugin;
registerPlugin('ima', init);
registerPlugin('imaDai', initDai);

export default ImaPlugin;
export {
  VodStream,
  LiveStream,
};
