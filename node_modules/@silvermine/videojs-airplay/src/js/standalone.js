/**
 * This module is used as an entry point for the build system to bundle this plugin into a
 * single javascript file that can be loaded by a script tag on a web page. The javascript
 * file that is built assumes that `videojs` is available globally at `window.videojs`, so
 * Video.js must be loaded **before** this plugin is loaded.
 *
 * Run `npm install` and then `grunt build` to build the plugin's bundled javascript
 * file, as well as the CSS and image assets into the project's `./dist/` folder.
 *
 * @module standalone
 */

require('./index')();
