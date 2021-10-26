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

/**
 * Ad UI implementation.
 *
 * @param {Controller} controller Plugin controller.
 * @constructor
 * @struct
 * @final
 */
const AdUi = function(controller) {
  /**
   * Plugin controller.
   */
  this.controller = controller;

  /**
   * Div used as an ad container.
   */
  this.adContainerDiv = document.createElement('div');

  /**
   * Div used to display ad controls.
   */
  this.controlsDiv = document.createElement('div');

  /**
   * Div used to display ad countdown timer.
   */
  this.countdownDiv = document.createElement('div');

  /**
   * Div used to display add seek bar.
   */
  this.seekBarDiv = document.createElement('div');

  /**
   * Div used to display ad progress (in seek bar).
   */
  this.progressDiv = document.createElement('div');

  /**
   * Div used to display ad play/pause button.
   */
  this.playPauseDiv = document.createElement('div');

  /**
   * Div used to display ad mute button.
   */
  this.muteDiv = document.createElement('div');

  /**
   * Div used by the volume slider.
   */
  this.sliderDiv = document.createElement('div');

  /**
   * Volume slider level visuals
   */
  this.sliderLevelDiv = document.createElement('div');

  /**
   * Div used to display ad fullscreen button.
   */
  this.fullscreenDiv = document.createElement('div');

  /**
   * Bound event handler for onMouseUp.
   */
  this.boundOnMouseUp = this.onMouseUp.bind(this);

  /**
   * Bound event handler for onMouseMove.
   */
  this.boundOnMouseMove = this.onMouseMove.bind(this);

  /**
   * Stores data for the ad playhead tracker.
   */
  this.adPlayheadTracker = {
    'currentTime': 0,
    'duration': 0,
    'isPod': false,
    'adPosition': 0,
    'totalAds': 0,
  };

  /**
   * Used to prefix videojs ima controls.
   */
  this.controlPrefix = this.controller.getPlayerId() + '_';

  /**
   * Boolean flag to show or hide the ad countdown timer.
   */
  this.showCountdown = true;
  if (this.controller.getSettings().showCountdown === false) {
    this.showCountdown = false;
  }

  /**
   * Boolean flag if the current ad is nonlinear.
   */
  this.isAdNonlinear = false;

  this.createAdContainer();
};

/**
 * Creates the ad container.
 */
AdUi.prototype.createAdContainer = function() {
  this.assignControlAttributes(
      this.adContainerDiv, 'ima-ad-container');
  this.adContainerDiv.style.position = 'absolute';
  this.adContainerDiv.style.zIndex = 1111;
  this.adContainerDiv.addEventListener(
      'mouseenter',
      this.showAdControls.bind(this),
      false);
  this.adContainerDiv.addEventListener(
      'mouseleave',
      this.hideAdControls.bind(this),
      false);
  this.adContainerDiv.addEventListener(
      'click',
      this.onAdContainerClick.bind(this),
      false);
  this.createControls();
  this.controller.injectAdContainerDiv(this.adContainerDiv);
};


/**
 * Create the controls.
 */
