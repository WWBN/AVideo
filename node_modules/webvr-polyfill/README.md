# WebVR Polyfill

[![Build Status](http://img.shields.io/travis/immersive-web/webvr-polyfill.svg?style=flat-square)](https://travis-ci.org/immersive-web/webvr-polyfill)
[![Build Status](http://img.shields.io/npm/v/webvr-polyfill.svg?style=flat-square)](https://www.npmjs.org/package/webvr-polyfill)

A JavaScript implementation of the [WebVR spec][spec]. This project ensures
your WebVR content works on any platform, whether or not the browser/device has
native WebVR support, or when there are inconsistencies in implementation.

Take a look at [basic WebVR samples][samples] that use this polyfill.

## Installing

### Script

Download the build at [build/webvr-polyfill.js](build/webvr-polyfill.js) and include it as a script tag,
or use a CDN. You can also use the minified file in the same location as `webvr-polyfill.min.js`.

```html
  <script src='webvr-polyfill.js'></script>
  <!-- or use a link to a CDN -->
  <script src='https://cdn.jsdelivr.net/npm/webvr-polyfill@latest/build/webvr-polyfill.js'></script>
```

### npm

If you're using a build tool like [browserify] or [webpack], install it via [npm].

```
$ npm install --save webvr-polyfill
```

## Using

Instructions for using versions `>=0.10.0`. For `<=0.9.x` versions, see [0.9.40 tag](https://github.com/immersive-web/webvr-polyfill/tree/v0.9.40).

The webvr-polyfill exposes a single constructor, `WebVRPolyfill` that takes an
object for configuration. See full configuration options at [src/config.js](src/config.js).

Be sure to instantiate the polyfill before calling any of your VR code! The
polyfill needs to patch the API if it does not exist so your content code can
assume that the WebVR API will just work.

If using script tags, a `WebVRPolyfill` global constructor will exist.

```js
var polyfill = new WebVRPolyfill();
```

In a modular ES6 world, import and instantiate the constructor similarly.

```js
import WebVRPolyfill from 'webvr-polyfill';
const polyfill = new WebVRPolyfill();
```

Here's an example of querying displays and setting up controls based on
environment. Remember, you'll still need to provide controls and code
to support a desktop-like experience if no native VRDisplays are found,
as the CardboardVRDisplay is only on mobile. See the [example](examples/index.html).

```js
// Polyfill always provides us with `navigator.getVRDisplays`
navigator.getVRDisplays().then(displays => {
  // If we have a native VRDisplay, or if the polyfill
  // provided us with a CardboardVRDisplay, use it
  if (displays.length) {
    vrDisplay = displays[0];
    controls = new THREE.VRControls(camera);
    vrDisplay.requestAnimationFrame(animate);
  } else {
    // If we don't have a VRDisplay, we're probably on
    // a desktop environment, so set up desktop-oriented controls
    controls = new THREE.OrbitControls(camera);
    requestAnimationFrame(animate);
  }
});
```

### iframes

There are some concerns and caveats when embedding polyfilled WebVR content inside iframes. [More information is documented in the cardboard-vr-display README](https://github.com/immersive-web/cardboard-vr-display#iframes).

## Goals

The polyfill's goal is to provide a library so that developers can create
content targeting the WebVR API without worrying about what browsers and devices
their users have in a world of growing, [but fragmented](caniuse) support.

The three main components of the polyfill are:

* Injects a [WebVR 1.1](spec) JavaScript implementation if one does not exist
* Patches browsers that have an incomplete or inconsistent implementation of the API
* Provide a synthesized [CardboardVRDisplay] on mobile when WebVR is not supported, or if it does have native support but no native VRDisplays and `PROVIDE_MOBILE_VRDISPLAY` is true (default).

## Performance

Performance is critical for VR. If you find your application is too sluggish,
consider tweaking some of the above parameters. In particular, keeping
`BUFFER_SCALE` at 0.5 (the default) will likely help a lot.

## Developing

If you're interested in developing and contributing on the polyfill itself, you'll need to
have [npm] installed and familiarize yourself with some commands below. For full list
of commands available, see `package.json` scripts.

```
$ git clone git@github.com:immersive-web/webvr-polyfill.git
$ cd webvr-polyfill/

# Install dependencies
$ npm install

# Build uncompressed JS file
$ npm run build

# Run tests
$ npm test

# Watch src/* directory and auto-rebuild on changes
$ npm watch
```

### Testing

Right now there are some unit tests in the configuration and logic for how things get polyfilled.
Be sure to run tests before submitting any PRs, and bonus points for having new tests!

```
$ npm test
```

Due to the nature of the polyfill, be also sure to test the examples with your changes where appropriate.

### Releasing a new version

For maintainers only, to cut a new release for npm, use the [npm version] command. The `preversion`, `version` and `postversion` npm scripts will run tests, build, add built files and tag to git, push to github, and publish the new npm version.

`npm version <semverstring>`

## License

This program is free software for both commercial and non-commercial use,
distributed under the [Apache 2.0 License](LICENSE).

[samples]: https://webvr.info/samples/
[npm]: https://www.npmjs.com
[browserify]: http://browserify.org/
[webpack]: https://webpack.github.io/
[caniuse]: https://caniuse.com/#search=webvr
[spec]: https://immersive-web.github.io/webvr/spec/1.1
[CardboardVRDisplay]: https://github.com/immersive-web/cardboard-vr-display
