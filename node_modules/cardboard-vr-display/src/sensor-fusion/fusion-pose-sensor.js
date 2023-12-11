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
import ComplementaryFilter from './complementary-filter.js';
import PosePredictor from './pose-predictor.js';
import * as MathUtil from '../math-util.js';
import * as Util from '../util.js';

/**
 * The pose sensor, implemented using DeviceMotion APIs.
 *
 * @param {number} kFilter
 * @param {number} predictionTime
 * @param {boolean} yawOnly
 * @param {boolean} isDebug
 */
function FusionPoseSensor(kFilter, predictionTime, yawOnly, isDebug) {
  this.yawOnly = yawOnly;

  this.accelerometer = new MathUtil.Vector3();
  this.gyroscope = new MathUtil.Vector3();

  this.filter = new ComplementaryFilter(kFilter, isDebug);
  this.posePredictor = new PosePredictor(predictionTime, isDebug);

  this.isFirefoxAndroid = Util.isFirefoxAndroid();
  this.isIOS = Util.isIOS();
  // Chrome as of m66 started reporting `rotationRate` in degrees rather
  // than radians, to be consistent with other browsers.
  // https://github.com/immersive-web/cardboard-vr-display/issues/18
  let chromeVersion = Util.getChromeVersion();
  this.isDeviceMotionInRadians = !this.isIOS && chromeVersion && chromeVersion < 66;
  // In Chrome m65 and Safari 13.4 there's a regression of devicemotion events. Fallback
  // to using deviceorientation for these specific builds. More information
  // at `Util.isChromeWithoutDeviceMotion`.
  this.isWithoutDeviceMotion = Util.isChromeWithoutDeviceMotion() || Util.isSafariWithoutDeviceMotion();

  this.filterToWorldQ = new MathUtil.Quaternion();

  // Set the filter to world transform, depending on OS.
  if (Util.isIOS()) {
    this.filterToWorldQ.setFromAxisAngle(new MathUtil.Vector3(1, 0, 0), Math.PI / 2);
  } else {
    this.filterToWorldQ.setFromAxisAngle(new MathUtil.Vector3(1, 0, 0), -Math.PI / 2);
  }

  this.inverseWorldToScreenQ = new MathUtil.Quaternion();
  this.worldToScreenQ = new MathUtil.Quaternion();
  this.originalPoseAdjustQ = new MathUtil.Quaternion();
  this.originalPoseAdjustQ.setFromAxisAngle(new MathUtil.Vector3(0, 0, 1),
                                           -window.orientation * Math.PI / 180);

  this.setScreenTransform_();
  // Adjust this filter for being in landscape mode.
  if (Util.isLandscapeMode()) {
    this.filterToWorldQ.multiply(this.inverseWorldToScreenQ);
  }

  // Keep track of a reset transform for resetSensor.
  this.resetQ = new MathUtil.Quaternion();

  this.orientationOut_ = new Float32Array(4);

  this.start();
}

FusionPoseSensor.prototype.getPosition = function() {
  // This PoseSensor doesn't support position
  return null;
};

