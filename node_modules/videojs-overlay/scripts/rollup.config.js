const generate = require('videojs-generate-rollup-config');

// see https://github.com/videojs/videojs-generate-rollup-config
// for options
const options = {
  input: 'src/index.js'
};

// Generate the original for plugin
const indexConfig = generate(options);

// Config for file that exports plugin without registering it
const pluginOnlyConfig = {
  watch: { clearScreen: false },
  input: 'src/plugin.js',
  external: indexConfig.builds.module.external,
  output: [
    {
      file: './dist/videojs-overlay.plugin.js',
      format: 'umd',
      name: indexConfig.settings.exportName,
      banner: indexConfig.settings.banner,
      globals: { 'video.js': 'videojs', 'global/window': 'window' }
    }
  ],
  plugins: indexConfig.plugins
};

// Add additonal builds/customization here!

const configs = Object.values(indexConfig.builds);

configs.push(pluginOnlyConfig);

// export the builds to rollup
export default configs;
