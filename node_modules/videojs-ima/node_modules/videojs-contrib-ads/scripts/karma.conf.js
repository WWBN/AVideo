const generate = require('videojs-generate-karma-config');

module.exports = function(config) {

  // see https://github.com/videojs/videojs-generate-karma-config
  // for options
  const options = {
    preferHeadless: false,
    serverBrowser(defaults) {
      return ['autoplayDisabledChrome'];
    },
    travisLaunchers(defaults) {
      defaults.travisChrome.flags.push('--autoplay-policy=no-user-gesture-required');

      return defaults;
    },
    customLaunchers(defaults) {
      return Object.assign(defaults, {
        autoplayDisabledChrome: {
          base: 'Chrome',
          flags: ['--autoplay-policy=no-user-gesture-required']
        }
      });
    },
    browsers(browsers) {
      if (process.env.TRAVIS) {
        return browsers;
      }
      const toKeep = ['Firefox', 'Chrome'];
      const filteredBrowsers = [];

      browsers.forEach((e) => {
        if (e === 'Chrome') {
          filteredBrowsers.push('autoplayDisabledChrome');
        } else if (toKeep.indexOf(e) !== -1) {
          filteredBrowsers.push(e);
        }
      });

      return filteredBrowsers;
    },
    files(defaults) {
      return [
        'node_modules/video.js/dist/video-js.css',
        'node_modules/lodash/lodash.js',
        'node_modules/sinon/pkg/sinon.js',
        'node_modules/video.js/dist/video.js',
        'dist/videojs-contrib-ads.js',
        'dist/videojs-contrib-ads.css',
        'test/dist/bundle.js'
      ];
    }
  };

  config = generate(config, options);

  // any other custom stuff not supported by options here!
};
