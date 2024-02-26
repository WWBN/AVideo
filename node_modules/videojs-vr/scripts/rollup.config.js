const generate = require('videojs-generate-rollup-config');

// see https://github.com/videojs/videojs-generate-rollup-config
// for options
const config = generate({});

// Add additional builds/customization here!

// export the builds to rollup
export default Object.values(config.builds);