AdUi.prototype.createControls = function() {
  this.assignControlAttributes(this.controlsDiv, 'ima-controls-div');
  this.controlsDiv.style.width = '100%';

  if (!this.controller.getIsMobile()) {
    this.assignControlAttributes(this.countdownDiv, 'ima-countdown-div');
    this.countdownDiv.innerHTML = this.controller.getSettings().adLabel;
    this.countdownDiv.style.display = this.showCountdown ? 'block' : 'none';
  } else {
    this.countdownDiv.style.display = 'none';
  }

  this.assignControlAttributes(this.seekBarDiv, 'ima-seek-bar-div');
  this.seekBarDiv.style.width = '100%';

  this.assignControlAttributes(this.progressDiv, 'ima-progress-div');

  this.assignControlAttributes(this.playPauseDiv, 'ima-play-pause-div');
  this.addClass(this.playPauseDiv, 'ima-playing');
  this.playPauseDiv.addEventListener(
      'click',
      this.onAdPlayPauseClick.bind(this),
      false);

  this.assignControlAttributes(this.muteDiv, 'ima-mute-div');
  this.addClass(this.muteDiv, 'ima-non-muted');
  this.muteDiv.addEventListener(
      'click',
      this.onAdMuteClick.bind(this),
      false);

  this.assignControlAttributes(this.sliderDiv, 'ima-slider-div');
  this.sliderDiv.addEventListener(
      'mousedown',
      this.onAdVolumeSliderMouseDown.bind(this),
      false);

  // Hide volume slider controls on iOS as they aren't supported.
  if (this.controller.getIsIos()) {
    this.sliderDiv.style.display = 'none';
  }

  this.assignControlAttributes(this.sliderLevelDiv, 'ima-slider-level-div');

  this.assignControlAttributes(this.fullscreenDiv, 'ima-fullscreen-div');
  this.addClass(this.fullscreenDiv, 'ima-non-fullscreen');
  this.fullscreenDiv.addEventListener(
      'click',
      this.onAdFullscreenClick.bind(this),
      false);

  this.adContainerDiv.appendChild(this.controlsDiv);
  this.controlsDiv.appendChild(this.countdownDiv);
  this.controlsDiv.appendChild(this.seekBarDiv);
  this.controlsDiv.appendChild(this.playPauseDiv);
  this.controlsDiv.appendChild(this.muteDiv);
  this.controlsDiv.appendChild(this.sliderDiv);
  this.controlsDiv.appendChild(this.fullscreenDiv);
  this.seekBarDiv.appendChild(this.progressDiv);
  this.sliderDiv.appendChild(this.sliderLevelDiv);
};


/**
 * Listener for clicks on the play/pause button during ad playback.
 */
AdUi.prototype.onAdPlayPauseClick = function() {
  this.controller.onAdPlayPauseClick();
};


/**
 * Listener for clicks on the play/pause button during ad playback.
 */
AdUi.prototype.onAdMuteClick = function() {
  this.controller.onAdMuteClick();
};


/**
 * Listener for clicks on the fullscreen button during ad playback.
 */
AdUi.prototype.onAdFullscreenClick = function() {
  this.controller.toggleFullscreen();
};


/**
 * Show pause and hide play button
 */
AdUi.prototype.onAdsPaused = function() {
  this.controller.sdkImpl.adPlaying = false;
  this.addClass(this.playPauseDiv, 'ima-paused');
  this.removeClass(this.playPauseDiv, 'ima-playing');
  this.showAdControls();
};


/**
 * Show pause and hide play button
 */
AdUi.prototype.onAdsResumed = function() {
  this.onAdsPlaying();
  this.showAdControls();
};


/**
 * Show play and hide pause button
 */
AdUi.prototype.onAdsPlaying = function() {
  this.controller.sdkImpl.adPlaying = true;
  this.addClass(this.playPauseDiv, 'ima-playing');
  this.removeClass(this.playPauseDiv, 'ima-paused');
};


/**
 * Takes data from the controller to update the UI.
 *
 * @param {number} currentTime Current time of the ad.
 * @param {number} remainingTime Remaining time of the ad.
 * @param {number} duration Duration of the ad.
 * @param {number} adPosition Index of the ad in the pod.
 * @param {number} totalAds Total number of ads in the pod.
 */
AdUi.prototype.updateAdUi =
    function(currentTime, remainingTime, duration, adPosition, totalAds) {
  // Update countdown timer data
  const remainingMinutes = Math.floor(remainingTime / 60);
  let remainingSeconds = Math.floor(remainingTime % 60);
  if (remainingSeconds.toString().length < 2) {
    remainingSeconds = '0' + remainingSeconds;
  }
  let podCount = ': ';
  if (totalAds > 1) {
    podCount = ' (' + adPosition + ' ' +
        this.controller.getSettings().adLabelNofN + ' ' + totalAds + '): ';
  }
  this.countdownDiv.innerHTML =
      this.controller.getSettings().adLabel + podCount +
      remainingMinutes + ':' + remainingSeconds;

  // Update UI
  const playProgressRatio = currentTime / duration;
  const playProgressPercent = playProgressRatio * 100;
  this.progressDiv.style.width = playProgressPercent + '%';
};


/**
 * Handles UI changes when the ad is unmuted.
 */
AdUi.prototype.unmute = function() {
  this.addClass(this.muteDiv, 'ima-non-muted');
  this.removeClass(this.muteDiv, 'ima-muted');
  this.sliderLevelDiv.style.width =
      this.controller.getPlayerVolume() * 100 + '%';
};


