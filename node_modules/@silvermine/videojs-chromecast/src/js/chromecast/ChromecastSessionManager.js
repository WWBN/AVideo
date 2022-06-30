'use strict';

var Class = require('class.extend'),
    hasConnected = false, // See the `isChromecastConnected` function.
    ChromecastSessionManager;

function getCastContext() {
   return cast.framework.CastContext.getInstance();
}

ChromecastSessionManager = Class.extend(/** @lends ChromecastSessionManager.prototype **/ {

   /**
    * Stores the state of the current Chromecast session and its associated objects such
    * as the
    * [RemotePlayerController](https://developers.google.com/cast/docs/reference/chrome/cast.framework.RemotePlayerController),
    * and the
    * [RemotePlayer](https://developers.google.com/cast/docs/reference/chrome/cast.framework.RemotePlayer).
    *
    * WARNING: Do not instantiate this class until the
    * [CastContext](https://developers.google.com/cast/docs/reference/chrome/cast.framework.CastContext)
    * has been configured.
    *
    * For an undocumented (and thus unknown) reason, RemotePlayer and
    * RemotePlayerController instances created before the cast context has been configured
    * or after requesting a session or loading media will not stay in sync with media
    * items that are loaded later.
    *
    * For example, the first item that you cast will work as expected: events on
    * RemotePlayerController will fire and the state (currentTime, duration, etc) of the
    * RemotePlayer instance will update as the media item plays. However, if a new media
    * item is loaded via a `loadMedia` request, the media item will play, but the
    * remotePlayer will be in a "media unloaded" state where the duration is 0, the
    * currentTime does not update, and no change events are fired (except, strangely,
    * displayStatus updates).
    *
    * @param player {object} Video.js Player
    * @constructs ChromecastSessionManager
    */
   init: function(player) {
      this.player = player;

      this._sessionListener = this._onSessionStateChange.bind(this);
      this._castListener = this._onCastStateChange.bind(this);

      this._addCastContextEventListeners();

      // Remove global event listeners when this player instance is destroyed to prevent
      // memory leaks.
      this.player.on('dispose', this._removeCastContextEventListeners.bind(this));

      this._notifyPlayerOfDevicesAvailabilityChange(this.getCastContext().getCastState());

      this.remotePlayer = new cast.framework.RemotePlayer();
      this.remotePlayerController = new cast.framework.RemotePlayerController(this.remotePlayer);
   },

   /**
    * Add event listeners for events triggered on the current CastContext.
    *
    * @private
    */
   _addCastContextEventListeners: function() {
      var sessionStateChangedEvt = cast.framework.CastContextEventType.SESSION_STATE_CHANGED,
          castStateChangedEvt = cast.framework.CastContextEventType.CAST_STATE_CHANGED;

      this.getCastContext().addEventListener(sessionStateChangedEvt, this._sessionListener);
      this.getCastContext().addEventListener(castStateChangedEvt, this._castListener);
   },

   /**
    * Remove event listeners that were added in {@link
    * ChromecastSessionManager#_addCastContextEventListeners}.
    *
    * @private
    */
   _removeCastContextEventListeners: function() {
      var sessionStateChangedEvt = cast.framework.CastContextEventType.SESSION_STATE_CHANGED,
          castStateChangedEvt = cast.framework.CastContextEventType.CAST_STATE_CHANGED;

      this.getCastContext().removeEventListener(sessionStateChangedEvt, this._sessionListener);
      this.getCastContext().removeEventListener(castStateChangedEvt, this._castListener);
   },

   /**
    * Handle the CastContext's SessionState change event.
    *
    * @private
    */
   _onSessionStateChange: function(event) {
      if (event.sessionState === cast.framework.SessionState.SESSION_ENDED) {
         this.player.trigger('chromecastDisconnected');
         this._reloadTech();
      }
   },

   /**
    * Handle the CastContext's CastState change event.
    *
    * @private
    */
   _onCastStateChange: function(event) {
      this._notifyPlayerOfDevicesAvailabilityChange(event.castState);
   },

   /**
    * Triggers player events that notifies listeners that Chromecast devices are
    * either available or unavailable.
    *
    * @private
    */
   _notifyPlayerOfDevicesAvailabilityChange: function(castState) {
      if (this.hasAvailableDevices(castState)) {
         this.player.trigger('chromecastDevicesAvailable');
      } else {
         this.player.trigger('chromecastDevicesUnavailable');
      }
   },

   /**
    * Returns whether or not there are Chromecast devices available to cast to.
    *
    * @see https://developers.google.com/cast/docs/reference/chrome/cast.framework#.CastState
    * @param {String} castState
    * @return {boolean} true if there are Chromecast devices available to cast to.
    */
   hasAvailableDevices: function(castState) {
      castState = castState || this.getCastContext().getCastState();

      return castState === cast.framework.CastState.NOT_CONNECTED ||
         castState === cast.framework.CastState.CONNECTING ||
         castState === cast.framework.CastState.CONNECTED;
   },

   /**
    * Opens the Chromecast casting menu by requesting a CastSession. Does nothing if the
    * Video.js player does not have a source.
    */
   openCastMenu: function() {
      var onSessionSuccess;

      if (!this.player.currentSource()) {
         // Do not cast if there is no media item loaded in the player
         return;
      }
      onSessionSuccess = function() {
         hasConnected = true;
         this.player.trigger('chromecastConnected');
         this._reloadTech();
      }.bind(this);

      // It is the `requestSession` function call that actually causes the cast menu to
      // open.
      // The second parameter to `.then` is an error handler. We use a noop function here
      // because we handle errors in the ChromecastTech class and we do not want an
      // error to bubble up to the console. This error handler is also triggered when
      // the user closes out of the chromecast selector pop-up without choosing a
      // casting destination.
      this.getCastContext().requestSession()
         .then(onSessionSuccess, function() { /* noop */ });
   },

   /**
    * Reloads the Video.js player's Tech. This causes the player to re-evaluate which
    * Tech should be used for the current source by iterating over available Tech and
    * calling `Tech.isSupported` and `Tech.canPlaySource`. Video.js uses the first
    * Tech that returns true from both of those functions. This is what allows us to
    * switch back and forth between the Chromecast Tech and other available Tech when a
    * CastSession is connected or disconnected.
    *
    * @private
    */
   _reloadTech: function() {
      var player = this.player,
          currentTime = player.currentTime(),
          wasPaused = player.paused(),
          sources = player.currentSources();

      // Reload the current source(s) to re-lookup and use the currently available Tech.
      // The chromecast Tech gets used if `ChromecastSessionManager.isChromecastConnected`
      // is true (effectively, if a chromecast session is currently in progress),
      // otherwise Video.js continues to search through the Tech list for other eligible
      // Tech to use, such as the HTML5 player.
      player.src(sources);

      player.ready(function() {
         if (wasPaused) {
            player.pause();
         } else {
            player.play();
         }
         player.currentTime(currentTime || 0);
      });
   },

   /**
    * @see https://developers.google.com/cast/docs/reference/chrome/cast.framework.CastContext
    * @returns {object} the current CastContext, if one exists
    */
   getCastContext: getCastContext,

   /**
    * @see https://developers.google.com/cast/docs/reference/chrome/cast.framework.RemotePlayer
    * @returns {object} the current RemotePlayer, if one exists
    */
   getRemotePlayer: function() {
      return this.remotePlayer;
   },

   /**
    * @see https://developers.google.com/cast/docs/reference/chrome/cast.framework.RemotePlayerController
    * @returns {object} the current RemotePlayerController, if one exists
    */
   getRemotePlayerController: function() {
      return this.remotePlayerController;
   },
});


/**
 * Returns whether or not the current Chromecast API is available (that is,
 * `window.chrome`, `window.chrome.cast`, and `window.cast` exist).
 *
 * @static
 * @returns {boolean} true if the Chromecast API is available
 */
ChromecastSessionManager.isChromecastAPIAvailable = function() {
   return window.chrome && window.chrome.cast && window.cast;
};

/**
 * Returns whether or not there is a current CastSession and it is connected.
 *
 * @static
 * @returns {boolean} true if the current CastSession exists and is connected
 */
ChromecastSessionManager.isChromecastConnected = function() {
   // We must also check the `hasConnected` flag because
   // `getCastContext().getCastState()` returns `CONNECTED` even when the current casting
   // session was initiated by another tab in the browser or by another process.
   return ChromecastSessionManager.isChromecastAPIAvailable() &&
      (getCastContext().getCastState() === cast.framework.CastState.CONNECTED) &&
      hasConnected;
};

module.exports = ChromecastSessionManager;
