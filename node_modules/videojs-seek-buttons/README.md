# videojs-seek-buttons

Plugin for video.js to add seek buttons to the control bar. These buttons allow the user to skip forward or back by a configured number of seconds.

## Table of Contents

<!-- START doctoc generated TOC please keep comment here to allow auto update -->
<!-- DON'T EDIT THIS SECTION, INSTEAD RE-RUN doctoc TO UPDATE -->


- [Installation](#installation)
- [Options](#options)
  - [Control position](#control-position)
- [Usage](#usage)
  - [`<script>` Tag](#script-tag)
  - [Browserify/CommonJS](#browserifycommonjs)
  - [RequireJS/AMD](#requirejsamd)
- [License](#license)

<!-- END doctoc generated TOC please keep comment here to allow auto update -->
## Installation

Version 3.x requires video.js version 6.x or 7.x to be installed as a peer dependency (latest v7 is recommended).

```sh
npm install videojs-seek-buttons@latest7
```

Version 4.x requires video.js version 8.x to be installed as a peer dependency. Earlier versions of Video.js are not supported.

```sh
npm install videojs-seek-buttons@latest8
```

However Video.js 8.2.0 + has a [built-in seek buttons functionality](https://videojs.com/guides/options/#skipbuttons). Consider using that instead of this plugin.

## Options

- `forward` - if a number greater than 0, a seek forward button will be added which seeks that number of seconds
- `back` - if a number greater than 0, a seek back button will be added which seeks that number of seconds
- `forwardIndex` - the position in the control bar to insert the button. Defaults to `1`. See note below.
- `backIndex` - the position in the control bar to insert the button. Defaults to `1`. See note below.

### Control position

`forwardIndex` and `backIndex` set the posiiton of the button in the control bar. Note if both a back and forward button are used, the forward button is inserted first.

Assuming the standard control bar, the play button is at index `0`. With the default index of `1` for both, the forward button is inserted after the play button, then the back button is inserted after the play button and before the forward button. Setting `backIndex` to `0` would place the back button before the play button instead, so they surround the play button.

## Usage

To include videojs-seek-buttons on your website or web application, use any of the following methods to include the script.

You also need to include the plugin's CSS.

### `<script>` Tag

This is the simplest case. Get the script in whatever way you prefer and include the plugin _after_ you include [video.js][videojs], so that the `videojs` global is available.

```html
<link rel="stylesheet" href="//path/to/video-js.css">
<link rel="stylesheet" href="//path/to/videojs-seek-buttons.css">
<script src="//path/to/video.min.js"></script>
<script src="//path/to/videojs-seek-buttons.min.js"></script>
<script>
  var player = videojs('my-video');

  player.seekButtons({
    forward: 30,
    back: 10
  });


// You could alternatively include the plugin in the setup options, e.g.
// var player = videojs('my-video', {
//   plugins: {
//     seekButtons: {
//       forward: 30,
//       back: 10
//     }
//   }
// });

</script>
```

The dist versions will be available from services which host npm packages such as jsdelivr:

* https://cdn.jsdelivr.net/npm/videojs-seek-buttons/dist/videojs-seek-buttons.min.js
* https://cdn.jsdelivr.net/npm/videojs-seek-buttons/dist/videojs-seek-buttons.css

### Browserify/CommonJS

When using with Browserify, install videojs-seek-buttons via npm and `require` the plugin as you would any other module.
Make sure if using React to also `include "videojs-seek-buttons/dist/videojs-seek-buttons.css"`, otherwise the icons will not appear in the control bar.

```js
var videojs = require('video.js');

// The actual plugin function is exported by this module, but it is also
// attached to the `Player.prototype`; so, there is no need to assign it
// to a variable.
require('videojs-seek-buttons');

var player = videojs('my-video');

player.seekButtons({
    forward: 30,
    back: 10
  });
```

### RequireJS/AMD

When using with RequireJS (or another AMD library), get the script in whatever way you prefer and `require` the plugin as you normally would:

```js
require(['video.js', 'videojs-seek-buttons'], function(videojs) {
  var player = videojs('my-video');

  player.seekButtons({
    forward: 30,
    back: 10
  });
});
```

## License

Apache-2.0. Copyright (c) mister-ben &lt;git@misterben.me&gt;


[videojs]: http://videojs.com/
