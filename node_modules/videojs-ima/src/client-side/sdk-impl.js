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

 import pkg from '../../package.json';

 /**
  * Implementation of the IMA SDK for the plugin.
  *
  * @param {Object} controller Reference to the parent controller.
  *
  * @constructor
  * @struct
  * @final
  */
const SdkImpl = function(controller) {
  /**
   * Plugin controller.
   */
  this.controller = controller;

  /**
   * IMA SDK AdDisplayContainer.
   */
  this.adDisplayContainer = null;

  /**
   * True if the AdDisplayContainer has been initialized. False otherwise.
   */
  this.adDisplayContainerInitialized = false;

  /**
   * IMA SDK AdsLoader
   */
  this.adsLoader = null;

  /**
   * IMA SDK AdsManager
   */
  this.adsManager = null;

  /**
   * IMA SDK AdsRenderingSettings.
   */
  this.adsRenderingSettings = null;

  /**
   * VAST, VMAP, or ad rules response. Used in lieu of fetching a response
   * from an ad tag URL.
   */
  this.adsResponse = null;

  /**
   * Current IMA SDK Ad.
   */
  this.currentAd = null;

  /**
   * Timer used to track ad progress.
   */
  this.adTrackingTimer = null;

  /**
   * True if ALL_ADS_COMPLETED has fired, false until then.
   */
  this.allAdsCompleted = false;

  /**
   * True if ads are currently displayed, false otherwise.
   * True regardless of ad pause state if an ad is currently being displayed.
   */
  this.adsActive = false;

  /**
   * True if ad is currently playing, false if ad is paused or ads are not
   * currently displayed.
   */
  this.adPlaying = false;

  /**
   * True if the ad is muted, false otherwise.
   */
  this.adMuted = false;

  /**
   * Listener to be called to trigger manual ad break playback.
   */
  this.adBreakReadyListener = undefined;

  /**
   * Tracks whether or not we have already called adsLoader.contentComplete().
   */
  this.contentCompleteCalled = false;

  /**
   * True if the ad has timed out.
   */
  this.isAdTimedOut = false;

  /**
   * Stores the dimensions for the ads manager.
   */
  this.adsManagerDimensions = {
    width: 0,
    height: 0,
  };

  /**
   * Boolean flag to enable manual ad break playback.
   */
  this.autoPlayAdBreaks = true;
  if (this.controller.getSettings().autoPlayAdBreaks === false) {
    this.autoPlayAdBreaks = false;
  }

  // Set SDK settings from plugin settings.
  if (this.controller.getSettings().locale) {
    /* eslint no-undef: 'error' */
    /* global google */
    google.ima.settings.setLocale(this.controller.getSettings().locale);
  }
  if (this.controller.getSettings().disableFlashAds) {
    google.ima.settings.setDisableFlashAds(
        this.controller.getSettings().disableFlashAds);
  }
  if (this.controller.getSettings().disableCustomPlaybackForIOS10Plus) {
    google.ima.settings.setDisableCustomPlaybackForIOS10Plus(
        this.controller.getSettings().disableCustomPlaybackForIOS10Plus);
  }

  if (this.controller.getSettings().ppid) {
    google.ima.settings.setPpid(this.controller.getSettings().ppid);
  }

  if (this.controller.getSettings().featureFlags) {
    google.ima.settings
      .setFeatureFlags(this.controller.getSettings().featureFlags);
  }
};


/**
 * Creates and initializes the IMA SDK objects.
 */
