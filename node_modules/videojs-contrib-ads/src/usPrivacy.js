import window from 'global/window';

const findUspApiLocatorWindow = (windowObj) => {
  let targetWindow = windowObj.parent;

  while (targetWindow !== windowObj.top) {
    try {
      if (targetWindow.frames && targetWindow.frames.__uspapiLocator) {
        return targetWindow;
      }
    } catch (ignore) {
      // do nothing
    }

    targetWindow = targetWindow.parent;
  }

  // Check for the __uspapiLocator frame in the top window
  try {
    if (windowObj.top.frames && windowObj.top.frames.__uspapiLocator) {
      return windowObj.top;
    }
  } catch (ignore) {
    // do nothing
  }

  // Return null if no __uspapiLocator frame is found in any window
  return null;
};

let uspString = '';

export const getCurrentUspString = () => uspString;

// Call the USP API to get the US Privacy String, either by invoking it directly or via postMessage() if inside an iframe.
// In the former case the callback is synchronous, if the latter it is asynchronous, so to be safe it should always be
// assumed to be asynchronous.
// The window is passable as an argument for ease of testing
export const obtainUsPrivacyString = (callback, windowObj = window) => {
  if (windowObj.__uspapi) {
    windowObj.__uspapi('getUSPData', 1, (uspData, success) => {
      const privacyString = success ? uspData.uspString : null;

      uspString = privacyString;

      callback(privacyString);
    });
  } else {
    const targetWindow = findUspApiLocatorWindow(windowObj);

    // If no __uspapiLocator frame is found, execute the callback with a null privacy string
    if (!targetWindow) {
      callback(null);
      return;
    }

    const uniqueId = Math.random().toString(36).substring(2);
    const message = {
      __uspapiCall: {
        command: 'getUSPData',
        version: 1,
        callId: uniqueId
      }
    };

    const handleMessageEvent = (event) => {
      if (
        event &&
        event.data &&
        event.data.__uspapiReturn &&
        event.data.__uspapiReturn.callId === uniqueId
      ) {
        windowObj.removeEventListener('message', handleMessageEvent, false);

        const {returnValue, success} = event.data.__uspapiReturn;
        const privacyString = success ? returnValue.uspString : null;

        uspString = privacyString;

        callback(privacyString);
      }
    };

    windowObj.addEventListener('message', handleMessageEvent, false);

    targetWindow.postMessage(message, '*');
  }
};
