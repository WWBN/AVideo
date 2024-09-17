"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

var _window = _interopRequireDefault(require("global/window"));

var DEFAULT_LOCATION = 'https://example.com';

var resolveUrl = function resolveUrl(baseUrl, relativeUrl) {
  // return early if we don't need to resolve
  if (/^[a-z]+:/i.test(relativeUrl)) {
    return relativeUrl;
  } // if baseUrl is a data URI, ignore it and resolve everything relative to window.location


  if (/^data:/.test(baseUrl)) {
    baseUrl = _window.default.location && _window.default.location.href || '';
  }

  var protocolLess = /^\/\//.test(baseUrl); // remove location if window.location isn't available (i.e. we're in node)
  // and if baseUrl isn't an absolute url

  var removeLocation = !_window.default.location && !/\/\//i.test(baseUrl); // if the base URL is relative then combine with the current location

  baseUrl = new _window.default.URL(baseUrl, _window.default.location || DEFAULT_LOCATION);
  var newUrl = new URL(relativeUrl, baseUrl); // if we're a protocol-less url, remove the protocol
  // and if we're location-less, remove the location
  // otherwise, return the url unmodified

  if (removeLocation) {
    return newUrl.href.slice(DEFAULT_LOCATION.length);
  } else if (protocolLess) {
    return newUrl.href.slice(newUrl.protocol.length);
  }

  return newUrl.href;
};

var _default = resolveUrl;
exports.default = _default;
module.exports = exports.default;