SdkImpl.prototype.initAdObjects = function() {
  this.adDisplayContainer = new google.ima.AdDisplayContainer(
      this.controller.getAdContainerDiv(),
      this.controller.getContentPlayer());

  this.adsLoader = new google.ima.AdsLoader(this.adDisplayContainer);

  this.adsLoader.getSettings().setVpaidMode(
      google.ima.ImaSdkSettings.VpaidMode.ENABLED);
  if (this.controller.getSettings().vpaidAllowed == false) {
    this.adsLoader.getSettings().setVpaidMode(
        google.ima.ImaSdkSettings.VpaidMode.DISABLED);
  }
  if (this.controller.getSettings().vpaidMode !== undefined) {
    this.adsLoader.getSettings().setVpaidMode(
        this.controller.getSettings().vpaidMode);
  }

  if (this.controller.getSettings().locale) {
    this.adsLoader.getSettings().setLocale(
        this.controller.getSettings().locale);
  }

  if (this.controller.getSettings().numRedirects) {
    this.adsLoader.getSettings().setNumRedirects(
        this.controller.getSettings().numRedirects);
  }

  if (this.controller.getSettings().sessionId) {
    this.adsLoader.getSettings().setSessionId(
        this.controller.getSettings().sessionId);
  }

  this.adsLoader.getSettings().setPlayerType('videojs-ima');
  this.adsLoader.getSettings().setPlayerVersion(pkg.version);
  this.adsLoader.getSettings().setAutoPlayAdBreaks(this.autoPlayAdBreaks);

  this.adsLoader.addEventListener(
    google.ima.AdsManagerLoadedEvent.Type.ADS_MANAGER_LOADED,
    this.onAdsManagerLoaded.bind(this),
    false);
  this.adsLoader.addEventListener(
    google.ima.AdErrorEvent.Type.AD_ERROR,
    this.onAdsLoaderError.bind(this),
    false);

    this.controller.playerWrapper.vjsPlayer.trigger({
      type: 'ads-loader',
      adsLoader: this.adsLoader,
    });
};

/**
 * Creates the AdsRequest and request ads through the AdsLoader.
 */
SdkImpl.prototype.requestAds = function() {
  const adsRequest = new google.ima.AdsRequest();
  if (this.controller.getSettings().adTagUrl) {
    adsRequest.adTagUrl = this.controller.getSettings().adTagUrl;
  } else {
    adsRequest.adsResponse = this.controller.getSettings().adsResponse;
  }
  if (this.controller.getSettings().forceNonLinearFullSlot) {
    adsRequest.forceNonLinearFullSlot = true;
  }

  if (this.controller.getSettings().vastLoadTimeout) {
    adsRequest.vastLoadTimeout = this.controller.getSettings().vastLoadTimeout;
  }

  if (this.controller.getSettings().omidMode) {
    window.console.warn('The additional setting `omidMode` has been removed. ' +
                        'Use `omidVendorAccess` instead.');
  }

  if (this.controller.getSettings().omidVendorAccess) {
    adsRequest.omidAccessModeRules = {};
    const omidVendorValues = this.controller.getSettings().omidVendorAccess;

    Object.keys(omidVendorValues).forEach((vendorKey) => {
      adsRequest.omidAccessModeRules[vendorKey] = omidVendorValues[vendorKey];
    });
  }

  adsRequest.linearAdSlotWidth = this.controller.getPlayerWidth();
  adsRequest.linearAdSlotHeight = this.controller.getPlayerHeight();
  adsRequest.nonLinearAdSlotWidth =
      this.controller.getSettings().nonLinearWidth ||
      this.controller.getPlayerWidth();
  adsRequest.nonLinearAdSlotHeight =
      this.controller.getSettings().nonLinearHeight ||
      this.controller.getPlayerHeight();
  adsRequest.setAdWillAutoPlay(this.controller.adsWillAutoplay());
  adsRequest.setAdWillPlayMuted(this.controller.adsWillPlayMuted());

  // Populate the adsRequestproperties with those provided in the AdsRequest
  // object in the settings.
  let providedAdsRequest = this.controller.getSettings().adsRequest;
  if (providedAdsRequest && typeof providedAdsRequest === 'object') {
    Object.keys(providedAdsRequest).forEach((key) => {
      adsRequest[key] = providedAdsRequest[key];
    });
  }

  this.adsLoader.requestAds(adsRequest);
  this.controller.playerWrapper.vjsPlayer.trigger({
    type: 'ads-request',
    AdsRequest: adsRequest,
  });
};


