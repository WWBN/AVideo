import videojs from 'video.js';
import initOverlayComponent from './overlay-component';

const Plugin = videojs.getPlugin('plugin');

const defaults = {
  align: 'top-left',
  class: '',
  content: 'This overlay will show up while the video is playing',
  debug: false,
  showBackground: true,
  attachToControlBar: false,
  overlays: [{
    start: 'playing',
    end: 'paused'
  }]
};

/**
 * A plugin for handling overlays in the Brightcove Player.
 */
class OverlayPlugin extends Plugin {
  /**
   * Create an Overlay Plugin instance.
   *
   * @param  {Player} player
   *         A Video.js Player instance.
   *
   * @param  {Object} [options]
   *         An options object.
   */
  constructor(player, options) {
    super(player);

    this.reset(options);
  }

  /**
   * Adds one or more items to the existing list of overlays.
   *
   * @param {Object|Array} item
   *        An item (or an array of items) to be added as overlay/s
   *
   * @return {Array[Overlay]}
   *         The array of overlay objects that were added
   */
  add(item) {
    if (!Array.isArray(item)) {
      item = [item];
    }

    const addedOverlays = this.mapOverlays_(item);

    this.player.overlays_ = this.player.overlays_.concat(addedOverlays);

    return addedOverlays;
  }

  /**
   *
   * @param {Overlay} item
   *        An item to be removed from the array of overlays
   *
   * @throws {Error}
   *        Item to remove must be present in the array of overlays
   *
   */
  remove(item) {
    const index = this.player.overlays_.indexOf(item);

    if (index !== -1) {
      item.el().parentNode.removeChild(item.el());
      this.player.overlays_.splice(index, 1);
    } else {
      this.player.log.warn('overlay does not exist and cannot be removed');
    }
  }

  /**
   * Gets the array of overlays used for the current video
   *
   * @return The array of overlay objects currently used by the plugin
   */
  get() {
    return this.player.overlays_;
  }

  /**
   * Updates the overlay options
   *
   * @param  {Object} [options]
   *         An options object.
   */
  reset(options) {
    this.clearOverlays_();

    // Use merge function based on video.js version.
    const merge = videojs.obj && videojs.obj.merge || videojs.mergeOptions;

    this.options = merge(defaults, options);

    const overlays = this.options.overlays;

    // We don't want to keep the original array of overlay options around
    // because it doesn't make sense to pass it to each Overlay component.
    delete this.options.overlays;

    this.player.overlays_ = this.mapOverlays_(overlays);
  }

  /**
   * Disposes the plugin
   */
  dispose() {
    this.clearOverlays_();

    delete this.player.overlays_;
    super.dispose();
  }

  clearOverlays_() {
    // Remove child components
    if (Array.isArray(this.player.overlays_)) {
      this.player.overlays_.forEach(overlay => {
        this.player.removeChild(overlay);
        if (this.player.controlBar) {
          this.player.controlBar.removeChild(overlay);
        }
      });
    }
  }

  mapOverlays_(items) {
    return items.map(o => {
      const mergeOptions = videojs.mergeOptions(this.options, o);
      const attachToControlBar = typeof mergeOptions.attachToControlBar === 'string' || mergeOptions.attachToControlBar === true;

      if (!this.player.controls() || !this.player.controlBar) {
        return this.player.addChild('overlay', mergeOptions);
      }

      if (attachToControlBar && mergeOptions.align.indexOf('bottom') !== -1) {
        let referenceChild = this.player.controlBar.children()[0];

        if (this.player.controlBar.getChild(mergeOptions.attachToControlBar) !== undefined) {
          referenceChild = this.player.controlBar.getChild(mergeOptions.attachToControlBar);
        }

        if (referenceChild) {
          const referenceChildIndex = this.player.controlBar.children().indexOf(referenceChild);
          const controlBarChild = this.player.controlBar.addChild('overlay', mergeOptions, referenceChildIndex);

          return controlBarChild;
        }
      }

      const playerChild = this.player.addChild('overlay', mergeOptions);

      this.player.el().insertBefore(
        playerChild.el(),
        this.player.controlBar.el()
      );

      return playerChild;
    });
  }
}

export { initOverlayComponent };

export default OverlayPlugin;
