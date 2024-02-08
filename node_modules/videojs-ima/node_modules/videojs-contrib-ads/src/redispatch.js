/*
The goal of this feature is to make player events work as an integrator would
expect despite the presense of ads. For example, an integrator would expect
an `ended` event to happen once the content is ended. If an `ended` event is sent
as a result of a preroll ending, that is a bug. The `redispatch` method should recognize
such `ended` events and prefix them so they are sent as `adended`, and so on with
all other player events.
*/

// Cancel an event.
// Video.js wraps native events. This technique stops propagation for the Video.js event
// (AKA player event or wrapper event) while native events continue propagating.
const cancelEvent = (player, event) => {
  event.isImmediatePropagationStopped = function() {
    return true;
  };
  event.cancelBubble = true;
  event.isPropagationStopped = function() {
    return true;
  };
};

// Redispatch an event with a prefix.
// Cancels the event, then sends a new event with the type of the original
// event with the given prefix added.
// The inclusion of the "state" property should be removed in a future
// major version update with instructions to migrate any code that relies on it.
// It is an implementation detail and relying on it creates fragility.
const prefixEvent = (player, prefix, event) => {
  cancelEvent(player, event);
  player.trigger({
    type: prefix + event.type,
    originalEvent: event
  });
};

// Playing event
// Requirements:
// * Normal playing event when there is no preroll
// * No playing event before preroll
// * At least one playing event after preroll
const handlePlaying = (player, event) => {
  if (player.ads.isInAdMode()) {

    if (player.ads.isContentResuming()) {

      // Prefix playing event when switching back to content after postroll.
      if (player.ads._contentEnding) {
        prefixEvent(player, 'content', event);
      }

    // Prefix all other playing events during ads.
    } else {
      prefixEvent(player, 'ad', event);
    }

  }
};

// Ended event
// Requirements:
// * A single ended event when there is no postroll
// * No ended event before postroll
// * A single ended event after postroll
const handleEnded = (player, event) => {
  if (player.ads.isInAdMode()) {

    // Cancel ended events during content resuming. Normally we would
    // prefix them, but `contentended` has a special meaning. In the
    // future we'd like to rename the existing `contentended` to
    // `readyforpostroll`, then we could remove the special `resumeended`
    // and do a conventional content prefix here.
    if (player.ads.isContentResuming()) {
      cancelEvent(player, event);

      // Important: do not use this event outside of videojs-contrib-ads.
      // It will be removed and your code will break.
      // Ideally this would simply be `contentended`, but until
      // `contentended` no longer has a special meaning it cannot be
      // changed.
      player.trigger('resumeended');

    // Ad prefix in ad mode
    } else {
      prefixEvent(player, 'ad', event);
    }

  // Prefix ended due to content ending before postroll check
  } else if (!player.ads._contentHasEnded && !player.ads.stitchedAds()) {

    // This will change to cancelEvent after the contentended deprecation
    // period (contrib-ads 7)
    prefixEvent(player, 'content', event);

    // Content ended for the first time, time to check for postrolls
    player.trigger('readyforpostroll');
  }
};

// handleLoadEvent is used for loadstart, loadeddata, and loadedmetadata
// Requirements:
// * Initial event is not prefixed
// * Event due to ad loading is prefixed
// * Event due to content source change is not prefixed
// * Event due to content resuming is prefixed
const handleLoadEvent = (player, event) => {

  // Initial event
  if (event.type === 'loadstart' && !player.ads._hasThereBeenALoadStartDuringPlayerLife ||
      event.type === 'loadeddata' && !player.ads._hasThereBeenALoadedData ||
      event.type === 'loadedmetadata' && !player.ads._hasThereBeenALoadedMetaData) {
    return;

  // Ad playing
  } else if (player.ads.inAdBreak()) {
    prefixEvent(player, 'ad', event);

  // Source change
  } else if (player.currentSrc() !== player.ads.contentSrc) {
    return;

  // Content resuming
  } else {
    prefixEvent(player, 'content', event);
  }
};

// Play event
// Requirements:
// * Play events have the "ad" prefix when an ad is playing
// * Play events have the "content" prefix when content is resuming
// Play requests are unique because they represent user intention to play. They happen
// because the user clicked play, or someone called player.play(), etc. It could happen
// multiple times during ad loading, regardless of where we are in the process. With our
// current architecture, this could cause the content to start playing.
// Therefore, contrib-ads must always either:
//   - cancelContentPlay if there is any possible chance the play caused the
//     content to start playing, even if we are technically in ad mode. In order for
//     that to happen, play events need to be unprefixed until the last possible moment.
//   - use playMiddleware to stop the play from reaching the Tech so there is no risk
//     of the content starting to play.
// Currently, playMiddleware is only supported on desktop browsers with
// video.js after version 6.7.1.
const handlePlay = (player, event) => {
  if (player.ads.inAdBreak()) {
    prefixEvent(player, 'ad', event);

  // Content resuming
  } else if (player.ads.isContentResuming()) {
    prefixEvent(player, 'content', event);
  }
};

// Handle a player event, either by redispatching it with a prefix, or by
// letting it go on its way without any meddling.
export default function redispatch(event) {

  // Events with special treatment
  if (event.type === 'playing') {
    handlePlaying(this, event);
  } else if (event.type === 'ended') {
    handleEnded(this, event);
  } else if (event.type === 'loadstart' ||
             event.type === 'loadeddata' ||
             event.type === 'loadedmetadata') {
    handleLoadEvent(this, event);
  } else if (event.type === 'play') {
    handlePlay(this, event);

  // Standard handling for all other events
  } else if (this.ads.isInAdMode()) {
    if (this.ads.isContentResuming()) {

      // Event came from snapshot restore after an ad, use "content" prefix
      prefixEvent(this, 'content', event);
    } else {

      // Event came from ad playback, use "ad" prefix
      prefixEvent(this, 'ad', event);
    }
  }

}