/**
 * Listener for the ADS_MANAGER_LOADED event. Creates the AdsManager,
 * sets up event listeners, and triggers the 'adsready' event for
 * videojs-ads-contrib.
 *
 * @param {google.ima.AdsManagerLoadedEvent} adsManagerLoadedEvent Fired when
 *     the AdsManager loads.
 */
SdkImpl.prototype.onAdsManagerLoaded = function(adsManagerLoadedEvent) {
  this.createAdsRenderingSettings();

  this.adsManager = adsManagerLoadedEvent.getAdsManager(
      this.controller.getContentPlayheadTracker(), this.adsRenderingSettings);

  this.adsManager.addEventListener(
      google.ima.AdErrorEvent.Type.AD_ERROR,
      this.onAdError.bind(this));
  this.adsManager.addEventListener(
      google.ima.AdEvent.Type.AD_BREAK_READY,
      this.onAdBreakReady.bind(this));
  this.adsManager.addEventListener(
      google.ima.AdEvent.Type.CONTENT_PAUSE_REQUESTED,
      this.onContentPauseRequested.bind(this));
  this.adsManager.addEventListener(
      google.ima.AdEvent.Type.CONTENT_RESUME_REQUESTED,
      this.onContentResumeRequested.bind(this));
  this.adsManager.addEventListener(
      google.ima.AdEvent.Type.ALL_ADS_COMPLETED,
      this.onAllAdsCompleted.bind(this));

  this.adsManager.addEventListener(
      google.ima.AdEvent.Type.LOADED,
      this.onAdLoaded.bind(this));
  this.adsManager.addEventListener(
      google.ima.AdEvent.Type.STARTED,
      this.onAdStarted.bind(this));
  this.adsManager.addEventListener(
      google.ima.AdEvent.Type.COMPLETE,
      this.onAdComplete.bind(this));
  this.adsManager.addEventListener(
      google.ima.AdEvent.Type.SKIPPED,
      this.onAdComplete.bind(this));
  this.adsManager.addEventListener(
      google.ima.AdEvent.Type.LOG,
      this.onAdLog.bind(this));
  this.adsManager.addEventListener(
      google.ima.AdEvent.Type.PAUSED,
      this.onAdPaused.bind(this));
  this.adsManager.addEventListener(
      google.ima.AdEvent.Type.RESUMED,
      this.onAdResumed.bind(this));

  this.controller.playerWrapper.vjsPlayer.trigger({
    type: 'ads-manager',
    adsManager: this.adsManager,
  });

  if (!this.autoPlayAdBreaks) {
    this.initAdsManager();
  }

  const {preventLateAdStart} = this.controller.getSettings();

  if (!preventLateAdStart) {
    this.controller.onAdsReady();
  } else if (preventLateAdStart &&
    !this.isAdTimedOut) {
    this.controller.onAdsReady();
  }

  if (this.controller.getSettings().adsManagerLoadedCallback) {
    this.controller.getSettings().adsManagerLoadedCallback();
  }
};


/**
 * Listener for errors fired by the AdsLoader.
 * @param {google.ima.AdErrorEvent} event The error event thrown by the
 *     AdsLoader. See
 *     https://developers.google.com/interactive-media-ads/docs/sdks/html5/client-side/reference/js/google.ima.AdError#.Type
 */
SdkImpl.prototype.onAdsLoaderError = function(event) {
  window.console.warn('AdsLoader error: ' + event.getError());
  this.controller.onErrorLoadingAds(event);
  if (this.adsManager) {
    this.adsManager.destroy();
  }
};


/**
 * Initialize the ads manager.
 */
SdkImpl.prototype.initAdsManager = function() {
  try {
    const initWidth = this.controller.getPlayerWidth();
    const initHeight = this.controller.getPlayerHeight();
    this.adsManagerDimensions.width = initWidth;
    this.adsManagerDimensions.height = initHeight;
    this.adsManager.init(
        initWidth,
        initHeight,
        google.ima.ViewMode.NORMAL);
    this.adsManager.setVolume(this.controller.getPlayerVolume());
    this.initializeAdDisplayContainer();
  } catch (adError) {
    this.onAdError(adError);
  }
};


