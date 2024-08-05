import {setup} from './auto-advance.js';

/**
 * Removes all remote text tracks from a player.
 *
 * @param  {Player} player
 *         The player to clear tracks on
 */
const clearTracks = (player) => {
  const tracks = player.remoteTextTracks();
  let i = tracks && tracks.length || 0;

  // This uses a `while` loop rather than `forEach` because the
  // `TextTrackList` object is a live DOM list (not an array).
  while (i--) {
    player.removeRemoteTextTrack(tracks[i]);
  }
};

/**
 * Plays an item on a player's playlist.
 *
 * @param  {Player} player
 *         The player to play the item on
 *
 * @param  {Object} item
 *         A source from the playlist.
 * @param {boolean} [suppressPoster]
 *         Should the native poster be suppressed? Defaults to false.
 *
 * @return {Player}
 *         The player that is now playing the item
 */
const playItem = (player, item, suppressPoster = false) => {
  const replay = !player.paused() || player.ended();
  const displayPoster = () => {
    if (player.audioPosterMode()) {
      player.poster(item.poster || '');
    }
  };

  player.trigger('beforeplaylistitem', item.originalValue || item);

  if (item.playlistItemId_) {
    player.playlist.currentPlaylistItemId_ = item.playlistItemId_;
  }

  player.poster(suppressPoster ? '' : item.poster || '');

  player.off('audiopostermodechange', displayPoster);
  player.one('audiopostermodechange', displayPoster);

  player.src(item.sources);
  clearTracks(player);

  player.ready(() => {

    (item.textTracks || []).forEach(player.addRemoteTextTrack.bind(player));
    player.trigger('playlistitem', item.originalValue || item);

    if (replay) {
      const playPromise = player.play();

      // silence error when a pause interrupts a play request
      // on browsers which return a promise
      if (typeof playPromise !== 'undefined' && typeof playPromise.then === 'function') {
        playPromise.then(null, (e) => {});
      }
    }

    setup(player, player.playlist.autoadvance_.delay);
  });

  return player;
};

export default playItem;
export {clearTracks};
