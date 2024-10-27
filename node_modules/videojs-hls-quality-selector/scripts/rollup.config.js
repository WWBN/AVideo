const generate = require('videojs-generate-rollup-config');

// see https://github.com/videojs/videojs-generate-rollup-config
// for options
const options = {};
const config = generate(options);

// Add additonal builds/customization here!

// do not build module dists with rollup
// this is handled by build:es and build:cjs
if (config.builds.module) {
  delete config.builds.module;
}

// export the builds to rollup
export default Object.values(config.builds);
