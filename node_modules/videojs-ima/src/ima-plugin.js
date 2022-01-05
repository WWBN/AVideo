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

import Controller from './controller.js';
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


const init = function(options) {
  /* eslint no-invalid-this: 'off' */
  this.ima = new ImaPlugin(this, options);
};

const registerPlugin = videojs.registerPlugin || videojs.plugin;
registerPlugin('ima', init);

export default ImaPlugin;
