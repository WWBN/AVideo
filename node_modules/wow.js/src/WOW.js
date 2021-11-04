function isIn(needle, haystack) {
  return haystack.indexOf(needle) >= 0;
}

function extend(custom, defaults) {
  for (const key in defaults) {
    if (custom[key] == null) {
      const value = defaults[key];
      custom[key] = value;
    }
  }
  return custom;
}

function isMobile(agent) {
  return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(agent);
}

function createEvent(event, bubble = false, cancel = false, detail = null) {
  let customEvent;
  if (document.createEvent != null) { // W3C DOM
    customEvent = document.createEvent('CustomEvent');
    customEvent.initCustomEvent(event, bubble, cancel, detail);
  } else if (document.createEventObject != null) { // IE DOM < 9
    customEvent = document.createEventObject();
    customEvent.eventType = event;
  } else {
    customEvent.eventName = event;
  }

  return customEvent;
}

function emitEvent(elem, event) {
  if (elem.dispatchEvent != null) { // W3C DOM
    elem.dispatchEvent(event);
  } else if (event in (elem != null)) {
    elem[event]();
  } else if (`on${event}` in (elem != null)) {
    elem[`on${event}`]();
  }
}

function addEvent(elem, event, fn) {
  if (elem.addEventListener != null) { // W3C DOM
    elem.addEventListener(event, fn, false);
  } else if (elem.attachEvent != null) { // IE DOM
    elem.attachEvent(`on${event}`, fn);
  } else { // fallback
    elem[event] = fn;
  }
}

function removeEvent(elem, event, fn) {
  if (elem.removeEventListener != null) { // W3C DOM
    elem.removeEventListener(event, fn, false);
  } else if (elem.detachEvent != null) { // IE DOM
    elem.detachEvent(`on${event}`, fn);
  } else { // fallback
    delete elem[event];
  }
}

function getInnerHeight() {
  if ('innerHeight' in window) {
    return window.innerHeight;
  }

  return document.documentElement.clientHeight;
}

// Minimalistic WeakMap shim, just in case.
const WeakMap = window.WeakMap || window.MozWeakMap ||
class WeakMap {
  constructor() {
    this.keys = [];
    this.values = [];
  }

  get(key) {
    for (let i = 0; i < this.keys.length; i++) {
      const item = this.keys[i];
      if (item === key) {
        return this.values[i];
      }
    }
    return undefined;
  }

  set(key, value) {
    for (let i = 0; i < this.keys.length; i++) {
      const item = this.keys[i];
      if (item === key) {
        this.values[i] = value;
        return this;
      }
    }
    this.keys.push(key);
    this.values.push(value);
    return this;
  }
};

// Dummy MutationObserver, to avoid raising exceptions.
const MutationObserver =
  window.MutationObserver || window.WebkitMutationObserver ||
  window.MozMutationObserver ||
  class MutationObserver {
    constructor() {
      if (typeof console !== 'undefined' && console !== null) {
        console.warn('MutationObserver is not supported by your browser.');
        console.warn(
          'WOW.js cannot detect dom mutations, please call .sync() after loading new content.'
        );
      }
    }

    static notSupported = true;

    observe() {}
  };

// getComputedStyle shim, from http://stackoverflow.com/a/21797294
const getComputedStyle = window.getComputedStyle ||
function getComputedStyle(el) {
  const getComputedStyleRX = /(\-([a-z]){1})/g;
  return {
    getPropertyValue(prop) {
      if (prop === 'float') { prop = 'styleFloat'; }
      if (getComputedStyleRX.test(prop)) {
        prop.replace(getComputedStyleRX, (_, _char) => _char.toUpperCase());
      }
      const { currentStyle } = el;
      return (currentStyle != null ? currentStyle[prop] : void 0) || null;
    },
  };
};

export default class WOW {
  defaults = {
    boxClass: 'wow',
    animateClass: 'animated',
    offset: 0,
    mobile: true,
    live: true,
    callback: null,
    scrollContainer: null,
  };

