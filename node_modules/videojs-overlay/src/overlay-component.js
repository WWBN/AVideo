import window from 'global/window';

const initOverlayComponent = (videojs) => {
  const Component = videojs.getComponent('Component');

  const dom = videojs.dom || videojs;

  /**
   * Whether the value is a `Number`.
   *
   * Both `Infinity` and `-Infinity` are accepted, but `NaN` is not.
   *
   * @param  {Number} n
   * @return {Boolean}
   */

  /* eslint-disable no-self-compare */
  const isNumber = n => typeof n === 'number' && n === n;
  /* eslint-enable no-self-compare */

  /**
   * Whether a value is a string with no whitespace.
   *
   * @param  {string} s
   * @return {boolean}
   */
  const hasNoWhitespace = s => typeof s === 'string' && (/^\S+$/).test(s);

  /**
   * Overlay component.
   *
   * @class   Overlay
   * @extends {videojs.Component}
   */
  class Overlay extends Component {

    constructor(player, options) {
      super(player, options);

      ['start', 'end'].forEach(key => {
        const value = this.options_[key];

        if (isNumber(value)) {
          this[key + 'Event_'] = 'timeupdate';
        } else if (hasNoWhitespace(value)) {
          this[key + 'Event_'] = value;

        // An overlay MUST have a start option. Otherwise, it's pointless.
        } else if (key === 'start') {
          throw new Error('invalid "start" option; expected number or string');
        }
      });

      // video.js does not like components with multiple instances binding
      // events to the player because it tracks them at the player level,
      // not at the level of the object doing the binding. This could also be
      // solved with Function.prototype.bind (but not videojs.bind because of
      // its GUID magic), but the anonymous function approach avoids any issues
      // caused by crappy libraries clobbering Function.prototype.bind.
      // - https://github.com/videojs/video.js/issues/3097
      ['endListener_', 'rewindListener_', 'startListener_'].forEach(name => {
        this[name] = (e) => Overlay.prototype[name].call(this, e);
      });

      // If the start event is a timeupdate, we need to watch for rewinds (i.e.,
      // when the user seeks backward).
      if (this.startEvent_ === 'timeupdate') {
        this.on(player, 'timeupdate', this.rewindListener_);
      }

      this.debug(`created, listening to "${this.startEvent_}" for "start" and "${this.endEvent_ || 'nothing'}" for "end"`);

      this.hide();
    }

    createEl() {
      const options = this.options_;
      const content = options.content;

      const background = options.showBackground ? 'vjs-overlay-background' : 'vjs-overlay-no-background';
      const el = dom.createEl('div', {
        className: `
          vjs-overlay
          vjs-overlay-${options.align}
          ${options.class}
          ${background}
          vjs-hidden
        `
      });

      if (typeof content === 'string') {
        el.innerHTML = content;
      } else if (content instanceof window.DocumentFragment) {
        el.appendChild(content);
      } else {
        dom.appendContent(el, content);
      }

      return el;
    }

    /**
     * Logs debug errors
     *
     * @param  {...[type]} args [description]
     * @return {[type]}         [description]
     */
    debug(...args) {
      if (!this.options_.debug) {
        return;
      }

      const log = videojs.log;
      let fn = log;

      // Support `videojs.log.foo` calls.
      if (log.hasOwnProperty(args[0]) && typeof log[args[0]] === 'function') {
        fn = log[args.shift()];
      }

      fn(...[`overlay#${this.id()}: `, ...args]);
    }

    /**
     * Overrides the inherited method to perform some event binding
     *
     * @return {Overlay}
     */
    hide() {
      super.hide();

      this.debug('hidden');
      this.debug(`bound \`startListener_\` to "${this.startEvent_}"`);

      // Overlays without an "end" are valid.
      if (this.endEvent_) {
        this.debug(`unbound \`endListener_\` from "${this.endEvent_}"`);
        this.off(this.player(), this.endEvent_, this.endListener_);
      }

      this.on(this.player(), this.startEvent_, this.startListener_);

      return this;
    }

    /**
     * Determine whether or not the overlay should hide.
     *
     * @param  {number} time
     *         The current time reported by the player.
     * @param  {string} type
     *         An event type.
     * @return {boolean}
     */
    shouldHide_(time, type) {
      const end = this.options_.end;

      return isNumber(end) ? (time >= end) : end === type;
    }

    /**
     * Overrides the inherited method to perform some event binding
     *
     * @return {Overlay}
     */
    show() {
      super.show();
      this.off(this.player(), this.startEvent_, this.startListener_);
      this.debug('shown');
      this.debug(`unbound \`startListener_\` from "${this.startEvent_}"`);

      // Overlays without an "end" are valid.
      if (this.endEvent_) {
        this.debug(`bound \`endListener_\` to "${this.endEvent_}"`);
        this.on(this.player(), this.endEvent_, this.endListener_);
      }

      return this;
    }

    /**
     * Determine whether or not the overlay should show.
     *
     * @param  {number} time
     *         The current time reported by the player.
     * @param  {string} type
     *         An event type.
     * @return {boolean}
     */
    shouldShow_(time, type) {
      const start = this.options_.start;
      const end = this.options_.end;

      if (isNumber(start)) {

        if (isNumber(end)) {
          return time >= start && time < end;

        // In this case, the start is a number and the end is a string. We need
        // to check whether or not the overlay has shown since the last seek.
        } else if (!this.hasShownSinceSeek_) {
          this.hasShownSinceSeek_ = true;
          return time >= start;
        }

        // In this case, the start is a number and the end is a string, but
        // the overlay has shown since the last seek. This means that we need
        // to be sure we aren't re-showing it at a later time than it is
        // scheduled to appear.
        return Math.floor(time) === start;
      }

      return start === type;
    }

    /**
     * Event listener that can trigger the overlay to show.
     *
     * @param  {Event} e
     */
    startListener_(e) {
      const time = this.player().currentTime();

      if (this.shouldShow_(time, e.type)) {
        this.show();
      }
    }

    /**
     * Event listener that can trigger the overlay to show.
     *
     * @param  {Event} e
     */
    endListener_(e) {
      const time = this.player().currentTime();

      if (this.shouldHide_(time, e.type)) {
        this.hide();
      }
    }

    /**
     * Event listener that can looks for rewinds - that is, backward seeks
     * and may hide the overlay as needed.
     *
     * @param  {Event} e
     */
    rewindListener_(e) {
      const time = this.player().currentTime();
      const previous = this.previousTime_;
      const start = this.options_.start;
      const end = this.options_.end;

      // Did we seek backward?
      if (time < previous) {
        this.debug('rewind detected');

        // The overlay remains visible if two conditions are met: the end value
        // MUST be an integer and the the current time indicates that the
        // overlay should NOT be visible.
        if (isNumber(end) && !this.shouldShow_(time)) {
          this.debug(`hiding; ${end} is an integer and overlay should not show at this time`);
          this.hasShownSinceSeek_ = false;
          this.hide();

        // If the end value is an event name, we cannot reliably decide if the
        // overlay should still be displayed based solely on time; so, we can
        // only queue it up for showing if the seek took us to a point before
        // the start time.
        } else if (hasNoWhitespace(end) && time < start) {
          this.debug(`hiding; show point (${start}) is before now (${time}) and end point (${end}) is an event`);
          this.hasShownSinceSeek_ = false;
          this.hide();
        }
      }

      this.previousTime_ = time;
    }
  }

  videojs.registerComponent('Overlay', Overlay);

  return Overlay;
};

export default initOverlayComponent;
