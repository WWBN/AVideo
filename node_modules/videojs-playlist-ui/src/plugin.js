import document from 'global/document';
import videojs from 'video.js';
import {version as VERSION} from '../package.json';
import PlaylistMenu from './playlist-menu';

// see https://github.com/Modernizr/Modernizr/blob/master/feature-detects/css/pointerevents.js
const supportsCssPointerEvents = (() => {
  const element = document.createElement('x');

  element.style.cssText = 'pointer-events:auto';
  return element.style.pointerEvents === 'auto';
})();

const defaults = {
  className: 'vjs-playlist',
  playOnSelect: false,
  supportsCssPointerEvents
};

const Plugin = videojs.getPlugin('plugin');

/**
 * Initialize the plugin on a player.
 *
 * @param  {Object} [options]
 *         An options object.
 *
 * @param  {HTMLElement} [options.el]
 *         A DOM element to use as a root node for the playlist.
 *
 * @param  {string} [options.className]
 *         An HTML class name to use to find a root node for the playlist.
 *
 * @param  {boolean} [options.playOnSelect = false]
 *         If true, will attempt to begin playback upon selecting a new
 *         playlist item in the UI.
 */
class PlaylistUI extends Plugin {

  constructor(player, options) {
    super(player, options);

    if (!player.usingPlugin('playlist')) {
      player.log.error('videojs-playlist plugin is required by the videojs-playlist-ui plugin');
      return;
    }

    options = this.options_ = videojs.obj.merge(defaults, options);

    if (!videojs.dom.isEl(options.el)) {
      options.el = this.findRoot_(options.className);
    }

    // Expose the playlist menu component on the player as well as the plugin
    // This is a bit of an anti-pattern, but it's been that way forever and
    // there are likely to be integrations relying on it.
    this.playlistMenu = player.playlistMenu = new PlaylistMenu(player, options);
  }

  /**
   * Dispose the plugin.
   */
  dispose() {
    super.dispose();
    this.playlistMenu.dispose();
  }

  /**
   * Returns a boolean indicating whether an element has child elements.
   *
   * Note that this is distinct from whether it has child _nodes_.
   *
   * @param  {HTMLElement} el
   *         A DOM element.
   *
   * @return {boolean}
   *         Whether the element has child elements.
   */
  hasChildEls_(el) {
    for (let i = 0; i < el.childNodes.length; i++) {
      if (videojs.dom.isEl(el.childNodes[i])) {
        return true;
      }
    }
    return false;
  }

  /**
   * Finds the first empty root element.
   *
   * @param  {string} className
   *         An HTML class name to search for.
   *
   * @return {HTMLElement}
   *         A DOM element to use as the root for a playlist.
   */
  findRoot_(className) {
    const all = document.querySelectorAll('.' + className);

    for (let i = 0; i < all.length; i++) {
      if (!this.hasChildEls_(all[i])) {
        return all[i];
      }
    }
  }
}

videojs.registerPlugin('playlistUi', PlaylistUI);

PlaylistUI.VERSION = VERSION;

export default PlaylistUI;
