# Silvermine Video.js AirPlay Plugin

<!-- markdownlint-disable line-length -->
[![Build Status](https://travis-ci.org/silvermine/videojs-airplay.svg?branch=master)](https://travis-ci.org/silvermine/videojs-airplay)
[![Coverage Status](https://coveralls.io/repos/github/silvermine/videojs-airplay/badge.svg?branch=master)](https://coveralls.io/github/silvermine/videojs-airplay?branch=master)
[![Dependency Status](https://david-dm.org/silvermine/videojs-airplay.svg)](https://david-dm.org/silvermine/videojs-airplay)
[![Dev Dependency Status](https://david-dm.org/silvermine/videojs-airplay/dev-status.svg)](https://david-dm.org/silvermine/videojs-airplay#info=devDependencies&view=table)
<!-- markdownlint-enable line-length -->

## What is it?

A plugin for [Video.js](http://videojs.com/) versions 6+ that adds a button to the control
bar that will open the AirPlay menu if it is available on the user's device.

_NOTE: there is a [`videojs-airplay`](https://www.npmjs.com/package/videojs-airplay)
package that is in no way associated with `@silvermine/videojs-airplay`. The
`videojs-airplay` module appears to only support VideoJS version 5.x, whereas our
`@silvermine/videojs-airplay` module supports VideoJS 6.x._

## How do I use it?

The `@silvermine/videojs-airplay` plugin includes 3 types of assets: javascript, CSS and
images.

You can either build the plugin locally and use the assets that are output from the build
process directly, or you can install the plugin as an npm module, include the
javascript and SCSS source in your project using a Common-JS module loader and SASS build
process, and copy the images from the image source folder to your project.

### Building the plugin locally

   1. Either clone this repository or install the `@silvermine/videojs-airplay` module
      using `npm install @silvermine/videojs-airplay`.
   2. Ensure that the project's `devDependencies` are installed by running `npm install`
      from within the folder you cloned or installed the project.
   3. Run `grunt build` to build and copy the javascript, CSS and image files to the
      `dist` folder.
   4. Copy the plugin's files from the `dist` folder into your project as needed.
   5. Ensure that the images in the `dist/images` folder are accessible at `./images/`,
      relative to where the plugin's CSS is located. If, for example, your CSS is located
      at `https://example.com/plugins/silvermine-videojs-airplay.css`, then the plugin's
      images should be located at `https://example.com/plugins/images/`.

Note: when adding the plugin's javascript to your web page, include the `silvermine-
videojs-airplay.min.js` javascript file in your HTML _after_ loading Video.js. The
plugin's built javascript file expects there to be a reference to Video.js at
`window.videojs` and will throw an error if it does not exist.

After both Video.js and `@silvermine/videojs-airplay` have loaded, follow the steps in the
"Configuration" section below.


### Configuration

Once the plugin has been loaded and registered, add it to your Video.js player using
Video.js' plugin configuration option (see the section under the heading "Setting up a
Plugin" on [Video.js' plugin documentation page][videojs-docs]. Use these options to
configure the plugin:

   * **`plugins.airPlay.addButtonToControlBar`** - a `boolean` flag that tells the plugin
     whether or not it should automatically add the AirPlay button to the Video.js
     player's control bar component. Defaults to `true`.
   * **`plugins.airPlay.buttonPositionIndex`** - a zero-based number specifying the index
     of the AirPlay button among the control bar's child components (if
     `addButtonToControlBar` is set to `true`). By default the AirPlay Button is added as
     the last child of the control bar. A value less than 0 puts the button at the
     specified position from the end of the control bar. Note that it's likely not all
     child components of the control bar are visible.
   * **`plugins.airPlay.addAirPlayLabelToButton`** (default: `false`) - by default, the
     AirPlay button component will display only an icon. Setting `addAirPlayLabelToButton`
     to `true` will display a label titled `"AirPlay"` alongside the default icon.

For example:

```js
var options;

options = {
   controls: true,
   plugins: {
      airPlay: {
         addButtonToControlBar: false, // defaults to `true`
      }
   }
};

videojs(document.getElementById('myVideoElement'), options);
```

Even though there are no configuration options, to enable the plugin you must either
provide an `airPlay` entry in the `plugins` option as shown above or you must call the
`airPlay` plugin function manually:

```js
var player = videojs(document.getElementById('myVideoElement'));

player.airPlay(); // initializes the AirPlay plugin
```

#### Localization

The `AirPlayButton` component has two translated strings: "Start AirPlay" and "AirPlay".

   * The "Start AirPlay" string appears in both of the standard places for Button
     component text: inside the `.vjs-control-text` span and as the `<button>` element's
     `title` attribute.
   * The "AirPlay" string appears in an optional label within the Button component: inside
     the `.vjs-airplay-button-label` span.

To localize the AirPlay button text, follow the steps in the [Video.js Languages
tutorial][videojs-docs] to add `"Start AirPlay"` and `"AirPlay"` keys to the map of
translation strings.

### Using the npm module

If you are using a module loader such as Browserify or Webpack, first install
`@silvermine/videojs-airplay` using `npm install`. Then, use
`require('@silvermine/videojs-airplay')` to require `@silvermine/videojs-airplay` into
your project's source code. `require('@silvermine/videojs-airplay')` returns a function
that you can use to register the plugin with videojs by passing in a reference to
`videojs`:

```js
   var videojs = require('video.js');

   // Initialize the AirPlay plugin
   require('@silvermine/videojs-airplay')(videojs);
```

Then, follow the steps in the "Configuration" section above.

> [!WARNING]
> This plugin's source code uses ES6+ syntax and keywords, such as `class` and `static`.
> If you need to support [browsers that do not support newer JavaScript
> syntax](https://caniuse.com/es6), you will need to use a tool like
> [Babel](https://babeljs.io/) to transpile and polyfill your code.
>
> Alternatively, you can
> `require('@silvermine/videojs-airplay/dist/silvermine-videojs-airplay.js')`
> to use a JavaScript file that has already been polyfilled/transpiled down to ES5
> compatibility.

### Using the CSS and images

If you are using SCSS in your project, you can simply reference the plugin's main SCSS
file in your project's SCSS:

```scss
@import "path/to/node_modules/@silvermine/videojs-airplay/src/scss/videojs-airplay";
```

Optionally, you can override the SCSS variables that contain the paths to the icon
image files:

   * **`$icon-airplay--default`** - the path to the icon image that is displayed when the
     AirPlay button is in its normal, default state. Defaults to
     `"images/ic_airplay_white_24px.svg"`.
   * **`$icon-airplay--hover`** - the path to the icon image that is displayed when the
     user hovers over the AirPlay button. Defaults to
     `"images/ic_airplay_white_24px.svg"`.
   * **`$airplay-icon-size`** - the width and height of the icon (the button and icon is a
     square). Defaults to `12px`.

#### Images

The plugin's images are located at `@silvermine/videojs-airplay/src/images`. If you have
not overridden the icon image path variables in the SCSS, then copy the images from the
`src/images` folder to a folder that is accessible at `./images/`, relative to where the
plugin's CSS is located. If, for example, your CSS is located at
`https://example.com/plugins/silvermine-videojs-airplay.css`, then the plugin's images
should be located at `https://example.com/plugins/images/`.


## How do I contribute?

We genuinely appreciate external contributions. See [our extensive
documentation][contributing] on how to contribute.


## License

This software is released under the MIT license. See [the license file](LICENSE) for more
details.

[videojs-docs]: http://docs.videojs.com/tutorial-plugins.html
[contributing]: https://github.com/silvermine/silvermine-info#contributing
