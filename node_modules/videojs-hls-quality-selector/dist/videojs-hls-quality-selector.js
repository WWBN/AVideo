/*! @name videojs-hls-quality-selector @version 2.0.0 @license MIT */
(function (global, factory) {
  typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory(require('video.js')) :
  typeof define === 'function' && define.amd ? define(['video.js'], factory) :
  (global = typeof globalThis !== 'undefined' ? globalThis : global || self, global.videojsHlsQualitySelector = factory(global.videojs));
}(this, (function (videojs) { 'use strict';

  function _interopDefaultLegacy (e) { return e && typeof e === 'object' && 'default' in e ? e : { 'default': e }; }

  var videojs__default = /*#__PURE__*/_interopDefaultLegacy(videojs);

  var version = "2.0.0";

  const MenuButton = videojs__default['default'].getComponent('MenuButton');
  const Menu = videojs__default['default'].getComponent('Menu');
  const Component = videojs__default['default'].getComponent('Component');
  const Dom = videojs__default['default'].dom;

  /**
   * Convert string to title case.
   *
   * @param {string} string - the string to convert
   * @return {string} the returned titlecase string
   */
  function toTitleCase(string) {
    if (typeof string !== 'string') {
      return string;
    }
    return string.charAt(0).toUpperCase() + string.slice(1);
  }

  /**
   * Extend vjs button class for quality button.
   */
  class ConcreteButton extends MenuButton {
    /**
     * Button constructor.
     *
     * @param {Player} player - videojs player instance
     */
    constructor(player) {
      super(player, {
        title: player.localize('Quality'),
        name: 'QualityButton'
      });
    }

    /**
     * Creates button items.
     *
     * @return {Array} - Button items
     */
    createItems() {
      return [];
    }

    /**
     * Create the menu and add all items to it.
     *
     * @return {Menu}
     *         The constructed menu
     */
    createMenu() {
      const menu = new Menu(this.player_, {
        menuButton: this
      });
      this.hideThreshold_ = 0;

      // Add a title list item to the top
      if (this.options_.title) {
        const titleEl = Dom.createEl('li', {
          className: 'vjs-menu-title',
          innerHTML: toTitleCase(this.options_.title),
          tabIndex: -1
        });
        const titleComponent = new Component(this.player_, {
          el: titleEl
        });
        this.hideThreshold_ += 1;
        menu.addItem(titleComponent);
      }
      this.items = this.createItems();
      if (this.items) {
        // Add menu items to the menu
        for (let i = 0; i < this.items.length; i++) {
          menu.addItem(this.items[i]);
        }
      }
      return menu;
    }
  }

  // Concrete classes
  const VideoJsMenuItemClass = videojs__default['default'].getComponent('MenuItem');

  /**
   * Extend vjs menu item class.
   */
  class ConcreteMenuItem extends VideoJsMenuItemClass {
    /**
     * Menu item constructor.
     *
     * @param {Player} player - vjs player
     * @param {Object} item - Item object
     * @param {ConcreteButton} qualityButton - The containing button.
     * @param {HlsQualitySelector} plugin - This plugin instance.
     */
    constructor(player, item, qualityButton, plugin) {
      super(player, {
        label: item.label,
        selectable: true,
        selected: item.selected || false
      });
      this.item = item;
      this.qualityButton = qualityButton;
      this.plugin = plugin;
    }

    /**
     * Click event for menu item.
     */
    handleClick() {
      // Reset other menu items selected status.
      for (let i = 0; i < this.qualityButton.items.length; ++i) {
        this.qualityButton.items[i].selected(false);
      }

      // Set this menu item to selected, and set quality.
      this.plugin.setQuality(this.item.value);
      this.selected(true);
    }
  }

  const Plugin = videojs__default['default'].getPlugin('plugin');

  // Default options for the plugin.
  const defaults = {};

  /**
   * An advanced Video.js plugin. For more information on the API
   *
   * See: https://blog.videojs.com/feature-spotlight-advanced-plugins/
   */
  class HlsQualitySelector extends Plugin {
    /**
     * Create a HlsQualitySelector plugin instance.
     *
     * @param  {Player} player
     *         A Video.js Player instance.
     *
     * @param  {Object} [options]
     *         An optional options object.
     *
     *         While not a core part of the Video.js plugin architecture, a
     *         second argument of options is a convenient way to accept inputs
     *         from your plugin's caller.
     */
    constructor(player, options) {
      // the parent class will add player under this.player
      super(player);
      this.options = videojs__default['default'].obj.merge(defaults, options);
      this.player.ready(() => {
        // If there is quality levels plugin and the HLS tech exists then continue.
        if (this.player.qualityLevels) {
          this.player.addClass('vjs-hls-quality-selector');
          // Create the quality button.
          this.createQualityButton();
          this.bindPlayerEvents();
        }
      });
    }

    /**
     * Binds listener for quality level changes.
     */
    bindPlayerEvents() {
      this.player.qualityLevels().on('addqualitylevel', this.onAddQualityLevel.bind(this));
    }

    /**
     * Adds the quality menu button to the player control bar.
     */
    createQualityButton() {
      const player = this.player;
      this._qualityButton = new ConcreteButton(player);
      const placementIndex = player.controlBar.children().length - 2;
      const concreteButtonInstance = player.controlBar.addChild(this._qualityButton, {
        componentClass: 'qualitySelector'
      }, this.options.placementIndex || placementIndex);
      concreteButtonInstance.addClass('vjs-quality-selector');
      if (!this.options.displayCurrentQuality) {
        const icon = ` ${this.options.vjsIconClass || 'vjs-icon-hd'}`;
        concreteButtonInstance.menuButton_.$('.vjs-icon-placeholder').className += icon;
      } else {
        this.setButtonInnerText(player.localize('Auto'));
      }
      concreteButtonInstance.removeClass('vjs-hidden');
    }

    /**
     *Set inner button text.
     *
     * @param {string} text - the text to display in the button.
     */
    setButtonInnerText(text) {
      this._qualityButton.menuButton_.$('.vjs-icon-placeholder').innerHTML = text;
    }

    /**
     * Builds individual quality menu items.
     *
     * @param {Object} item - Individual quality menu item.
     * @return {ConcreteMenuItem} - Menu item
     */
    getQualityMenuItem(item) {
      const player = this.player;
      return new ConcreteMenuItem(player, item, this._qualityButton, this);
    }

    /**
     * Executed when a quality level is added from HLS playlist.
     */
    onAddQualityLevel() {
      const player = this.player;
      const qualityList = player.qualityLevels();
      const levels = qualityList.levels_ || [];
      const levelItems = [];
      for (let i = 0; i < levels.length; ++i) {
        const {
          width,
          height
        } = levels[i];
        const pixels = width > height ? height : width;
        if (!pixels) {
          continue;
        }
        if (!levelItems.filter(_existingItem => {
          return _existingItem.item && _existingItem.item.value === pixels;
        }).length) {
          const levelItem = this.getQualityMenuItem.call(this, {
            label: pixels + 'p',
            value: pixels
          });
          levelItems.push(levelItem);
        }
      }
      levelItems.sort((current, next) => {
        if (typeof current !== 'object' || typeof next !== 'object') {
          return -1;
        }
        if (current.item.value < next.item.value) {
          return -1;
        }
        if (current.item.value > next.item.value) {
          return 1;
        }
        return 0;
      });
      levelItems.push(this.getQualityMenuItem.call(this, {
        label: this.player.localize('Auto'),
        value: 'auto',
        selected: true
      }));
      if (this._qualityButton) {
        this._qualityButton.createItems = () => {
          return levelItems;
        };
        this._qualityButton.update();
      }
    }

    /**
     * Sets quality (based on media short side)
     *
     * @param {number} quality - A number representing HLS playlist.
     */
    setQuality(quality) {
      const qualityList = this.player.qualityLevels();

      // Set quality on plugin
      this._currentQuality = quality;
      if (this.options.displayCurrentQuality) {
        this.setButtonInnerText(quality === 'auto' ? this.player.localize('Auto') : `${quality}p`);
      }
      for (let i = 0; i < qualityList.length; ++i) {
        const {
          width,
          height
        } = qualityList[i];
        const pixels = width > height ? height : width;
        qualityList[i].enabled = pixels === quality || quality === 'auto';
      }
      this._qualityButton.unpressButton();
    }

    /**
     * Return the current set quality or 'auto'
     *
     * @return {string} the currently set quality
     */
    getCurrentQuality() {
      return this._currentQuality || 'auto';
    }
  }

  // Include the version number.
  HlsQualitySelector.VERSION = version;

  // Register the plugin with video.js.
  videojs__default['default'].registerPlugin('hlsQualitySelector', HlsQualitySelector);

  return HlsQualitySelector;

})));