FusionPoseSensor.prototype.getOrientation = function() {
  let orientation;

  // Hack around using deviceorientation instead of devicemotion
  if (this.isWithoutDeviceMotion && this._deviceOrientationQ) {
    // We must rotate 90 (or -90, based on initial rotation) degrees
    // on the Y axis to get the correct orientation of looking down the -Z axis.
    this.deviceOrientationFixQ = this.deviceOrientationFixQ || (function () {
      const z = new MathUtil.Quaternion().setFromAxisAngle(new MathUtil.Vector3(0, 0, -1), 0);
      const y = new MathUtil.Quaternion()

      if (window.orientation === -90) {
        y.setFromAxisAngle(new MathUtil.Vector3(0, 1, 0), Math.PI / -2);
      } else {
        y.setFromAxisAngle(new MathUtil.Vector3(0, 1, 0), Math.PI / 2);
      }

      return z.multiply(y);
    })();

    this.deviceOrientationFilterToWorldQ = this.deviceOrientationFilterToWorldQ || (function () {
      const q = new MathUtil.Quaternion();
      q.setFromAxisAngle(new MathUtil.Vector3(1, 0, 0), -Math.PI / 2);
      return q;
    })();

    orientation = this._deviceOrientationQ;
    var out = new MathUtil.Quaternion();
    out.copy(orientation);
    out.multiply(this.deviceOrientationFilterToWorldQ);
    out.multiply(this.resetQ);
    out.multiply(this.worldToScreenQ);
    out.multiplyQuaternions(this.deviceOrientationFixQ, out);

    // Handle the yaw-only case.
    if (this.yawOnly) {
      // Make a quaternion that only turns around the Y-axis.
      out.x = 0;
      out.z = 0;
      out.normalize();
    }

    this.orientationOut_[0] = out.x;
    this.orientationOut_[1] = out.y;
    this.orientationOut_[2] = out.z;
    this.orientationOut_[3] = out.w;
    return this.orientationOut_;
  } else {
    // Convert from filter space to the the same system used by the
    // deviceorientation event.
    let filterOrientation = this.filter.getOrientation();

    // Predict orientation.
    orientation = this.posePredictor.getPrediction(filterOrientation,
                                                   this.gyroscope,
                                                   this.previousTimestampS);
  }

  // Convert to THREE coordinate system: -Z forward, Y up, X right.
  var out = new MathUtil.Quaternion();
  out.copy(this.filterToWorldQ);
  out.multiply(this.resetQ);
  out.multiply(orientation);
  out.multiply(this.worldToScreenQ);

  // Handle the yaw-only case.
  if (this.yawOnly) {
    // Make a quaternion that only turns around the Y-axis.
    out.x = 0;
    out.z = 0;
    out.normalize();
  }

  this.orientationOut_[0] = out.x;
  this.orientationOut_[1] = out.y;
  this.orientationOut_[2] = out.z;
  this.orientationOut_[3] = out.w;
  return this.orientationOut_;
};

FusionPoseSensor.prototype.resetPose = function() {
  // Reduce to inverted yaw-only.
  this.resetQ.copy(this.filter.getOrientation());
  this.resetQ.x = 0;
  this.resetQ.y = 0;
  this.resetQ.z *= -1;
  this.resetQ.normalize();

  // Take into account extra transformations in landscape mode.
  if (Util.isLandscapeMode()) {
    this.resetQ.multiply(this.inverseWorldToScreenQ);
  }

  // Take into account original pose.
  this.resetQ.multiply(this.originalPoseAdjustQ);
};

FusionPoseSensor.prototype.onDeviceOrientation_ = function(e) {
  this._deviceOrientationQ = this._deviceOrientationQ || new MathUtil.Quaternion();
  let { alpha, beta, gamma } = e;
  alpha = (alpha || 0) * Math.PI / 180;
  beta = (beta || 0) * Math.PI / 180;
  gamma = (gamma || 0) * Math.PI / 180;
  this._deviceOrientationQ.setFromEulerYXZ(beta, alpha, -gamma);
};

FusionPoseSensor.prototype.onDeviceMotion_ = function(deviceMotion) {
  this.updateDeviceMotion_(deviceMotion);
};

