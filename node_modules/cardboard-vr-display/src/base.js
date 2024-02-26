/*
 * Copyright 2015 Google Inc. All Rights Reserved.
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
 */

import * as Util from './util.js';
import NoSleep from 'nosleep.js/dist/NoSleep.js';

// Start at a higher number to reduce chance of conflict.
var nextDisplayId = 1000;

var defaultLeftBounds = [0, 0, 0.5, 1];
var defaultRightBounds = [0.5, 0, 0.5, 1];

var raf = window.requestAnimationFrame;
var caf = window.cancelAnimationFrame;

/**
 * The base class for all VR frame data.
 */

export function VRFrameData() {
  this.leftProjectionMatrix = new Float32Array(16);
  this.leftViewMatrix = new Float32Array(16);
  this.rightProjectionMatrix = new Float32Array(16);
  this.rightViewMatrix = new Float32Array(16);
  this.pose = null;
};

export function VRDisplayCapabilities (config) {
  Object.defineProperties(this, {
    hasPosition: {
      writable: false, enumerable: true, value: config.hasPosition,
    },
    hasExternalDisplay: {
      writable: false, enumerable: true, value: config.hasExternalDisplay,
    },
    canPresent: {
      writable: false, enumerable: true, value: config.canPresent,
    },
    maxLayers: {
      writable: false, enumerable: true, value: config.maxLayers,
    },
    hasOrientation: {
      enumerable: true, get: function() {
        Util.deprecateWarning('VRDisplayCapabilities.prototype.hasOrientation',
                              'VRDisplay.prototype.getFrameData');
        return config.hasOrientation;
      },
    },
  });
}

/**
 * The base class for all VR displays.
 */
export function VRDisplay(config) {
  config = config || {};
  var USE_WAKELOCK = 'wakelock' in config ? config.wakelock : true;

  this.isPolyfilled = true;
  this.displayId = nextDisplayId++;
  this.displayName = '';

  this.depthNear = 0.01;
  this.depthFar = 10000.0;

  this.isPresenting = false;

  Object.defineProperty(this, 'isConnected', {
    get: function() {
      Util.deprecateWarning('VRDisplay.prototype.isConnected',
                            'VRDisplayCapabilities.prototype.hasExternalDisplay');
      return false;
    },
  });

  this.capabilities = new VRDisplayCapabilities({
    hasPosition: false,
    hasOrientation: false,
    hasExternalDisplay: false,
    canPresent: false,
    maxLayers: 1
  });

  this.stageParameters = null;

  // "Private" members.
  this.waitingForPresent_ = false;
  this.layer_ = null;
  // Keep track of the original parent of the source passed into
  // `requestPresent`. While the fullscreenWrapper will be a child of the parent
  // in most cases, we must keep track when there's no parent (like
  // when it was never in the DOM, e.g. WebXR polyfill), or if the `source`
  // changes during presentation (in which case we need something to track
  // the newer `source`'s parent when we clean up).
  this.originalParent_ = null;

  this.fullscreenElement_ = null;
  this.fullscreenWrapper_ = null;
  this.fullscreenElementCachedStyle_ = null;

  this.fullscreenEventTarget_ = null;
  this.fullscreenChangeHandler_ = null;
  this.fullscreenErrorHandler_ = null;

  // Get an appropriate wakelock for Android or iOS if MOBILE_WAKE_LOCK
  // is true.
  if (USE_WAKELOCK && Util.isMobile()) {
    this.wakelock_ = new NoSleep();
  }
}

VRDisplay.prototype.getFrameData = function(frameData) {
  // TODO: Technically this should retain it's value for the duration of a frame
  // but I doubt that's practical to do in javascript.
  return Util.frameDataFromPose(frameData, this._getPose(), this);
};

VRDisplay.prototype.getPose = function() {
  // TODO: Technically this should retain it's value for the duration of a frame
  // but I doubt that's practical to do in javascript.
  Util.deprecateWarning('VRDisplay.prototype.getPose',
                        'VRDisplay.prototype.getFrameData');
  return this._getPose();
};

VRDisplay.prototype.resetPose = function() {
  Util.deprecateWarning('VRDisplay.prototype.resetPose');
  return this._resetPose();
};

