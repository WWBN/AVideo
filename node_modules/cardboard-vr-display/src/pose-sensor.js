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

import FusionPoseSensor from './sensor-fusion/fusion-pose-sensor.js';
import { Vector3, Quaternion } from './math-util.js';

// Frequency which the Sensors will attempt to fire their
// `reading` functions at. Use 60hz since we generally
// can't get higher without native VR hardware.
const SENSOR_FREQUENCY = 60;

const X_AXIS = new Vector3(1, 0, 0);
const Z_AXIS = new Vector3(0, 0, 1);

// Quaternion to rotate from sensor coordinates to WebVR coordinates
const SENSOR_TO_VR = new Quaternion();
SENSOR_TO_VR.setFromAxisAngle(X_AXIS, -Math.PI / 2);
SENSOR_TO_VR.multiply(new Quaternion().setFromAxisAngle(Z_AXIS, Math.PI / 2));

/**
 * An abstraction class around either using the new RelativeOrientationSensor,
 * or `devicemotion` events with complimentary filter via fusion-pose-sensor.js.
 */
export default class PoseSensor {
  constructor(config) {
    this.config = config;
    this.sensor = null;
    this.fusionSensor = null;
    this._out = new Float32Array(4);

    // Can be 'sensor' (using RelativeOrientationSensor) or
    // 'devicemotion' (using devicemotion events via FusionPoseSensor),
    // or `null` if not yet set.
    this.api = null;

    // Store any errors from Sensors for debugging purposes
    this.errors = [];

    // Quaternions for caching transforms
    this._sensorQ = new Quaternion();
    this._outQ = new Quaternion();

    this._onSensorRead = this._onSensorRead.bind(this);
    this._onSensorError = this._onSensorError.bind(this);

    this.init();
  }

  init() {
    // Attempt to use the RelativeOrientationSensor from Generic Sensor APIs.
    // First available in Chrome M63, this can fail for several reasons, and attempt
    // to fallback to devicemotion. Failure scenarios include:
    //
    // * Generic Sensor APIs do not exist; fallback to devicemotion.
    // * Underlying sensor does not exist; no fallback possible.
    // * Feature Policy failure (in an iframe); no fallback.
    //   https://github.com/immersive-web/webxr/issues/86
    // * Permission to sensor data denied; respect user agent; no fallback to devicemotion.
    //   Browsers are heading towards disabling devicemotion when sensors are denied as well.
    //   https://www.chromestatus.com/feature/5023919287304192
    let sensor = null;
    try {
      sensor = new RelativeOrientationSensor({
        frequency: SENSOR_FREQUENCY,
        // Use `referenceFrame: screen` so we don't have to manage the orientation
        // of the device. First available in Chrome m66 (in release at time of writing),
        // and this will fail in earlier versions, kicking off `devicemotion` fallback.
        // @see https://w3c.github.io/accelerometer/#screen-coordinate-system
        referenceFrame: 'screen',
      });
      sensor.addEventListener('error', this._onSensorError);
    } catch (error) {
      this.errors.push(error);

      // Sensors are available in Chrome M63, however the Feature Policy
      // integration is not available until Chrome M65, resulting in Sensors
      // only being available in main frames.
      // https://developers.google.com/web/updates/2017/09/sensors-for-the-web#feature_policy_integration
      if (error.name === 'SecurityError') {
        console.error('Cannot construct sensors due to the Feature Policy');
        console.warn('Attempting to fall back using "devicemotion"; however this will ' +
                     'fail in the future without correct permissions.');
        this.useDeviceMotion();
      } else if (error.name === 'ReferenceError') {
        // Fall back to devicemotion.
        this.useDeviceMotion();
      } else {
        console.error(error);
      }
    }

    if (sensor) {
      this.api = 'sensor';
      this.sensor = sensor;
      this.sensor.addEventListener('reading', this._onSensorRead);
      this.sensor.start();
    }
  }

  useDeviceMotion() {
    this.api = 'devicemotion';
    this.fusionSensor = new FusionPoseSensor(this.config.K_FILTER,
                                             this.config.PREDICTION_TIME_S,
                                             this.config.YAW_ONLY,
                                             this.config.DEBUG);
    if (this.sensor) {
      this.sensor.removeEventListener('reading', this._onSensorRead);
      this.sensor.removeEventListener('error', this._onSensorError);
      this.sensor = null;
    }
  }

  getOrientation() {
    if (this.fusionSensor) {
      return this.fusionSensor.getOrientation();
    }

    if (!this.sensor || !this.sensor.quaternion) {
      this._out[0] = this._out[1] = this._out[2] = 0;
      this._out[3] = 1;
      return this._out;
    }

    // Convert to THREE coordinate system: -Z forward, Y up, X right.
    const q = this.sensor.quaternion;
    this._sensorQ.set(q[0], q[1], q[2], q[3]);

    const out = this._outQ;
    out.copy(SENSOR_TO_VR);
    out.multiply(this._sensorQ);

    // Handle the yaw-only case.
    if (this.config.YAW_ONLY) {
      // Make a quaternion that only turns around the Y-axis.
      out.x = out.z = 0;
      out.normalize();
    }

    this._out[0] = out.x;
    this._out[1] = out.y;
    this._out[2] = out.z;
    this._out[3] = out.w;
    return this._out;
  }

  _onSensorError(event) {
    this.errors.push(event.error);
    if (event.error.name === 'NotAllowedError') {
      console.error('Permission to access sensor was denied');
    } else if (event.error.name === 'NotReadableError') {
      console.error('Sensor could not be read');
    } else {
      console.error(event.error);
    }
    this.useDeviceMotion();
  }

  _onSensorRead() {}
}