/**
 * Create AdsRenderingSettings for the IMA SDK.
 */
SdkImpl.prototype.createAdsRenderingSettings = function() {
  this.adsRenderingSettings = new google.ima.AdsRenderingSettings();
  this.adsRenderingSettings.restoreCustomPlaybackStateOnAdBreakComplete =
      true;
  if (this.controller.getSettings().adsRenderingSettings) {
    for (let setting in this.controller.getSettings().adsRenderingSettings) {
      if (setting !== '') {
        this.adsRenderingSettings[setting] =
            this.controller.getSettings().adsRenderingSettings[setting];
      }
    }
  }
};

/**
 * Listener for errors thrown by the AdsManager.
 * @param {google.ima.AdErrorEvent} adErrorEvent The error event thrown by
 *     the AdsManager.
 */
SdkImpl.prototype.onAdError = function(adErrorEvent) {
  const errorMessage =
      adErrorEvent.getError !== undefined ?
          adErrorEvent.getError() : adErrorEvent.stack;
  window.console.warn('Ad error: ' + errorMessage);

  this.adsManager.destroy();
  this.controller.onAdError(adErrorEvent);

  // reset these so consumers don't think we are still in an ad break,
  // but reset them after any prior cleanup happens
  this.adsActive = false;
  this.adPlaying = false;
};

/**
 * Listener for AD_BREAK_READY. Passes event on to publisher's listener.
 * @param {google.ima.AdEvent} adEvent AdEvent thrown by the AdsManager.
 */
SdkImpl.prototype.onAdBreakReady = function(adEvent) {
  this.adBreakReadyListener(adEvent);
};


/**
 * Pauses the content video and displays the ad container so ads can play.
 * @param {google.ima.AdEvent} adEvent The AdEvent thrown by the AdsManager.
 */
SdkImpl.prototype.onContentPauseRequested = function(adEvent) {
  this.adsActive = true;
  this.adPlaying = true;
  this.controller.onAdBreakStart(adEvent);
};


/**
 * Resumes content video and hides the ad container.
 * @param {google.ima.AdEvent} adEvent The AdEvent thrown by the AdsManager.
 */
SdkImpl.prototype.onContentResumeRequested = function(adEvent) {
  this.adsActive = false;
  this.adPlaying = false;
  this.controller.onAdBreakEnd();
  // Hide controls in case of future non-linear ads. They'll be unhidden in
  // content_pause_requested.
};


/**
 * Records that ads have completed and calls contentAndAdsEndedListeners
 * if content is also complete.
 * @param {google.ima.AdEvent} adEvent The AdEvent thrown by the AdsManager.
 */
SdkImpl.prototype.onAllAdsCompleted = function(adEvent) {
  this.allAdsCompleted = true;
  this.controller.onAllAdsCompleted();
};


/**
 * Starts the content video when a non-linear ad is loaded.
 * @param {google.ima.AdEvent} adEvent The AdEvent thrown by the AdsManager.
 */
SdkImpl.prototype.onAdLoaded = function(adEvent) {
  if (!adEvent.getAd().isLinear()) {
    this.controller.onNonLinearAdLoad();
    this.controller.playContent();
  }
};

/**
 * Starts the interval timer to check the current ad time when an ad starts
 * playing.
 * @param {google.ima.AdEvent} adEvent The AdEvent thrown by the AdsManager.
 */
SdkImpl.prototype.onAdStarted = function(adEvent) {
  this.currentAd = adEvent.getAd();
  if (this.currentAd.isLinear()) {
    this.adTrackingTimer = setInterval(
        this.onAdPlayheadTrackerInterval.bind(this), 250);
    this.controller.onLinearAdStart();
  } else {
    this.controller.onNonLinearAdStart();
  }
};


/**
 * Handles an ad click. Puts the player UI in a paused state.
 */
SdkImpl.prototype.onAdPaused = function() {
  this.controller.onAdsPaused();
};


/**
 * Syncs controls when an ad resumes.
 * @param {google.ima.AdEvent} adEvent The AdEvent thrown by the AdsManager.
 */