VRDisplay.prototype.getImmediatePose = function() {
  // TODO: Technically this should retain it's value for the duration of a frame
  // but I doubt that's practical to do in javascript.
  Util.deprecateWarning('VRDisplay.prototype.getImmediatePose',
                        'VRDisplay.prototype.getFrameData');
  return this._getPose();
};

VRDisplay.prototype.requestAnimationFrame = function(callback) {
  return raf(callback);
};

VRDisplay.prototype.cancelAnimationFrame = function(id) {
  return caf(id);
};

VRDisplay.prototype.wrapForFullscreen = function(element) {
  // Don't wrap in iOS.
  if (Util.isIOS()) {
    return element;
  }
  if (!this.fullscreenWrapper_) {
    this.fullscreenWrapper_ = document.createElement('div');
    var cssProperties = [
      'height: ' + Math.min(screen.height, screen.width) + 'px !important',
      'top: 0 !important',
      'left: 0 !important',
      'right: 0 !important',
      'border: 0',
      'margin: 0',
      'padding: 0',
      'z-index: 999999 !important',
      'position: fixed',
    ];
    this.fullscreenWrapper_.setAttribute('style', cssProperties.join('; ') + ';');
    this.fullscreenWrapper_.classList.add('webvr-polyfill-fullscreen-wrapper');
  }

  if (this.fullscreenElement_ == element) {
    return this.fullscreenWrapper_;
  }

  // If fullscreenElement_ already exists, swap it out with the new element.
  // This is necessary for changing the layer's `source` context, beyond just
  // changing the bounds. This is used in the WebXRPolyfill, which calls requestPresent
  // twice -- once with a dummy canvas to call requestFullscreen immediately after
  // a user gesture, and again once an `XRSession`'s `baseLayer` is set (with the
  // real canvas).
  if (this.fullscreenElement_) {
    // Move the current fullscreenElement_ back to its originalParent_
    if (this.originalParent_) {
      this.originalParent_.appendChild(this.fullscreenElement_);
    } else {
      this.fullscreenElement_.parentElement.removeChild(this.fullscreenElement_);
    }
  }

  this.fullscreenElement_ = element;
  this.originalParent_ = element.parentElement;
  // We may have to inject the canvas in the DOM
  if (!this.originalParent_) {
    document.body.appendChild(element);
  }

  // If the fullscreenWrapper is already in the DOM, don't move it. Otherwise,
  // make it a child of `element`'s parent.
  if (!this.fullscreenWrapper_.parentElement) {
    var parent = this.fullscreenElement_.parentElement;
    parent.insertBefore(this.fullscreenWrapper_, this.fullscreenElement_);
    parent.removeChild(this.fullscreenElement_);
  }

  this.fullscreenWrapper_.insertBefore(this.fullscreenElement_, this.fullscreenWrapper_.firstChild);
  this.fullscreenElementCachedStyle_ = this.fullscreenElement_.getAttribute('style');

  var self = this;
  function applyFullscreenElementStyle() {
    if (!self.fullscreenElement_) {
      return;
    }

    var cssProperties = [
      'position: absolute',
      'top: 0',
      'left: 0',
      'width: ' + Math.max(screen.width, screen.height) + 'px',
      'height: ' + Math.min(screen.height, screen.width) + 'px',
      'border: 0',
      'margin: 0',
      'padding: 0',
    ];
    self.fullscreenElement_.setAttribute('style', cssProperties.join('; ') + ';');
  }

  applyFullscreenElementStyle();

  return this.fullscreenWrapper_;
};

VRDisplay.prototype.removeFullscreenWrapper = function() {
  if (!this.fullscreenElement_) {
    return;
  }

  var element = this.fullscreenElement_;
  if (this.fullscreenElementCachedStyle_) {
    element.setAttribute('style', this.fullscreenElementCachedStyle_);
  } else {
    element.removeAttribute('style');
  }

  this.fullscreenElement_ = null;
  this.fullscreenElementCachedStyle_ = null;

  var parent = this.fullscreenWrapper_.parentElement;
  this.fullscreenWrapper_.removeChild(element);

  if (this.originalParent_ === parent) {
    parent.insertBefore(element, this.fullscreenWrapper_);
  }
  // If it has an original parent but different than the wrapper parent,
  // make a best attempt at reinserting into the DOM. This occurs when swapping
  // canvases during a presentation.
  else if (this.originalParent_) {
    this.originalParent_.appendChild(element);
  }

  parent.removeChild(this.fullscreenWrapper_);

  return element;
};

