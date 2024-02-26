# VR

https://videojs-vr.netlify.com

[![Build Status](https://travis-ci.org/videojs/videojs-vr.svg?branch=master)](https://travis-ci.org/videojs/videojs-vr)
[![Greenkeeper badge](https://badges.greenkeeper.io/videojs/videojs-vr.svg)](https://greenkeeper.io/)
[![Slack Status](http://slack.videojs.com/badge.svg)](http://slack.videojs.com)
[![Netlify Status](https://api.netlify.com/api/v1/badges/72fb5012-1a07-433d-a286-2f4bf152c410/deploy-status)](https://app.netlify.com/sites/videojs-vr/deploys)

[![NPM](https://nodei.co/npm/videojs-vr.png?downloads=true&downloadRank=true)](https://nodei.co/npm/videojs-vr/)

A video.js plugin that turns a video element into a HTML5 Panoramic 360 video player. Project video onto different shapes. Optionally supports Oculus Rift, HTC Vive and the GearVR.

Lead Maintainer: Brandon Casey [@brandonocasey](https://github.com/brandonocasey)

Maintenance Status: Stable

<!-- START doctoc generated TOC please keep comment here to allow auto update -->
<!-- DON'T EDIT THIS SECTION, INSTEAD RE-RUN doctoc TO UPDATE -->

- [Installation](#installation)
- [Browser Support](#browser-support)
- [Caveats](#caveats)
- [Projection support](#projection-support)
- [Usage](#usage)
  - [`<script>` Tag](#script-tag)
  - [Browserify/CommonJS](#browserifycommonjs)
  - [RequireJS/AMD](#requirejsamd)
  - [Optional integration with videojs-errors](#optional-integration-with-videojs-errors)
- [Setting a global projection](#setting-a-global-projection)
  - [Passing a projection on a source by source basis](#passing-a-projection-on-a-source-by-source-basis)
- [Oculus Rift and HTC Vive Support](#oculus-rift-and-htc-vive-support)
- [Accessing the Camera Position](#accessing-the-camera-position)
- [Accessing THREE.js objects](#accessing-threejs-objects)
- [Options](#options)
  - [`forceCardboard`](#forcecardboard)
  - [`motionControls`](#motioncontrols)
  - [`projection`](#projection)
    - [`'180'`](#180)
    - [`'180_LR'`](#180_lr)
    - [`'180_MONO'`](#180_mono)
    - [`'360'`, `'Sphere'`, or `'equirectangular'`](#360-sphere-or-equirectangular)
    - [`'Cube'` or `'360_CUBE'`](#cube-or-360_cube)
    - [`'NONE'`](#none)
    - [`'AUTO'`](#auto)
    - [`'360_LR'`](#360_lr)
    - [`'360_TB'`](#360_tb)
    - [`'EAC'`](#eac)
    - [`'EAC_LR'`](#eac_lr)
  - [`sphereDetail`](#spheredetail)
  - [`player.mediainfo.projection`](#playermediainfoprojection)
  - [`debug`](#debug)
  - [`omnitone`](#omnitone)
  - [`omnitoneOptions`](#omnitoneoptions)
  - [`disableTogglePlay`](#disabletoggleplay)
- [Credits](#credits)
- [Support](#support)

<!-- END doctoc generated TOC please keep comment here to allow auto update -->

## Installation

```sh
npm i videojs-vr
```

## Browser Support
The most recent versions of:
* Desktop
  * Chrome
  * Firefox
  * Safari
* Mobile
  * Chrome on Android
  * Safari on iOS

## Caveats
* HLS captions on safari will not be visible as they are located inside of the shadowRoot in the video element and we cannot get access to them.

## Projection support
Currently we only support:
* Projections
  * Spherical Videos, via the 360/equirectangular projection
  * 360 cube videos
* Mappings
  * Monoscopic (single video pane)
  * Stereoscopic (dual video pane for both eyes) via the cardboard button
## Usage

To include videojs-vr on your website or web application, use any of the following methods.

### `<script>` Tag

This is the simplest case. Get the script in whatever way you prefer and include the plugin _after_ you include [video.js][videojs], so that the `videojs` global is available.

```html
<script src="//path/to/video.min.js"></script>
<script src="//path/to/videojs-vr.min.js"></script>
<script>
  var player = videojs('my-video');

  player.vr();
</script>
```

### Browserify/CommonJS

When using with Browserify, install videojs-vr via npm and `require` the plugin as you would any other module.

```js
var videojs = require('video.js');

// The actual plugin function is exported by this module, but it is also
// attached to the `Player.prototype`; so, there is no need to assign it
// to a variable.
require('videojs-vr');

var player = videojs('my-video');

player.vr({projection: '360'});
```

### RequireJS/AMD

When using with RequireJS (or another AMD library), get the script in whatever way you prefer and `require` the plugin as you normally would:

```js
require(['video.js', 'videojs-vr'], function(videojs) {
  var player = videojs('my-video');

  player.vr({projection: '360'});
});
```

### Optional integration with videojs-errors
If the [videojs-errors](https://github.com/brightcove/videojs-errors) plugin is intialized before `videojs-vr`, then it will be used to display errors to users.

## Setting a global projection
If you are only going to be playing 360 videos you can set the global plugin projection like so:

```js

var player = videojs('my-video');

player.vr({projection: '360'});

// or change player.vr.defaultProjection
// and call player.vr.initScene again

```

### Passing a projection on a source by source basis
Set `player.mediainfo` and `player.mediainfo.projection` to a valid projection value and pass in 'AUTO' or nothing for the `projection` key when initializing this plugin.
EX:
```js
var player = videojs('my-video');

if (!player.mediainfo) {
  player.mediainfo = {};
}

if (!player.mediainfo.projection) {
  player.mediainfo.projection = '360';
}

player.vr({projection: 'AUTO'});

// or player.vr(); since 'AUTO' is the default
```

## Oculus Rift and HTC Vive Support
This project leverages the [webvr-polyfill](https://github.com/borismus/webvr-polyfill) and [three.js](https://github.com/mrdoob/three.js) libraries to create a 'responsive VR' experience across multiple devices.

Oculus Rift and HTC Vive playback requires Firefox >= 55, experimental WebVR-enabled builds of Chromium, or via Chrome by enabling webvr in `chrome://flags`. Go to [WebVR.info](http://www.webvr.info) for more info.

GearVR playback requires the latest Samsung Internet for Gear VR with WebVR support enabled. Go [here](https://webvr.rocks/samsung_internet) for more info.

## Accessing the Camera Position
The Three.js rotation values are exposed under the property `cameraVector` on the `vr` plugin namespace.

```js
var player = videojs('my-video');

player.vr().cameraVector;
```

## Accessing THREE.js objects
The Three.js Scene, renderer, and perspective camera are exposed under the `threeJs` object as the properties `scene`, `renderer`, and `camera` on the `vr` plugin namespace.

```js
var player = videojs('my-video');

player.vr().camera;
player.vr().scene;
player.vr().renderer;
```

## Options
### `forceCardboard`
> Type: `boolean`, default: `false`

Force the cardboard button to display on all devices even if we don't think they support it.

### `motionControls`
> Type: `boolean`, default: `true on ios and andriod`

Whether motion/gyro controls should be enabled.

### `projection`

> Type `string`, default: `'auto'`
Can be any of the following:

#### `'180'`
The video is half sphere and the user should not be able to look behind themselves

#### `'180_LR'`
Used for side-by-side 180 videos
The video is half sphere and the user should not be able to look behind themselves

#### `'180_MONO'`
Used for monoscopic 180 videos
The video is half sphere and the user should not be able to look behind themselves

#### `'360'`, `'Sphere'`, or `'equirectangular'`
The video is a sphere

#### `'Cube'` or `'360_CUBE'`
The video is a cube

#### `'NONE'`
This video is not a 360 video

#### `'AUTO'`
Check `player.mediainfo.projection` to see if the current video is a 360 video.

#### `'360_LR'`
Used for side-by-side 360 videos

#### `'360_TB'`
Used for top-to-bottom 360 videos

#### `'EAC'`
Used for Equi-Angular Cubemap videos

#### `'EAC_LR'`
Used for side-by-side Equi-Angular Cubemap videos

### `sphereDetail`

> type: `number`, default: `32`

This alters the number of segments in the spherical mesh onto which equirectangular
videos are projected. The default is `32` but in some circumstances you may notice
artifacts and need to increase this number.

### `player.mediainfo.projection`

> type: `string`

This should be set on a source-by-source basis to turn 360 videos on an off depending upon the video.

See [`projection`](#projection) above for information of values. Note that `AUTO` is the same as `NONE` for `player.mediainfo.projection`.

### `debug`

> type: `boolean`, default: `false`

Enable debug logging for this plugin

### `omnitone`

> type: `Omnitone library object`

Use this property to pass the Omnitone library object to the plugin.
Please be aware of, the Omnitone library is not included in the build files.

### `omnitoneOptions`

> type: `object`, default: `{}`

Default options for the Omnitone library. Please check available options on https://github.com/GoogleChrome/omnitone

### `disableTogglePlay`

> type: `boolean`, default: `false`

Feature to disable the togglePlay manually.
This functionality is useful in live events so that users cannot stop the live, but still have a controlBar available.

## Credits ##

This project is a conglomeration of a few amazing open source libraries.

* [VideoJS](http://www.videojs.com)
* [Three.js](http://threejs.org)
* [webvr-polyfill](https://github.com/borismus/webvr-polyfill)
* [Omnitone](https://googlechrome.github.io/omnitone)

## Support ##
This work is sponsored by [Brightcove](https://www.brightcove.com), [HapYak](http://corp.hapyak.com/) and [StreamShark](https://streamshark.io)