FusionPoseSensor.prototype.updateDeviceMotion_ = function(deviceMotion) {
  var accGravity = deviceMotion.accelerationIncludingGravity;
  var rotRate = deviceMotion.rotationRate;
  var timestampS = deviceMotion.timeStamp / 1000;

  var deltaS = timestampS - this.previousTimestampS;

  // On Firefox/iOS the `timeStamp` properties can come in out of order.
  // so emit a warning about it and then stop. The rotation still ends up
  // working.
  // @TODO is there a better way to handle this with the `interval` property
  // from the device motion event? `timeStamp` seems to be non-standard.
  if (deltaS < 0) {
    Util.warnOnce('fusion-pose-sensor:invalid:non-monotonic',
                  'Invalid timestamps detected: non-monotonic timestamp from devicemotion');
    this.previousTimestampS = timestampS;
    return;
  } else if (deltaS <= Util.MIN_TIMESTEP || deltaS > Util.MAX_TIMESTEP) {
    Util.warnOnce('fusion-pose-sensor:invalid:outside-threshold',
                  'Invalid timestamps detected: Timestamp from devicemotion outside expected range.');
    this.previousTimestampS = timestampS;
    return;
  }

  this.accelerometer.set(-accGravity.x, -accGravity.y, -accGravity.z);
  if (rotRate) {
    if (Util.isR7()) {
      this.gyroscope.set(-rotRate.beta, rotRate.alpha, rotRate.gamma);
    } else {
      this.gyroscope.set(rotRate.alpha, rotRate.beta, rotRate.gamma);
    }

    // DeviceMotionEvents should report `rotationRate` in degrees, so we need
    // to convert to radians. However, some browsers (Android Chrome < m66) report
    // the rotation as radians, in which case no conversion is needed.
    if (!this.isDeviceMotionInRadians) {
      this.gyroscope.multiplyScalar(Math.PI / 180);
    }

    this.filter.addGyroMeasurement(this.gyroscope, timestampS);
  }

  this.filter.addAccelMeasurement(this.accelerometer, timestampS);

  this.previousTimestampS = timestampS;
};

FusionPoseSensor.prototype.onOrientationChange_ = function(screenOrientation) {
  this.setScreenTransform_();
};

/**
 * This is only needed if we are in an cross origin iframe on iOS to work around
 * this issue: https://bugs.webkit.org/show_bug.cgi?id=152299.
 */
FusionPoseSensor.prototype.onMessage_ = function(event) {
  var message = event.data;

  // If there's no message type, ignore it.
  if (!message || !message.type) {
    return;
  }

  // Ignore all messages that aren't devicemotion.
  var type = message.type.toLowerCase();
  if (type !== 'devicemotion') {
    return;
  }

  // Update device motion.
  this.updateDeviceMotion_(message.deviceMotionEvent);
};

FusionPoseSensor.prototype.setScreenTransform_ = function() {
  this.worldToScreenQ.set(0, 0, 0, 1);
  switch (window.orientation) {
    case 0:
      break;
    case 90:
      this.worldToScreenQ.setFromAxisAngle(new MathUtil.Vector3(0, 0, 1), -Math.PI / 2);
      break;
    case -90:
      this.worldToScreenQ.setFromAxisAngle(new MathUtil.Vector3(0, 0, 1), Math.PI / 2);
      break;
    case 180:
      // TODO.
      break;
  }
  this.inverseWorldToScreenQ.copy(this.worldToScreenQ);
  this.inverseWorldToScreenQ.inverse();
};

FusionPoseSensor.prototype.start = function() {
  this.onDeviceMotionCallback_ = this.onDeviceMotion_.bind(this);
  this.onOrientationChangeCallback_ = this.onOrientationChange_.bind(this);
  this.onMessageCallback_ = this.onMessage_.bind(this);
  this.onDeviceOrientationCallback_ = this.onDeviceOrientation_.bind(this);

  // Only listen for postMessages if we're in an iOS and embedded inside a cross
  // origin IFrame. In this case, the polyfill can still work if the containing
  // page sends synthetic devicemotion events. For an example of this, see
  // the iframe example in the repo at `examples/iframe.html`
  if (Util.isIOS() && Util.isInsideCrossOriginIFrame()) {
    window.addEventListener('message', this.onMessageCallback_);
  }
  window.addEventListener('orientationchange', this.onOrientationChangeCallback_);
  if (this.isWithoutDeviceMotion) {
    window.addEventListener('deviceorientation', this.onDeviceOrientationCallback_);
  } else {
    window.addEventListener('devicemotion', this.onDeviceMotionCallback_);
  }
};

FusionPoseSensor.prototype.stop = function() {
  window.removeEventListener('devicemotion', this.onDeviceMotionCallback_);
  window.removeEventListener('deviceorientation', this.onDeviceOrientationCallback_);
  window.removeEventListener('orientationchange', this.onOrientationChangeCallback_);
  window.removeEventListener('message', this.onMessageCallback_);
};

export default FusionPoseSensor;
