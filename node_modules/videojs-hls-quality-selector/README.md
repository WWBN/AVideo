# videojs-hls-quality-selector
[![CircleCI](https://circleci.com/gh/chrisboustead/videojs-hls-quality-selector/tree/master.svg?style=svg)](https://circleci.com/gh/chrisboustead/videojs-hls-quality-selector/tree/master)
[![npm version](https://badge.fury.io/js/videojs-hls-quality-selector.svg)](https://badge.fury.io/js/videojs-hls-quality-selector)

**Note:** 
- v1.2.0 is compatible with videojs 8
- v1.x.x is Only compatible with VideoJS 7.x due to the move from `videojs-contrib-hls` to `videojs/http-streaming`.  For VideoJS v5 or v6 support please use a `v0.x.x` tag

## Description

Adds a quality selector menu for HLS sources played in videojs.

Any HLS manifest with multiple playlists/renditions should be selectable from within the added control.  

**Native HLS**

Does not yet support browsers using native HLS (Safari, Edge, etc).  To enable plugin in browsers with native HLS, you must force non-native HLS playback:

## Options

**displayCurrentQuality** `boolean` - _false_

Set to true to display the currently selected resolution in the menu button.  When not enabled, displayed an included VJS "HD" icon.

**placementIndex** `integer`

Set this to override the default positioning of the menu button in the control bar relative to the other components in the control bar.

**vjsIconClass** `string` - _"vjs-icon-hd"_

Set this to one of the custom VJS icons ([https://videojs.github.io/font/](https://videojs.github.io/font/)) to override the icon for the menu button. 


## Methods

**getCurrentQuality** `string` - _'auto'__

Return the current set quality or 'auto'


## Screenshots

Default setup - Menu selected:
![Example](example.png)


Display Current Quality option enabled:
![Example](example-2.png)

## Table of Contents

<!-- START doctoc -->
<!-- END doctoc -->
## Installation

```sh
npm install --save videojs-hls-quality-selector
```

## Usage

To include videojs-hls-quality-selector on your website or web application, use any of the following methods.

### `<script>` Tag

This is the simplest case. Get the script in whatever way you prefer and include the plugin _after_ you include [video.js][videojs], so that the `videojs` global is available.

```html
<script src="//path/to/video.min.js"></script>
<script src="//path/to/videojs-hls-quality-selector.min.js"></script>
<script>
  var player = videojs('my-video');

  player.hlsQualitySelector();
</script>
```

### Browserify/CommonJS

When using with Browserify, install videojs-hls-quality-selector via npm and `require` the plugin as you would any other module.

```js
var videojs = require('video.js');

// The actual plugin function is exported by this module, but it is also
// attached to the `Player.prototype`; so, there is no need to assign it
// to a variable.
require('videojs-hls-quality-selector');

var player = videojs('my-video');

player.hlsQualitySelector({
    displayCurrentQuality: true,
});
```

### RequireJS/AMD

When using with RequireJS (or another AMD library), get the script in whatever way you prefer and `require` the plugin as you normally would:

```js
require(['video.js', 'videojs-hls-quality-selector'], function(videojs) {
  var player = videojs('my-video');

  player.hlsQualitySelector();
});
```

## License

MIT. Copyright (c) Chris Boustead (chris@forgemotion.com)


[videojs]: http://videojs.com/
