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

import { extend, isMobile, race, copyArray } from './util';
import CardboardVRDisplay from 'cardboard-vr-display';
import { version } from '../package.json';
import DefaultConfig from './config';

function WebVRPolyfill(config) {
  this.config = extend(extend({}, DefaultConfig), config);
  this.polyfillDisplays = [];
  this.enabled = false;

  // Must handle this in constructor before we start
  // destructively polyfilling `navigator`
  this.hasNative = 'getVRDisplays' in navigator;
  // Store initial references to native constructors
  // and functions
  this.native = {};
  this.native.getVRDisplays = navigator.getVRDisplays;
  this.native.VRFrameData = window.VRFrameData;
  this.native.VRDisplay = window.VRDisplay;

  // If we don't have native 1.1 support, or if we want to provide
  // a CardboardVRDisplay in the event of native support with no displays,
  // inject our own polyfill
  if (!this.hasNative || this.config.PROVIDE_MOBILE_VRDISPLAY && isMobile()) {
    this.enable();
    // If we need to create a CardboardVRDisplay, fire the `vrdisplayconnect`
    // event when the polyfill is enabled
    this.getVRDisplays().then(function (displays) {
      if (displays && displays[0] && displays[0].fireVRDisplayConnect_) {
        displays[0].fireVRDisplayConnect_();
      }
    });
  }
}

WebVRPolyfill.prototype.getPolyfillDisplays = function() {
  if (this._polyfillDisplaysPopulated) {
    return this.polyfillDisplays;
  }

  // Add a Cardboard VRDisplay on compatible mobile devices
  if (isMobile()) {
    var vrDisplay = new CardboardVRDisplay({
      ADDITIONAL_VIEWERS:           this.config.ADDITIONAL_VIEWERS,
      DEFAULT_VIEWER:               this.config.DEFAULT_VIEWER,
      MOBILE_WAKE_LOCK:             this.config.MOBILE_WAKE_LOCK,
      DEBUG:                        this.config.DEBUG,
      DPDB_URL:                     this.config.DPDB_URL,
      CARDBOARD_UI_DISABLED:        this.config.CARDBOARD_UI_DISABLED,
      K_FILTER:                     this.config.K_FILTER,
      PREDICTION_TIME_S:            this.config.PREDICTION_TIME_S,
      ROTATE_INSTRUCTIONS_DISABLED: this.config.ROTATE_INSTRUCTIONS_DISABLED,
      YAW_ONLY:                     this.config.YAW_ONLY,
      BUFFER_SCALE:                 this.config.BUFFER_SCALE,
      DIRTY_SUBMIT_FRAME_BINDINGS:  this.config.DIRTY_SUBMIT_FRAME_BINDINGS,
    });

    this.polyfillDisplays.push(vrDisplay);
  }

  this._polyfillDisplaysPopulated = true;
  return this.polyfillDisplays;
};

WebVRPolyfill.prototype.enable = function() {
  this.enabled = true;

  // Polyfill native VRDisplay.getFrameData when the platform
  // has native WebVR support, but for use with a polyfilled
  // CardboardVRDisplay
  if (this.hasNative && this.native.VRFrameData) {
    var NativeVRFrameData = this.native.VRFrameData;
    var nativeFrameData = new this.native.VRFrameData();
    var nativeGetFrameData = this.native.VRDisplay.prototype.getFrameData;

    // When using a native display with a polyfilled VRFrameData
    window.VRDisplay.prototype.getFrameData = function(frameData) {
      // This should only be called in the event of code instantiating
      // `window.VRFrameData` before the polyfill kicks in, which is
      // unrecommended, but happens anyway
      if (frameData instanceof NativeVRFrameData) {
        nativeGetFrameData.call(this, frameData);
        return;
      }

      /*
      Copy frame data from the native object into the polyfilled object.
      */

      nativeGetFrameData.call(this, nativeFrameData);
      frameData.pose = nativeFrameData.pose;
      copyArray(nativeFrameData.leftProjectionMatrix, frameData.leftProjectionMatrix);
      copyArray(nativeFrameData.rightProjectionMatrix, frameData.rightProjectionMatrix);
      copyArray(nativeFrameData.leftViewMatrix, frameData.leftViewMatrix);
      copyArray(nativeFrameData.rightViewMatrix, frameData.rightViewMatrix);
      //todo: copy
    };
  }

  // Provide navigator.getVRDisplays.
  navigator.getVRDisplays = this.getVRDisplays.bind(this);

  // Provide the `VRDisplay` object.
  window.VRDisplay = CardboardVRDisplay.VRDisplay;

  // Provide the VRFrameData object.
  window.VRFrameData = CardboardVRDisplay.VRFrameData;
};

WebVRPolyfill.prototype.getVRDisplays = function() {
  var config = this.config;

  if (!this.hasNative) {
    return Promise.resolve(this.getPolyfillDisplays());
  }

  return this.native.getVRDisplays.call(navigator).then((nativeDisplays) => {
    return nativeDisplays.length > 0 ? nativeDisplays : this.getPolyfillDisplays();
  });
};

WebVRPolyfill.version = version;

// Attach polyfilled constructors
WebVRPolyfill.VRFrameData = CardboardVRDisplay.VRFrameData;
WebVRPolyfill.VRDisplay = CardboardVRDisplay.VRDisplay;

export default WebVRPolyfill;