VRDisplay.prototype.requestPresent = function(layers) {
  var wasPresenting = this.isPresenting;
  var self = this;

  if (!(layers instanceof Array)) {
    Util.deprecateWarning('VRDisplay.prototype.requestPresent with non-array argument',
                          'an array of VRLayers as the first argument');
    layers = [layers];
  }

  return new Promise(function(resolve, reject) {
    if (!self.capabilities.canPresent) {
      reject(new Error('VRDisplay is not capable of presenting.'));
      return;
    }

    if (layers.length == 0 || layers.length > self.capabilities.maxLayers) {
      reject(new Error('Invalid number of layers.'));
      return;
    }

    var incomingLayer = layers[0];
    if (!incomingLayer.source) {
      /*
      todo: figure out the correct behavior if the source is not provided.
      see https://github.com/w3c/webvr/issues/58
      */
      resolve();
      return;
    }

    var leftBounds = incomingLayer.leftBounds || defaultLeftBounds;
    var rightBounds = incomingLayer.rightBounds || defaultRightBounds;
    if (wasPresenting) {
      // Already presenting, just changing configuration
      var layer = self.layer_;
      if (layer.source !== incomingLayer.source) {
        layer.source = incomingLayer.source;
      }

      for (var i = 0; i < 4; i++) {
        layer.leftBounds[i] = leftBounds[i];
        layer.rightBounds[i] = rightBounds[i];
      }

      // Call another wrap to swap out canvases in the fullscreen wrapper.
      self.wrapForFullscreen(self.layer_.source);
      self.updatePresent_();
      resolve();
      return;
    }

    // Was not already presenting.
    self.layer_ = {
      predistorted: incomingLayer.predistorted,
      source: incomingLayer.source,
      leftBounds: leftBounds.slice(0),
      rightBounds: rightBounds.slice(0)
    };

    self.waitingForPresent_ = false;
    if (self.layer_ && self.layer_.source) {

      var fullscreenElement = self.wrapForFullscreen(self.layer_.source);

      var onFullscreenChange = function() {
        var actualFullscreenElement = Util.getFullscreenElement();

        self.isPresenting = (fullscreenElement === actualFullscreenElement);
        if (self.isPresenting) {
          if (screen.orientation && screen.orientation.lock) {
            screen.orientation.lock('landscape-primary').catch(function(error){
                    console.error('screen.orientation.lock() failed due to', error.message)
            });
          }
          self.waitingForPresent_ = false;
          self.beginPresent_();
          resolve();
        } else {
          if (screen.orientation && screen.orientation.unlock) {
            screen.orientation.unlock();
          }
          self.removeFullscreenWrapper();
          self.disableWakeLock();
          self.endPresent_();
          self.removeFullscreenListeners_();
        }
        self.fireVRDisplayPresentChange_();
      }
      var onFullscreenError = function() {
        if (!self.waitingForPresent_) {
          return;
        }

        self.removeFullscreenWrapper();
        self.removeFullscreenListeners_();

        self.disableWakeLock();
        self.waitingForPresent_ = false;
        self.isPresenting = false;

        reject(new Error('Unable to present.'));
      }

      self.addFullscreenListeners_(fullscreenElement,
          onFullscreenChange, onFullscreenError);

      if (Util.requestFullscreen(fullscreenElement)) {
        self.enableWakeLock();
        self.waitingForPresent_ = true;
      } else if (Util.isIOS() || Util.isWebViewAndroid()) {
        // *sigh* Just fake it.
        self.enableWakeLock();
        self.isPresenting = true;
        self.beginPresent_();
        self.fireVRDisplayPresentChange_();
        resolve();
      }
    }

    if (!self.waitingForPresent_ && !Util.isIOS()) {
      Util.exitFullscreen();
      reject(new Error('Unable to present.'));
    }
  });
};

VRDisplay.prototype.exitPresent = function() {
  var wasPresenting = this.isPresenting;
  var self = this;
  this.isPresenting = false;
  this.layer_ = null;
  this.disableWakeLock();

  return new Promise(function(resolve, reject) {
    if (wasPresenting) {
      if (!Util.exitFullscreen() && Util.isIOS()) {
        self.endPresent_();
        self.fireVRDisplayPresentChange_();
      }

      if (Util.isWebViewAndroid()) {
        self.removeFullscreenWrapper();
        self.removeFullscreenListeners_();
        self.endPresent_();
        self.fireVRDisplayPresentChange_();
      }

      resolve();
    } else {
      reject(new Error('Was not presenting to VRDisplay.'));
    }
  });
};