SdkImpl.prototype.onAdResumed = function(adEvent) {
  this.controller.onAdsResumed();
};

/**
 * Clears the interval timer for current ad time when an ad completes.
 */
SdkImpl.prototype.onAdComplete = function() {
  if (this.currentAd.isLinear()) {
    clearInterval(this.adTrackingTimer);
  }
};

/**
 * Handles ad log messages.
 * @param {google.ima.AdEvent} adEvent The AdEvent thrown by the AdsManager.
 */
SdkImpl.prototype.onAdLog = function(adEvent) {
  this.controller.onAdLog(adEvent);
};

/**
 * Gets the current time and duration of the ad and calls the method to
 * update the ad UI.
 */
SdkImpl.prototype.onAdPlayheadTrackerInterval = function() {
  if (this.adsManager === null) return;
  const remainingTime = this.adsManager.getRemainingTime();
  const duration = this.currentAd.getDuration();
  let currentTime = duration - remainingTime;
  currentTime = currentTime > 0 ? currentTime : 0;
  let totalAds = 0;
  let adPosition;
  if (this.currentAd.getAdPodInfo()) {
    adPosition = this.currentAd.getAdPodInfo().getAdPosition();
    totalAds = this.currentAd.getAdPodInfo().getTotalAds();
  }

  this.controller.onAdPlayheadUpdated(
      currentTime, remainingTime, duration, adPosition, totalAds);
};


/**
 * Called by the player wrapper when content completes.
 */
SdkImpl.prototype.onContentComplete = function() {
  if (this.adsLoader) {
    this.adsLoader.contentComplete();
    this.contentCompleteCalled = true;
  }

  if ((this.adsManager &&
      this.adsManager.getCuePoints() &&
      !this.adsManager.getCuePoints().includes(-1))
      ||
      !this.adsManager) {
    this.controller.onNoPostroll();
  }

  if (this.allAdsCompleted) {
    this.controller.onContentAndAdsCompleted();
  }
};


/**
 * Called when the player is disposed.
 */
SdkImpl.prototype.onPlayerDisposed = function() {
  if (this.adTrackingTimer) {
    clearInterval(this.adTrackingTimer);
  }
  if (this.adsManager) {
    this.adsManager.destroy();
    this.adsManager = null;
  }
};


SdkImpl.prototype.onPlayerReadyForPreroll = function() {
  if (this.autoPlayAdBreaks) {
    this.initAdsManager();
    try {
      this.controller.showAdContainer();
      // Sync ad volume with content volume.
      this.adsManager.setVolume(this.controller.getPlayerVolume());
      this.adsManager.start();
    } catch (adError) {
      this.onAdError(adError);
    }
  }
};

SdkImpl.prototype.onAdTimeout = function() {
  this.isAdTimedOut = true;
};

SdkImpl.prototype.onPlayerReady = function() {
  this.initAdObjects();

  if ((this.controller.getSettings().adTagUrl ||
      this.controller.getSettings().adsResponse) &&
      this.controller.getSettings().requestMode === 'onLoad') {
        this.requestAds();
  }
};


SdkImpl.prototype.onPlayerEnterFullscreen = function() {
  if (this.adsManager) {
    this.adsManager.resize(
        window.screen.width,
        window.screen.height,
        google.ima.ViewMode.FULLSCREEN);
  }
};


SdkImpl.prototype.onPlayerExitFullscreen = function() {
  if (this.adsManager) {
    this.adsManager.resize(
        this.controller.getPlayerWidth(),
        this.controller.getPlayerHeight(),
        google.ima.ViewMode.NORMAL);
  }
};


/**
 * Called when the player volume changes.
 *
 * @param {number} volume The new player volume.
 */
SdkImpl.prototype.onPlayerVolumeChanged = function(volume) {
  if (this.adsManager) {
    this.adsManager.setVolume(volume);
  }

  if (volume == 0) {
    this.adMuted = true;
  } else {
    this.adMuted = false;
  }
};


