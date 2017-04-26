/**
* jQuery asPieProgress v0.4.6
* https://github.com/amazingSurge/jquery-asPieProgress
*
* Copyright (c) amazingSurge
* Released under the LGPL-3.0 license
*/
import $ from 'jquery';

const SvgElement = (tag, attrs) => {
  'use strict';
  const elem = document.createElementNS('http://www.w3.org/2000/svg', tag);

  if (!attrs) {
    return elem;
  }

  for (let key in attrs) {
    if (!Object.hasOwnProperty.call(attrs, key)) {
      continue;
    }

    elem.setAttribute(key, attrs[key]);
  }
  return elem;
};

if (!Date.now) {
  Date.now = () => {
    'use strict';
    return new Date().getTime();
  };
}

const vendors = ['webkit', 'moz'];
for (let i = 0; i < vendors.length && !window.requestAnimationFrame; ++i) {
  const vp = vendors[i];
  window.requestAnimationFrame = window[`${vp}RequestAnimationFrame`];
  window.cancelAnimationFrame = (window[`${vp}CancelAnimationFrame`] || window[`${vp}CancelRequestAnimationFrame`]);
}

if (/iP(ad|hone|od).*OS (6|7|8)/.test(window.navigator.userAgent) // iOS6 is buggy
  ||
  !window.requestAnimationFrame || !window.cancelAnimationFrame) {
  let lastTime = 0;
  window.requestAnimationFrame = callback => {
    'use strict';
    const now = getTime();
    const nextTime = Math.max(lastTime + 16, now);
    return setTimeout(() => {
        callback(lastTime = nextTime);
      },
      nextTime - now);
  };
  window.cancelAnimationFrame = clearTimeout;
}

const getTime = () => {
  if (typeof window.performance !== 'undefined' && window.performance.now) {
    return window.performance.now();
  }
  return Date.now();
};

const isPercentage = (n) => {
  'use strict';

  return typeof n === 'string' && n.indexOf('%') !== -1;
};

const svgSupported = 'createElementNS' in document && new SvgElement('svg', {}).createSVGRect;

const easingBezier = (mX1, mY1, mX2, mY2) => {
  'use strict';

  const a = (aA1, aA2) => {
    return 1.0 - 3.0 * aA2 + 3.0 * aA1;
  };

  const b = (aA1, aA2) => {
    return 3.0 * aA2 - 6.0 * aA1;
  };

  const c = (aA1) => {
    return 3.0 * aA1;
  };

  // Returns x(t) given t, x1, and x2, or y(t) given t, y1, and y2.
  const calcBezier = (aT, aA1, aA2) => {
    return ((a(aA1, aA2) * aT + b(aA1, aA2)) * aT + c(aA1)) * aT;
  };

  // Returns dx/dt given t, x1, and x2, or dy/dt given t, y1, and y2.
  const getSlope = (aT, aA1, aA2) => {
    return 3.0 * a(aA1, aA2) * aT * aT + 2.0 * b(aA1, aA2) * aT + c(aA1);
  };

  const getTForX = (aX) => {
    // Newton raphson iteration
    let aGuessT = aX;
    for (let i = 0; i < 4; ++i) {
      let currentSlope = getSlope(aGuessT, mX1, mX2);
      if (currentSlope === 0.0) {
        return aGuessT;
      }
      let currentX = calcBezier(aGuessT, mX1, mX2) - aX;
      aGuessT -= currentX / currentSlope;
    }
    return aGuessT;
  };

  if (mX1 === mY1 && mX2 === mY2) {
    return {
      css: 'linear',
      fn(aX) {
        return aX;
      }
    };
  }

  return {
    css: `cubic-bezier(${mX1},${mY1},${mX2},${mY2})`,
    fn(aX) {
      return calcBezier(getTForX(aX), mY1, mY2);
    }
  };
};

var EASING = {
  ease: easingBezier(0.25, 0.1, 0.25, 1.0),
  linear: easingBezier(0.00, 0.0, 1.00, 1.0),
  'ease-in': easingBezier(0.42, 0.0, 1.00, 1.0),
  'ease-out': easingBezier(0.00, 0.0, 0.58, 1.0),
  'ease-in-out': easingBezier(0.42, 0.0, 0.58, 1.0)
};

