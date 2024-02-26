/*!
 * Circle Progress - v0.2.4 - 2022-05-16
 * https://tigrr.github.io/circle-progress/
 * Copyright (c) Tigran Sargsyan
 * Licensed MIT
 */
'use strict';

function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

(function (root, factory) {
  if (typeof define === 'function' && define.amd) {
    // AMD. Register as an anonymous module.
    define([], factory);
  } else if ((typeof module === "undefined" ? "undefined" : _typeof(module)) === 'object' && module.exports) {
    // Node. Does not work with strict CommonJS, but
    // only CommonJS-like environments that support module.exports,
    // like Node.
    module.exports = factory();
  } else {
    // Browser globals (root is window)
    root.CircleProgress = factory();
  }
})(typeof self !== 'undefined' ? self : void 0, function () {
  // Source: https://github.com/rogodec/svg-innerhtml-polyfill
  (function () {
    try {
      if (typeof SVGElement === 'undefined' || Boolean(SVGElement.prototype.innerHTML)) {
        return;
      }
    } catch (e) {
      return;
    }

    function serializeNode(node) {
      switch (node.nodeType) {
        case 1:
          return serializeElementNode(node);

        case 3:
          return serializeTextNode(node);

        case 8:
          return serializeCommentNode(node);
      }
    }

    function serializeTextNode(node) {
      return node.textContent.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function serializeCommentNode(node) {
      return '<!--' + node.nodeValue + '-->';
    }

    function serializeElementNode(node) {
      var output = '';
      output += '<' + node.tagName;

      if (node.hasAttributes()) {
        [].forEach.call(node.attributes, function (attrNode) {
          output += ' ' + attrNode.name + '="' + attrNode.value + '"';
        });
      }

      output += '>';

      if (node.hasChildNodes()) {
        [].forEach.call(node.childNodes, function (childNode) {
          output += serializeNode(childNode);
        });
      }

      output += '</' + node.tagName + '>';
      return output;
    }

    Object.defineProperty(SVGElement.prototype, 'innerHTML', {
      get: function get() {
        var output = '';
        [].forEach.call(this.childNodes, function (childNode) {
          output += serializeNode(childNode);
        });
        return output;
      },
      set: function set(markup) {
        while (this.firstChild) {
          this.removeChild(this.firstChild);
        }

        try {
          var dXML = new DOMParser();
          dXML.async = false;
          var sXML = '<svg xmlns=\'http://www.w3.org/2000/svg\' xmlns:xlink=\'http://www.w3.org/1999/xlink\'>' + markup + '</svg>';
          var svgDocElement = dXML.parseFromString(sXML, 'text/xml').documentElement;
          [].forEach.call(svgDocElement.childNodes, function (childNode) {
            this.appendChild(this.ownerDocument.importNode(childNode, true));
          }.bind(this));
        } catch (e) {
          throw new Error('Error parsing markup string');
        }
      }
    });
    Object.defineProperty(SVGElement.prototype, 'innerSVG', {
      get: function get() {
        return this.innerHTML;
      },
      set: function set(markup) {
        this.innerHTML = markup;
      }
    });
  })();

  'use strict';

  var svgpaper = function () {
    var paper, paperproto, _element, elementproto;
    /**
     * Create new paper holding a new SVG element
     * @param  {(HTMLElement|string)} container      Container element or selector string
     * @param  {(number|string)}      width          SVG width
     * @param  {(number|string)}      height         SVG height
     * @param  {Document}             [doc=document] HTML document. Defaults to current document
     * @return {Object}                              The paper
     */


    paper = function paper(container, width, height, doc) {
      var svg, me;
      doc = doc || document;
      me = Object.create(paperproto);
      if (typeof container === 'string') container = doc.querySelector(container);
      if (!container) return;
      svg = doc.createElementNS('http://www.w3.org/2000/svg', 'svg');
      svg.setAttribute('version', '1.1');
      if (width) svg.setAttribute('width', width);
      if (height) svg.setAttribute('height', height);
      if (width && height) svg.setAttribute('viewBox', '0 0 ' + width + ' ' + height);
      container.appendChild(svg);
      me.svg = svg;
      return me;
    };

    paperproto = {
      /**
       * Create a new SVG element
       * @param  {string}     name      Element name
       * @param  {Object}     attrs     Map of attributes
       * @param  {string}     content   Element content
       * @param  {SVGElement} [parent]  An element to append to. Defaults to the root SVG element
       * @return {object}               Element
       */
      element: function element(name, attrs, content, parent) {
        var el;
        el = _element(this, name, attrs, parent);
        if (content) el.el.innerHTML = content;
        return el;
      }
    };
    /**
     * General purpose element maker
     * @param  {Object}     paper    SVG Paper
     * @param  {string}     name     Element tag name
     * @param  {Object}     attrs    Attributes for the element
     * @param  {SVGElement} [parent] Another SVG Element to append the
     * @param  {Document}   [doc]    Document
     * @return {Object}              Element
     */

    _element = function _element(paper, name, attrs, parent, doc) {
      var attrNames, me;
      doc = doc || document;
      me = Object.create(elementproto);
      me.el = doc.createElementNS('http://www.w3.org/2000/svg', name);
      me.attr(attrs);
      (parent ? parent.el || parent : paper.svg).appendChild(me.el);
      return me;
    };

    elementproto = {
      /**
       * Set an attribute to a value
       * @param  {string} name  Attribute name
       * @param  {*}      value Attribute value
       * @return {object}       The element
       * */

      /**
      * Set attributes
      * @param {object} attrs  Map of name - values
      * @return {object}       The element
      */
      attr: function attr(name, value) {
        if (name === undefined) return this;

        if (_typeof(name) === 'object') {
          for (var key in name) {
            this.attr(key, name[key]);
          }

          return this;
        }

        if (value === undefined) return this.el.getAttributeNS(null, name);
        this.el.setAttribute(name, value);
        return this;
      },

      /**
       * Set content (innerHTML) for the element
       * @param  {string} content String of SVG
       * @return {object}         The element
       */
      content: function content(_content) {
        this.el.innerHTML = _content;
        return this;
      }
    }; // Export paper.

    return paper;
  }();

  'use strict';
  /**
   * Change any value using an animation easing function.
   * @param  {string}   Easing function.
   * @param  {number}   The initial value
   * @param  {number}   Change in value
   * @param  {number}   Animation duration
   * @param  {Function} Callback to be called on each iteration. The callback is passed one argument: current value.
   */


  var animator = function animator(easing, startValue, valueChange, dur, cb) {
    var easeFunc = typeof easing === 'string' ? animator.easings[easing] : easing;
    var tStart;

    var frame = function frame(t) {
      if (!tStart) tStart = t;
      t -= tStart;
      t = Math.min(t, dur);
      var curVal = easeFunc(t, startValue, valueChange, dur);
      cb(curVal);
      if (t < dur) requestAnimationFrame(frame);else cb(startValue + valueChange);
    };

    requestAnimationFrame(frame);
  };
  /**
   * Map of easings' strings to functions
   * Easing functions from http://gizma.com/easing/
   * @type {Object}
   */


  animator.easings = {
    linear: function linear(t, b, c, d) {
      return c * t / d + b;
    },
    easeInQuad: function easeInQuad(t, b, c, d) {
      t /= d;
      return c * t * t + b;
    },
    easeOutQuad: function easeOutQuad(t, b, c, d) {
      t /= d;
      return -c * t * (t - 2) + b;
    },
    easeInOutQuad: function easeInOutQuad(t, b, c, d) {
      t /= d / 2;
      if (t < 1) return c / 2 * t * t + b;
      t--;
      return -c / 2 * (t * (t - 2) - 1) + b;
    },
    easeInCubic: function easeInCubic(t, b, c, d) {
      t /= d;
      return c * t * t * t + b;
    },
    easeOutCubic: function easeOutCubic(t, b, c, d) {
      t /= d;
      t--;
      return c * (t * t * t + 1) + b;
    },
    easeInOutCubic: function easeInOutCubic(t, b, c, d) {
      t /= d / 2;
      if (t < 1) return c / 2 * t * t * t + b;
      t -= 2;
      return c / 2 * (t * t * t + 2) + b;
    },
    easeInQuart: function easeInQuart(t, b, c, d) {
      t /= d;
      return c * t * t * t * t + b;
    },
    easeOutQuart: function easeOutQuart(t, b, c, d) {
      t /= d;
      t--;
      return -c * (t * t * t * t - 1) + b;
    },
    easeInOutQuart: function easeInOutQuart(t, b, c, d) {
      t /= d / 2;
      if (t < 1) return c / 2 * t * t * t * t + b;
      t -= 2;
      return -c / 2 * (t * t * t * t - 2) + b;
    },
    easeInQuint: function easeInQuint(t, b, c, d) {
      t /= d;
      return c * t * t * t * t * t + b;
    },
    easeOutQuint: function easeOutQuint(t, b, c, d) {
      t /= d;
      t--;
      return c * (t * t * t * t * t + 1) + b;
    },
    easeInOutQuint: function easeInOutQuint(t, b, c, d) {
      t /= d / 2;
      if (t < 1) return c / 2 * t * t * t * t * t + b;
      t -= 2;
      return c / 2 * (t * t * t * t * t + 2) + b;
    },
    easeInSine: function easeInSine(t, b, c, d) {
      return -c * Math.cos(t / d * (Math.PI / 2)) + c + b;
    },
    easeOutSine: function easeOutSine(t, b, c, d) {
      return c * Math.sin(t / d * (Math.PI / 2)) + b;
    },
    easeInOutSine: function easeInOutSine(t, b, c, d) {
      return -c / 2 * (Math.cos(Math.PI * t / d) - 1) + b;
    },
    easeInExpo: function easeInExpo(t, b, c, d) {
      return c * Math.pow(2, 10 * (t / d - 1)) + b;
    },
    easeOutExpo: function easeOutExpo(t, b, c, d) {
      return c * (-Math.pow(2, -10 * t / d) + 1) + b;
    },
    easeInOutExpo: function easeInOutExpo(t, b, c, d) {
      t /= d / 2;
      if (t < 1) return c / 2 * Math.pow(2, 10 * (t - 1)) + b;
      t--;
      return c / 2 * (-Math.pow(2, -10 * t) + 2) + b;
    },
    easeInCirc: function easeInCirc(t, b, c, d) {
      t /= d;
      return -c * (Math.sqrt(1 - t * t) - 1) + b;
    },
    easeOutCirc: function easeOutCirc(t, b, c, d) {
      t /= d;
      t--;
      return c * Math.sqrt(1 - t * t) + b;
    },
    easeInOutCirc: function easeInOutCirc(t, b, c, d) {
      t /= d / 2;
      if (t < 1) return -c / 2 * (Math.sqrt(1 - t * t) - 1) + b;
      t -= 2;
      return c / 2 * (Math.sqrt(1 - t * t) + 1) + b;
    }
  };
  /* globals svgpaper, animator */

  var CircleProgress = function () {
    /**
     * Utility functions
     * @type {Object}
     */
    var util = {
      /**
       * Mathematical functions
       * @type {Object}
       */
      math: {
        /**
         * Convert polar coordinates (radius, angle) to cartesian ones (x, y)
         * @param  {float} r      Radius
         * @param  {float} angle  Angle
         * @return {object}       Cartesian coordinates as object: {x, y}
         */
        polarToCartesian: function polarToCartesian(r, angle) {
          return {
            x: r * Math.cos(angle * Math.PI / 180),
            y: r * Math.sin(angle * Math.PI / 180)
          };
        }
      }
    };
    /**
     * Create a new Circle Progress bar
     * @global
     * @class Circle Progress class
     */

    var CircleProgress = /*#__PURE__*/function () {
      _createClass(CircleProgress, [{
        key: "value",
        get: function get() {
          return this._attrs.value;
        },
        set: function set(val) {
          this.attr('value', val);
        }
      }, {
        key: "min",
        get: function get() {
          return this._attrs.min;
        },
        set: function set(val) {
          this.attr('min', val);
        }
      }, {
        key: "max",
        get: function get() {
          return this._attrs.max;
        },
        set: function set(val) {
          this.attr('max', val);
        }
      }, {
        key: "startAngle",
        get: function get() {
          return this._attrs.startAngle;
        },
        set: function set(val) {
          this.attr('startAngle', val);
        }
      }, {
        key: "clockwise",
        get: function get() {
          return this._attrs.clockwise;
        },
        set: function set(val) {
          this.attr('clockwise', val);
        }
      }, {
        key: "constrain",
        get: function get() {
          return this._attrs.constrain;
        },
        set: function set(val) {
          this.attr('constrain', val);
        }
      }, {
        key: "indeterminateText",
        get: function get() {
          return this._attrs.indeterminateText;
        },
        set: function set(val) {
          this.attr('indeterminateText', val);
        }
      }, {
        key: "textFormat",
        get: function get() {
          return this._attrs.textFormat;
        },
        set: function set(val) {
          this.attr('textFormat', val);
        }
      }, {
        key: "animation",
        get: function get() {
          return this._attrs.animation;
        },
        set: function set(val) {
          this.attr('animation', val);
        }
      }, {
        key: "animationDuration",
        get: function get() {
          return this._attrs.animationDuration;
        },
        set: function set(val) {
          this.attr('animationDuration', val);
        }
        /**
         * Construct the new CircleProgress instance
         * @constructs
         * @param {(HTMLElement|string)}  el    Either HTML element or a selector string
         * @param {Object}                opts  Options
         * @param {Document}              [doc] Document
         */

      }]);

      function CircleProgress(el) {
        var opts = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
        var doc = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : document;

        _classCallCheck(this, CircleProgress);

        var circleThickness;
        if (typeof el === 'string') el = doc.querySelector(el);
        if (!el) throw new Error('CircleProgress: you must pass the container element as the first argument'); // If element is already circleProgress, return the circleProgress object.

        if (el.circleProgress) return el.circleProgress;
        el.circleProgress = this;
        this.doc = doc;
        el.setAttribute('role', 'progressbar');
        this.el = el;
        opts = _objectSpread(_objectSpread({}, CircleProgress.defaults), opts);
        Object.defineProperty(this, '_attrs', {
          value: {},
          enumerable: false
        });
        circleThickness = opts.textFormat === 'valueOnCircle' ? 16 : 8;
        this.graph = {
          paper: svgpaper(el, 100, 100),
          value: 0
        };
        this.graph.paper.svg.setAttribute('class', 'circle-progress');
        this.graph.circle = this.graph.paper.element('circle').attr({
          "class": 'circle-progress-circle',
          cx: 50,
          cy: 50,
          r: 50 - circleThickness / 2,
          fill: 'none',
          stroke: '#ddd',
          'stroke-width': circleThickness
        });
        this.graph.sector = this.graph.paper.element('path').attr({
          d: CircleProgress._makeSectorPath(50, 50, 50 - circleThickness / 2, 0, 0),
          "class": 'circle-progress-value',
          fill: 'none',
          stroke: '#00E699',
          'stroke-width': circleThickness
        });
        this.graph.text = this.graph.paper.element('text', {
          "class": 'circle-progress-text',
          x: 50,
          y: 50,
          'font': '16px Arial, sans-serif',
          'text-anchor': 'middle',
          fill: '#999'
        });

        this._initText();

        this.attr(['indeterminateText', 'textFormat', 'startAngle', 'clockwise', 'animation', 'animationDuration', 'constrain', 'min', 'max', 'value'].filter(function (key) {
          return key in opts;
        }).map(function (key) {
          return [key, opts[key]];
        }));
      }
      /**
       * Set attributes
       * @param  {(Array|Object)} attrs Attributes as an array [[key,value],...] or map {key: value,...}
       * @return {CircleProgress}       The CircleProgress instance
       */


      _createClass(CircleProgress, [{
        key: "attr",
        value: function attr(attrs) {
          var _this = this;

          if (typeof attrs === 'string') {
            if (arguments.length === 1) return this._attrs[attrs];

            this._set(arguments[0], arguments[1]);

            this._updateGraph();

            return this;
          } else if (_typeof(attrs) !== 'object') {
            throw new TypeError("Wrong argument passed to attr. Expected object, got \"".concat(_typeof(attrs), "\""));
          }

          if (!Array.isArray(attrs)) {
            attrs = Object.keys(attrs).map(function (key) {
              return [key, attrs[key]];
            });
          }

          attrs.forEach(function (attr) {
            return _this._set(attr[0], attr[1]);
          });

          this._updateGraph();

          return this;
        }
        /**
         * Set an attribute to a value
         * @private
         * @param {string} key Attribute name
         * @param {*}      val Attribute value
         */

      }, {
        key: "_set",
        value: function _set(key, val) {
          var ariaAttrs = {
            value: 'aria-valuenow',
            min: 'aria-valuemin',
            max: 'aria-valuemax'
          },
              circleThickness;
          val = this._formatValue(key, val);
          if (val === undefined) throw new TypeError("Failed to set the ".concat(key, " property on CircleProgress: The provided value is non-finite."));
          if (this._attrs[key] === val) return;
          if (key === 'min' && val >= this.max) return;
          if (key === 'max' && val <= this.min) return;

          if (key === 'value' && val !== undefined && this.constrain) {
            if (this.min != null && val < this.min) val = this.min;
            if (this.max != null && val > this.max) val = this.max;
          }

          this._attrs[key] = val;

          if (key in ariaAttrs) {
            if (val !== undefined) this.el.setAttribute(ariaAttrs[key], val);else this.el.removeAttribute(ariaAttrs[key]);
          }

          if (['min', 'max', 'constrain'].indexOf(key) !== -1 && (this.value > this.max || this.value < this.min)) {
            this.value = Math.min(this.max, Math.max(this.min, this.value));
          }

          if (key === 'textFormat') {
            this._initText();

            circleThickness = val === 'valueOnCircle' ? 16 : 8;
            this.graph.sector.attr('stroke-width', circleThickness);
            this.graph.circle.attr('stroke-width', circleThickness);
          }
        }
        /**
         * Format attribute value according to its type
         * @private
         * @param  {string} key Attribute name
         * @param  {*}      val Attribute value
         * @return {*}          Formatted attribute value
         */

      }, {
        key: "_formatValue",
        value: function _formatValue(key, val) {
          switch (key) {
            case 'value':
            case 'min':
            case 'max':
              val = parseFloat(val);
              if (!isFinite(val)) val = undefined;
              break;

            case 'startAngle':
              val = parseFloat(val);
              if (!isFinite(val)) val = undefined;else val = Math.max(0, Math.min(360, val));
              break;

            case 'clockwise':
            case 'constrain':
              val = !!val;
              break;

            case 'indeterminateText':
              val = '' + val;
              break;

            case 'textFormat':
              if (typeof val !== 'function' && ['valueOnCircle', 'horizontal', 'vertical', 'percent', 'value', 'none'].indexOf(val) === -1) {
                throw new Error("Failed to set the \"textFormat\" property on CircleProgress: the provided value \"".concat(val, "\" is not a legal textFormat identifier."));
              }

              break;

            case 'animation':
              if (typeof val !== 'string' && typeof val !== 'function') {
                throw new TypeError("Failed to set \"animation\" property on CircleProgress: the value must be either string or function, ".concat(_typeof(val), " passed."));
              }

              if (typeof val === 'string' && val !== 'none' && !animator.easings[val]) {
                throw new Error("Failed to set \"animation\" on CircleProgress: the provided value ".concat(val, " is not a legal easing function name."));
              }

              break;
          }

          return val;
        }
        /**
         * Convert current value to angle
         * The caller is responsible to check if the state is not indeterminate.
         * This is done for optimization purposes as this method is called from within an animation.
         * @private
         * @return {float} Angle in degrees
         */

      }, {
        key: "_valueToAngle",
        value: function _valueToAngle() {
          var value = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : this.value;
          return Math.min(360, Math.max(0, (value - this.min) / (this.max - this.min) * 360));
        }
        /**
         * Check wether the progressbar is in indeterminate state
         * @private
         * @return {bool} True if the state is indeterminate, false if it is determinate
         */

      }, {
        key: "_isIndeterminate",
        value: function _isIndeterminate() {
          return !(typeof this.value === 'number' && typeof this.max === 'number' && typeof this.min === 'number');
        }
        /**
         * Make sector path for use in the "d" path attribute
         * @private
         * @param  {float} cx         Center x
         * @param  {float} cy         Center y
         * @param  {float} r          Radius
         * @param  {float} startAngle Start angle relative to straight upright axis
         * @param  {float} angle      Angle to rotate relative to straight upright axis
         * @param  {bool}  clockwise  Direction of rotation. Clockwise if truethy, anticlockwise if falsy
         * @return {string}           Path string
         */

      }, {
        key: "_positionValueText",

        /**
         * Position the value text on the circle
         * @private
         * @param  {float} angle Angle at which to position the text
         * @param  {float} r Circle radius measured to the middle of the stroke
         *                   as returned by {@link CircleProgress#_getRadius}, where text should be.
         *                   The radius is passed rather than calculated inside the function
         *                   for optimization purposes as this method is called from within an animation.
         */
        value: function _positionValueText(angle, r) {
          var coords = util.math.polarToCartesian(r, angle);
          this.graph.textVal.attr({
            x: 50 + coords.x,
            y: 50 + coords.y
          });
        }
        /**
         * Generate text representation of the values based on {@link CircleProgress#textFormat}
         * @private
         * TODO: Remove offsets in em when support for IE is dropped
         */

      }, {
        key: "_initText",
        value: function _initText() {
          this.graph.text.content('');

          switch (this.textFormat) {
            case 'valueOnCircle':
              this.graph.textVal = this.graph.paper.element('tspan', {
                x: 0,
                y: 0,
                dy: '0.4em',
                "class": 'circle-progress-text-value',
                'font-size': '12',
                fill: this.textFormat === 'valueOnCircle' ? '#fff' : '#888'
              }, '', this.graph.text);
              this.graph.textMax = this.graph.paper.element('tspan', {
                x: 50,
                y: 50,
                "class": 'circle-progress-text-max',
                'font-size': '22',
                'font-weight': 'bold',
                fill: '#ddd'
              }, '', this.graph.text); // IE

              if (!this.graph.text.el.hasAttribute('dominant-baseline')) this.graph.textMax.attr('dy', '0.4em');
              break;

            case 'horizontal':
              this.graph.textVal = this.graph.paper.element('tspan', {
                "class": 'circle-progress-text-value'
              }, '', this.graph.text);
              this.graph.textSeparator = this.graph.paper.element('tspan', {
                "class": 'circle-progress-text-separator'
              }, '/', this.graph.text);
              this.graph.textMax = this.graph.paper.element('tspan', {
                "class": 'circle-progress-text-max'
              }, '', this.graph.text);
              break;

            case 'vertical':
              if (this.graph.text.el.hasAttribute('dominant-baseline')) this.graph.text.attr('dominant-baseline', 'text-after-edge');
              this.graph.textVal = this.graph.paper.element('tspan', {
                "class": 'circle-progress-text-value',
                x: 50,
                dy: '-0.2em'
              }, '', this.graph.text);
              this.graph.textSeparator = this.graph.paper.element('tspan', {
                "class": 'circle-progress-text-separator',
                x: 50,
                dy: '0.1em',
                "font-family": "Arial, sans-serif"
              }, '___', this.graph.text);
              this.graph.textMax = this.graph.paper.element('tspan', {
                "class": 'circle-progress-text-max',
                x: 50,
                dy: '1.2em'
              }, '', this.graph.text);
              break;
          }

          if (this.textFormat !== 'vertical') {
            if (this.graph.text.el.hasAttribute('dominant-baseline')) this.graph.text.attr('dominant-baseline', 'central'); // IE
            else this.graph.text.attr('dy', '0.4em');
          }
        }
        /**
         * Update graphics
         * @private
         */

      }, {
        key: "_updateGraph",
        value: function _updateGraph() {
          var _this2 = this;

          var startAngle = this.startAngle - 90;

          var r = this._getRadius();

          if (!this._isIndeterminate()) {
            var clockwise = this.clockwise;

            var angle = this._valueToAngle();

            this.graph.circle.attr('r', r);

            if (this.animation !== 'none' && this.value !== this.graph.value) {
              animator(this.animation, this.graph.value, this.value - this.graph.value, this.animationDuration, function (value) {
                _this2._updateText(Math.round(value), (2 * startAngle + angle) / 2, r);

                angle = _this2._valueToAngle(value);

                _this2.graph.sector.attr('d', CircleProgress._makeSectorPath(50, 50, r, startAngle, angle, clockwise));
              });
            } else {
              this.graph.sector.attr('d', CircleProgress._makeSectorPath(50, 50, r, startAngle, angle, clockwise));

              this._updateText(this.value, (2 * startAngle + angle) / 2, r);
            }

            this.graph.value = this.value;
          } else {
            this._updateText(this.value, startAngle, r);
          }
        }
        /**
         * Update texts
         */

      }, {
        key: "_updateText",
        value: function _updateText(value, angle, r) {
          if (typeof this.textFormat === 'function') {
            this.graph.text.content(this.textFormat(value, this.max));
          } else if (this.textFormat === 'value') {
            this.graph.text.el.textContent = value !== undefined ? value : this.indeterminateText;
          } else if (this.textFormat === 'percent') {
            this.graph.text.el.textContent = (value !== undefined && this.max != null ? Math.round(value / this.max * 100) : this.indeterminateText) + '%';
          } else if (this.textFormat === 'none') {
            this.graph.text.el.textContent = '';
          } else {
            this.graph.textVal.el.textContent = value !== undefined ? value : this.indeterminateText;
            this.graph.textMax.el.textContent = this.max !== undefined ? this.max : this.indeterminateText;
          }

          if (this.textFormat === 'valueOnCircle') {
            this._positionValueText(angle, r);
          }
        }
        /**
         * Get circles' radius based on the calculated stroke widths of the value path and circle
         * @private
         * @return {float} The radius
         */

      }, {
        key: "_getRadius",
        value: function _getRadius() {
          return 50 - Math.max(parseFloat(this.doc.defaultView.getComputedStyle(this.graph.circle.el, null)['stroke-width']), parseFloat(this.doc.defaultView.getComputedStyle(this.graph.sector.el, null)['stroke-width'])) / 2;
        }
      }], [{
        key: "_makeSectorPath",
        value: function _makeSectorPath(cx, cy, r, startAngle, angle, clockwise) {
          clockwise = !!clockwise;

          if (angle > 0 && angle < 0.3) {
            // Tiny angles smaller than ~0.3° can produce weird-looking paths
            angle = 0;
          } else if (angle > 359.999) {
            // If progress is full, notch it back a little, so the path doesn't become 0-length
            angle = 359.999;
          }

          var endAngle = startAngle + angle * (clockwise * 2 - 1),
              startCoords = util.math.polarToCartesian(r, startAngle),
              endCoords = util.math.polarToCartesian(r, endAngle),
              x1 = cx + startCoords.x,
              x2 = cx + endCoords.x,
              y1 = cy + startCoords.y,
              y2 = cy + endCoords.y;
          return ["M", x1, y1, "A", r, r, 0, +(angle > 180), +clockwise, x2, y2].join(' ');
        }
      }]);

      return CircleProgress;
    }();

    CircleProgress.defaults = {
      startAngle: 0,
      min: 0,
      max: 1,
      constrain: true,
      indeterminateText: '?',
      clockwise: true,
      textFormat: 'horizontal',
      animation: 'easeInOutCubic',
      animationDuration: 600
    }; // Export circleProgress.

    return CircleProgress;
  }(); // Just return a value to define the module export.
  // This example returns an object, but the module
  // can return a function as the exported value.


  return CircleProgress;
});
