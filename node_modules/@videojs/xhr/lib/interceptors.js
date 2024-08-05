"use strict";

function _createForOfIteratorHelperLoose(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (it) return (it = it.call(o)).next.bind(it); if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; return function () { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

var InterceptorsStorage = /*#__PURE__*/function () {
  function InterceptorsStorage() {
    this.typeToInterceptorsMap_ = new Map();
    this.enabled_ = false;
  }

  var _proto = InterceptorsStorage.prototype;

  _proto.getIsEnabled = function getIsEnabled() {
    return this.enabled_;
  };

  _proto.enable = function enable() {
    this.enabled_ = true;
  };

  _proto.disable = function disable() {
    this.enabled_ = false;
  };

  _proto.reset = function reset() {
    this.typeToInterceptorsMap_ = new Map();
    this.enabled_ = false;
  };

  _proto.addInterceptor = function addInterceptor(type, interceptor) {
    if (!this.typeToInterceptorsMap_.has(type)) {
      this.typeToInterceptorsMap_.set(type, new Set());
    }

    var interceptorsSet = this.typeToInterceptorsMap_.get(type);

    if (interceptorsSet.has(interceptor)) {
      // already have this interceptor
      return false;
    }

    interceptorsSet.add(interceptor);
    return true;
  };

  _proto.removeInterceptor = function removeInterceptor(type, interceptor) {
    var interceptorsSet = this.typeToInterceptorsMap_.get(type);

    if (interceptorsSet && interceptorsSet.has(interceptor)) {
      interceptorsSet.delete(interceptor);
      return true;
    }

    return false;
  };

  _proto.clearInterceptorsByType = function clearInterceptorsByType(type) {
    var interceptorsSet = this.typeToInterceptorsMap_.get(type);

    if (!interceptorsSet) {
      return false;
    }

    this.typeToInterceptorsMap_.delete(type);
    this.typeToInterceptorsMap_.set(type, new Set());
    return true;
  };

  _proto.clear = function clear() {
    if (!this.typeToInterceptorsMap_.size) {
      return false;
    }

    this.typeToInterceptorsMap_ = new Map();
    return true;
  };

  _proto.getForType = function getForType(type) {
    return this.typeToInterceptorsMap_.get(type) || new Set();
  };

  _proto.execute = function execute(type, payload) {
    var interceptors = this.getForType(type);

    for (var _iterator = _createForOfIteratorHelperLoose(interceptors), _step; !(_step = _iterator()).done;) {
      var interceptor = _step.value;

      try {
        payload = interceptor(payload);
      } catch (e) {//ignore
      }
    }

    return payload;
  };

  return InterceptorsStorage;
}();

module.exports = InterceptorsStorage;