var DEFAULTS = {
  namespace: 'asPieProgress',
  classes: {
    svg: 'pie_progress__svg',
    element: 'pie_progress',
    number: 'pie_progress__number',
    content: 'pie_progress__content'
  },
  min: 0,
  max: 100,
  goal: 100,
  size: 160,
  speed: 15, // speed of 1/100
  barcolor: '#ef1e25',
  barsize: '4',
  trackcolor: '#f2f2f2',
  fillcolor: 'none',
  easing: 'ease',
  numberCallback(n) {
    'use strict';
    const percentage = Math.round(this.getPercentage(n));
    return `${percentage}%`;
  },
  contentCallback: null
};

const NAMESPACE$1 = 'asPieProgress';

class asPieProgress {
  constructor(element, options) {
    this.element = element;
    this.$element = $(element);

    this.options = $.extend(true, {}, DEFAULTS, options, this.$element.data());
    this.namespace = this.options.namespace;

    this.classes = this.options.classes;
    this.easing = EASING[this.options.easing] || EASING.ease;
    this.$element.addClass(this.classes.element);

    this.min = this.$element.attr('aria-valuemin');
    this.max = this.$element.attr('aria-valuemax');
    this.min = this.min ? parseInt(this.min, 10) : this.options.min;
    this.max = this.max ? parseInt(this.max, 10) : this.options.max;
    this.first = this.$element.attr('aria-valuenow');
    this.first = this.first ? parseInt(this.first, 10) : (this.options.first ? this.options.first : this.min);
    this.now = this.first;
    this.goal = this.options.goal;

    this._frameId = null;

    this.initialized = false;

    this._trigger('init');
    this.init();
  }

  init() {
    this.$number = this.$element.find(`.${this.classes.number}`);
    this.$content = this.$element.find(`.${this.classes.content}`);

    this.size = this.options.size;
    this.width = this.size;
    this.height = this.size;

    this.prepare();

    this.initialized = true;
    this._trigger('ready');
  }

  prepare() {
    if (!svgSupported) {
      return;
    }

    this.svg = new SvgElement('svg', {
      version: '1.1',
      preserveAspectRatio: 'xMinYMin meet',
      viewBox: `0 0 ${this.width} ${this.height}`
    });

    this.buildTrack();
    this.buildBar();

    $(`<div class="${this.classes.svg}"></div>`).append(this.svg).appendTo(this.$element);
  }

  buildTrack() {
    const height = this.size,
      width = this.size;

    const cx = width / 2,
      cy = height / 2;

    const barsize = this.options.barsize;

    const ellipse = new SvgElement('ellipse', {
      rx: cx - barsize / 2,
      ry: cy - barsize / 2,
      cx,
      cy,
      stroke: this.options.trackcolor,
      fill: this.options.fillcolor,
      'stroke-width': barsize
    });

    this.svg.appendChild(ellipse);
  }

  buildBar() {
    if (!svgSupported) {
      return;
    }

    const path = new SvgElement('path', {
      fill: 'none',
      'stroke-width': this.options.barsize,
      stroke: this.options.barcolor
    });
    this.bar = path;
    this.svg.appendChild(path);

    this._drawBar(this.first);
    this._updateBar();
  }

  _drawBar(n) {
    if (!svgSupported) {
      return;
    }

    this.barGoal = n;
    const height = this.size,
      width = this.size;

    const cx = width / 2,
      cy = height / 2,
      startAngle = 0;

    const barsize = this.options.barsize;

    const r = Math.min(cx, cy) - barsize / 2;
    this.r = r;
    let percentage = this.getPercentage(n);

    if (percentage === 100) {
      percentage -= 0.0001;
    }
    const endAngle = startAngle + percentage * Math.PI * 2 / 100;

    const x1 = cx + r * Math.sin(startAngle),
      x2 = cx + r * Math.sin(endAngle),
      y1 = cy - r * Math.cos(startAngle),
      y2 = cy - r * Math.cos(endAngle);

    // This is a flag for angles larger than than a half circle
    // It is required by the SVG arc drawing component
    let big = 0;
    if (endAngle - startAngle > Math.PI) {
      big = 1;
    }

    // This string holds the path details
    const d = `M${x1},${y1} A${r},${r} 0 ${big} 1 ${x2},${y2}`;

    this.bar.setAttribute('d', d);
  }

  _updateBar() {
    if (!svgSupported) {
      return;
    }
    const percenage = this.getPercentage(this.now);

    const length = this.bar.getTotalLength();
    const offset = length * (1 - percenage / this.getPercentage(this.barGoal));

    this.bar.style.strokeDasharray = `${length} ${length}`;
    this.bar.style.strokeDashoffset = offset;
  }