/**
 * Handles UI changes when the ad is muted.
 */
AdUi.prototype.mute = function() {
  this.addClass(this.muteDiv, 'ima-muted');
  this.removeClass(this.muteDiv, 'ima-non-muted');
  this.sliderLevelDiv.style.width = '0%';
};


/*
 * Listener for mouse down events during ad playback. Used for volume.
 */
AdUi.prototype.onAdVolumeSliderMouseDown = function() {
   document.addEventListener('mouseup', this.boundOnMouseUp, false);
   document.addEventListener('mousemove', this.boundOnMouseMove, false);
};


/*
 * Mouse movement listener used for volume slider.
 */
AdUi.prototype.onMouseMove = function(event) {
  this.changeVolume(event);
};


/*
 * Mouse release listener used for volume slider.
 */
AdUi.prototype.onMouseUp = function(event) {
  this.changeVolume(event);
  document.removeEventListener('mouseup', this.boundOnMouseUp);
  document.removeEventListener('mousemove', this.boundOnMouseMove);
};


/*
 * Utility function to set volume and associated UI
 */
AdUi.prototype.changeVolume = function(event) {
  let percent =
      (event.clientX - this.sliderDiv.getBoundingClientRect().left) /
          this.sliderDiv.offsetWidth;
  percent *= 100;
  // Bounds value 0-100 if mouse is outside slider region.
  percent = Math.min(Math.max(percent, 0), 100);
  this.sliderLevelDiv.style.width = percent + '%';
  if (this.percent == 0) {
    this.addClass(this.muteDiv, 'ima-muted');
    this.removeClass(this.muteDiv, 'ima-non-muted');
  } else {
    this.addClass(this.muteDiv, 'ima-non-muted');
    this.removeClass(this.muteDiv, 'ima-muted');
  }
  this.controller.setVolume(percent / 100); // 0-1
};


/**
 * Show the ad container.
 */
AdUi.prototype.showAdContainer = function() {
  this.adContainerDiv.style.display = 'block';
};


/**
 * Hide the ad container
 */
AdUi.prototype.hideAdContainer = function() {
  this.adContainerDiv.style.display = 'none';
};

/**
 * Handles clicks on the ad container
 */
AdUi.prototype.onAdContainerClick = function() {
  if (this.isAdNonlinear) {
    this.controller.togglePlayback();
  }
};

/**
 * Resets the state of the ad ui.
 */
AdUi.prototype.reset = function() {
  this.hideAdContainer();
};


/**
 * Handles ad errors.
 */
AdUi.prototype.onAdError = function() {
  this.hideAdContainer();
};


/**
 * Handles ad break starting.
 *
 * @param {Object} adEvent The event fired by the IMA SDK.
 */
AdUi.prototype.onAdBreakStart = function(adEvent) {
  this.showAdContainer();

  const contentType = adEvent.getAd().getContentType();
  if ((contentType === 'application/javascript') &&
      !this.controller.getSettings().showControlsForJSAds) {
    this.controlsDiv.style.display = 'none';
  } else {
    this.controlsDiv.style.display = 'block';
  }
  this.onAdsPlaying();
  // Start with the ad controls minimized.
  this.hideAdControls();
};


/**
 * Handles ad break ending.
 */
AdUi.prototype.onAdBreakEnd = function() {
  const currentAd = this.controller.getCurrentAd();
  if (currentAd == null || // hide for post-roll only playlist
      currentAd.isLinear()) { // don't hide for non-linear ads
    this.hideAdContainer();
  }
  this.controlsDiv.style.display = 'none';
  this.countdownDiv.innerHTML = '';
};


/**
 * Handles when all ads have finished playing.
 */
AdUi.prototype.onAllAdsCompleted = function() {
  this.hideAdContainer();
};


/**
 * Handles when a linear ad starts.
 */
AdUi.prototype.onLinearAdStart = function() {
  // Don't bump container when controls are shown
  this.removeClass(this.adContainerDiv, 'bumpable-ima-ad-container');
  this.isAdNonlinear = false;
};


/**
 * Handles when a non-linear ad starts.
 */
