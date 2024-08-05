"use strict";

var window = require("global/window");

var _extends = require("@babel/runtime/helpers/extends");

var isFunction = require('is-function');

var InterceptorsStorage = require('./interceptors.js');

var RetryManager = require("./retry.js");

createXHR.httpHandler = require('./http-handler.js');
createXHR.requestInterceptorsStorage = new InterceptorsStorage();
createXHR.responseInterceptorsStorage = new InterceptorsStorage();
createXHR.retryManager = new RetryManager();
/**
 * @license
 * slighly modified parse-headers 2.0.2 <https://github.com/kesla/parse-headers/>
 * Copyright (c) 2014 David Björklund
 * Available under the MIT license
 * <https://github.com/kesla/parse-headers/blob/master/LICENCE>
 */

var parseHeaders = function parseHeaders(headers) {
  var result = {};

  if (!headers) {
    return result;
  }

  headers.trim().split('\n').forEach(function (row) {
    var index = row.indexOf(':');
    var key = row.slice(0, index).trim().toLowerCase();
    var value = row.slice(index + 1).trim();

    if (typeof result[key] === 'undefined') {
      result[key] = value;
    } else if (Array.isArray(result[key])) {
      result[key].push(value);
    } else {
      result[key] = [result[key], value];
    }
  });
  return result;
};

module.exports = createXHR; // Allow use of default import syntax in TypeScript

module.exports.default = createXHR;
createXHR.XMLHttpRequest = window.XMLHttpRequest || noop;
createXHR.XDomainRequest = "withCredentials" in new createXHR.XMLHttpRequest() ? createXHR.XMLHttpRequest : window.XDomainRequest;
forEachArray(["get", "put", "post", "patch", "head", "delete"], function (method) {
  createXHR[method === "delete" ? "del" : method] = function (uri, options, callback) {
    options = initParams(uri, options, callback);
    options.method = method.toUpperCase();
    return _createXHR(options);
  };
});

function forEachArray(array, iterator) {
  for (var i = 0; i < array.length; i++) {
    iterator(array[i]);
  }
}

function isEmpty(obj) {
  for (var i in obj) {
    if (obj.hasOwnProperty(i)) return false;
  }

  return true;
}

function initParams(uri, options, callback) {
  var params = uri;

  if (isFunction(options)) {
    callback = options;

    if (typeof uri === "string") {
      params = {
        uri: uri
      };
    }
  } else {
    params = _extends({}, options, {
      uri: uri
    });
  }

  params.callback = callback;
  return params;
}

function createXHR(uri, options, callback) {
  options = initParams(uri, options, callback);
  return _createXHR(options);
}