  _trigger(eventType, ...params) {
    const data = [this].concat(params);

    // event
    this.$element.trigger(`${NAMESPACE$1}::${eventType}`, data);

    // callback
    eventType = eventType.replace(/\b\w+\b/g, (word) => {
      return word.substring(0, 1).toUpperCase() + word.substring(1);
    });
    const onFunction = `on${eventType}`;

    if (typeof this.options[onFunction] === 'function') {
      this.options[onFunction].apply(this, params);
    }
  }

  // Return the percentage based on the current step
  getPercentage(n) {
    return 100 * (n - this.min) / (this.max - this.min);
  }

  go(goal) {
    const that = this;
    this._clear();

    if (isPercentage(goal)) {
      goal = parseInt(goal.replace('%', ''), 10);
      goal = Math.round(this.min + (goal / 100) * (this.max - this.min));
    }
    if (typeof goal === 'undefined') {
      goal = this.goal;
    }

    if (goal > this.max) {
      goal = this.max;
    } else if (goal < this.min) {
      goal = this.min;
    }

    if (this.barGoal < goal) {
      this._drawBar(goal);
    }

    const start = that.now;
    const startTime = getTime();
    const endTime = startTime + Math.abs(start - goal) * 100 * that.options.speed / (that.max - that.min);

    const animation = time => {
      let next;

      if (time > endTime) {
        next = goal;
      } else {
        const distance = (time - startTime) / that.options.speed;
        next = Math.round(that.easing.fn(distance / 100) * (that.max - that.min));

        if (goal > start) {
          next = start + next;
          if (next > goal) {
            next = goal;
          }
        } else {
          next = start - next;
          if (next < goal) {
            next = goal;
          }
        }
      }

      that._update(next);
      if (next === goal) {
        window.cancelAnimationFrame(that._frameId);
        that._frameId = null;

        if (that.now === that.goal) {
          that._trigger('finish');
        }
      } else {
        that._frameId = window.requestAnimationFrame(animation);
      }
    };

    that._frameId = window.requestAnimationFrame(animation);
  }

  _update(n) {
    this.now = n;

    this._updateBar();

    this.$element.attr('aria-valuenow', this.now);
    if (this.$number.length > 0 && typeof this.options.numberCallback === 'function') {
      this.$number.html(this.options.numberCallback.call(this, [this.now]));
    }
    if (this.$content.length > 0 && typeof this.options.contentCallback === 'function') {
      this.$content.html(this.options.contentCallback.call(this, [this.now]));
    }

    this._trigger('update', n);
  }

  _clear() {
    if (this._frameId) {
      window.cancelAnimationFrame(this._frameId);
      this._frameId = null;
    }
  }

  get() {
    return this.now;
  }

  start() {
    this._clear();
    this._trigger('start');
    this.go(this.goal);
  }

  reset() {
    this._clear();
    this._drawBar(this.first);
    this._update(this.first);
    this._trigger('reset');
  }

  stop() {
    this._clear();
    this._trigger('stop');
  }

  finish() {
    this._clear();
    this._update(this.goal);
    this._trigger('finish');
  }

  destroy() {
    this.$element.data(NAMESPACE$1, null);
    this._trigger('destroy');
  }

  static registerEasing(name, ...args) {
    EASING[name] = easingBezier(...args);
  }

  static getEasing(name) {
    return EASING[name];
  }

  static setDefaults(options) {
    $.extend(true, DEFAULTS, $.isPlainObject(options) && options);
  }
}

var info = {
  version:'0.4.6'
};

const NAMESPACE = 'asPieProgress';
const OtherAsPieProgress = $.fn.asPieProgress;

const jQueryAsPieProgress = function(options, ...args) {
  if (typeof options === 'string') {
    const method = options;

    if (/^_/.test(method)) {
      return false;
    } else if ((/^(get)/.test(method))) {
      const instance = this.first().data(NAMESPACE);
      if (instance && typeof instance[method] === 'function') {
        return instance[method](...args);
      }
    } else {
      return this.each(function() {
        const instance = $.data(this, NAMESPACE);
        if (instance && typeof instance[method] === 'function') {
          instance[method](...args);
        }
      });
    }
  }

  return this.each(function() {
    if (!$(this).data(NAMESPACE)) {
      $(this).data(NAMESPACE, new asPieProgress(this, options));
    }
  });
};

$.fn.asPieProgress = jQueryAsPieProgress;

$.asPieProgress = $.extend({
  setDefaults: asPieProgress.setDefaults,
  registerEasing: asPieProgress.registerEasing,
  getEasing: asPieProgress.getEasing,
  noConflict: function() {
    $.fn.asPieProgress = OtherAsPieProgress;
    return jQueryAsPieProgress;
  }
}, info);
