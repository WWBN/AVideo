"use strict";
(function(factory){
  /*!
   * Custom Universal Module Definition (UMD)
   *
   * Video.js will never be a non-browser lib so we can simplify UMD a bunch and
   * still support requirejs and browserify. This also needs to be closure
   * compiler compatible, so string keys are used.
   */
  if (typeof define === 'function' && define['amd']) {
    define(['./video'], function(vjs){ factory(window, document, vjs) });
  // checking that module is an object too because of umdjs/umd#35
  } else if (typeof exports === 'object' && typeof module === 'object') {
    factory(window, document, require('video.js'));
  } else if(typeof videojs != 'undefined'){
    factory(window, document, videojs);
  }

})(function(window, document, vjs) {
  //cookie functions from https://developer.mozilla.org/en-US/docs/DOM/document.cookie
  var
  getCookieItem = function(sKey) {
    if (!sKey || !hasCookieItem(sKey)) { return null; }
    var reg_ex = new RegExp(
      "(?:^|.*;\\s*)" +
      window.escape(sKey).replace(/[\-\.\+\*]/g, "\\$&") +
      "\\s*\\=\\s*((?:[^;](?!;))*[^;]?).*"
    );
    return window.unescape(document.cookie.replace(reg_ex,"$1"));
  },

  setCookieItem = function(sKey, sValue, vEnd, sPath, sDomain, bSecure) {
    if (!sKey || /^(?:expires|max\-age|path|domain|secure)$/i.test(sKey)) { return; }
    var sExpires = "";
    if (vEnd) {
      switch (vEnd.constructor) {
        case Number:
          sExpires = vEnd === Infinity ? "; expires=Tue, 19 Jan 2038 03:14:07 GMT" : "; max-age=" + vEnd;
          break;
        case String:
          sExpires = "; expires=" + vEnd;
          break;
        case Date:
          sExpires = "; expires=" + vEnd.toGMTString();
          break;
      }
    }
    document.cookie =
      window.escape(sKey) + "=" +
      window.escape(sValue) +
      sExpires +
      (sDomain ? "; domain=" + sDomain : "") +
      (sPath ? "; path=" + sPath : "") +
      (bSecure ? "; secure" : "");
  },

  hasCookieItem = function(sKey) {
    return (new RegExp(
      "(?:^|;\\s*)" +
      window.escape(sKey).replace(/[\-\.\+\*]/g, "\\$&") +
      "\\s*\\=")
    ).test(document.cookie);
  },

  hasLocalStorage = function() {
    try {
      window.localStorage.setItem('persistVolume', 'persistVolume');
      window.localStorage.removeItem('persistVolume');
      return true;
    } catch(e) {
      return false;
    }
  },
  getStorageItem = function(key) {
    return hasLocalStorage() ? window.localStorage.getItem(key) : getCookieItem(key);
  },
  setStorageItem = function(key, value) {
    return hasLocalStorage() ? window.localStorage.setItem(key, value) : setCookieItem(key, value, Infinity, '/');
  },

  extend = function(obj) {
    var arg, i, k;
    for (i = 1; i < arguments.length; i++) {
      arg = arguments[i];
      for (k in arg) {
        if (arg.hasOwnProperty(k)) {
          obj[k] = arg[k];
        }
      }
    }
    return obj;
  },

  defaults = {
    namespace: ""
  },

  volumePersister = function(options) {
    var player = this;
    var settings = extend({}, defaults, options || {});

    var key = settings.namespace + '-' + 'volume';
    var muteKey = settings.namespace + '-' + 'mute';

    player.on("volumechange", function() {
      var currentVolume = player.volume();
      var currentMuted = player.muted();
      setStorageItem(key, currentVolume);
      setStorageItem(muteKey, currentMuted);
    });

    var persistedVolume = getStorageItem(key);
    if(persistedVolume !== null){
      var volumeValue = parseFloat(persistedVolume);
      if(!isNaN(volumeValue) && volumeValue >= 0 && volumeValue <= 1){
        player.volume(volumeValue);
      }
    }

    var persistedMute = getStorageItem(muteKey);
    if(persistedMute !== null){
      var isMuted = (persistedMute === 'true' || persistedMute === true);
      player.muted(isMuted);
    }

    console.debug('persistvolume: plugin initialized with namespace', settings.namespace);
  };

  vjs.registerPlugin("persistvolume", volumePersister);

});
