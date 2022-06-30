/**
 * Copyright 2021 Google Inc.
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
import PlayerWrapper from './player-wrapper.js';
import SdkImpl from './sdk-impl.js';

/**
 * The coordinator for the DAI portion of the plugin. Facilitates
 * communication between all other plugin classes.
 *
 * @param {Object!} player Instance of the video.js player.
 * @param {Object!} options Options provided by the implementation.
 * @constructor
 * @struct
 * @final
 */
const DaiController = function(player, options) {
  /**
  * If the stream is currently in an ad break.
  * @type {boolean}
  */
  this.inAdBreak = false;

  /**
  * Stores user-provided settings.
  * @type {Object!}
  */
  this.settings = {};

  /**
  * Whether or not we are running on a mobile platform.
  */
  this.isMobile = (navigator.userAgent.match(/iPhone/i) ||
    navigator.userAgent.match(/iPad/i) ||
    navigator.userAgent.match(/Android/i));

  /**
  * Whether or not we are running on an iOS platform.
  */
  this.isIos = (navigator.userAgent.match(/iPhone/i) ||
    navigator.userAgent.match(/iPad/i));

  this.initWithSettings(options);

  /**
  * Stores contrib-ads default settings.
  */
  const contribAdsDefaults = {
    debug: this.settings.debug,
    timeout: this.settings.timeout,
    prerollTimeout: this.settings.prerollTimeout,
  };
  const adsPluginSettings =
      Object.assign({}, contribAdsDefaults, options.contribAdsSettings || {});

  this.playerWrapper = new PlayerWrapper(player, adsPluginSettings, this);
  this.sdkImpl = new SdkImpl(this);
};

DaiController.IMA_DEFAULTS = {
  adLabel: 'Advertisement',
  adLabelNofN: 'of',
  debug: false,
  disableAdControls: false,
  showControlsForJSAds: true,
};

/**
 * Extends the settings to include user-provided settings.
 *
 * @param {Object!} options Options to be used in initialization.
 */
DaiController.prototype.initWithSettings = function(options) {
  this.settings = Object.assign({}, DaiController.IMA_DEFAULTS, options || {});

  this.warnAboutDeprecatedSettings();

  // Default showing countdown timer to true.
  this.showCountdown = true;
  if (this.settings.showCountdown === false) {
    this.showCountdown = false;
  }
};

/**
 * Logs console warnings when deprecated settings are used.
 */
DaiController.prototype.warnAboutDeprecatedSettings = function() {
  const deprecatedSettings = [
    // Currently no DAI plugin settings are deprecated.
  ];
  deprecatedSettings.forEach((setting) => {
    if (this.settings[setting] !== undefined) {
      console.warn(
        'WARNING: videojs.imaDai setting ' + setting + ' is deprecated');
    }
  });
};

/**
 * Return the settings object.
 *
 * @return {Object!} The settings object.
 */
DaiController.prototype.getSettings = function() {
  return this.settings;
};

/**
 * Return whether or not we're in a mobile environment.
 *
 * @return {boolean} True if running on mobile, false otherwise.
 */
DaiController.prototype.getIsMobile = function() {
  return this.isMobile;
};

/**
 * Return whether or not we're in an iOS environment.
 *
 * @return {boolean} True if running on iOS, false otherwise.
 */
DaiController.prototype.getIsIos = function() {
  return this.isIos;
};

/**
 * @return {Object!} The html5 player.
 */
DaiController.prototype.getStreamPlayer = function() {
  return this.playerWrapper.getStreamPlayer();
};

/**
 * @return {Object!} The video.js player.
 */
 DaiController.prototype.getVjsPlayer = function() {
  return this.playerWrapper.getVjsPlayer();
};

/**
 * Requests the stream.
 */
DaiController.prototype.requestStream = function() {
  this.sdkImpl.requestStream();
};

/**
 * Add or modify a setting.
 *
 * @param {string} key Key to modify
 * @param {Object!} value Value to set at key.
*/
DaiController.prototype.setSetting = function(key, value) {
  this.settings[key] = value;
};

/**
 * Called when there is an error loading ads.
 *
 * @param {Object!} adErrorEvent The ad error event thrown by the IMA SDK.
 */
DaiController.prototype.onErrorLoadingAds = function(adErrorEvent) {
  this.playerWrapper.onAdError(adErrorEvent);
};

/**
 * Relays ad errors to the player wrapper.
 *
 * @param {Object!} adErrorEvent The ad error event thrown by the IMA SDK.
 */
DaiController.prototype.onAdError = function(adErrorEvent) {
  this.playerWrapper.onAdError(adErrorEvent);
};

/**
 * Signals player that an ad break has started.
 */
 DaiController.prototype.onAdBreakStart = function() {
  this.inAdBreak = true;
  this.playerWrapper.onAdBreakStart();
};

/**
 * Signals player that an ad break has ended.
 */
 DaiController.prototype.onAdBreakEnd = function() {
  this.inAdBreak = false;
  this.playerWrapper.onAdBreakEnd();
};

/**
 * Called when the player is disposed.
 */
DaiController.prototype.onPlayerDisposed = function() {
  this.contentAndAdsEndedListeners = [];
  this.sdkImpl.onPlayerDisposed();
};

/**
 * Returns if the stream is currently in an ad break.
 * @return {boolean} If the stream is currently in an ad break.
 */
 DaiController.prototype.isInAdBreak = function() {
  return this.inAdBreak;
};

/**
 * Called on seek end to check for ad snapback.
 * @param {number} currentTime the current time of the stream.
 */
 DaiController.prototype.onSeekEnd = function(currentTime) {
  this.sdkImpl.onSeekEnd(currentTime);
};

/**
 * Called when the player is ready.
 */
 DaiController.prototype.onPlayerReady = function() {
  this.sdkImpl.onPlayerReady();
};

/**
 * Resets the state of the plugin.
 */
DaiController.prototype.reset = function() {
  this.sdkImpl.reset();
  this.playerWrapper.reset();
};

/**
 * Adds an EventListener to the StreamManager. For a list of available events,
 * see
 * https://developers.google.com/interactive-media-ads/docs/sdks/html5/dai/reference/js/StreamEvent
 * @param {google.ima.StreamEvent.Type!} event The AdEvent.Type for which to
 *     listen.
 * @param {callback!} callback The method to call when the event is fired.
 */
DaiController.prototype.addEventListener = function(event, callback) {
  this.sdkImpl.addEventListener(event, callback);
};

/**
 * Returns the instance of the StreamManager.
 * @return {google.ima.StreamManager!} The StreamManager being used by the
 * plugin.
 */
DaiController.prototype.getStreamManager = function() {
  return this.sdkImpl.getStreamManager();
};

/**
 * Returns the instance of the player id.
 * @return {string} The player id.
 */
DaiController.prototype.getPlayerId = function() {
  return this.playerWrapper.getPlayerId();
};

/**
 * @return {boolean} true if we expect that the stream will autoplay. false
 * otherwise.
 */
DaiController.prototype.streamWillAutoplay = function() {
  if (this.settings.streamWillAutoplay !== undefined) {
    return this.settings.streamWillAutoplay;
  } else {
    return !!this.playerWrapper.getPlayerOptions().autoplay;
  }
};

/**
 * Triggers an event on the VJS player
 * @param  {string} name The event name.
 * @param  {Object!} data The event data.
 */
DaiController.prototype.triggerPlayerEvent = function(name, data) {
  this.playerWrapper.triggerPlayerEvent(name, data);
};

export default DaiController;