  constructor(options = {}) {
    this.start = this.start.bind(this);
    this.resetAnimation = this.resetAnimation.bind(this);
    this.scrollHandler = this.scrollHandler.bind(this);
    this.scrollCallback = this.scrollCallback.bind(this);
    this.scrolled = true;
    this.config = extend(options, this.defaults);
    if (options.scrollContainer != null) {
      this.config.scrollContainer = document.querySelector(options.scrollContainer);
    }
  // Map of elements to animation names:
    this.animationNameCache = new WeakMap();
    this.wowEvent = createEvent(this.config.boxClass);
  }

  init() {
    this.element = window.document.documentElement;
    if (isIn(document.readyState, ['interactive', 'complete'])) {
      this.start();
    } else {
      addEvent(document, 'DOMContentLoaded', this.start);
    }
    this.finished = [];
  }

  start() {
    this.stopped = false;
    this.boxes = [].slice.call(this.element.querySelectorAll(`.${this.config.boxClass}`));
    this.all = this.boxes.slice(0);
    if (this.boxes.length) {
      if (this.disabled()) {
        this.resetStyle();
      } else {
        for (let i = 0; i < this.boxes.length; i++) {
          const box = this.boxes[i];
          this.applyStyle(box, true);
        }
      }
    }
    if (!this.disabled()) {
      addEvent(this.config.scrollContainer || window, 'scroll', this.scrollHandler);
      addEvent(window, 'resize', this.scrollHandler);
      this.interval = setInterval(this.scrollCallback, 50);
    }
    if (this.config.live) {
      const mut = new MutationObserver(records => {
        for (let j = 0; j < records.length; j++) {
          const record = records[j];
          for (let k = 0; k < record.addedNodes.length; k++) {
            const node = record.addedNodes[k];
            this.doSync(node);
          }
        }
        return undefined;
      });
      mut.observe(document.body, {
        childList: true,
        subtree: true,
      });
    }
  }

// unbind the scroll event
  stop() {
    this.stopped = true;
    removeEvent(this.config.scrollContainer || window, 'scroll', this.scrollHandler);
    removeEvent(window, 'resize', this.scrollHandler);
    if (this.interval != null) {
      clearInterval(this.interval);
    }
  }

  sync() {
    if (MutationObserver.notSupported) {
      this.doSync(this.element);
    }
  }

  doSync(element) {
    if (typeof element === 'undefined' || element === null) { ({ element } = this); }
    if (element.nodeType !== 1) { return; }
    element = element.parentNode || element;
    const iterable = element.querySelectorAll(`.${this.config.boxClass}`);
    for (let i = 0; i < iterable.length; i++) {
      const box = iterable[i];
      if (!isIn(box, this.all)) {
        this.boxes.push(box);
        this.all.push(box);
        if (this.stopped || this.disabled()) {
          this.resetStyle();
        } else {
          this.applyStyle(box, true);
        }
        this.scrolled = true;
      }
    }
  }

// show box element
  show(box) {
    this.applyStyle(box);
    box.className = `${box.className} ${this.config.animateClass}`;
    if (this.config.callback != null) { this.config.callback(box); }
    emitEvent(box, this.wowEvent);

    addEvent(box, 'animationend', this.resetAnimation);
    addEvent(box, 'oanimationend', this.resetAnimation);
    addEvent(box, 'webkitAnimationEnd', this.resetAnimation);
    addEvent(box, 'MSAnimationEnd', this.resetAnimation);

    return box;
  }

  applyStyle(box, hidden) {
    const duration = box.getAttribute('data-wow-duration');
    const delay = box.getAttribute('data-wow-delay');
    const iteration = box.getAttribute('data-wow-iteration');

    return this.animate(() => this.customStyle(box, hidden, duration, delay, iteration));
  }

  animate = (function animateFactory() {
    if ('requestAnimationFrame' in window) {
      return callback => window.requestAnimationFrame(callback);
    }
    return callback => callback();
  }());

