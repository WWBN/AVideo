import window from 'global/window';
import videojs from 'video.js';

const proxy = (props) => {
  let poster_ = '';
  const player = Object.assign({}, videojs.EventTarget.prototype, {
    play: () => {},
    paused: () => {},
    ended: () => {},
    poster: (src) => {
      if (src !== undefined) {
        poster_ = src;
      } return poster_;
    },
    src: () => {},
    currentSrc: () => {},
    addRemoteTextTrack: () => {},
    removeRemoteTextTrack: () => {},
    remoteTextTracks: () => {},
    playlist: () => [],
    ready: (cb) => cb(),
    setTimeout: (cb, wait) => window.setTimeout(cb, wait),
    clearTimeout: (id) => window.clearTimeout(id)
  }, props);

  player.constructor = videojs.getComponent('Player');
  player.playlist.player_ = player;

  player.playlist.autoadvance_ = {};
  player.playlist.currentIndex_ = -1;
  player.playlist.autoadvance = () => {};
  player.playlist.contains = () => {};
  player.playlist.currentItem = () => {};
  player.playlist.first = () => {};
  player.playlist.indexOf = () => {};
  player.playlist.next = () => {};
  player.playlist.previous = () => {};

  return player;
};

export default proxy;
