"use strict";

function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

(function (factory) {
  if (typeof define === 'function' && define.amd) {
    // AMD. Register as an anonymous module.
    define(['jquery'], factory);
  } else if ((typeof module === "undefined" ? "undefined" : _typeof(module)) === 'object' && module.exports) {
    // Node/CommonJS
    module.exports = function (root, jQuery) {
      if (jQuery === undefined) {
        // require('jQuery') returns a factory that requires window to
        // build a jQuery instance, we normalize how we use modules
        // that require this pattern but the window provided is a noop
        // if it's defined (how jquery works)
        if (typeof window !== 'undefined') {
          jQuery = require('jquery');
        } else {
          jQuery = require('jquery')(root);
        }
      }

      factory(jQuery);
      return jQuery;
    };
  } else {
    // Browser globals
    factory(jQuery);
  }
})(function (jQuery) {
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
  }();
  /*! jQuery UI - v1.11.4 - 2016-05-05
  * http://jqueryui.com
  * Includes: widget.js
  * Copyright jQuery Foundation and other contributors; Licensed MIT */


  (function (factory) {
    if (typeof define === "function" && define.amd) {
      // AMD. Register as an anonymous module.
      define(["jquery"], factory);
    } else {
      // Browser globals
      factory(jQuery);
    }
  })(function ($) {
    /*!
     * jQuery UI Widget 1.11.4
     * http://jqueryui.com
     *
     * Copyright jQuery Foundation and other contributors
     * Released under the MIT license.
     * http://jquery.org/license
     *
     * http://api.jqueryui.com/jQuery.widget/
     */
    var widget_uuid = 0,
        widget_slice = Array.prototype.slice;

    $.cleanData = function (orig) {
      return function (elems) {
        var events, elem, i;

        for (i = 0; (elem = elems[i]) != null; i++) {
          try {
            // Only trigger remove when necessary to save time
            events = $._data(elem, "events");

            if (events && events.remove) {
              $(elem).triggerHandler("remove");
            } // http://bugs.jquery.com/ticket/8235

          } catch (e) {}
        }

        orig(elems);
      };
    }($.cleanData);

    $.widget = function (name, base, prototype) {
      var fullName,
          existingConstructor,
          constructor,
          basePrototype,
          // proxiedPrototype allows the provided prototype to remain unmodified
      // so that it can be used as a mixin for multiple widgets (#8876)
      proxiedPrototype = {},
          namespace = name.split(".")[0];
      name = name.split(".")[1];
      fullName = namespace + "-" + name;

      if (!prototype) {
        prototype = base;
        base = $.Widget;
      } // create selector for plugin


      $.expr[":"][fullName.toLowerCase()] = function (elem) {
        return !!$.data(elem, fullName);
      };

      $[namespace] = $[namespace] || {};
      existingConstructor = $[namespace][name];

      constructor = $[namespace][name] = function (options, element) {
        // allow instantiation without "new" keyword
        if (!this._createWidget) {
          return new constructor(options, element);
        } // allow instantiation without initializing for simple inheritance
        // must use "new" keyword (the code above always passes args)


        if (arguments.length) {
          this._createWidget(options, element);
        }
      }; // extend with the existing constructor to carry over any static properties


      $.extend(constructor, existingConstructor, {
        version: prototype.version,
        // copy the object used to create the prototype in case we need to
        // redefine the widget later
        _proto: $.extend({}, prototype),
        // track widgets that inherit from this widget in case this widget is
        // redefined after a widget inherits from it
        _childConstructors: []
      });
      basePrototype = new base(); // we need to make the options hash a property directly on the new instance
      // otherwise we'll modify the options hash on the prototype that we're
      // inheriting from

      basePrototype.options = $.widget.extend({}, basePrototype.options);
      $.each(prototype, function (prop, value) {
        if (!$.isFunction(value)) {
          proxiedPrototype[prop] = value;
          return;
        }

        proxiedPrototype[prop] = function () {
          var _super = function _super() {
            return base.prototype[prop].apply(this, arguments);
          },
              _superApply = function _superApply(args) {
            return base.prototype[prop].apply(this, args);
          };

          return function () {
            var __super = this._super,
                __superApply = this._superApply,
                returnValue;
            this._super = _super;
            this._superApply = _superApply;
            returnValue = value.apply(this, arguments);
            this._super = __super;
            this._superApply = __superApply;
            return returnValue;
          };
        }();
      });
      constructor.prototype = $.widget.extend(basePrototype, {
        // TODO: remove support for widgetEventPrefix
        // always use the name + a colon as the prefix, e.g., draggable:start
        // don't prefix for widgets that aren't DOM-based
        widgetEventPrefix: existingConstructor ? basePrototype.widgetEventPrefix || name : name
      }, proxiedPrototype, {
        constructor: constructor,
        namespace: namespace,
        widgetName: name,
        widgetFullName: fullName
      }); // If this widget is being redefined then we need to find all widgets that
      // are inheriting from it and redefine all of them so that they inherit from
      // the new version of this widget. We're essentially trying to replace one
      // level in the prototype chain.

      if (existingConstructor) {
        $.each(existingConstructor._childConstructors, function (i, child) {
          var childPrototype = child.prototype; // redefine the child widget using the same prototype that was
          // originally used, but inherit from the new version of the base

          $.widget(childPrototype.namespace + "." + childPrototype.widgetName, constructor, child._proto);
        }); // remove the list of existing child constructors from the old constructor
        // so the old child constructors can be garbage collected

        delete existingConstructor._childConstructors;
      } else {
        base._childConstructors.push(constructor);
      }

      $.widget.bridge(name, constructor);
      return constructor;
    };

    $.widget.extend = function (target) {
      var input = widget_slice.call(arguments, 1),
          inputIndex = 0,
          inputLength = input.length,
          key,
          value;

      for (; inputIndex < inputLength; inputIndex++) {
        for (key in input[inputIndex]) {
          value = input[inputIndex][key];

          if (input[inputIndex].hasOwnProperty(key) && value !== undefined) {
            // Clone objects
            if ($.isPlainObject(value)) {
              target[key] = $.isPlainObject(target[key]) ? $.widget.extend({}, target[key], value) : // Don't extend strings, arrays, etc. with objects
              $.widget.extend({}, value); // Copy everything else by reference
            } else {
              target[key] = value;
            }
          }
        }
      }

      return target;
    };

    $.widget.bridge = function (name, object) {
      var fullName = object.prototype.widgetFullName || name;

      $.fn[name] = function (options) {
        var isMethodCall = typeof options === "string",
            args = widget_slice.call(arguments, 1),
            returnValue = this;

        if (isMethodCall) {
          this.each(function () {
            var methodValue,
                instance = $.data(this, fullName);

            if (options === "instance") {
              returnValue = instance;
              return false;
            }

            if (!instance) {
              return $.error("cannot call methods on " + name + " prior to initialization; " + "attempted to call method '" + options + "'");
            }

            if (!$.isFunction(instance[options]) || options.charAt(0) === "_") {
              return $.error("no such method '" + options + "' for " + name + " widget instance");
            }

            methodValue = instance[options].apply(instance, args);

            if (methodValue !== instance && methodValue !== undefined) {
              returnValue = methodValue && methodValue.jquery ? returnValue.pushStack(methodValue.get()) : methodValue;
              return false;
            }
          });
        } else {
          // Allow multiple hashes to be passed on init
          if (args.length) {
            options = $.widget.extend.apply(null, [options].concat(args));
          }

          this.each(function () {
            var instance = $.data(this, fullName);

            if (instance) {
              instance.option(options || {});

              if (instance._init) {
                instance._init();
              }
            } else {
              $.data(this, fullName, new object(options, this));
            }
          });
        }

        return returnValue;
      };
    };

    $.Widget = function ()
    /* options, element */
    {};

    $.Widget._childConstructors = [];
    $.Widget.prototype = {
      widgetName: "widget",
      widgetEventPrefix: "",
      defaultElement: "<div>",
      options: {
        disabled: false,
        // callbacks
        create: null
      },
      _createWidget: function _createWidget(options, element) {
        element = $(element || this.defaultElement || this)[0];
        this.element = $(element);
        this.uuid = widget_uuid++;
        this.eventNamespace = "." + this.widgetName + this.uuid;
        this.bindings = $();
        this.hoverable = $();
        this.focusable = $();

        if (element !== this) {
          $.data(element, this.widgetFullName, this);

          this._on(true, this.element, {
            remove: function remove(event) {
              if (event.target === element) {
                this.destroy();
              }
            }
          });

          this.document = $(element.style ? // element within the document
          element.ownerDocument : // element is window or document
          element.document || element);
          this.window = $(this.document[0].defaultView || this.document[0].parentWindow);
        }

        this.options = $.widget.extend({}, this.options, this._getCreateOptions(), options);

        this._create();

        this._trigger("create", null, this._getCreateEventData());

        this._init();
      },
      _getCreateOptions: $.noop,
      _getCreateEventData: $.noop,
      _create: $.noop,
      _init: $.noop,
      destroy: function destroy() {
        this._destroy(); // we can probably remove the unbind calls in 2.0
        // all event bindings should go through this._on()


        this.element.unbind(this.eventNamespace).removeData(this.widgetFullName) // support: jquery <1.6.3
        // http://bugs.jquery.com/ticket/9413
        .removeData($.camelCase(this.widgetFullName));
        this.widget().unbind(this.eventNamespace).removeAttr("aria-disabled").removeClass(this.widgetFullName + "-disabled " + "ui-state-disabled"); // clean up events and states

        this.bindings.unbind(this.eventNamespace);
        this.hoverable.removeClass("ui-state-hover");
        this.focusable.removeClass("ui-state-focus");
      },
      _destroy: $.noop,
      widget: function widget() {
        return this.element;
      },
      option: function option(key, value) {
        var options = key,
            parts,
            curOption,
            i;

        if (arguments.length === 0) {
          // don't return a reference to the internal hash
          return $.widget.extend({}, this.options);
        }

        if (typeof key === "string") {
          // handle nested keys, e.g., "foo.bar" => { foo: { bar: ___ } }
          options = {};
          parts = key.split(".");
          key = parts.shift();

          if (parts.length) {
            curOption = options[key] = $.widget.extend({}, this.options[key]);

            for (i = 0; i < parts.length - 1; i++) {
              curOption[parts[i]] = curOption[parts[i]] || {};
              curOption = curOption[parts[i]];
            }

            key = parts.pop();

            if (arguments.length === 1) {
              return curOption[key] === undefined ? null : curOption[key];
            }

            curOption[key] = value;
          } else {
            if (arguments.length === 1) {
              return this.options[key] === undefined ? null : this.options[key];
            }

            options[key] = value;
          }
        }

        this._setOptions(options);

        return this;
      },
      _setOptions: function _setOptions(options) {
        var key;

        for (key in options) {
          this._setOption(key, options[key]);
        }

        return this;
      },
      _setOption: function _setOption(key, value) {
        this.options[key] = value;

        if (key === "disabled") {
          this.widget().toggleClass(this.widgetFullName + "-disabled", !!value); // If the widget is becoming disabled, then nothing is interactive

          if (value) {
            this.hoverable.removeClass("ui-state-hover");
            this.focusable.removeClass("ui-state-focus");
          }
        }

        return this;
      },
      enable: function enable() {
        return this._setOptions({
          disabled: false
        });
      },
      disable: function disable() {
        return this._setOptions({
          disabled: true
        });
      },
      _on: function _on(suppressDisabledCheck, element, handlers) {
        var delegateElement,
            instance = this; // no suppressDisabledCheck flag, shuffle arguments

        if (typeof suppressDisabledCheck !== "boolean") {
          handlers = element;
          element = suppressDisabledCheck;
          suppressDisabledCheck = false;
        } // no element argument, shuffle and use this.element


        if (!handlers) {
          handlers = element;
          element = this.element;
          delegateElement = this.widget();
        } else {
          element = delegateElement = $(element);
          this.bindings = this.bindings.add(element);
        }

        $.each(handlers, function (event, handler) {
          function handlerProxy() {
            // allow widgets to customize the disabled handling
            // - disabled as an array instead of boolean
            // - disabled class as method for disabling individual parts
            if (!suppressDisabledCheck && (instance.options.disabled === true || $(this).hasClass("ui-state-disabled"))) {
              return;
            }

            return (typeof handler === "string" ? instance[handler] : handler).apply(instance, arguments);
          } // copy the guid so direct unbinding works


          if (typeof handler !== "string") {
            handlerProxy.guid = handler.guid = handler.guid || handlerProxy.guid || $.guid++;
          }

          var match = event.match(/^([\w:-]*)\s*(.*)$/),
              eventName = match[1] + instance.eventNamespace,
              selector = match[2];

          if (selector) {
            delegateElement.delegate(selector, eventName, handlerProxy);
          } else {
            element.bind(eventName, handlerProxy);
          }
        });
      },
      _off: function _off(element, eventName) {
        eventName = (eventName || "").split(" ").join(this.eventNamespace + " ") + this.eventNamespace;
        element.unbind(eventName).undelegate(eventName); // Clear the stack to avoid memory leaks (#10056)

        this.bindings = $(this.bindings.not(element).get());
        this.focusable = $(this.focusable.not(element).get());
        this.hoverable = $(this.hoverable.not(element).get());
      },
      _delay: function _delay(handler, delay) {
        function handlerProxy() {
          return (typeof handler === "string" ? instance[handler] : handler).apply(instance, arguments);
        }

        var instance = this;
        return setTimeout(handlerProxy, delay || 0);
      },
      _hoverable: function _hoverable(element) {
        this.hoverable = this.hoverable.add(element);

        this._on(element, {
          mouseenter: function mouseenter(event) {
            $(event.currentTarget).addClass("ui-state-hover");
          },
          mouseleave: function mouseleave(event) {
            $(event.currentTarget).removeClass("ui-state-hover");
          }
        });
      },
      _focusable: function _focusable(element) {
        this.focusable = this.focusable.add(element);

        this._on(element, {
          focusin: function focusin(event) {
            $(event.currentTarget).addClass("ui-state-focus");
          },
          focusout: function focusout(event) {
            $(event.currentTarget).removeClass("ui-state-focus");
          }
        });
      },
      _trigger: function _trigger(type, event, data) {
        var prop,
            orig,
            callback = this.options[type];
        data = data || {};
        event = $.Event(event);
        event.type = (type === this.widgetEventPrefix ? type : this.widgetEventPrefix + type).toLowerCase(); // the original event may come from any element
        // so we need to reset the target on the new event

        event.target = this.element[0]; // copy original event properties over to the new event

        orig = event.originalEvent;

        if (orig) {
          for (prop in orig) {
            if (!(prop in event)) {
              event[prop] = orig[prop];
            }
          }
        }

        this.element.trigger(event, data);
        return !($.isFunction(callback) && callback.apply(this.element[0], [event].concat(data)) === false || event.isDefaultPrevented());
      }
    };
    $.each({
      show: "fadeIn",
      hide: "fadeOut"
    }, function (method, defaultEffect) {
      $.Widget.prototype["_" + method] = function (element, options, callback) {
        if (typeof options === "string") {
          options = {
            effect: options
          };
        }

        var hasOptions,
            effectName = !options ? method : options === true || typeof options === "number" ? defaultEffect : options.effect || defaultEffect;
        options = options || {};

        if (typeof options === "number") {
          options = {
            duration: options
          };
        }

        hasOptions = !$.isEmptyObject(options);
        options.complete = callback;

        if (options.delay) {
          element.delay(options.delay);
        }

        if (hasOptions && $.effects && $.effects.effect[effectName]) {
          element[method](options);
        } else if (effectName !== method && element[effectName]) {
          element[effectName](options.duration, options.easing, callback);
        } else {
          element.queue(function (next) {
            $(this)[method]();

            if (callback) {
              callback.call(element[0]);
            }

            next();
          });
        }
      };
    });
    var widget = $.widget;
  });

  'use strict';

  jQuery.widget('tl.circleProgress', {
    options: jQuery.extend({}, CircleProgress.defaults),
    _create: function _create() {
      this.circleProgress = new CircleProgress(this.element[0], this.options);
      this.options = this.circleProgress._attrs;
    },
    _destroy: function _destroy() {},
    _setOptions: function _setOptions(opts) {
      this.circleProgress.attr(opts); // this._super(Object.keys(opts).reduce((opts, key) => {
      // 	opts[key] = this.circleProgress.attr(key);
      // 	return opts;
      // }, {}));
    },
    value: function value(val) {
      if (val === undefined) return this.options.value;

      this._setOptions({
        value: val
      });
    },
    min: function min(val) {
      if (val === undefined) return this.options.min;

      this._setOptions({
        min: val
      });
    },
    max: function max(val) {
      if (val === undefined) return this.options.max;

      this._setOptions({
        max: val
      });
    }
  });
  jQuery.fn.circleProgress.defaults = CircleProgress.defaults;
});
