'use strict';

Object.defineProperty(exports, '__esModule', { value: true });

var preact = require('preact');
var compat = require('preact/compat');
var internalCommon = require('./internal-common.cjs');



Object.defineProperty(exports, 'createPortal', {
	enumerable: true,
	get: function () { return compat.createPortal; }
});
exports.createContext = internalCommon.createContext;
exports.flushSync = internalCommon.flushSync;
Object.keys(preact).forEach(function (k) {
	if (k !== 'default' && !exports.hasOwnProperty(k)) Object.defineProperty(exports, k, {
		enumerable: true,
		get: function () { return preact[k]; }
	});
});
