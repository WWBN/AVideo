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
  * Implementation of the IMA DAI SDK for the plugin.
  *
  * @param {DaiController!} daiController Reference to the parent DAI
  * controller.
  *
  * @constructor
  * @struct
  * @final
  */
const SdkImpl = function(daiController) {
  /**
   * Plugin DAI controller.
   */
  this.daiController = daiController;

  /**
   * The html5 stream player.
   */
  this.streamPlayer = null;

  /**
   * The videoJS stream player.
   */
  this.vjsPlayer = null;

  /**
   * IMA SDK StreamManager
   */
  this.streamManager = null;

  /**
   * IMA stream UI settings.
   */
  /* eslint no-undef: 'error' */
  /* global google */
  this.uiSettings = new google.ima.dai.api.UiSettings();

  /**
   * If the stream is currently in an ad break.
   */
  this.isAdBreak = false;

  /**
   * If the stream is currently seeking from a snapback.
   */
  this.isSnapback = false;

  /**
   * Originally seeked to time, to return stream to after ads.
   */
  this.snapForwardTime = 0;

  /**
   * Timed metadata for the stream.
   */
   this.timedMetadata;

  /**
   * Timed metadata record.
   */
   this.metadataLoaded = {};

  this.SOURCE_TYPES = {
    hls: 'application/x-mpegURL',
    dash: 'application/dash+xml',
  };
};


/**
 * Creates and initializes the IMA DAI SDK objects.
 */
SdkImpl.prototype.initImaDai = function() {
  this.streamPlayer = this.daiController.getStreamPlayer();
  this.vjsPlayer = this.daiController.getVjsPlayer();

  this.createAdUiDiv();
  if (this.daiController.getSettings().locale) {
    this.uiSettings.setLocale(this.daiController.getSettings().locale);
  }

  this.streamManager = new google.ima.dai.api.StreamManager(
    this.streamPlayer,
    this.adUiDiv,
    this.uiSettings);

  this.streamPlayer.addEventListener('pause', this.onStreamPause);
  this.streamPlayer.addEventListener('play', this.onStreamPlay);

  this.streamManager.addEventListener(
    [
      google.ima.dai.api.StreamEvent.Type.LOADED,
      google.ima.dai.api.StreamEvent.Type.ERROR,
      google.ima.dai.api.StreamEvent.Type.AD_BREAK_STARTED,
      google.ima.dai.api.StreamEvent.Type.AD_BREAK_ENDED,
    ],
    this.onStreamEvent.bind(this),
    false);

  this.vjsPlayer.textTracks().onaddtrack = this.onAddTrack.bind(this);

  this.vjsPlayer.trigger({
    type: 'stream-manager',
    StreamManager: this.streamManager,
  });

  this.requestStream();
};

/**
 * Called when the video player has metadata to process.
 * @param {Event!} event The event that triggered this call.
 */
SdkImpl.prototype.onAddTrack = function(event) {
  const track = event.track;
  if (track.kind === 'metadata') {
    track.mode = 'hidden';
    track.oncuechange = (e) => {
      for (const cue of track.activeCues_) {
        const metadata = {};
        metadata[cue.value.key] = cue.value.data;
        this.streamManager.onTimedMetadata(metadata);
      }
    };
  }
};

/**
 * Creates the ad UI container.
 */
 SdkImpl.prototype.createAdUiDiv = function() {
  const uiDiv = document.createElement('div');
  uiDiv.id = 'ad-ui';
  // 3em is the height of the control bar.
  uiDiv.style.height = 'calc(100% - 3em)';
  this.streamPlayer.parentNode.appendChild(uiDiv);
  this.adUiDiv = uiDiv;
};

/**
 * Called on pause to update the ad UI.
 */
SdkImpl.prototype.onStreamPause = function() {
  if (this.isAdBreak) {
    this.adUiDiv.style.display = 'none';
  }
};

/**
 * Called on play to update the ad UI.
 */
 SdkImpl.prototype.onStreamPlay = function() {
  if (this.isAdBreak) {
    this.adUiDiv.style.display = 'block';
  }
};

/**
 * Called on play to update the ad UI.
 * @param {number} currentTime the current time of the stream.
 */
 SdkImpl.prototype.onSeekEnd = function(currentTime) {
  const streamType = this.daiController.getSettings().streamType;
  if (streamType === 'live') {
    return;
  }
  if (this.isSnapback) {
    this.isSnapback = false;
    return;
  }
  const previousCuePoint =
      this.streamManager.previousCuePointForStreamTime(currentTime);
  if (previousCuePoint && !previousCuePoint.played) {
    this.isSnapback = true;
    this.snapForwardTime = currentTime;
    this.vjsPlayer.currentTime(previousCuePoint.start);
  }
};

