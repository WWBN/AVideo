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

 /**
  * Wraps the video.js stream player for the plugin.
  *
  * @param {!Object} player Video.js player instance.
  * @param {!Object} adsPluginSettings Settings for the contrib-ads plugin.
  * @param {!DaiController} daiController Reference to the parent controller.
  */
const PlayerWrapper = function(player, adsPluginSettings, daiController) {
  /**
   * Instance of the video.js player.
   */
  this.vjsPlayer = player;

  /**
   * Plugin DAI controller.
   */
  this.daiController = daiController;

  /**
   * Video.js control bar.
   */
  this.vjsControls = this.vjsPlayer.getChild('controlBar');

  /**
   * Vanilla HTML5 video player underneath the video.js player.
   */
  this.h5Player = null;

  this.vjsPlayer.on('dispose', this.playerDisposedListener.bind(this));
  this.vjsPlayer.on('pause', this.onPause.bind(this));
  this.vjsPlayer.on('play', this.onPlay.bind(this));
  this.vjsPlayer.on('seeked', this.onSeekEnd.bind(this));
  this.vjsPlayer.ready(this.onPlayerReady.bind(this));
  this.vjsPlayer.ads(adsPluginSettings);
};

/**
 * Called in response to the video.js player's 'disposed' event.
 */
PlayerWrapper.prototype.playerDisposedListener = function() {
  this.contentEndedListeners = [];
  this.daiController.onPlayerDisposed();
};

/**
 * Called on the player 'pause' event. Handles displaying controls during
 * paused ad breaks.
 */
 PlayerWrapper.prototype.onPause = function() {
  // This code will run if the stream is paused during an ad break. Since
  // controls are usually hidden during ads, they will now show to allow
  // users to resume ad playback.
  if (this.daiController.isInAdBreak()) {
    this.vjsControls.show();
  }
};

/**
 * Called on the player 'play' event. Handles hiding controls during
 * ad breaks while playing.
 */
 PlayerWrapper.prototype.onPlay = function() {
  if (this.daiController.isInAdBreak()) {
    this.vjsControls.hide();
  }
};

/**
 * Called on the player's 'seeked' event. Sets up handling for ad break
 * snapback for VOD streams.
 */
 PlayerWrapper.prototype.onSeekEnd = function() {
  this.daiController.onSeekEnd(this.vjsPlayer.currentTime());
};

/**
 * Called on the player's 'ready' event to begin initiating IMA.
 */
PlayerWrapper.prototype.onPlayerReady = function() {
  this.h5Player =
      document.getElementById(
          this.getPlayerId()).getElementsByClassName(
              'vjs-tech')[0];
  this.daiController.onPlayerReady();
};

/**
 * @return {!Object} The stream player.
 */
PlayerWrapper.prototype.getStreamPlayer = function() {
  return this.h5Player;
};

/**
 * @return {!Object} The video.js player.
 */
 PlayerWrapper.prototype.getVjsPlayer = function() {
  return this.vjsPlayer;
};

/**
 * @return {!Object} The vjs player's options object.
 */
PlayerWrapper.prototype.getPlayerOptions = function() {
  return this.vjsPlayer.options_;
};

/**
 * Returns the instance of the player id.
 * @return {string} The player id.
 */
PlayerWrapper.prototype.getPlayerId = function() {
  return this.vjsPlayer.id();
};

/**
 * Handles ad errors.
 *
 * @param {!Object} adErrorEvent The ad error event thrown by the IMA SDK.
 */
PlayerWrapper.prototype.onAdError = function(adErrorEvent) {
  this.vjsControls.show();
  const errorMessage =
      (adErrorEvent.getError !== undefined) ?
          adErrorEvent.getError() : adErrorEvent.stack;
  this.vjsPlayer.trigger({type: 'adserror', data: {
    AdError: errorMessage,
    AdErrorEvent: adErrorEvent,
  }});
};

/**
 * Handles ad break starting.
 */
PlayerWrapper.prototype.onAdBreakStart = function() {
  this.vjsControls.hide();
};

/**
 * Handles ad break ending.
 */
PlayerWrapper.prototype.onAdBreakEnd = function() {
  this.vjsControls.show();
};

/**
 * Reset the player.
 */
PlayerWrapper.prototype.reset = function() {
  this.vjsControls.show();
};

export default PlayerWrapper;
