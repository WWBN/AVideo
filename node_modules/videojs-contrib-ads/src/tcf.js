import window from 'global/window';

import videojs from 'video.js';

/**
 * Current tcfData returned from CMP
 * Updated on event listener rather than having to make an asyc
 * check within the macro resolver
 */
export let tcData = {};

/**
 * Sets up a proxy for the TCF API in an iframed player, if a parent frame
 * that has implemented the TCF API is detected
 * https://github.com/InteractiveAdvertisingBureau/GDPR-Transparency-and-Consent-Framework/blob/master/TCFv2/IAB%20Tech%20Lab%20-%20CMP%20API%20v2.md#is-there-a-sample-iframe-script-call-to-the-cmp-api
 */
const proxyTcfApi = _ => {
  if (videojs.dom.isInFrame() && typeof window.__tcfapi !== 'function') {
    let frame = window;
    let cmpFrame;
    const cmpCallbacks = {};

    while (frame) {
      try {
        if (frame.frames.__tcfapiLocator) {
          cmpFrame = frame;
          break;
        }
      } catch (ignore) {
        // empty
      }
      if (frame === window.top) {
        break;
      }
      frame = frame.parent;
    }

    if (!cmpFrame) {
      return;
    }

    window.__tcfapi = function(cmd, version, callback, arg) {
      const callId = Math.random() + '';
      const msg = {
        __tcfapiCall: {
          command: cmd,
          parameter: arg,
          version,
          callId
        }
      };

      cmpCallbacks[callId] = callback;
      cmpFrame.postMessage(msg, '*');
    };

    window.addEventListener('message', function(event) {
      let json = {};

      try {
        json = typeof event.data === 'string' ? JSON.parse(event.data) : event.data;
      } catch (ignore) {
        // empty
      }

      const payload = json.__tcfapiReturn;

      if (payload) {
        if (typeof cmpCallbacks[payload.callId] === 'function') {
          cmpCallbacks[payload.callId](payload.returnValue, payload.success);
          cmpCallbacks[payload.callId] = null;
        }
      }
    }, false);
  }
};

/**
 * Sets up event listener for changes to consent data.
 */
export const listenToTcf = () => {
  proxyTcfApi();

  if (typeof window.__tcfapi === 'function') {
    window.__tcfapi('addEventListener', 2, (data, success) => {
      if (success) {
        tcData = data;
      }
    });
  }
};