VRDisplay.prototype.getLayers = function() {
  if (this.layer_) {
    return [this.layer_];
  }
  return [];
};

VRDisplay.prototype.fireVRDisplayPresentChange_ = function() {
  // Important: unfortunately we cannot have full spec compliance here.
  // CustomEvent custom fields all go under e.detail (so the VRDisplay ends up
  // being e.detail.display, instead of e.display as per WebVR spec).
  var event = new CustomEvent('vrdisplaypresentchange', {detail: {display: this}});
  window.dispatchEvent(event);
};

VRDisplay.prototype.fireVRDisplayConnect_ = function() {
  // Important: unfortunately we cannot have full spec compliance here.
  // CustomEvent custom fields all go under e.detail (so the VRDisplay ends up
  // being e.detail.display, instead of e.display as per WebVR spec).
  var event = new CustomEvent('vrdisplayconnect', {detail: {display: this}});
  window.dispatchEvent(event);
};

VRDisplay.prototype.addFullscreenListeners_ = function(element, changeHandler, errorHandler) {
  this.removeFullscreenListeners_();

  this.fullscreenEventTarget_ = element;
  this.fullscreenChangeHandler_ = changeHandler;
  this.fullscreenErrorHandler_ = errorHandler;

  if (changeHandler) {
    if (document.fullscreenEnabled) {
      element.addEventListener('fullscreenchange', changeHandler, false);
    } else if (document.webkitFullscreenEnabled) {
      element.addEventListener('webkitfullscreenchange', changeHandler, false);
    } else if (document.mozFullScreenEnabled) {
      document.addEventListener('mozfullscreenchange', changeHandler, false);
    } else if (document.msFullscreenEnabled) {
      element.addEventListener('msfullscreenchange', changeHandler, false);
    }
  }

  if (errorHandler) {
    if (document.fullscreenEnabled) {
      element.addEventListener('fullscreenerror', errorHandler, false);
    } else if (document.webkitFullscreenEnabled) {
      element.addEventListener('webkitfullscreenerror', errorHandler, false);
    } else if (document.mozFullScreenEnabled) {
      document.addEventListener('mozfullscreenerror', errorHandler, false);
    } else if (document.msFullscreenEnabled) {
      element.addEventListener('msfullscreenerror', errorHandler, false);
    }
  }
};

VRDisplay.prototype.removeFullscreenListeners_ = function() {
  if (!this.fullscreenEventTarget_)
    return;

  var element = this.fullscreenEventTarget_;

  if (this.fullscreenChangeHandler_) {
    var changeHandler = this.fullscreenChangeHandler_;
    element.removeEventListener('fullscreenchange', changeHandler, false);
    element.removeEventListener('webkitfullscreenchange', changeHandler, false);
    document.removeEventListener('mozfullscreenchange', changeHandler, false);
    element.removeEventListener('msfullscreenchange', changeHandler, false);
  }

  if (this.fullscreenErrorHandler_) {
    var errorHandler = this.fullscreenErrorHandler_;
    element.removeEventListener('fullscreenerror', errorHandler, false);
    element.removeEventListener('webkitfullscreenerror', errorHandler, false);
    document.removeEventListener('mozfullscreenerror', errorHandler, false);
    element.removeEventListener('msfullscreenerror', errorHandler, false);
  }

  this.fullscreenEventTarget_ = null;
  this.fullscreenChangeHandler_ = null;
  this.fullscreenErrorHandler_ = null;
};

VRDisplay.prototype.enableWakeLock = function() {
  if (this.wakelock_) {
    this.wakelock_.enable();
  }
}

VRDisplay.prototype.disableWakeLock = function() {
  if (this.wakelock_) {
    this.wakelock_.disable();
  }
}

VRDisplay.prototype.beginPresent_ = function() {
  // Override to add custom behavior when presentation begins.
};

VRDisplay.prototype.endPresent_ = function() {
  // Override to add custom behavior when presentation ends.
};

VRDisplay.prototype.submitFrame = function(pose) {
  // Override to add custom behavior for frame submission.
};

VRDisplay.prototype.getEyeParameters = function(whichEye) {
  // Override to return accurate eye parameters if canPresent is true.
  return null;
};