AdUi.prototype.onNonLinearAdLoad = function() {
  // For non-linear ads that show after a linear ad. For linear ads, we show the
  // ad container in onAdBreakStart to prevent blinking in pods.
  this.adContainerDiv.style.display = 'block';
  // Bump container when controls are shown
  this.addClass(this.adContainerDiv, 'bumpable-ima-ad-container');
  this.isAdNonlinear = true;
};


AdUi.prototype.onPlayerEnterFullscreen = function() {
  this.addClass(this.fullscreenDiv, 'ima-fullscreen');
  this.removeClass(this.fullscreenDiv, 'ima-non-fullscreen');
};


AdUi.prototype.onPlayerExitFullscreen = function() {
  this.addClass(this.fullscreenDiv, 'ima-non-fullscreen');
  this.removeClass(this.fullscreenDiv, 'ima-fullscreen');
};


/**
 * Called when the player volume changes.
 *
 * @param {number} volume The new player volume.
 */
AdUi.prototype.onPlayerVolumeChanged = function(volume) {
  if (volume == 0) {
    this.addClass(this.muteDiv, 'ima-muted');
    this.removeClass(this.muteDiv, 'ima-non-muted');
    this.sliderLevelDiv.style.width = '0%';
  } else {
    this.addClass(this.muteDiv, 'ima-non-muted');
    this.removeClass(this.muteDiv, 'ima-muted');
    this.sliderLevelDiv.style.width = volume * 100 + '%';
  }
};

/**
 * Shows ad controls on mouseover.
 */
AdUi.prototype.showAdControls = function() {
  const {disableAdControls} = this.controller.getSettings();
  if (!disableAdControls) {
    this.addClass(this.controlsDiv, 'ima-controls-div-showing');
  }
};


/**
 * Hide the ad controls.
 */
AdUi.prototype.hideAdControls = function() {
  this.removeClass(this.controlsDiv, 'ima-controls-div-showing');
};


/**
 * Assigns the unique id and class names to the given element as well as the
 * style class.
 * @param {HTMLElement} element Element that needs the controlName assigned.
 * @param {string} controlName Control name to assign.
 */
AdUi.prototype.assignControlAttributes = function(element, controlName) {
  element.id = this.controlPrefix + controlName;
  element.className = this.controlPrefix + controlName + ' ' + controlName;
};


/**
 * Returns a regular expression to test a string for the given className.
 *
 * @param {string} className The name of the class.
 * @return {RegExp} The regular expression used to test for that class.
 */
AdUi.prototype.getClassRegexp = function(className) {
  // Matches on
  // (beginning of string OR NOT word char)
  // classname
  // (negative lookahead word char OR end of string)
  return new RegExp('(^|[^A-Za-z-])' + className +
      '((?![A-Za-z-])|$)', 'gi');
};


/**
 * Returns whether or not the provided element has the provied class in its
 * className.
 * @param {HTMLElement} element Element to tes.t
 * @param {string} className Class to look for.
 * @return {boolean} True if element has className in class list. False
 *     otherwise.
 */
AdUi.prototype.elementHasClass = function(element, className) {
  const classRegexp = this.getClassRegexp(className);
  return classRegexp.test(element.className);
};


/**
 * Adds a class to the given element if it doesn't already have the class
 * @param {HTMLElement} element Element to which the class will be added.
 * @param {string} classToAdd Class to add.
 */
AdUi.prototype.addClass = function(element, classToAdd) {
  element.className = element.className.trim() + ' ' + classToAdd;
};


/**
 * Removes a class from the given element if it has the given class
 *
 * @param {HTMLElement} element Element from which the class will be removed.
 * @param {string} classToRemove Class to remove.
 */
AdUi.prototype.removeClass = function(element, classToRemove) {
  const classRegexp = this.getClassRegexp(classToRemove);
  element.className =
      element.className.trim().replace(classRegexp, '');
};


/**
 * @return {HTMLElement} The div for the ad container.
 */
AdUi.prototype.getAdContainerDiv = function() {
  return this.adContainerDiv;
};


/**
 * Changes the flag to show or hide the ad countdown timer.
 *
 * @param {boolean} showCountdownIn Show or hide the countdown timer.
 */
AdUi.prototype.setShowCountdown = function(showCountdownIn) {
  this.showCountdown = showCountdownIn;
  this.countdownDiv.style.display = this.showCountdown ? 'block' : 'none';
};

export default AdUi;