/**
 * Handles IMA events.
 * @param {google.ima.StreamEvent!} event the IMA event
 */
 SdkImpl.prototype.onStreamEvent = function(event) {
  switch (event.type) {
    case google.ima.dai.api.StreamEvent.Type.LOADED:
      this.loadUrl(event.getStreamData().url);
      break;
    case google.ima.dai.api.StreamEvent.Type.ERROR:
      window.console.warn('Error loading stream, attempting to play backup ' +
        'stream. ' + event.getStreamData().errorMessage);
      this.daiController.onErrorLoadingAds(event);
      if (this.daiController.getSettings().fallbackStreamUrl) {
        this.loadurl(this.daiController.getSettings().fallbackStreamUrl);
      }
      break;
    case google.ima.dai.api.StreamEvent.Type.AD_BREAK_STARTED:
      this.isAdBreak = true;
      this.adUiDiv.style.display = 'block';
      this.daiController.onAdBreakStart();
      break;
    case google.ima.dai.api.StreamEvent.Type.AD_BREAK_ENDED:
      this.isAdBreak = false;
      this.adUiDiv.style.display = 'none';
      this.daiController.onAdBreakEnd();
      if (this.snapForwardTime && this.snapForwardTime >
          this.vjsPlayer.currentTime()) {
        this.vjsPlayer.currentTime(this.snapForwardTime);
        this.snapForwardTime = 0;
      }
      break;
    default:
      break;
  }
};

/**
 * Loads the stream URL .
 * @param {string} streamUrl the URL for the stream being loaded.
 */
SdkImpl.prototype.loadUrl = function(streamUrl) {
  this.vjsPlayer.ready(function() {
    const streamFormat = this.daiController.getSettings().streamFormat;
    this.vjsPlayer.src({
      src: streamUrl,
      type: this.SOURCE_TYPES[streamFormat],
    });

    const bookmarkTime = this.daiController.getSettings().bookmarkTime;
    if (bookmarkTime) {
      const startTime =
          this.streamManager.streamTimeForContentTime(bookmarkTime);
      // Seeking on load triggers the onSeekEnd event, so treat this seek as
      // if it's snapback. Without this, resuming at a bookmark kicks you
      // back to the ad before the bookmark.
      this.isSnapback = true;
      this.vjsPlayer.currentTime(startTime);
    }
  }.bind(this));
};

/**
 * Creates the AdsRequest and request ads through the AdsLoader.
 */
SdkImpl.prototype.requestStream = function() {
  let streamRequest;
  const streamType = this.daiController.getSettings().streamType;
  if (streamType === 'vod') {
    streamRequest = new google.ima.dai.api.VODStreamRequest();
    streamRequest.contentSourceId = this.daiController.getSettings().cmsId;
    streamRequest.videoId = this.daiController.getSettings().videoId;
  } else if (streamType === 'live') {
    streamRequest = new google.ima.dai.api.LiveStreamRequest();
    streamRequest.assetKey = this.daiController.getSettings().assetKey;
  } else {
    window.console.warn('No valid stream type selected');
  }
  streamRequest.format = this.daiController.getSettings().streamFormat;

  if (this.daiController.getSettings().apiKey) {
    streamRequest.apiKey = this.daiController.getSettings().apiKey;
  }
  if (this.daiController.getSettings().authKey) {
    streamRequest.authKey = this.daiController.getSettings().authKey;
  }
  if (this.daiController.getSettings().adTagParameters) {
    streamRequest.adTagParameters =
        this.daiController.getSettings().adTagParameters;
  }
  if (this.daiController.getSettings().streamActivityMonitorId) {
    streamRequest.streamActivityMonitorId =
      this.daiController.getSettings().streamActivityMonitorId;
  }

  if (this.daiController.getSettings().omidMode) {
    streamRequest.omidAccessModeRules = {};
    const omidValues = this.daiController.getSettings().omidMode;

    if (omidValues.FULL) {
      streamRequest.omidAccessModeRules[google.ima.OmidAccessMode.FULL] =
        omidValues.FULL;
    }
    if (omidValues.DOMAIN) {
      streamRequest.omidAccessModeRules[google.ima.OmidAccessMode.DOMAIN] =
        omidValues.DOMAIN;
    }
    if (omidValues.LIMITED) {
      streamRequest.omidAccessModeRules[google.ima.OmidAccessMode.LIMITED] =
        omidValues.LIMITED;
    }
  }

  this.streamManager.requestStream(streamRequest);
  this.vjsPlayer.trigger({
    type: 'stream-request',
    StreamRequest: streamRequest,
  });
};

/**
 * Initiates IMA when the player is ready.
 */
SdkImpl.prototype.onPlayerReady = function() {
  this.initImaDai();
};


/**
 * Reset the StreamManager when the player is disposed.
 */
SdkImpl.prototype.onPlayerDisposed = function() {
  if (this.streamManager) {
    this.streamManager.reset();
  }
};

/**
 * Returns the instance of the StreamManager.
 * @return {google.ima.StreamManager!} The StreamManager being used by the
 * plugin.
 */
SdkImpl.prototype.getStreamManager = function() {
  return this.StreamManager;
};


/**
 * Reset the SDK implementation.
 */
SdkImpl.prototype.reset = function() {
  if (this.StreamManager) {
    this.StreamManager.reset();
  }
};

export default SdkImpl;
