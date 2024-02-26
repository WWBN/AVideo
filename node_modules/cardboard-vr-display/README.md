# cardboard-vr-display

[![Build Status](http://img.shields.io/travis/immersive-web/cardboard-vr-display.svg?style=flat-square)](https://travis-ci.org/immersive-web/cardboard-vr-display)
[![Build Status](http://img.shields.io/npm/v/cardboard-vr-display.svg?style=flat-square)](https://www.npmjs.org/package/cardboard-vr-display)

A JavaScript implementation of a [WebVR 1.1 VRDisplay][VRDisplay]. This is the magic
behind rendering distorted stereoscopic views for browsers that do not support the [WebVR API]
with the [webvr-polyfill].

Unless you're building a WebVR wrapper, you probably want to use [webvr-polyfill] directly
rather than this. This component **does not** polyfill interfaces like `VRFrameData` and
`navigator.getVRDisplays`, and up to the consumer, although trivial (see examples).

## How It Works

As of [1.0.4](https://github.com/immersive-web/cardboard-vr-display/tree/v1.0.4), `CardboardVRDisplay` uses [RelativeOrientationSensor] for orientation tracking,
falling back to [DeviceMotionEvents] using [sensor fusion and pose prediction][fusion].
[RelativeOrientationSensor] is a new API ([read more about the new Sensors on the web][sensors])
first implemented in Chrome M63. This API uses the new [Feature Policy] specification which allows
developers to selectively enable or disable browser features.

It can also render in stereo mode, and includes mesh-based
lens distortion. This display also includes user interface elements in VR mode
to make the VR experience more intuitive, including:

* A gear icon to select your VR viewer.
* A back button to exit VR mode.
* An interstitial which only appears in portrait orientation, requesting you switch
  into landscape orientation (if [orientation lock][ol] is not available).

### iframes

By default, main frames and same-origin iframes have access to Sensor APIs,
but cross-origin iframes must specify feature policy and allow `gyroscope` and
`accelerometer` features. If your experience is attempting to use the native
[WebXR Device API] in an iframe, you'll have to specify that feature as well ([WebXR's
feature name may change](https://github.com/immersive-web/webxr/issues/308)). All of these features require HTTPS to function, except for `localhost`, where HTTP is allowed.

```html
<iframe src="https://otherdomain.com" allow="gyroscope; accelerometer; xr"></iframe>
```

While `devicemotion` is a fallback for Sensors, eventually `devicemotion` will be behind the same
Feature Policy as Sensors and it is encouraged to adhere to these policies in the meantime.
If the Feature Policy for Sensors is denied, `CardboardVRDisplay` will **not** always attempt
to fall back to `devicemotion`. Using Feature Policies now will guarantee a more future-proof experience.

#### Caveats

* On iOS, cross-origin iframes do not have access to the `devicemotion` events.
  The `CardboardVRDisplay` however does respond to events passed in from a parent
  frame via `postMessage`. See the [iframe example][iframe-example] to see how
  the events must be formatted.
* Chrome M63 supports Sensors, although not the corresponding Feature Policy [until Chrome M65][sensors-main-frame].
  This results in Chrome M63/M64 only supporting Sensors in main frames, and these browsers
  will fall back to using devicemotion if in iframes.
* Using Sensors in a cross-origin iframe [requires the frame to be in focus](https://www.w3.org/TR/generic-sensor/#focused-area). In builds of Chrome prior to M69, this logic is [erroneously reversed](https://bugs.chromium.org/p/chromium/issues/detail?id=849501). If loading content via cross-origin iframe, you can disable Sensors, triggering the `devicemotion` fallback with this [hacky workaround](https://github.com/immersive-web/cardboard-vr-display/blob/c196e15a8c7ccf594fe6a5044fbdcb51cc2eff91/examples/index.html#L117-L124). More info in [#27](https://github.com/immersive-web/cardboard-vr-display/issues/27).

### Magic Window

It is possible to have a magic window using a VRDisplay that isn't 100% width/height of the window, and can jump into fullscreen WebVR. See the [magic window][magicwindow-example] for usage.

## Installation

```
$ npm install --save cardboard-vr-display
```

## Browser Support

Should support most modern browsers (IE11 is missing a few, for example) and requires [ES5](https://kangax.github.io/compat-table/es5/) JavaScript support. If you want to support a non-ES5 browser, or browser lacking some DOM globals, you must use a transformation or provide polyfills to support older environments.

Globals required:

* [`Promise`](https://caniuse.com/#feat=promises)
* [`CustomEvent`](https://caniuse.com/#feat=customevent)
* [`requestAnimationFrame`](https://caniuse.com/#feat=requestanimationframe)

Additionally, WebGL support, [`devicemotion`](https://caniuse.com/#feat=deviceorientation) events, and common browser globals (`window`, `navigator`, `document`) are also required in the environment.

## Usage

`cardboard-vr-display` exposes a constructor for a `CardboardVRDisplay` that takes
a single options configuration, detailed below. Check out [running the demo](#running-the-demo)
to try the different options.

```js
import CardboardVRDisplay from 'cardboard-vr-display';

// Default options
const options = {
  // Optionally inject custom Viewer parameters as an option. Each item
  // in the array must be an object with the following properties; here is
  // an example of the built in CardboardV2 viewer:
  //
  // {
  //   id: 'CardboardV2',
  //   label: 'Cardboard I/O 2015',
  //   fov: 60,
  //   interLensDistance: 0.064,
  //   baselineLensDistance: 0.035,
  //   screenLensDistance: 0.039,
  //   distortionCoefficients: [0.34, 0.55],
  //   inverseCoefficients: [-0.33836704, -0.18162185, 0.862655, -1.2462051,
  //     1.0560602, -0.58208317, 0.21609078, -0.05444823, 0.009177956,
  //     -9.904169E-4, 6.183535E-5, -1.6981803E-6]
  // }
  // Added in 1.0.12.
  ADDITIONAL_VIEWERS: [],

  // Select the viewer by ID. If unspecified, defaults to 'CardboardV1'.
  // Added in 1.0.12.
  DEFAULT_VIEWER: '',

  // By default, on mobile, a wakelock is necessary to prevent the device's screen
  // from turning off without user input. Disable if you're keeping the screen awake through
  // other means on mobile. A wakelock is never used on desktop.
  // Added in 1.0.3.
  MOBILE_WAKE_LOCK: true,

  // Whether or not CardboardVRDisplay is in debug mode. Logs extra
  // messages. Added in 1.0.2.
  DEBUG: false,

  // The URL to JSON of DPDB information. By default, uses the data
  // from https://github.com/WebVRRocks/webvr-polyfill-dpdb; if left
  // falsy, then no attempt is made.
  // Added in 1.0.1
  DPDB_URL: 'https://dpdb.webvr.rocks/dpdb.json',

  // Complementary filter coefficient. 0 for accelerometer, 1 for gyro.
  K_FILTER: 0.98,

  // How far into the future to predict during fast motion (in seconds).
  PREDICTION_TIME_S: 0.040,

  // Flag to disabled the UI in VR Mode.
  CARDBOARD_UI_DISABLED: false,

  // Flag to disable the instructions to rotate your device.
  ROTATE_INSTRUCTIONS_DISABLED: false,

  // Enable yaw panning only, disabling roll and pitch. This can be useful
  // for panoramas with nothing interesting above or below.
  YAW_ONLY: false,

  // Scales the recommended buffer size reported by WebVR, which can improve
  // performance.
  // UPDATE(2016-05-03): Setting this to 0.5 by default since 1.0 does not
  // perform well on many mobile devices.
  BUFFER_SCALE: 0.5,

  // Allow VRDisplay.submitFrame to change gl bindings, which is more
  // efficient if the application code will re-bind its resources on the
  // next frame anyway. This has been seen to cause rendering glitches with
  // THREE.js.
  // Dirty bindings include: gl.FRAMEBUFFER_BINDING, gl.CURRENT_PROGRAM,
  // gl.ARRAY_BUFFER_BINDING, gl.ELEMENT_ARRAY_BUFFER_BINDING,
  // and gl.TEXTURE_BINDING_2D for texture unit 0.
  DIRTY_SUBMIT_FRAME_BINDINGS: false,
};

const display = new CardboardVRDisplay(options);

function MockVRFrameData () {
  this.leftViewMatrix = new Float32Array(16);
  this.rightViewMatrix = new Float32Array(16);
  this.leftProjectionMatrix = new Float32Array(16);
  this.rightProjectionMatrix = new Float32Array(16);
  this.pose = null;
};

const frame = new (window.VRFrameData || MockVRFrameData)();

display.isConnected; // true
display.getFrameData(frame);

frame.rightViewMatrix; // Float32Array
frame.pose; // { orientation, position }
```

## Development

* `npm install`: installs the dependencies.
* `npm run build`: builds the distributable.
* `npm run watch`: watches `src/` for changes and rebuilds on change.

### Releasing a new version

For maintainers only, to cut a new release for npm, use the [npm version] command. The `preversion`, `version` and `postversion` npm scripts will run tests, build, add built files and tag to git, push to github, and publish the new npm version.

`npm version <semverstring>`

## Running The Demo

View the [example] to see a demo running the CardboardVRDisplay. This executes
a minimal WebVR 1.1 polyfill and parses query params to inject configuration parameters.
View some premade links at [index.html]. For example, to set the buffer scale to 1.0
and limit rotation to yaw, go to [https://immersive-web.github.io/cardboard-vr-display/examples/index.html?YAW_ONLY=true&BUFFER_SCALE=1.0].
View all config options at `src/options.js`.

## License

This program is free software for both commercial and non-commercial use,
distributed under the [Apache 2.0 License](LICENSE).

[VRDisplay]: https://immersive-web.github.io/webvr/spec/1.1/#interface-vrdisplay
[WebVR API]: https://immersive-web.github.io/webvr/spec/1.1/
[WebXR Device API]: https://immersive-web.github.io/webxr/spec/latest/
[webvr-polyfill]: https://github.com/immersive-web/webvr-polyfill
[example]: https://immersive-web.github.io/cardboard-vr-display/examples
[iframe-example]: examples/iframe.html
[magicwindow-example]: examples/magicwindow.html
[index.html]: https://immersive-web.github.io/cardboard-vr-display
[fusion]: http://smus.com/sensor-fusion-prediction-webvr/
[ol]: https://www.w3.org/TR/screen-orientation/
[sensors]: https://developers.google.com/web/updates/2017/09/sensors-for-the-web
[DeviceMotionEvents]: https://developer.mozilla.org/en-US/docs/Web/API/DeviceMotionEvent
[RelativeOrientationSensor]: https://www.w3.org/TR/orientation-sensor/#relativeorientationsensor-model
[Feature Policy]: https://wicg.github.io/feature-policy/
[sensors-main-frame]: https://developers.google.com/web/updates/2017/09/sensors-for-the-web#feature_policy_integration
