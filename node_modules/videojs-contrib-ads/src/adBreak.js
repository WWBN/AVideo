/*
 * Encapsulates logic for starting and ending ad breaks. An ad break
 * is the time between startLinearAdMode and endLinearAdMode. The ad
 * plugin may play 0 or more ads during this time.
 */

import * as snapshot from './snapshot.js';

function start(player) {
  player.ads.debug('Starting ad break');

  player.ads._inLinearAdMode = true;

  // No longer does anything, used to move us to ad-playback
  player.trigger('adstart');

  // Capture current player state snapshot
  if (player.ads.shouldTakeSnapshots()) {
    player.ads.snapshot = snapshot.getPlayerSnapshot(player);
  }

  // Mute the player behind the ad
  if (player.ads.shouldPlayContentBehindAd(player)) {
    player.ads.preAdVolume_ = player.volume();
    player.volume(0);
  }

  // Add css to the element to indicate and ad is playing.
  player.addClass('vjs-ad-playing');

  // We should remove the vjs-live class if it has been added in order to
  // show the adprogress control bar on Android devices for falsely
  // determined LIVE videos due to the duration incorrectly reported as Infinity
  if (player.hasClass('vjs-live')) {
    player.removeClass('vjs-live');
  }

  // This removes the native poster so the ads don't show the content
  // poster if content element is reused for ad playback.
  player.ads.removeNativePoster();
}

function end(player, callback) {
  player.ads.debug('Ending ad break');

  if (callback === undefined) {
    callback = () => {};
  }

  player.ads.adType = null;

  player.ads._inLinearAdMode = false;

  // Signals the end of the ad break to anyone listening.
  player.trigger('adend');

  player.removeClass('vjs-ad-playing');

  // We should add the vjs-live class back if the video is a LIVE video
  // If we dont do this, then for a LIVE Video, we will get an incorrect
  // styled control, which displays the time for the video
  if (player.ads.isLive(player)) {
    player.addClass('vjs-live');
  }

  // Restore snapshot
  if (player.ads.shouldTakeSnapshots()) {
    snapshot.restorePlayerSnapshot(player, callback);

  // Reset the volume to pre-ad levels
  } else {
    player.volume(player.ads.preAdVolume_);
    callback();
  }

}

const obj = {start, end};

export default obj;