/**
 * Called when the player wrapper detects that the player has been resized.
 *
 * @param {number} width The post-resize width of the player.
 * @param {number} height The post-resize height of the player.
 */
SdkImpl.prototype.onPlayerResize = function(width, height) {
  if (this.adsManager) {
    this.adsManagerDimensions.width = width;
    this.adsManagerDimensions.height = height;
    /* eslint no-undef: 'error' */
    this.adsManager.resize(width, height, google.ima.ViewMode.NORMAL);
  }
};


/**
 * @return {Object} The current ad.
 */
SdkImpl.prototype.getCurrentAd = function() {
  return this.currentAd;
};


/**
 * Listener JSDoc for ESLint. This listener can be passed to
 * setAdBreakReadyListener.
 * @callback listener
 */


/**
 * Sets the listener to be called to trigger manual ad break playback.
 * @param {listener} listener The listener to be called to trigger manual ad
 *     break playback.
 */
SdkImpl.prototype.setAdBreakReadyListener = function(listener) {
  this.adBreakReadyListener = listener;
};


/**
 * @return {boolean} True if an ad is currently playing. False otherwise.
 */
SdkImpl.prototype.isAdPlaying = function() {
  return this.adPlaying;
};


/**
 * @return {boolean} True if an ad is currently playing. False otherwise.
 */
SdkImpl.prototype.isAdMuted = function() {
  return this.adMuted;
};


/**
 * Pause ads.
 */
SdkImpl.prototype.pauseAds = function() {
  this.adsManager.pause();
  this.adPlaying = false;
};


/**
 * Resume ads.
 */
SdkImpl.prototype.resumeAds = function() {
  this.adsManager.resume();
  this.adPlaying = true;
};


/**
 * Unmute ads.
 */
SdkImpl.prototype.unmute = function() {
  this.adsManager.setVolume(1);
  this.adMuted = false;
};


/**
 * Mute ads.
 */
SdkImpl.prototype.mute = function() {
  this.adsManager.setVolume(0);
  this.adMuted = true;
};


/**
 * Set the volume of the ads. 0-1.
 *
 * @param {number} volume The new volume.
 */
SdkImpl.prototype.setVolume = function(volume) {
  this.adsManager.setVolume(volume);
  if (volume == 0) {
    this.adMuted = true;
  } else {
    this.adMuted = false;
  }
};


/**
 * Initializes the AdDisplayContainer. On mobile, this must be done as a
 * result of user action.
 */
SdkImpl.prototype.initializeAdDisplayContainer = function() {
  if (this.adDisplayContainer) {
    if (!this.adDisplayContainerInitialized) {
      this.adDisplayContainer.initialize();
      this.adDisplayContainerInitialized = true;
    }
  }
};

/**
 * Called by publishers in manual ad break playback mode to start an ad
 * break.
 */
SdkImpl.prototype.playAdBreak = function() {
  if (!this.autoPlayAdBreaks) {
    this.controller.showAdContainer();
    // Sync ad volume with content volume.
    this.adsManager.setVolume(this.controller.getPlayerVolume());
    this.adsManager.start();
  }
};


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
SdkImpl.prototype.addEventListener = function(event, callback) {
  if (this.adsManager) {
    this.adsManager.addEventListener(event, callback);
  }
};


/**
 * Returns the instance of the AdsManager.
 * @return {google.ima.AdsManager} The AdsManager being used by the plugin.
 */
SdkImpl.prototype.getAdsManager = function() {
  return this.adsManager;
};


/**
 * Reset the SDK implementation.
 */
SdkImpl.prototype.reset = function() {
  this.adsActive = false;
  this.adPlaying = false;
  if (this.adTrackingTimer) {
    // If this is called while an ad is playing, stop trying to get that
    // ad's current time.
    clearInterval(this.adTrackingTimer);
  }
  if (this.adsManager) {
    this.adsManager.destroy();
    this.adsManager = null;
  }
  if (this.adsLoader && !this.contentCompleteCalled) {
    this.adsLoader.contentComplete();
  }
  this.contentCompleteCalled = false;
  this.allAdsCompleted = false;
};

export default SdkImpl;
