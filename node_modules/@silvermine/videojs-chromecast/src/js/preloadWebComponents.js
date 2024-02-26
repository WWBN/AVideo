function doesUserAgentContainString(str) {
   return typeof window.navigator.userAgent === 'string' && window.navigator.userAgent.indexOf(str) >= 0;
}

// For information as to why this is needed, please see:
// https://github.com/silvermine/videojs-chromecast/issues/17
// https://github.com/silvermine/videojs-chromecast/issues/22

module.exports = function() {
   var needsWebComponents = !document.registerElement,
       iosChrome = doesUserAgentContainString('CriOS'),
       androidChrome;

   androidChrome = doesUserAgentContainString('Android')
      && doesUserAgentContainString('Chrome/')
      && window.navigator.presentation;

   // These checks are based on the checks found in `cast_sender.js` which
   // determine if `cast_framework.js` needs to be loaded
   if ((androidChrome || iosChrome) && needsWebComponents) {
      // This is requiring webcomponents.js@0.7.24 because that's what was used
      // by the Chromecast framework at the time this was added.
      // We are using webcomponents-lite.js because it doesn't interfere with jQuery as
      // badly (e.g. it doesn't interfere with jQuery's fix for consistently bubbling
      // events, see #21). While the "lite" version does not include the shadow DOM
      // polyfills that the Chromecast framework may need for the <google-cast-button>
      // component to work properly, this plugin does not use the <google-cast-button>
      // component.
      require('webcomponents.js/webcomponents-lite.js'); // eslint-disable-line global-require
   }
};
