import videojs from 'video.js';
import playlistMaker from './playlist-maker';
import {version as VERSION} from '../package.json';

// Video.js 5/6 cross-compatible.
const registerPlugin = videojs.registerPlugin || videojs.plugin;

/**
 * The video.js playlist plugin. Invokes the playlist-maker to create a
 * playlist function on the specific player.
 *
 * @param {Array} list
 *        a list of sources
 *
 * @param {number} item
 *        The index to start at
 */
const plugin = function(list, item) {
  playlistMaker(this, list, item);
};

registerPlugin('playlist', plugin);

plugin.VERSION = VERSION;

export default plugin;