  resetStyle() {
    for (let i = 0; i < this.boxes.length; i++) {
      const box = this.boxes[i];
      box.style.visibility = 'visible';
    }
    return undefined;
  }

  resetAnimation(event) {
    if (event.type.toLowerCase().indexOf('animationend') >= 0) {
      const target = event.target || event.srcElement;
      target.className = target.className.replace(this.config.animateClass, '').trim();
    }
  }

  customStyle(box, hidden, duration, delay, iteration) {
    if (hidden) { this.cacheAnimationName(box); }
    box.style.visibility = hidden ? 'hidden' : 'visible';

    if (duration) { this.vendorSet(box.style, { animationDuration: duration }); }
    if (delay) { this.vendorSet(box.style, { animationDelay: delay }); }
    if (iteration) { this.vendorSet(box.style, { animationIterationCount: iteration }); }
    this.vendorSet(box.style, { animationName: hidden ? 'none' : this.cachedAnimationName(box) });

    return box;
  }

  vendors = ['moz', 'webkit'];
  vendorSet(elem, properties) {
    for (const name in properties) {
      if (properties.hasOwnProperty(name)) {
        const value = properties[name];
        elem[`${name}`] = value;
        for (let i = 0; i < this.vendors.length; i++) {
          const vendor = this.vendors[i];
          elem[`${vendor}${name.charAt(0).toUpperCase()}${name.substr(1)}`] = value;
        }
      }
    }
  }
  vendorCSS(elem, property) {
    const style = getComputedStyle(elem);
    let result = style.getPropertyCSSValue(property);
    for (let i = 0; i < this.vendors.length; i++) {
      const vendor = this.vendors[i];
      result = result || style.getPropertyCSSValue(`-${vendor}-${property}`);
    }
    return result;
  }

  animationName(box) {
    let aName;
    try {
      aName = this.vendorCSS(box, 'animation-name').cssText;
    } catch (error) { // Opera, fall back to plain property value
      aName = getComputedStyle(box).getPropertyValue('animation-name');
    }

    if (aName === 'none') {
      return '';  // SVG/Firefox, unable to get animation name?
    }

    return aName;
  }

  cacheAnimationName(box) {
  // https://bugzilla.mozilla.org/show_bug.cgi?id=921834
  // box.dataset is not supported for SVG elements in Firefox
    return this.animationNameCache.set(box, this.animationName(box));
  }
  cachedAnimationName(box) {
    return this.animationNameCache.get(box);
  }

  // fast window.scroll callback
  scrollHandler() {
    this.scrolled = true;
  }

  scrollCallback() {
    if (this.scrolled) {
      this.scrolled = false;
      const results = [];
      for (let i = 0; i < this.boxes.length; i++) {
        const box = this.boxes[i];
        if (box) {
          if (this.isVisible(box)) {
            this.show(box);
            continue;
          }
          results.push(box);
        }
      }
      this.boxes = results;
      if (!this.boxes.length && !this.config.live) {
        this.stop();
      }
    }
  }


  // Calculate element offset top
  offsetTop(element) {
    // SVG elements don't have an offsetTop in Firefox.
    // This will use their nearest parent that has an offsetTop.
    // Also, using ('offsetTop' of element) causes an exception in Firefox.
    while (element.offsetTop === undefined) {
      element = element.parentNode;
    }
    let top = element.offsetTop;
    while (element.offsetParent) {
      element = element.offsetParent;
      top += element.offsetTop;
    }
    return top;
  }

// check if box is visible
  isVisible(box) {
    const offset = box.getAttribute('data-wow-offset') || this.config.offset;
    const viewTop = (
      this.config.scrollContainer && this.config.scrollContainer.scrollTop
    ) || window.pageYOffset;
    const viewBottom =
      viewTop + Math.min(this.element.clientHeight, getInnerHeight()) - offset;
    const top = this.offsetTop(box);
    const bottom = top + box.clientHeight;

    return top <= viewBottom && bottom >= viewTop;
  }

  disabled() {
    return !this.config.mobile && isMobile(navigator.userAgent);
  }
}