function _createXHR(options) {
  if (typeof options.callback === "undefined") {
    throw new Error("callback argument missing");
  } // call all registered request interceptors for a given request type:


  if (options.requestType && createXHR.requestInterceptorsStorage.getIsEnabled()) {
    var requestInterceptorPayload = {
      uri: options.uri || options.url,
      headers: options.headers || {},
      body: options.body,
      metadata: options.metadata || {},
      retry: options.retry,
      timeout: options.timeout
    };
    var updatedPayload = createXHR.requestInterceptorsStorage.execute(options.requestType, requestInterceptorPayload);
    options.uri = updatedPayload.uri;
    options.headers = updatedPayload.headers;
    options.body = updatedPayload.body;
    options.metadata = updatedPayload.metadata;
    options.retry = updatedPayload.retry;
    options.timeout = updatedPayload.timeout;
  }

  var called = false;

  var callback = function cbOnce(err, response, body) {
    if (!called) {
      called = true;
      options.callback(err, response, body);
    }
  };

  function readystatechange() {
    // do not call load 2 times when response interceptors are enabled
    // why do we even need this 2nd load?
    if (xhr.readyState === 4 && !createXHR.responseInterceptorsStorage.getIsEnabled()) {
      setTimeout(loadFunc, 0);
    }
  }

  function getBody() {
    // Chrome with requestType=blob throws errors arround when even testing access to responseText
    var body = undefined;

    if (xhr.response) {
      body = xhr.response;
    } else {
      body = xhr.responseText || getXml(xhr);
    }

    if (isJson) {
      try {
        body = JSON.parse(body);
      } catch (e) {}
    }

    return body;
  }

  function errorFunc(evt) {
    clearTimeout(timeoutTimer);
    clearTimeout(options.retryTimeout);

    if (!(evt instanceof Error)) {
      evt = new Error("" + (evt || "Unknown XMLHttpRequest Error"));
    }

    evt.statusCode = 0; // we would like to retry on error:

    if (!aborted && createXHR.retryManager.getIsEnabled() && options.retry && options.retry.shouldRetry()) {
      options.retryTimeout = setTimeout(function () {
        options.retry.moveToNextAttempt(); // we want to re-use the same options and the same xhr object:

        options.xhr = xhr;

        _createXHR(options);
      }, options.retry.getCurrentFuzzedDelay());
      return;
    } // call all registered response interceptors for a given request type:


    if (options.requestType && createXHR.responseInterceptorsStorage.getIsEnabled()) {
      var responseInterceptorPayload = {
        headers: failureResponse.headers || {},
        body: failureResponse.body,
        responseUrl: xhr.responseURL,
        responseType: xhr.responseType
      };

      var _updatedPayload = createXHR.responseInterceptorsStorage.execute(options.requestType, responseInterceptorPayload);

      failureResponse.body = _updatedPayload.body;
      failureResponse.headers = _updatedPayload.headers;
    }

    return callback(evt, failureResponse);
  } // will load the data & process the response in a special response object


  function loadFunc() {
    if (aborted) return;
    var status;
    clearTimeout(timeoutTimer);
    clearTimeout(options.retryTimeout);

    if (options.useXDR && xhr.status === undefined) {
      //IE8 CORS GET successful response doesn't have a status field, but body is fine
      status = 200;
    } else {
      status = xhr.status === 1223 ? 204 : xhr.status;
    }

    var response = failureResponse;
    var err = null;

    if (status !== 0) {
      response = {
        body: getBody(),
        statusCode: status,
        method: method,
        headers: {},
        url: uri,
        rawRequest: xhr
      };

      if (xhr.getAllResponseHeaders) {
        //remember xhr can in fact be XDR for CORS in IE
        response.headers = parseHeaders(xhr.getAllResponseHeaders());
      }
    } else {
      err = new Error("Internal XMLHttpRequest Error");
    } // call all registered response interceptors for a given request type:


    if (options.requestType && createXHR.responseInterceptorsStorage.getIsEnabled()) {
      var responseInterceptorPayload = {
        headers: response.headers || {},
        body: response.body,
        responseUrl: xhr.responseURL,
        responseType: xhr.responseType
      };

      var _updatedPayload2 = createXHR.responseInterceptorsStorage.execute(options.requestType, responseInterceptorPayload);

      response.body = _updatedPayload2.body;
      response.headers = _updatedPayload2.headers;
    }

    return callback(err, response, response.body);
  }

  var xhr = options.xhr || null;

  if (!xhr) {
    if (options.cors || options.useXDR) {
      xhr = new createXHR.XDomainRequest();
    } else {
      xhr = new createXHR.XMLHttpRequest();
    }
  }

  var key;
  var aborted;
  var uri = xhr.url = options.uri || options.url;
  var method = xhr.method = options.method || "GET";
  var body = options.body || options.data;
  var headers = xhr.headers = options.headers || {};
  var sync = !!options.sync;
  var isJson = false;
  var timeoutTimer;
  var failureResponse = {
    body: undefined,
    headers: {},
    statusCode: 0,
    method: method,
    url: uri,
    rawRequest: xhr
  };

  if ("json" in options && options.json !== false) {
    isJson = true;
    headers["accept"] || headers["Accept"] || (headers["Accept"] = "application/json"); //Don't override existing accept header declared by user

    if (method !== "GET" && method !== "HEAD") {
      headers["content-type"] || headers["Content-Type"] || (headers["Content-Type"] = "application/json"); //Don't override existing accept header declared by user

      body = JSON.stringify(options.json === true ? body : options.json);
    }
  }

  xhr.onreadystatechange = readystatechange;
  xhr.onload = loadFunc;
  xhr.onerror = errorFunc; // IE9 must have onprogress be set to a unique function.

  xhr.onprogress = function () {// IE must die
  };

  xhr.onabort = function () {
    aborted = true;
    clearTimeout(options.retryTimeout);
  };

  xhr.ontimeout = errorFunc;
  xhr.open(method, uri, !sync, options.username, options.password); //has to be after open

  if (!sync) {
    xhr.withCredentials = !!options.withCredentials;
  } // Cannot set timeout with sync request
  // not setting timeout on the xhr object, because of old webkits etc. not handling that correctly
  // both npm's request and jquery 1.x use this kind of timeout, so this is being consistent


  if (!sync && options.timeout > 0) {
    timeoutTimer = setTimeout(function () {
      if (aborted) return;
      aborted = true; //IE9 may still call readystatechange

      xhr.abort("timeout");
      var e = new Error("XMLHttpRequest timeout");
      e.code = "ETIMEDOUT";
      errorFunc(e);
    }, options.timeout);
  }

  if (xhr.setRequestHeader) {
    for (key in headers) {
      if (headers.hasOwnProperty(key)) {
        xhr.setRequestHeader(key, headers[key]);
      }
    }
  } else if (options.headers && !isEmpty(options.headers)) {
    throw new Error("Headers cannot be set on an XDomainRequest object");
  }

  if ("responseType" in options) {
    xhr.responseType = options.responseType;
  }

  if ("beforeSend" in options && typeof options.beforeSend === "function") {
    options.beforeSend(xhr);
  } // Microsoft Edge browser sends "undefined" when send is called with undefined value.
  // XMLHttpRequest spec says to pass null as body to indicate no body
  // See https://github.com/naugtur/xhr/issues/100.


  xhr.send(body || null);
  return xhr;
}

function getXml(xhr) {
  // xhr.responseXML will throw Exception "InvalidStateError" or "DOMException"
  // See https://developer.mozilla.org/en-US/docs/Web/API/XMLHttpRequest/responseXML.
  try {
    if (xhr.responseType === "document") {
      return xhr.responseXML;
    }

    var firefoxBugTakenEffect = xhr.responseXML && xhr.responseXML.documentElement.nodeName === "parsererror";

    if (xhr.responseType === "" && !firefoxBugTakenEffect) {
      return xhr.responseXML;
    }
  } catch (e) {}

  return null;
}

function noop() {}