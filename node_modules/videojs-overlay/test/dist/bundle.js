/*! @name videojs-overlay @version 3.1.0 @license Apache-2.0 */
(function (QUnit, sinon, videojs) {
	'use strict';

	function _interopDefaultLegacy (e) { return e && typeof e === 'object' && 'default' in e ? e : { 'default': e }; }

	var QUnit__default = /*#__PURE__*/_interopDefaultLegacy(QUnit);
	var sinon__default = /*#__PURE__*/_interopDefaultLegacy(sinon);
	var videojs__default = /*#__PURE__*/_interopDefaultLegacy(videojs);

	var commonjsGlobal = typeof globalThis !== 'undefined' ? globalThis : typeof window !== 'undefined' ? window : typeof global !== 'undefined' ? global : typeof self !== 'undefined' ? self : {};

	function getAugmentedNamespace(n) {
		if (n.__esModule) return n;
		var a = Object.defineProperty({}, '__esModule', {value: true});
		Object.keys(n).forEach(function (k) {
			var d = Object.getOwnPropertyDescriptor(n, k);
			Object.defineProperty(a, k, d.get ? d : {
				enumerable: true,
				get: function () {
					return n[k];
				}
			});
		});
		return a;
	}

	var _nodeResolve_empty = {};

	var _nodeResolve_empty$1 = /*#__PURE__*/Object.freeze({
		__proto__: null,
		'default': _nodeResolve_empty
	});

	var require$$0 = /*@__PURE__*/getAugmentedNamespace(_nodeResolve_empty$1);

	var topLevel = typeof commonjsGlobal !== 'undefined' ? commonjsGlobal : typeof window !== 'undefined' ? window : {};
	var minDoc = require$$0;
	var doccy;
	if (typeof document !== 'undefined') {
	  doccy = document;
	} else {
	  doccy = topLevel['__GLOBAL_DOCUMENT_CACHE@4'];
	  if (!doccy) {
	    doccy = topLevel['__GLOBAL_DOCUMENT_CACHE@4'] = minDoc;
	  }
	}
	var document_1 = doccy;

	var win;
	if (typeof window !== "undefined") {
	  win = window;
	} else if (typeof commonjsGlobal !== "undefined") {
	  win = commonjsGlobal;
	} else if (typeof self !== "undefined") {
	  win = self;
	} else {
	  win = {};
	}
	var window_1 = win;

	var version = "3.1.0";

	function cov_111dw1zsow() {
	  var path = "/opt/teamcity/work/d11199ef50ebf023/src/plugin.js";
	  var hash = "3816beb1960bd4803e22f00b53461943da936940";
	  var global = new Function("return this")();
	  var gcv = "__coverage__";
	  var coverageData = {
	    path: "/opt/teamcity/work/d11199ef50ebf023/src/plugin.js",
	    statementMap: {
	      "0": {
	        start: {
	          line: 5,
	          column: 17
	        },
	        end: {
	          line: 16,
	          column: 1
	        }
	      },
	      "1": {
	        start: {
	          line: 18,
	          column: 18
	        },
	        end: {
	          line: 18,
	          column: 51
	        }
	      },
	      "2": {
	        start: {
	          line: 20,
	          column: 12
	        },
	        end: {
	          line: 20,
	          column: 34
	        }
	      },
	      "3": {
	        start: {
	          line: 21,
	          column: 23
	        },
	        end: {
	          line: 21,
	          column: 63
	        }
	      },
	      "4": {
	        start: {
	          line: 33,
	          column: 17
	        },
	        end: {
	          line: 33,
	          column: 54
	        }
	      },
	      "5": {
	        start: {
	          line: 33,
	          column: 22
	        },
	        end: {
	          line: 33,
	          column: 54
	        }
	      },
	      "6": {
	        start: {
	          line: 42,
	          column: 24
	        },
	        end: {
	          line: 42,
	          column: 71
	        }
	      },
	      "7": {
	        start: {
	          line: 42,
	          column: 29
	        },
	        end: {
	          line: 42,
	          column: 71
	        }
	      },
	      "8": {
	        start: {
	          line: 53,
	          column: 4
	        },
	        end: {
	          line: 53,
	          column: 27
	        }
	      },
	      "9": {
	        start: {
	          line: 55,
	          column: 4
	        },
	        end: {
	          line: 67,
	          column: 7
	        }
	      },
	      "10": {
	        start: {
	          line: 56,
	          column: 20
	        },
	        end: {
	          line: 56,
	          column: 38
	        }
	      },
	      "11": {
	        start: {
	          line: 58,
	          column: 6
	        },
	        end: {
	          line: 66,
	          column: 7
	        }
	      },
	      "12": {
	        start: {
	          line: 59,
	          column: 8
	        },
	        end: {
	          line: 59,
	          column: 44
	        }
	      },
	      "13": {
	        start: {
	          line: 60,
	          column: 13
	        },
	        end: {
	          line: 66,
	          column: 7
	        }
	      },
	      "14": {
	        start: {
	          line: 61,
	          column: 8
	        },
	        end: {
	          line: 61,
	          column: 37
	        }
	      },
	      "15": {
	        start: {
	          line: 64,
	          column: 13
	        },
	        end: {
	          line: 66,
	          column: 7
	        }
	      },
	      "16": {
	        start: {
	          line: 65,
	          column: 8
	        },
	        end: {
	          line: 65,
	          column: 77
	        }
	      },
	      "17": {
	        start: {
	          line: 76,
	          column: 4
	        },
	        end: {
	          line: 78,
	          column: 7
	        }
	      },
	      "18": {
	        start: {
	          line: 77,
	          column: 6
	        },
	        end: {
	          line: 77,
	          column: 64
	        }
	      },
	      "19": {
	        start: {
	          line: 77,
	          column: 26
	        },
	        end: {
	          line: 77,
	          column: 63
	        }
	      },
	      "20": {
	        start: {
	          line: 82,
	          column: 4
	        },
	        end: {
	          line: 84,
	          column: 5
	        }
	      },
	      "21": {
	        start: {
	          line: 83,
	          column: 6
	        },
	        end: {
	          line: 83,
	          column: 58
	        }
	      },
	      "22": {
	        start: {
	          line: 86,
	          column: 4
	        },
	        end: {
	          line: 86,
	          column: 121
	        }
	      },
	      "23": {
	        start: {
	          line: 88,
	          column: 4
	        },
	        end: {
	          line: 88,
	          column: 16
	        }
	      },
	      "24": {
	        start: {
	          line: 92,
	          column: 20
	        },
	        end: {
	          line: 92,
	          column: 33
	        }
	      },
	      "25": {
	        start: {
	          line: 93,
	          column: 20
	        },
	        end: {
	          line: 93,
	          column: 35
	        }
	      },
	      "26": {
	        start: {
	          line: 95,
	          column: 23
	        },
	        end: {
	          line: 95,
	          column: 102
	        }
	      },
	      "27": {
	        start: {
	          line: 96,
	          column: 15
	        },
	        end: {
	          line: 104,
	          column: 6
	        }
	      },
	      "28": {
	        start: {
	          line: 106,
	          column: 4
	        },
	        end: {
	          line: 112,
	          column: 5
	        }
	      },
	      "29": {
	        start: {
	          line: 107,
	          column: 6
	        },
	        end: {
	          line: 107,
	          column: 29
	        }
	      },
	      "30": {
	        start: {
	          line: 108,
	          column: 11
	        },
	        end: {
	          line: 112,
	          column: 5
	        }
	      },
	      "31": {
	        start: {
	          line: 109,
	          column: 6
	        },
	        end: {
	          line: 109,
	          column: 30
	        }
	      },
	      "32": {
	        start: {
	          line: 111,
	          column: 6
	        },
	        end: {
	          line: 111,
	          column: 37
	        }
	      },
	      "33": {
	        start: {
	          line: 114,
	          column: 4
	        },
	        end: {
	          line: 114,
	          column: 14
	        }
	      },
	      "34": {
	        start: {
	          line: 124,
	          column: 4
	        },
	        end: {
	          line: 126,
	          column: 5
	        }
	      },
	      "35": {
	        start: {
	          line: 125,
	          column: 6
	        },
	        end: {
	          line: 125,
	          column: 13
	        }
	      },
	      "36": {
	        start: {
	          line: 128,
	          column: 16
	        },
	        end: {
	          line: 128,
	          column: 27
	        }
	      },
	      "37": {
	        start: {
	          line: 129,
	          column: 13
	        },
	        end: {
	          line: 129,
	          column: 16
	        }
	      },
	      "38": {
	        start: {
	          line: 132,
	          column: 4
	        },
	        end: {
	          line: 134,
	          column: 5
	        }
	      },
	      "39": {
	        start: {
	          line: 133,
	          column: 6
	        },
	        end: {
	          line: 133,
	          column: 29
	        }
	      },
	      "40": {
	        start: {
	          line: 136,
	          column: 4
	        },
	        end: {
	          line: 136,
	          column: 47
	        }
	      },
	      "41": {
	        start: {
	          line: 145,
	          column: 4
	        },
	        end: {
	          line: 145,
	          column: 17
	        }
	      },
	      "42": {
	        start: {
	          line: 147,
	          column: 4
	        },
	        end: {
	          line: 147,
	          column: 25
	        }
	      },
	      "43": {
	        start: {
	          line: 148,
	          column: 4
	        },
	        end: {
	          line: 148,
	          column: 68
	        }
	      },
	      "44": {
	        start: {
	          line: 151,
	          column: 4
	        },
	        end: {
	          line: 154,
	          column: 5
	        }
	      },
	      "45": {
	        start: {
	          line: 152,
	          column: 6
	        },
	        end: {
	          line: 152,
	          column: 70
	        }
	      },
	      "46": {
	        start: {
	          line: 153,
	          column: 6
	        },
	        end: {
	          line: 153,
	          column: 65
	        }
	      },
	      "47": {
	        start: {
	          line: 156,
	          column: 4
	        },
	        end: {
	          line: 156,
	          column: 66
	        }
	      },
	      "48": {
	        start: {
	          line: 158,
	          column: 4
	        },
	        end: {
	          line: 158,
	          column: 16
	        }
	      },
	      "49": {
	        start: {
	          line: 171,
	          column: 16
	        },
	        end: {
	          line: 171,
	          column: 33
	        }
	      },
	      "50": {
	        start: {
	          line: 173,
	          column: 4
	        },
	        end: {
	          line: 173,
	          column: 56
	        }
	      },
	      "51": {
	        start: {
	          line: 182,
	          column: 4
	        },
	        end: {
	          line: 182,
	          column: 17
	        }
	      },
	      "52": {
	        start: {
	          line: 183,
	          column: 4
	        },
	        end: {
	          line: 183,
	          column: 67
	        }
	      },
	      "53": {
	        start: {
	          line: 184,
	          column: 4
	        },
	        end: {
	          line: 184,
	          column: 24
	        }
	      },
	      "54": {
	        start: {
	          line: 185,
	          column: 4
	        },
	        end: {
	          line: 185,
	          column: 72
	        }
	      },
	      "55": {
	        start: {
	          line: 188,
	          column: 4
	        },
	        end: {
	          line: 191,
	          column: 5
	        }
	      },
	      "56": {
	        start: {
	          line: 189,
	          column: 6
	        },
	        end: {
	          line: 189,
	          column: 66
	        }
	      },
	      "57": {
	        start: {
	          line: 190,
	          column: 6
	        },
	        end: {
	          line: 190,
	          column: 64
	        }
	      },
	      "58": {
	        start: {
	          line: 193,
	          column: 4
	        },
	        end: {
	          line: 193,
	          column: 16
	        }
	      },
	      "59": {
	        start: {
	          line: 206,
	          column: 18
	        },
	        end: {
	          line: 206,
	          column: 37
	        }
	      },
	      "60": {
	        start: {
	          line: 207,
	          column: 16
	        },
	        end: {
	          line: 207,
	          column: 33
	        }
	      },
	      "61": {
	        start: {
	          line: 209,
	          column: 4
	        },
	        end: {
	          line: 226,
	          column: 5
	        }
	      },
	      "62": {
	        start: {
	          line: 211,
	          column: 6
	        },
	        end: {
	          line: 219,
	          column: 7
	        }
	      },
	      "63": {
	        start: {
	          line: 212,
	          column: 8
	        },
	        end: {
	          line: 212,
	          column: 43
	        }
	      },
	      "64": {
	        start: {
	          line: 216,
	          column: 13
	        },
	        end: {
	          line: 219,
	          column: 7
	        }
	      },
	      "65": {
	        start: {
	          line: 217,
	          column: 8
	        },
	        end: {
	          line: 217,
	          column: 39
	        }
	      },
	      "66": {
	        start: {
	          line: 218,
	          column: 8
	        },
	        end: {
	          line: 218,
	          column: 29
	        }
	      },
	      "67": {
	        start: {
	          line: 225,
	          column: 6
	        },
	        end: {
	          line: 225,
	          column: 40
	        }
	      },
	      "68": {
	        start: {
	          line: 228,
	          column: 4
	        },
	        end: {
	          line: 228,
	          column: 26
	        }
	      },
	      "69": {
	        start: {
	          line: 237,
	          column: 17
	        },
	        end: {
	          line: 237,
	          column: 44
	        }
	      },
	      "70": {
	        start: {
	          line: 239,
	          column: 4
	        },
	        end: {
	          line: 241,
	          column: 5
	        }
	      },
	      "71": {
	        start: {
	          line: 240,
	          column: 6
	        },
	        end: {
	          line: 240,
	          column: 18
	        }
	      },
	      "72": {
	        start: {
	          line: 250,
	          column: 17
	        },
	        end: {
	          line: 250,
	          column: 44
	        }
	      },
	      "73": {
	        start: {
	          line: 252,
	          column: 4
	        },
	        end: {
	          line: 254,
	          column: 5
	        }
	      },
	      "74": {
	        start: {
	          line: 253,
	          column: 6
	        },
	        end: {
	          line: 253,
	          column: 18
	        }
	      },
	      "75": {
	        start: {
	          line: 264,
	          column: 17
	        },
	        end: {
	          line: 264,
	          column: 44
	        }
	      },
	      "76": {
	        start: {
	          line: 265,
	          column: 21
	        },
	        end: {
	          line: 265,
	          column: 39
	        }
	      },
	      "77": {
	        start: {
	          line: 266,
	          column: 18
	        },
	        end: {
	          line: 266,
	          column: 37
	        }
	      },
	      "78": {
	        start: {
	          line: 267,
	          column: 16
	        },
	        end: {
	          line: 267,
	          column: 33
	        }
	      },
	      "79": {
	        start: {
	          line: 270,
	          column: 4
	        },
	        end: {
	          line: 290,
	          column: 5
	        }
	      },
	      "80": {
	        start: {
	          line: 271,
	          column: 6
	        },
	        end: {
	          line: 271,
	          column: 36
	        }
	      },
	      "81": {
	        start: {
	          line: 276,
	          column: 6
	        },
	        end: {
	          line: 289,
	          column: 7
	        }
	      },
	      "82": {
	        start: {
	          line: 277,
	          column: 8
	        },
	        end: {
	          line: 277,
	          column: 92
	        }
	      },
	      "83": {
	        start: {
	          line: 278,
	          column: 8
	        },
	        end: {
	          line: 278,
	          column: 40
	        }
	      },
	      "84": {
	        start: {
	          line: 279,
	          column: 8
	        },
	        end: {
	          line: 279,
	          column: 20
	        }
	      },
	      "85": {
	        start: {
	          line: 285,
	          column: 13
	        },
	        end: {
	          line: 289,
	          column: 7
	        }
	      },
	      "86": {
	        start: {
	          line: 286,
	          column: 8
	        },
	        end: {
	          line: 286,
	          column: 111
	        }
	      },
	      "87": {
	        start: {
	          line: 287,
	          column: 8
	        },
	        end: {
	          line: 287,
	          column: 40
	        }
	      },
	      "88": {
	        start: {
	          line: 288,
	          column: 8
	        },
	        end: {
	          line: 288,
	          column: 20
	        }
	      },
	      "89": {
	        start: {
	          line: 292,
	          column: 4
	        },
	        end: {
	          line: 292,
	          column: 30
	        }
	      },
	      "90": {
	        start: {
	          line: 296,
	          column: 0
	        },
	        end: {
	          line: 296,
	          column: 46
	        }
	      },
	      "91": {
	        start: {
	          line: 304,
	          column: 15
	        },
	        end: {
	          line: 417,
	          column: 1
	        }
	      },
	      "92": {
	        start: {
	          line: 305,
	          column: 17
	        },
	        end: {
	          line: 305,
	          column: 21
	        }
	      },
	      "93": {
	        start: {
	          line: 306,
	          column: 19
	        },
	        end: {
	          line: 306,
	          column: 58
	        }
	      },
	      "94": {
	        start: {
	          line: 309,
	          column: 2
	        },
	        end: {
	          line: 317,
	          column: 3
	        }
	      },
	      "95": {
	        start: {
	          line: 310,
	          column: 4
	        },
	        end: {
	          line: 316,
	          column: 7
	        }
	      },
	      "96": {
	        start: {
	          line: 311,
	          column: 6
	        },
	        end: {
	          line: 311,
	          column: 32
	        }
	      },
	      "97": {
	        start: {
	          line: 312,
	          column: 6
	        },
	        end: {
	          line: 314,
	          column: 7
	        }
	      },
	      "98": {
	        start: {
	          line: 313,
	          column: 8
	        },
	        end: {
	          line: 313,
	          column: 45
	        }
	      },
	      "99": {
	        start: {
	          line: 315,
	          column: 6
	        },
	        end: {
	          line: 315,
	          column: 24
	        }
	      },
	      "100": {
	        start: {
	          line: 319,
	          column: 19
	        },
	        end: {
	          line: 319,
	          column: 36
	        }
	      },
	      "101": {
	        start: {
	          line: 323,
	          column: 2
	        },
	        end: {
	          line: 323,
	          column: 27
	        }
	      },
	      "102": {
	        start: {
	          line: 325,
	          column: 22
	        },
	        end: {
	          line: 358,
	          column: 3
	        }
	      },
	      "103": {
	        start: {
	          line: 326,
	          column: 4
	        },
	        end: {
	          line: 357,
	          column: 7
	        }
	      },
	      "104": {
	        start: {
	          line: 327,
	          column: 27
	        },
	        end: {
	          line: 327,
	          column: 60
	        }
	      },
	      "105": {
	        start: {
	          line: 328,
	          column: 33
	        },
	        end: {
	          line: 328,
	          column: 128
	        }
	      },
	      "106": {
	        start: {
	          line: 330,
	          column: 6
	        },
	        end: {
	          line: 332,
	          column: 7
	        }
	      },
	      "107": {
	        start: {
	          line: 331,
	          column: 8
	        },
	        end: {
	          line: 331,
	          column: 54
	        }
	      },
	      "108": {
	        start: {
	          line: 334,
	          column: 6
	        },
	        end: {
	          line: 347,
	          column: 7
	        }
	      },
	      "109": {
	        start: {
	          line: 335,
	          column: 29
	        },
	        end: {
	          line: 335,
	          column: 58
	        }
	      },
	      "110": {
	        start: {
	          line: 337,
	          column: 8
	        },
	        end: {
	          line: 339,
	          column: 9
	        }
	      },
	      "111": {
	        start: {
	          line: 338,
	          column: 10
	        },
	        end: {
	          line: 338,
	          column: 85
	        }
	      },
	      "112": {
	        start: {
	          line: 341,
	          column: 8
	        },
	        end: {
	          line: 346,
	          column: 9
	        }
	      },
	      "113": {
	        start: {
	          line: 342,
	          column: 38
	        },
	        end: {
	          line: 342,
	          column: 88
	        }
	      },
	      "114": {
	        start: {
	          line: 343,
	          column: 34
	        },
	        end: {
	          line: 343,
	          column: 104
	        }
	      },
	      "115": {
	        start: {
	          line: 345,
	          column: 10
	        },
	        end: {
	          line: 345,
	          column: 33
	        }
	      },
	      "116": {
	        start: {
	          line: 349,
	          column: 26
	        },
	        end: {
	          line: 349,
	          column: 64
	        }
	      },
	      "117": {
	        start: {
	          line: 351,
	          column: 6
	        },
	        end: {
	          line: 354,
	          column: 8
	        }
	      },
	      "118": {
	        start: {
	          line: 356,
	          column: 6
	        },
	        end: {
	          line: 356,
	          column: 25
	        }
	      },
	      "119": {
	        start: {
	          line: 360,
	          column: 2
	        },
	        end: {
	          line: 360,
	          column: 41
	        }
	      },
	      "120": {
	        start: {
	          line: 372,
	          column: 4
	        },
	        end: {
	          line: 374,
	          column: 5
	        }
	      },
	      "121": {
	        start: {
	          line: 373,
	          column: 6
	        },
	        end: {
	          line: 373,
	          column: 20
	        }
	      },
	      "122": {
	        start: {
	          line: 376,
	          column: 26
	        },
	        end: {
	          line: 376,
	          column: 43
	        }
	      },
	      "123": {
	        start: {
	          line: 378,
	          column: 4
	        },
	        end: {
	          line: 378,
	          column: 62
	        }
	      },
	      "124": {
	        start: {
	          line: 380,
	          column: 4
	        },
	        end: {
	          line: 380,
	          column: 25
	        }
	      },
	      "125": {
	        start: {
	          line: 393,
	          column: 18
	        },
	        end: {
	          line: 393,
	          column: 48
	        }
	      },
	      "126": {
	        start: {
	          line: 395,
	          column: 4
	        },
	        end: {
	          line: 400,
	          column: 5
	        }
	      },
	      "127": {
	        start: {
	          line: 396,
	          column: 6
	        },
	        end: {
	          line: 396,
	          column: 50
	        }
	      },
	      "128": {
	        start: {
	          line: 397,
	          column: 6
	        },
	        end: {
	          line: 397,
	          column: 40
	        }
	      },
	      "129": {
	        start: {
	          line: 399,
	          column: 6
	        },
	        end: {
	          line: 399,
	          column: 70
	        }
	      },
	      "130": {
	        start: {
	          line: 409,
	          column: 4
	        },
	        end: {
	          line: 409,
	          column: 28
	        }
	      },
	      "131": {
	        start: {
	          line: 412,
	          column: 2
	        },
	        end: {
	          line: 416,
	          column: 4
	        }
	      },
	      "132": {
	        start: {
	          line: 419,
	          column: 0
	        },
	        end: {
	          line: 419,
	          column: 25
	        }
	      },
	      "133": {
	        start: {
	          line: 421,
	          column: 0
	        },
	        end: {
	          line: 421,
	          column: 34
	        }
	      }
	    },
	    fnMap: {
	      "0": {
	        name: "(anonymous_0)",
	        decl: {
	          start: {
	            line: 33,
	            column: 17
	          },
	          end: {
	            line: 33,
	            column: 18
	          }
	        },
	        loc: {
	          start: {
	            line: 33,
	            column: 22
	          },
	          end: {
	            line: 33,
	            column: 54
	          }
	        },
	        line: 33
	      },
	      "1": {
	        name: "(anonymous_1)",
	        decl: {
	          start: {
	            line: 42,
	            column: 24
	          },
	          end: {
	            line: 42,
	            column: 25
	          }
	        },
	        loc: {
	          start: {
	            line: 42,
	            column: 29
	          },
	          end: {
	            line: 42,
	            column: 71
	          }
	        },
	        line: 42
	      },
	      "2": {
	        name: "(anonymous_2)",
	        decl: {
	          start: {
	            line: 52,
	            column: 2
	          },
	          end: {
	            line: 52,
	            column: 3
	          }
	        },
	        loc: {
	          start: {
	            line: 52,
	            column: 31
	          },
	          end: {
	            line: 89,
	            column: 3
	          }
	        },
	        line: 52
	      },
	      "3": {
	        name: "(anonymous_3)",
	        decl: {
	          start: {
	            line: 55,
	            column: 29
	          },
	          end: {
	            line: 55,
	            column: 30
	          }
	        },
	        loc: {
	          start: {
	            line: 55,
	            column: 36
	          },
	          end: {
	            line: 67,
	            column: 5
	          }
	        },
	        line: 55
	      },
	      "4": {
	        name: "(anonymous_4)",
	        decl: {
	          start: {
	            line: 76,
	            column: 66
	          },
	          end: {
	            line: 76,
	            column: 67
	          }
	        },
	        loc: {
	          start: {
	            line: 76,
	            column: 74
	          },
	          end: {
	            line: 78,
	            column: 5
	          }
	        },
	        line: 76
	      },
	      "5": {
	        name: "(anonymous_5)",
	        decl: {
	          start: {
	            line: 77,
	            column: 19
	          },
	          end: {
	            line: 77,
	            column: 20
	          }
	        },
	        loc: {
	          start: {
	            line: 77,
	            column: 26
	          },
	          end: {
	            line: 77,
	            column: 63
	          }
	        },
	        line: 77
	      },
	      "6": {
	        name: "(anonymous_6)",
	        decl: {
	          start: {
	            line: 91,
	            column: 2
	          },
	          end: {
	            line: 91,
	            column: 3
	          }
	        },
	        loc: {
	          start: {
	            line: 91,
	            column: 13
	          },
	          end: {
	            line: 115,
	            column: 3
	          }
	        },
	        line: 91
	      },
	      "7": {
	        name: "(anonymous_7)",
	        decl: {
	          start: {
	            line: 123,
	            column: 2
	          },
	          end: {
	            line: 123,
	            column: 3
	          }
	        },
	        loc: {
	          start: {
	            line: 123,
	            column: 17
	          },
	          end: {
	            line: 137,
	            column: 3
	          }
	        },
	        line: 123
	      },
	      "8": {
	        name: "(anonymous_8)",
	        decl: {
	          start: {
	            line: 144,
	            column: 2
	          },
	          end: {
	            line: 144,
	            column: 3
	          }
	        },
	        loc: {
	          start: {
	            line: 144,
	            column: 9
	          },
	          end: {
	            line: 159,
	            column: 3
	          }
	        },
	        line: 144
	      },
	      "9": {
	        name: "(anonymous_9)",
	        decl: {
	          start: {
	            line: 170,
	            column: 2
	          },
	          end: {
	            line: 170,
	            column: 3
	          }
	        },
	        loc: {
	          start: {
	            line: 170,
	            column: 26
	          },
	          end: {
	            line: 174,
	            column: 3
	          }
	        },
	        line: 170
	      },
	      "10": {
	        name: "(anonymous_10)",
	        decl: {
	          start: {
	            line: 181,
	            column: 2
	          },
	          end: {
	            line: 181,
	            column: 3
	          }
	        },
	        loc: {
	          start: {
	            line: 181,
	            column: 9
	          },
	          end: {
	            line: 194,
	            column: 3
	          }
	        },
	        line: 181
	      },
	      "11": {
	        name: "(anonymous_11)",
	        decl: {
	          start: {
	            line: 205,
	            column: 2
	          },
	          end: {
	            line: 205,
	            column: 3
	          }
	        },
	        loc: {
	          start: {
	            line: 205,
	            column: 26
	          },
	          end: {
	            line: 229,
	            column: 3
	          }
	        },
	        line: 205
	      },
	      "12": {
	        name: "(anonymous_12)",
	        decl: {
	          start: {
	            line: 236,
	            column: 2
	          },
	          end: {
	            line: 236,
	            column: 3
	          }
	        },
	        loc: {
	          start: {
	            line: 236,
	            column: 20
	          },
	          end: {
	            line: 242,
	            column: 3
	          }
	        },
	        line: 236
	      },
	      "13": {
	        name: "(anonymous_13)",
	        decl: {
	          start: {
	            line: 249,
	            column: 2
	          },
	          end: {
	            line: 249,
	            column: 3
	          }
	        },
	        loc: {
	          start: {
	            line: 249,
	            column: 18
	          },
	          end: {
	            line: 255,
	            column: 3
	          }
	        },
	        line: 249
	      },
	      "14": {
	        name: "(anonymous_14)",
	        decl: {
	          start: {
	            line: 263,
	            column: 2
	          },
	          end: {
	            line: 263,
	            column: 3
	          }
	        },
	        loc: {
	          start: {
	            line: 263,
	            column: 21
	          },
	          end: {
	            line: 293,
	            column: 3
	          }
	        },
	        line: 263
	      },
	      "15": {
	        name: "(anonymous_15)",
	        decl: {
	          start: {
	            line: 304,
	            column: 15
	          },
	          end: {
	            line: 304,
	            column: 16
	          }
	        },
	        loc: {
	          start: {
	            line: 304,
	            column: 33
	          },
	          end: {
	            line: 417,
	            column: 1
	          }
	        },
	        line: 304
	      },
	      "16": {
	        name: "(anonymous_16)",
	        decl: {
	          start: {
	            line: 310,
	            column: 27
	          },
	          end: {
	            line: 310,
	            column: 28
	          }
	        },
	        loc: {
	          start: {
	            line: 310,
	            column: 38
	          },
	          end: {
	            line: 316,
	            column: 5
	          }
	        },
	        line: 310
	      },
	      "17": {
	        name: "(anonymous_17)",
	        decl: {
	          start: {
	            line: 325,
	            column: 22
	          },
	          end: {
	            line: 325,
	            column: 23
	          }
	        },
	        loc: {
	          start: {
	            line: 325,
	            column: 33
	          },
	          end: {
	            line: 358,
	            column: 3
	          }
	        },
	        line: 325
	      },
	      "18": {
	        name: "(anonymous_18)",
	        decl: {
	          start: {
	            line: 326,
	            column: 21
	          },
	          end: {
	            line: 326,
	            column: 22
	          }
	        },
	        loc: {
	          start: {
	            line: 326,
	            column: 26
	          },
	          end: {
	            line: 357,
	            column: 5
	          }
	        },
	        line: 326
	      },
	      "19": {
	        name: "add",
	        decl: {
	          start: {
	            line: 371,
	            column: 11
	          },
	          end: {
	            line: 371,
	            column: 14
	          }
	        },
	        loc: {
	          start: {
	            line: 371,
	            column: 21
	          },
	          end: {
	            line: 381,
	            column: 3
	          }
	        },
	        line: 371
	      },
	      "20": {
	        name: "remove",
	        decl: {
	          start: {
	            line: 392,
	            column: 11
	          },
	          end: {
	            line: 392,
	            column: 17
	          }
	        },
	        loc: {
	          start: {
	            line: 392,
	            column: 24
	          },
	          end: {
	            line: 401,
	            column: 3
	          }
	        },
	        line: 392
	      },
	      "21": {
	        name: "get",
	        decl: {
	          start: {
	            line: 408,
	            column: 11
	          },
	          end: {
	            line: 408,
	            column: 14
	          }
	        },
	        loc: {
	          start: {
	            line: 408,
	            column: 17
	          },
	          end: {
	            line: 410,
	            column: 3
	          }
	        },
	        line: 408
	      }
	    },
	    branchMap: {
	      "0": {
	        loc: {
	          start: {
	            line: 20,
	            column: 12
	          },
	          end: {
	            line: 20,
	            column: 34
	          }
	        },
	        type: "binary-expr",
	        locations: [{
	          start: {
	            line: 20,
	            column: 12
	          },
	          end: {
	            line: 20,
	            column: 23
	          }
	        }, {
	          start: {
	            line: 20,
	            column: 27
	          },
	          end: {
	            line: 20,
	            column: 34
	          }
	        }],
	        line: 20
	      },
	      "1": {
	        loc: {
	          start: {
	            line: 21,
	            column: 23
	          },
	          end: {
	            line: 21,
	            column: 63
	          }
	        },
	        type: "binary-expr",
	        locations: [{
	          start: {
	            line: 21,
	            column: 23
	          },
	          end: {
	            line: 21,
	            column: 45
	          }
	        }, {
	          start: {
	            line: 21,
	            column: 49
	          },
	          end: {
	            line: 21,
	            column: 63
	          }
	        }],
	        line: 21
	      },
	      "2": {
	        loc: {
	          start: {
	            line: 33,
	            column: 22
	          },
	          end: {
	            line: 33,
	            column: 54
	          }
	        },
	        type: "binary-expr",
	        locations: [{
	          start: {
	            line: 33,
	            column: 22
	          },
	          end: {
	            line: 33,
	            column: 43
	          }
	        }, {
	          start: {
	            line: 33,
	            column: 47
	          },
	          end: {
	            line: 33,
	            column: 54
	          }
	        }],
	        line: 33
	      },
	      "3": {
	        loc: {
	          start: {
	            line: 42,
	            column: 29
	          },
	          end: {
	            line: 42,
	            column: 71
	          }
	        },
	        type: "binary-expr",
	        locations: [{
	          start: {
	            line: 42,
	            column: 29
	          },
	          end: {
	            line: 42,
	            column: 50
	          }
	        }, {
	          start: {
	            line: 42,
	            column: 54
	          },
	          end: {
	            line: 42,
	            column: 71
	          }
	        }],
	        line: 42
	      },
	      "4": {
	        loc: {
	          start: {
	            line: 58,
	            column: 6
	          },
	          end: {
	            line: 66,
	            column: 7
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 58,
	            column: 6
	          },
	          end: {
	            line: 66,
	            column: 7
	          }
	        }, {
	          start: {
	            line: 58,
	            column: 6
	          },
	          end: {
	            line: 66,
	            column: 7
	          }
	        }],
	        line: 58
	      },
	      "5": {
	        loc: {
	          start: {
	            line: 60,
	            column: 13
	          },
	          end: {
	            line: 66,
	            column: 7
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 60,
	            column: 13
	          },
	          end: {
	            line: 66,
	            column: 7
	          }
	        }, {
	          start: {
	            line: 60,
	            column: 13
	          },
	          end: {
	            line: 66,
	            column: 7
	          }
	        }],
	        line: 60
	      },
	      "6": {
	        loc: {
	          start: {
	            line: 64,
	            column: 13
	          },
	          end: {
	            line: 66,
	            column: 7
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 64,
	            column: 13
	          },
	          end: {
	            line: 66,
	            column: 7
	          }
	        }, {
	          start: {
	            line: 64,
	            column: 13
	          },
	          end: {
	            line: 66,
	            column: 7
	          }
	        }],
	        line: 64
	      },
	      "7": {
	        loc: {
	          start: {
	            line: 82,
	            column: 4
	          },
	          end: {
	            line: 84,
	            column: 5
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 82,
	            column: 4
	          },
	          end: {
	            line: 84,
	            column: 5
	          }
	        }, {
	          start: {
	            line: 82,
	            column: 4
	          },
	          end: {
	            line: 84,
	            column: 5
	          }
	        }],
	        line: 82
	      },
	      "8": {
	        loc: {
	          start: {
	            line: 86,
	            column: 79
	          },
	          end: {
	            line: 86,
	            column: 106
	          }
	        },
	        type: "binary-expr",
	        locations: [{
	          start: {
	            line: 86,
	            column: 79
	          },
	          end: {
	            line: 86,
	            column: 93
	          }
	        }, {
	          start: {
	            line: 86,
	            column: 97
	          },
	          end: {
	            line: 86,
	            column: 106
	          }
	        }],
	        line: 86
	      },
	      "9": {
	        loc: {
	          start: {
	            line: 95,
	            column: 23
	          },
	          end: {
	            line: 95,
	            column: 102
	          }
	        },
	        type: "cond-expr",
	        locations: [{
	          start: {
	            line: 95,
	            column: 48
	          },
	          end: {
	            line: 95,
	            column: 72
	          }
	        }, {
	          start: {
	            line: 95,
	            column: 75
	          },
	          end: {
	            line: 95,
	            column: 102
	          }
	        }],
	        line: 95
	      },
	      "10": {
	        loc: {
	          start: {
	            line: 106,
	            column: 4
	          },
	          end: {
	            line: 112,
	            column: 5
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 106,
	            column: 4
	          },
	          end: {
	            line: 112,
	            column: 5
	          }
	        }, {
	          start: {
	            line: 106,
	            column: 4
	          },
	          end: {
	            line: 112,
	            column: 5
	          }
	        }],
	        line: 106
	      },
	      "11": {
	        loc: {
	          start: {
	            line: 108,
	            column: 11
	          },
	          end: {
	            line: 112,
	            column: 5
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 108,
	            column: 11
	          },
	          end: {
	            line: 112,
	            column: 5
	          }
	        }, {
	          start: {
	            line: 108,
	            column: 11
	          },
	          end: {
	            line: 112,
	            column: 5
	          }
	        }],
	        line: 108
	      },
	      "12": {
	        loc: {
	          start: {
	            line: 124,
	            column: 4
	          },
	          end: {
	            line: 126,
	            column: 5
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 124,
	            column: 4
	          },
	          end: {
	            line: 126,
	            column: 5
	          }
	        }, {
	          start: {
	            line: 124,
	            column: 4
	          },
	          end: {
	            line: 126,
	            column: 5
	          }
	        }],
	        line: 124
	      },
	      "13": {
	        loc: {
	          start: {
	            line: 132,
	            column: 4
	          },
	          end: {
	            line: 134,
	            column: 5
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 132,
	            column: 4
	          },
	          end: {
	            line: 134,
	            column: 5
	          }
	        }, {
	          start: {
	            line: 132,
	            column: 4
	          },
	          end: {
	            line: 134,
	            column: 5
	          }
	        }],
	        line: 132
	      },
	      "14": {
	        loc: {
	          start: {
	            line: 132,
	            column: 8
	          },
	          end: {
	            line: 132,
	            column: 73
	          }
	        },
	        type: "binary-expr",
	        locations: [{
	          start: {
	            line: 132,
	            column: 8
	          },
	          end: {
	            line: 132,
	            column: 35
	          }
	        }, {
	          start: {
	            line: 132,
	            column: 39
	          },
	          end: {
	            line: 132,
	            column: 73
	          }
	        }],
	        line: 132
	      },
	      "15": {
	        loc: {
	          start: {
	            line: 151,
	            column: 4
	          },
	          end: {
	            line: 154,
	            column: 5
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 151,
	            column: 4
	          },
	          end: {
	            line: 154,
	            column: 5
	          }
	        }, {
	          start: {
	            line: 151,
	            column: 4
	          },
	          end: {
	            line: 154,
	            column: 5
	          }
	        }],
	        line: 151
	      },
	      "16": {
	        loc: {
	          start: {
	            line: 173,
	            column: 11
	          },
	          end: {
	            line: 173,
	            column: 55
	          }
	        },
	        type: "cond-expr",
	        locations: [{
	          start: {
	            line: 173,
	            column: 28
	          },
	          end: {
	            line: 173,
	            column: 39
	          }
	        }, {
	          start: {
	            line: 173,
	            column: 43
	          },
	          end: {
	            line: 173,
	            column: 55
	          }
	        }],
	        line: 173
	      },
	      "17": {
	        loc: {
	          start: {
	            line: 188,
	            column: 4
	          },
	          end: {
	            line: 191,
	            column: 5
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 188,
	            column: 4
	          },
	          end: {
	            line: 191,
	            column: 5
	          }
	        }, {
	          start: {
	            line: 188,
	            column: 4
	          },
	          end: {
	            line: 191,
	            column: 5
	          }
	        }],
	        line: 188
	      },
	      "18": {
	        loc: {
	          start: {
	            line: 209,
	            column: 4
	          },
	          end: {
	            line: 226,
	            column: 5
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 209,
	            column: 4
	          },
	          end: {
	            line: 226,
	            column: 5
	          }
	        }, {
	          start: {
	            line: 209,
	            column: 4
	          },
	          end: {
	            line: 226,
	            column: 5
	          }
	        }],
	        line: 209
	      },
	      "19": {
	        loc: {
	          start: {
	            line: 211,
	            column: 6
	          },
	          end: {
	            line: 219,
	            column: 7
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 211,
	            column: 6
	          },
	          end: {
	            line: 219,
	            column: 7
	          }
	        }, {
	          start: {
	            line: 211,
	            column: 6
	          },
	          end: {
	            line: 219,
	            column: 7
	          }
	        }],
	        line: 211
	      },
	      "20": {
	        loc: {
	          start: {
	            line: 212,
	            column: 15
	          },
	          end: {
	            line: 212,
	            column: 42
	          }
	        },
	        type: "binary-expr",
	        locations: [{
	          start: {
	            line: 212,
	            column: 15
	          },
	          end: {
	            line: 212,
	            column: 28
	          }
	        }, {
	          start: {
	            line: 212,
	            column: 32
	          },
	          end: {
	            line: 212,
	            column: 42
	          }
	        }],
	        line: 212
	      },
	      "21": {
	        loc: {
	          start: {
	            line: 216,
	            column: 13
	          },
	          end: {
	            line: 219,
	            column: 7
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 216,
	            column: 13
	          },
	          end: {
	            line: 219,
	            column: 7
	          }
	        }, {
	          start: {
	            line: 216,
	            column: 13
	          },
	          end: {
	            line: 219,
	            column: 7
	          }
	        }],
	        line: 216
	      },
	      "22": {
	        loc: {
	          start: {
	            line: 239,
	            column: 4
	          },
	          end: {
	            line: 241,
	            column: 5
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 239,
	            column: 4
	          },
	          end: {
	            line: 241,
	            column: 5
	          }
	        }, {
	          start: {
	            line: 239,
	            column: 4
	          },
	          end: {
	            line: 241,
	            column: 5
	          }
	        }],
	        line: 239
	      },
	      "23": {
	        loc: {
	          start: {
	            line: 252,
	            column: 4
	          },
	          end: {
	            line: 254,
	            column: 5
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 252,
	            column: 4
	          },
	          end: {
	            line: 254,
	            column: 5
	          }
	        }, {
	          start: {
	            line: 252,
	            column: 4
	          },
	          end: {
	            line: 254,
	            column: 5
	          }
	        }],
	        line: 252
	      },
	      "24": {
	        loc: {
	          start: {
	            line: 270,
	            column: 4
	          },
	          end: {
	            line: 290,
	            column: 5
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 270,
	            column: 4
	          },
	          end: {
	            line: 290,
	            column: 5
	          }
	        }, {
	          start: {
	            line: 270,
	            column: 4
	          },
	          end: {
	            line: 290,
	            column: 5
	          }
	        }],
	        line: 270
	      },
	      "25": {
	        loc: {
	          start: {
	            line: 276,
	            column: 6
	          },
	          end: {
	            line: 289,
	            column: 7
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 276,
	            column: 6
	          },
	          end: {
	            line: 289,
	            column: 7
	          }
	        }, {
	          start: {
	            line: 276,
	            column: 6
	          },
	          end: {
	            line: 289,
	            column: 7
	          }
	        }],
	        line: 276
	      },
	      "26": {
	        loc: {
	          start: {
	            line: 276,
	            column: 10
	          },
	          end: {
	            line: 276,
	            column: 50
	          }
	        },
	        type: "binary-expr",
	        locations: [{
	          start: {
	            line: 276,
	            column: 10
	          },
	          end: {
	            line: 276,
	            column: 23
	          }
	        }, {
	          start: {
	            line: 276,
	            column: 27
	          },
	          end: {
	            line: 276,
	            column: 50
	          }
	        }],
	        line: 276
	      },
	      "27": {
	        loc: {
	          start: {
	            line: 285,
	            column: 13
	          },
	          end: {
	            line: 289,
	            column: 7
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 285,
	            column: 13
	          },
	          end: {
	            line: 289,
	            column: 7
	          }
	        }, {
	          start: {
	            line: 285,
	            column: 13
	          },
	          end: {
	            line: 289,
	            column: 7
	          }
	        }],
	        line: 285
	      },
	      "28": {
	        loc: {
	          start: {
	            line: 285,
	            column: 17
	          },
	          end: {
	            line: 285,
	            column: 53
	          }
	        },
	        type: "binary-expr",
	        locations: [{
	          start: {
	            line: 285,
	            column: 17
	          },
	          end: {
	            line: 285,
	            column: 37
	          }
	        }, {
	          start: {
	            line: 285,
	            column: 41
	          },
	          end: {
	            line: 285,
	            column: 53
	          }
	        }],
	        line: 285
	      },
	      "29": {
	        loc: {
	          start: {
	            line: 309,
	            column: 2
	          },
	          end: {
	            line: 317,
	            column: 3
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 309,
	            column: 2
	          },
	          end: {
	            line: 317,
	            column: 3
	          }
	        }, {
	          start: {
	            line: 309,
	            column: 2
	          },
	          end: {
	            line: 317,
	            column: 3
	          }
	        }],
	        line: 309
	      },
	      "30": {
	        loc: {
	          start: {
	            line: 312,
	            column: 6
	          },
	          end: {
	            line: 314,
	            column: 7
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 312,
	            column: 6
	          },
	          end: {
	            line: 314,
	            column: 7
	          }
	        }, {
	          start: {
	            line: 312,
	            column: 6
	          },
	          end: {
	            line: 314,
	            column: 7
	          }
	        }],
	        line: 312
	      },
	      "31": {
	        loc: {
	          start: {
	            line: 328,
	            column: 33
	          },
	          end: {
	            line: 328,
	            column: 128
	          }
	        },
	        type: "binary-expr",
	        locations: [{
	          start: {
	            line: 328,
	            column: 33
	          },
	          end: {
	            line: 328,
	            column: 84
	          }
	        }, {
	          start: {
	            line: 328,
	            column: 88
	          },
	          end: {
	            line: 328,
	            column: 128
	          }
	        }],
	        line: 328
	      },
	      "32": {
	        loc: {
	          start: {
	            line: 330,
	            column: 6
	          },
	          end: {
	            line: 332,
	            column: 7
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 330,
	            column: 6
	          },
	          end: {
	            line: 332,
	            column: 7
	          }
	        }, {
	          start: {
	            line: 330,
	            column: 6
	          },
	          end: {
	            line: 332,
	            column: 7
	          }
	        }],
	        line: 330
	      },
	      "33": {
	        loc: {
	          start: {
	            line: 330,
	            column: 10
	          },
	          end: {
	            line: 330,
	            column: 46
	          }
	        },
	        type: "binary-expr",
	        locations: [{
	          start: {
	            line: 330,
	            column: 10
	          },
	          end: {
	            line: 330,
	            column: 26
	          }
	        }, {
	          start: {
	            line: 330,
	            column: 30
	          },
	          end: {
	            line: 330,
	            column: 46
	          }
	        }],
	        line: 330
	      },
	      "34": {
	        loc: {
	          start: {
	            line: 334,
	            column: 6
	          },
	          end: {
	            line: 347,
	            column: 7
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 334,
	            column: 6
	          },
	          end: {
	            line: 347,
	            column: 7
	          }
	        }, {
	          start: {
	            line: 334,
	            column: 6
	          },
	          end: {
	            line: 347,
	            column: 7
	          }
	        }],
	        line: 334
	      },
	      "35": {
	        loc: {
	          start: {
	            line: 334,
	            column: 10
	          },
	          end: {
	            line: 334,
	            column: 75
	          }
	        },
	        type: "binary-expr",
	        locations: [{
	          start: {
	            line: 334,
	            column: 10
	          },
	          end: {
	            line: 334,
	            column: 28
	          }
	        }, {
	          start: {
	            line: 334,
	            column: 32
	          },
	          end: {
	            line: 334,
	            column: 75
	          }
	        }],
	        line: 334
	      },
	      "36": {
	        loc: {
	          start: {
	            line: 337,
	            column: 8
	          },
	          end: {
	            line: 339,
	            column: 9
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 337,
	            column: 8
	          },
	          end: {
	            line: 339,
	            column: 9
	          }
	        }, {
	          start: {
	            line: 337,
	            column: 8
	          },
	          end: {
	            line: 339,
	            column: 9
	          }
	        }],
	        line: 337
	      },
	      "37": {
	        loc: {
	          start: {
	            line: 341,
	            column: 8
	          },
	          end: {
	            line: 346,
	            column: 9
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 341,
	            column: 8
	          },
	          end: {
	            line: 346,
	            column: 9
	          }
	        }, {
	          start: {
	            line: 341,
	            column: 8
	          },
	          end: {
	            line: 346,
	            column: 9
	          }
	        }],
	        line: 341
	      },
	      "38": {
	        loc: {
	          start: {
	            line: 372,
	            column: 4
	          },
	          end: {
	            line: 374,
	            column: 5
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 372,
	            column: 4
	          },
	          end: {
	            line: 374,
	            column: 5
	          }
	        }, {
	          start: {
	            line: 372,
	            column: 4
	          },
	          end: {
	            line: 374,
	            column: 5
	          }
	        }],
	        line: 372
	      },
	      "39": {
	        loc: {
	          start: {
	            line: 395,
	            column: 4
	          },
	          end: {
	            line: 400,
	            column: 5
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 395,
	            column: 4
	          },
	          end: {
	            line: 400,
	            column: 5
	          }
	        }, {
	          start: {
	            line: 395,
	            column: 4
	          },
	          end: {
	            line: 400,
	            column: 5
	          }
	        }],
	        line: 395
	      }
	    },
	    s: {
	      "0": 0,
	      "1": 0,
	      "2": 0,
	      "3": 0,
	      "4": 0,
	      "5": 0,
	      "6": 0,
	      "7": 0,
	      "8": 0,
	      "9": 0,
	      "10": 0,
	      "11": 0,
	      "12": 0,
	      "13": 0,
	      "14": 0,
	      "15": 0,
	      "16": 0,
	      "17": 0,
	      "18": 0,
	      "19": 0,
	      "20": 0,
	      "21": 0,
	      "22": 0,
	      "23": 0,
	      "24": 0,
	      "25": 0,
	      "26": 0,
	      "27": 0,
	      "28": 0,
	      "29": 0,
	      "30": 0,
	      "31": 0,
	      "32": 0,
	      "33": 0,
	      "34": 0,
	      "35": 0,
	      "36": 0,
	      "37": 0,
	      "38": 0,
	      "39": 0,
	      "40": 0,
	      "41": 0,
	      "42": 0,
	      "43": 0,
	      "44": 0,
	      "45": 0,
	      "46": 0,
	      "47": 0,
	      "48": 0,
	      "49": 0,
	      "50": 0,
	      "51": 0,
	      "52": 0,
	      "53": 0,
	      "54": 0,
	      "55": 0,
	      "56": 0,
	      "57": 0,
	      "58": 0,
	      "59": 0,
	      "60": 0,
	      "61": 0,
	      "62": 0,
	      "63": 0,
	      "64": 0,
	      "65": 0,
	      "66": 0,
	      "67": 0,
	      "68": 0,
	      "69": 0,
	      "70": 0,
	      "71": 0,
	      "72": 0,
	      "73": 0,
	      "74": 0,
	      "75": 0,
	      "76": 0,
	      "77": 0,
	      "78": 0,
	      "79": 0,
	      "80": 0,
	      "81": 0,
	      "82": 0,
	      "83": 0,
	      "84": 0,
	      "85": 0,
	      "86": 0,
	      "87": 0,
	      "88": 0,
	      "89": 0,
	      "90": 0,
	      "91": 0,
	      "92": 0,
	      "93": 0,
	      "94": 0,
	      "95": 0,
	      "96": 0,
	      "97": 0,
	      "98": 0,
	      "99": 0,
	      "100": 0,
	      "101": 0,
	      "102": 0,
	      "103": 0,
	      "104": 0,
	      "105": 0,
	      "106": 0,
	      "107": 0,
	      "108": 0,
	      "109": 0,
	      "110": 0,
	      "111": 0,
	      "112": 0,
	      "113": 0,
	      "114": 0,
	      "115": 0,
	      "116": 0,
	      "117": 0,
	      "118": 0,
	      "119": 0,
	      "120": 0,
	      "121": 0,
	      "122": 0,
	      "123": 0,
	      "124": 0,
	      "125": 0,
	      "126": 0,
	      "127": 0,
	      "128": 0,
	      "129": 0,
	      "130": 0,
	      "131": 0,
	      "132": 0,
	      "133": 0
	    },
	    f: {
	      "0": 0,
	      "1": 0,
	      "2": 0,
	      "3": 0,
	      "4": 0,
	      "5": 0,
	      "6": 0,
	      "7": 0,
	      "8": 0,
	      "9": 0,
	      "10": 0,
	      "11": 0,
	      "12": 0,
	      "13": 0,
	      "14": 0,
	      "15": 0,
	      "16": 0,
	      "17": 0,
	      "18": 0,
	      "19": 0,
	      "20": 0,
	      "21": 0
	    },
	    b: {
	      "0": [0, 0],
	      "1": [0, 0],
	      "2": [0, 0],
	      "3": [0, 0],
	      "4": [0, 0],
	      "5": [0, 0],
	      "6": [0, 0],
	      "7": [0, 0],
	      "8": [0, 0],
	      "9": [0, 0],
	      "10": [0, 0],
	      "11": [0, 0],
	      "12": [0, 0],
	      "13": [0, 0],
	      "14": [0, 0],
	      "15": [0, 0],
	      "16": [0, 0],
	      "17": [0, 0],
	      "18": [0, 0],
	      "19": [0, 0],
	      "20": [0, 0],
	      "21": [0, 0],
	      "22": [0, 0],
	      "23": [0, 0],
	      "24": [0, 0],
	      "25": [0, 0],
	      "26": [0, 0],
	      "27": [0, 0],
	      "28": [0, 0],
	      "29": [0, 0],
	      "30": [0, 0],
	      "31": [0, 0],
	      "32": [0, 0],
	      "33": [0, 0],
	      "34": [0, 0],
	      "35": [0, 0],
	      "36": [0, 0],
	      "37": [0, 0],
	      "38": [0, 0],
	      "39": [0, 0]
	    },
	    _coverageSchema: "1a1c01bbd47fc00a2c39e90264f33305004495a9",
	    hash: "3816beb1960bd4803e22f00b53461943da936940"
	  };
	  var coverage = global[gcv] || (global[gcv] = {});
	  if (!coverage[path] || coverage[path].hash !== hash) {
	    coverage[path] = coverageData;
	  }
	  var actualCoverage = coverage[path];
	  {
	    // @ts-ignore
	    cov_111dw1zsow = function () {
	      return actualCoverage;
	    };
	  }
	  return actualCoverage;
	}
	cov_111dw1zsow();
	const defaults = (cov_111dw1zsow().s[0]++, {
	  align: 'top-left',
	  class: '',
	  content: 'This overlay will show up while the video is playing',
	  debug: false,
	  showBackground: true,
	  attachToControlBar: false,
	  overlays: [{
	    start: 'playing',
	    end: 'paused'
	  }]
	});
	const Component = (cov_111dw1zsow().s[1]++, videojs__default["default"].getComponent('Component'));
	const dom$1 = (cov_111dw1zsow().s[2]++, (cov_111dw1zsow().b[0][0]++, videojs__default["default"].dom) || (cov_111dw1zsow().b[0][1]++, videojs__default["default"]));
	const registerPlugin = (cov_111dw1zsow().s[3]++, (cov_111dw1zsow().b[1][0]++, videojs__default["default"].registerPlugin) || (cov_111dw1zsow().b[1][1]++, videojs__default["default"].plugin)); /**
	                                                                                                                                                        * Whether the value is a `Number`.
	                                                                                                                                                        *
	                                                                                                                                                        * Both `Infinity` and `-Infinity` are accepted, but `NaN` is not.
	                                                                                                                                                        *
	                                                                                                                                                        * @param  {Number} n
	                                                                                                                                                        * @return {Boolean}
	                                                                                                                                                        */ /* eslint-disable no-self-compare */
	cov_111dw1zsow().s[4]++;
	const isNumber = n => {
	  cov_111dw1zsow().f[0]++;
	  cov_111dw1zsow().s[5]++;
	  return (cov_111dw1zsow().b[2][0]++, typeof n === 'number') && (cov_111dw1zsow().b[2][1]++, n === n);
	}; /* eslint-enable no-self-compare */ /**
	                                       * Whether a value is a string with no whitespace.
	                                       *
	                                       * @param  {string} s
	                                       * @return {boolean}
	                                       */
	cov_111dw1zsow().s[6]++;
	const hasNoWhitespace = s => {
	  cov_111dw1zsow().f[1]++;
	  cov_111dw1zsow().s[7]++;
	  return (cov_111dw1zsow().b[3][0]++, typeof s === 'string') && (cov_111dw1zsow().b[3][1]++, /^\S+$/.test(s));
	}; /**
	   * Overlay component.
	   *
	   * @class   Overlay
	   * @extends {videojs.Component}
	   */
	class Overlay extends Component {
	  constructor(player, options) {
	    cov_111dw1zsow().f[2]++;
	    cov_111dw1zsow().s[8]++;
	    super(player, options);
	    cov_111dw1zsow().s[9]++;
	    ['start', 'end'].forEach(key => {
	      cov_111dw1zsow().f[3]++;
	      const value = (cov_111dw1zsow().s[10]++, this.options_[key]);
	      cov_111dw1zsow().s[11]++;
	      if (isNumber(value)) {
	        cov_111dw1zsow().b[4][0]++;
	        cov_111dw1zsow().s[12]++;
	        this[key + 'Event_'] = 'timeupdate';
	      } else {
	        cov_111dw1zsow().b[4][1]++;
	        cov_111dw1zsow().s[13]++;
	        if (hasNoWhitespace(value)) {
	          cov_111dw1zsow().b[5][0]++;
	          cov_111dw1zsow().s[14]++;
	          this[key + 'Event_'] = value; // An overlay MUST have a start option. Otherwise, it's pointless.
	        } else {
	          cov_111dw1zsow().b[5][1]++;
	          cov_111dw1zsow().s[15]++;
	          if (key === 'start') {
	            cov_111dw1zsow().b[6][0]++;
	            cov_111dw1zsow().s[16]++;
	            throw new Error('invalid "start" option; expected number or string');
	          } else {
	            cov_111dw1zsow().b[6][1]++;
	          }
	        }
	      }
	    }); // video.js does not like components with multiple instances binding
	    // events to the player because it tracks them at the player level,
	    // not at the level of the object doing the binding. This could also be
	    // solved with Function.prototype.bind (but not videojs.bind because of
	    // its GUID magic), but the anonymous function approach avoids any issues
	    // caused by crappy libraries clobbering Function.prototype.bind.
	    // - https://github.com/videojs/video.js/issues/3097
	    cov_111dw1zsow().s[17]++;
	    ['endListener_', 'rewindListener_', 'startListener_'].forEach(name => {
	      cov_111dw1zsow().f[4]++;
	      cov_111dw1zsow().s[18]++;
	      this[name] = e => {
	        cov_111dw1zsow().f[5]++;
	        cov_111dw1zsow().s[19]++;
	        return Overlay.prototype[name].call(this, e);
	      };
	    }); // If the start event is a timeupdate, we need to watch for rewinds (i.e.,
	    // when the user seeks backward).
	    cov_111dw1zsow().s[20]++;
	    if (this.startEvent_ === 'timeupdate') {
	      cov_111dw1zsow().b[7][0]++;
	      cov_111dw1zsow().s[21]++;
	      this.on(player, 'timeupdate', this.rewindListener_);
	    } else {
	      cov_111dw1zsow().b[7][1]++;
	    }
	    cov_111dw1zsow().s[22]++;
	    this.debug(`created, listening to "${this.startEvent_}" for "start" and "${(cov_111dw1zsow().b[8][0]++, this.endEvent_) || (cov_111dw1zsow().b[8][1]++, 'nothing')}" for "end"`);
	    cov_111dw1zsow().s[23]++;
	    this.hide();
	  }
	  createEl() {
	    cov_111dw1zsow().f[6]++;
	    const options = (cov_111dw1zsow().s[24]++, this.options_);
	    const content = (cov_111dw1zsow().s[25]++, options.content);
	    const background = (cov_111dw1zsow().s[26]++, options.showBackground ? (cov_111dw1zsow().b[9][0]++, 'vjs-overlay-background') : (cov_111dw1zsow().b[9][1]++, 'vjs-overlay-no-background'));
	    const el = (cov_111dw1zsow().s[27]++, dom$1.createEl('div', {
	      className: `
        vjs-overlay
        vjs-overlay-${options.align}
        ${options.class}
        ${background}
        vjs-hidden
      `
	    }));
	    cov_111dw1zsow().s[28]++;
	    if (typeof content === 'string') {
	      cov_111dw1zsow().b[10][0]++;
	      cov_111dw1zsow().s[29]++;
	      el.innerHTML = content;
	    } else {
	      cov_111dw1zsow().b[10][1]++;
	      cov_111dw1zsow().s[30]++;
	      if (content instanceof window_1.DocumentFragment) {
	        cov_111dw1zsow().b[11][0]++;
	        cov_111dw1zsow().s[31]++;
	        el.appendChild(content);
	      } else {
	        cov_111dw1zsow().b[11][1]++;
	        cov_111dw1zsow().s[32]++;
	        dom$1.appendContent(el, content);
	      }
	    }
	    cov_111dw1zsow().s[33]++;
	    return el;
	  } /**
	    * Logs debug errors
	    *
	    * @param  {...[type]} args [description]
	    * @return {[type]}         [description]
	    */
	  debug(...args) {
	    cov_111dw1zsow().f[7]++;
	    cov_111dw1zsow().s[34]++;
	    if (!this.options_.debug) {
	      cov_111dw1zsow().b[12][0]++;
	      cov_111dw1zsow().s[35]++;
	      return;
	    } else {
	      cov_111dw1zsow().b[12][1]++;
	    }
	    const log = (cov_111dw1zsow().s[36]++, videojs__default["default"].log);
	    let fn = (cov_111dw1zsow().s[37]++, log); // Support `videojs.log.foo` calls.
	    cov_111dw1zsow().s[38]++;
	    if ((cov_111dw1zsow().b[14][0]++, log.hasOwnProperty(args[0])) && (cov_111dw1zsow().b[14][1]++, typeof log[args[0]] === 'function')) {
	      cov_111dw1zsow().b[13][0]++;
	      cov_111dw1zsow().s[39]++;
	      fn = log[args.shift()];
	    } else {
	      cov_111dw1zsow().b[13][1]++;
	    }
	    cov_111dw1zsow().s[40]++;
	    fn(...[`overlay#${this.id()}: `, ...args]);
	  } /**
	    * Overrides the inherited method to perform some event binding
	    *
	    * @return {Overlay}
	    */
	  hide() {
	    cov_111dw1zsow().f[8]++;
	    cov_111dw1zsow().s[41]++;
	    super.hide();
	    cov_111dw1zsow().s[42]++;
	    this.debug('hidden');
	    cov_111dw1zsow().s[43]++;
	    this.debug(`bound \`startListener_\` to "${this.startEvent_}"`); // Overlays without an "end" are valid.
	    cov_111dw1zsow().s[44]++;
	    if (this.endEvent_) {
	      cov_111dw1zsow().b[15][0]++;
	      cov_111dw1zsow().s[45]++;
	      this.debug(`unbound \`endListener_\` from "${this.endEvent_}"`);
	      cov_111dw1zsow().s[46]++;
	      this.off(this.player(), this.endEvent_, this.endListener_);
	    } else {
	      cov_111dw1zsow().b[15][1]++;
	    }
	    cov_111dw1zsow().s[47]++;
	    this.on(this.player(), this.startEvent_, this.startListener_);
	    cov_111dw1zsow().s[48]++;
	    return this;
	  } /**
	    * Determine whether or not the overlay should hide.
	    *
	    * @param  {number} time
	    *         The current time reported by the player.
	    * @param  {string} type
	    *         An event type.
	    * @return {boolean}
	    */
	  shouldHide_(time, type) {
	    cov_111dw1zsow().f[9]++;
	    const end = (cov_111dw1zsow().s[49]++, this.options_.end);
	    cov_111dw1zsow().s[50]++;
	    return isNumber(end) ? (cov_111dw1zsow().b[16][0]++, time >= end) : (cov_111dw1zsow().b[16][1]++, end === type);
	  } /**
	    * Overrides the inherited method to perform some event binding
	    *
	    * @return {Overlay}
	    */
	  show() {
	    cov_111dw1zsow().f[10]++;
	    cov_111dw1zsow().s[51]++;
	    super.show();
	    cov_111dw1zsow().s[52]++;
	    this.off(this.player(), this.startEvent_, this.startListener_);
	    cov_111dw1zsow().s[53]++;
	    this.debug('shown');
	    cov_111dw1zsow().s[54]++;
	    this.debug(`unbound \`startListener_\` from "${this.startEvent_}"`); // Overlays without an "end" are valid.
	    cov_111dw1zsow().s[55]++;
	    if (this.endEvent_) {
	      cov_111dw1zsow().b[17][0]++;
	      cov_111dw1zsow().s[56]++;
	      this.debug(`bound \`endListener_\` to "${this.endEvent_}"`);
	      cov_111dw1zsow().s[57]++;
	      this.on(this.player(), this.endEvent_, this.endListener_);
	    } else {
	      cov_111dw1zsow().b[17][1]++;
	    }
	    cov_111dw1zsow().s[58]++;
	    return this;
	  } /**
	    * Determine whether or not the overlay should show.
	    *
	    * @param  {number} time
	    *         The current time reported by the player.
	    * @param  {string} type
	    *         An event type.
	    * @return {boolean}
	    */
	  shouldShow_(time, type) {
	    cov_111dw1zsow().f[11]++;
	    const start = (cov_111dw1zsow().s[59]++, this.options_.start);
	    const end = (cov_111dw1zsow().s[60]++, this.options_.end);
	    cov_111dw1zsow().s[61]++;
	    if (isNumber(start)) {
	      cov_111dw1zsow().b[18][0]++;
	      cov_111dw1zsow().s[62]++;
	      if (isNumber(end)) {
	        cov_111dw1zsow().b[19][0]++;
	        cov_111dw1zsow().s[63]++;
	        return (cov_111dw1zsow().b[20][0]++, time >= start) && (cov_111dw1zsow().b[20][1]++, time < end); // In this case, the start is a number and the end is a string. We need
	        // to check whether or not the overlay has shown since the last seek.
	      } else {
	        cov_111dw1zsow().b[19][1]++;
	        cov_111dw1zsow().s[64]++;
	        if (!this.hasShownSinceSeek_) {
	          cov_111dw1zsow().b[21][0]++;
	          cov_111dw1zsow().s[65]++;
	          this.hasShownSinceSeek_ = true;
	          cov_111dw1zsow().s[66]++;
	          return time >= start;
	        } else {
	          cov_111dw1zsow().b[21][1]++;
	        }
	      } // In this case, the start is a number and the end is a string, but
	      // the overlay has shown since the last seek. This means that we need
	      // to be sure we aren't re-showing it at a later time than it is
	      // scheduled to appear.
	      cov_111dw1zsow().s[67]++;
	      return Math.floor(time) === start;
	    } else {
	      cov_111dw1zsow().b[18][1]++;
	    }
	    cov_111dw1zsow().s[68]++;
	    return start === type;
	  } /**
	    * Event listener that can trigger the overlay to show.
	    *
	    * @param  {Event} e
	    */
	  startListener_(e) {
	    cov_111dw1zsow().f[12]++;
	    const time = (cov_111dw1zsow().s[69]++, this.player().currentTime());
	    cov_111dw1zsow().s[70]++;
	    if (this.shouldShow_(time, e.type)) {
	      cov_111dw1zsow().b[22][0]++;
	      cov_111dw1zsow().s[71]++;
	      this.show();
	    } else {
	      cov_111dw1zsow().b[22][1]++;
	    }
	  } /**
	    * Event listener that can trigger the overlay to show.
	    *
	    * @param  {Event} e
	    */
	  endListener_(e) {
	    cov_111dw1zsow().f[13]++;
	    const time = (cov_111dw1zsow().s[72]++, this.player().currentTime());
	    cov_111dw1zsow().s[73]++;
	    if (this.shouldHide_(time, e.type)) {
	      cov_111dw1zsow().b[23][0]++;
	      cov_111dw1zsow().s[74]++;
	      this.hide();
	    } else {
	      cov_111dw1zsow().b[23][1]++;
	    }
	  } /**
	    * Event listener that can looks for rewinds - that is, backward seeks
	    * and may hide the overlay as needed.
	    *
	    * @param  {Event} e
	    */
	  rewindListener_(e) {
	    cov_111dw1zsow().f[14]++;
	    const time = (cov_111dw1zsow().s[75]++, this.player().currentTime());
	    const previous = (cov_111dw1zsow().s[76]++, this.previousTime_);
	    const start = (cov_111dw1zsow().s[77]++, this.options_.start);
	    const end = (cov_111dw1zsow().s[78]++, this.options_.end); // Did we seek backward?
	    cov_111dw1zsow().s[79]++;
	    if (time < previous) {
	      cov_111dw1zsow().b[24][0]++;
	      cov_111dw1zsow().s[80]++;
	      this.debug('rewind detected'); // The overlay remains visible if two conditions are met: the end value
	      // MUST be an integer and the the current time indicates that the
	      // overlay should NOT be visible.
	      cov_111dw1zsow().s[81]++;
	      if ((cov_111dw1zsow().b[26][0]++, isNumber(end)) && (cov_111dw1zsow().b[26][1]++, !this.shouldShow_(time))) {
	        cov_111dw1zsow().b[25][0]++;
	        cov_111dw1zsow().s[82]++;
	        this.debug(`hiding; ${end} is an integer and overlay should not show at this time`);
	        cov_111dw1zsow().s[83]++;
	        this.hasShownSinceSeek_ = false;
	        cov_111dw1zsow().s[84]++;
	        this.hide(); // If the end value is an event name, we cannot reliably decide if the
	        // overlay should still be displayed based solely on time; so, we can
	        // only queue it up for showing if the seek took us to a point before
	        // the start time.
	      } else {
	        cov_111dw1zsow().b[25][1]++;
	        cov_111dw1zsow().s[85]++;
	        if ((cov_111dw1zsow().b[28][0]++, hasNoWhitespace(end)) && (cov_111dw1zsow().b[28][1]++, time < start)) {
	          cov_111dw1zsow().b[27][0]++;
	          cov_111dw1zsow().s[86]++;
	          this.debug(`hiding; show point (${start}) is before now (${time}) and end point (${end}) is an event`);
	          cov_111dw1zsow().s[87]++;
	          this.hasShownSinceSeek_ = false;
	          cov_111dw1zsow().s[88]++;
	          this.hide();
	        } else {
	          cov_111dw1zsow().b[27][1]++;
	        }
	      }
	    } else {
	      cov_111dw1zsow().b[24][1]++;
	    }
	    cov_111dw1zsow().s[89]++;
	    this.previousTime_ = time;
	  }
	}
	cov_111dw1zsow().s[90]++;
	videojs__default["default"].registerComponent('Overlay', Overlay); /**
	                                               * Initialize the plugin.
	                                               *
	                                               * @function plugin
	                                               * @param    {Object} [options={}]
	                                               */
	cov_111dw1zsow().s[91]++;
	const plugin = function (options) {
	  cov_111dw1zsow().f[15]++;
	  const player = (cov_111dw1zsow().s[92]++, this);
	  const settings = (cov_111dw1zsow().s[93]++, videojs__default["default"].mergeOptions(defaults, options)); // De-initialize the plugin if it already has an array of overlays.
	  cov_111dw1zsow().s[94]++;
	  if (Array.isArray(this.overlays_)) {
	    cov_111dw1zsow().b[29][0]++;
	    cov_111dw1zsow().s[95]++;
	    this.overlays_.forEach(overlay => {
	      cov_111dw1zsow().f[16]++;
	      cov_111dw1zsow().s[96]++;
	      this.removeChild(overlay);
	      cov_111dw1zsow().s[97]++;
	      if (this.controlBar) {
	        cov_111dw1zsow().b[30][0]++;
	        cov_111dw1zsow().s[98]++;
	        this.controlBar.removeChild(overlay);
	      } else {
	        cov_111dw1zsow().b[30][1]++;
	      }
	      cov_111dw1zsow().s[99]++;
	      overlay.dispose();
	    });
	  } else {
	    cov_111dw1zsow().b[29][1]++;
	  }
	  const overlays = (cov_111dw1zsow().s[100]++, settings.overlays); // We don't want to keep the original array of overlay options around
	  // because it doesn't make sense to pass it to each Overlay component.
	  cov_111dw1zsow().s[101]++;
	  delete settings.overlays;
	  cov_111dw1zsow().s[102]++;
	  const mapOverlays = items => {
	    cov_111dw1zsow().f[17]++;
	    cov_111dw1zsow().s[103]++;
	    return items.map(o => {
	      cov_111dw1zsow().f[18]++;
	      const mergeOptions = (cov_111dw1zsow().s[104]++, videojs__default["default"].mergeOptions(settings, o));
	      const attachToControlBar = (cov_111dw1zsow().s[105]++, (cov_111dw1zsow().b[31][0]++, typeof mergeOptions.attachToControlBar === 'string') || (cov_111dw1zsow().b[31][1]++, mergeOptions.attachToControlBar === true));
	      cov_111dw1zsow().s[106]++;
	      if ((cov_111dw1zsow().b[33][0]++, !this.controls()) || (cov_111dw1zsow().b[33][1]++, !this.controlBar)) {
	        cov_111dw1zsow().b[32][0]++;
	        cov_111dw1zsow().s[107]++;
	        return this.addChild('overlay', mergeOptions);
	      } else {
	        cov_111dw1zsow().b[32][1]++;
	      }
	      cov_111dw1zsow().s[108]++;
	      if ((cov_111dw1zsow().b[35][0]++, attachToControlBar) && (cov_111dw1zsow().b[35][1]++, mergeOptions.align.indexOf('bottom') !== -1)) {
	        cov_111dw1zsow().b[34][0]++;
	        let referenceChild = (cov_111dw1zsow().s[109]++, this.controlBar.children()[0]);
	        cov_111dw1zsow().s[110]++;
	        if (this.controlBar.getChild(mergeOptions.attachToControlBar) !== undefined) {
	          cov_111dw1zsow().b[36][0]++;
	          cov_111dw1zsow().s[111]++;
	          referenceChild = this.controlBar.getChild(mergeOptions.attachToControlBar);
	        } else {
	          cov_111dw1zsow().b[36][1]++;
	        }
	        cov_111dw1zsow().s[112]++;
	        if (referenceChild) {
	          cov_111dw1zsow().b[37][0]++;
	          const referenceChildIndex = (cov_111dw1zsow().s[113]++, this.controlBar.children().indexOf(referenceChild));
	          const controlBarChild = (cov_111dw1zsow().s[114]++, this.controlBar.addChild('overlay', mergeOptions, referenceChildIndex));
	          cov_111dw1zsow().s[115]++;
	          return controlBarChild;
	        } else {
	          cov_111dw1zsow().b[37][1]++;
	        }
	      } else {
	        cov_111dw1zsow().b[34][1]++;
	      }
	      const playerChild = (cov_111dw1zsow().s[116]++, this.addChild('overlay', mergeOptions));
	      cov_111dw1zsow().s[117]++;
	      this.el().insertBefore(playerChild.el(), this.controlBar.el());
	      cov_111dw1zsow().s[118]++;
	      return playerChild;
	    });
	  };
	  cov_111dw1zsow().s[119]++;
	  this.overlays_ = mapOverlays(overlays); /**
	                                          * Adds one or more items to the existing list of overlays.
	                                          *
	                                          * @param {Object|Array} item
	                                          *        An item (or an array of items) to be added as overlay/s
	                                          *
	                                          * @return {Array[Overlay]}
	                                          *         The array of overlay objects that were added
	                                          */
	  function add(item) {
	    cov_111dw1zsow().f[19]++;
	    cov_111dw1zsow().s[120]++;
	    if (!Array.isArray(item)) {
	      cov_111dw1zsow().b[38][0]++;
	      cov_111dw1zsow().s[121]++;
	      item = [item];
	    } else {
	      cov_111dw1zsow().b[38][1]++;
	    }
	    const addedOverlays = (cov_111dw1zsow().s[122]++, mapOverlays(item));
	    cov_111dw1zsow().s[123]++;
	    player.overlays_ = player.overlays_.concat(addedOverlays);
	    cov_111dw1zsow().s[124]++;
	    return addedOverlays;
	  } /**
	    *
	    * @param {Overlay} item
	    *        An item to be removed from the array of overlays
	    *
	    * @throws {Error}
	    *        Item to remove must be present in the array of overlays
	    *
	    */
	  function remove(item) {
	    cov_111dw1zsow().f[20]++;
	    const index = (cov_111dw1zsow().s[125]++, player.overlays_.indexOf(item));
	    cov_111dw1zsow().s[126]++;
	    if (index !== -1) {
	      cov_111dw1zsow().b[39][0]++;
	      cov_111dw1zsow().s[127]++;
	      item.el().parentNode.removeChild(item.el());
	      cov_111dw1zsow().s[128]++;
	      player.overlays_.splice(index, 1);
	    } else {
	      cov_111dw1zsow().b[39][1]++;
	      cov_111dw1zsow().s[129]++;
	      player.log.warn('overlay does not exist and cannot be removed');
	    }
	  } /**
	    * Gets the array of overlays used for the current video
	    *
	    * @return The array of overlay objects currently used by the plugin
	    */
	  function get() {
	    cov_111dw1zsow().f[21]++;
	    cov_111dw1zsow().s[130]++;
	    return player.overlays_;
	  }
	  cov_111dw1zsow().s[131]++;
	  return {
	    add,
	    remove,
	    get
	  };
	};
	cov_111dw1zsow().s[132]++;
	plugin.VERSION = version;
	cov_111dw1zsow().s[133]++;
	registerPlugin('overlay', plugin);

	const Player = videojs__default["default"].getComponent('Player');
	const dom = videojs__default["default"].dom || videojs__default["default"];
	QUnit__default["default"].test('the environment is sane', function (assert) {
	  assert.strictEqual(typeof Array.isArray, 'function', 'es5 exists');
	  assert.strictEqual(typeof sinon__default["default"], 'object', 'sinon exists');
	  assert.strictEqual(typeof videojs__default["default"], 'function', 'videojs exists');
	  assert.strictEqual(typeof plugin, 'function', 'plugin is a function');
	});
	QUnit__default["default"].module('videojs-overlay', {
	  beforeEach() {
	    // Mock the environment's timers because certain things - particularly
	    // player readiness - are asynchronous in video.js 5. This MUST come
	    // before any player is created; otherwise, timers could get created
	    // with the actual timer methods!
	    this.clock = sinon__default["default"].useFakeTimers();
	    this.fixture = document_1.getElementById('qunit-fixture');
	    this.video = document_1.createElement('video');
	    this.video.controls = true;
	    this.fixture.appendChild(this.video);
	    this.player = videojs__default["default"](this.video);

	    // Simulate the video element playing to a specific time and stub
	    // the `currentTime` method of the player to return this.
	    this.currentTime = 0;
	    this.player.currentTime = () => this.currentTime;
	    this.updateTime = seconds => {
	      this.currentTime = seconds;
	      this.player.trigger('timeupdate');
	    };
	    this.assertOverlayCount = (assert, expected) => {
	      const overlays = Array.prototype.filter.call(this.player.$$('.vjs-overlay'), el => !dom.hasClass(el, 'vjs-hidden'));
	      const actual = overlays ? overlays.length : 0;
	      const one = expected === 1;
	      const msg = `${expected} overlay${one ? '' : 's'} exist${one ? 's' : ''}`;
	      assert.strictEqual(actual, expected, msg);
	    };
	  },
	  afterEach() {
	    this.player.dispose();
	    this.clock.restore();
	  }
	});
	QUnit__default["default"].test('registers itself with video.js', function (assert) {
	  assert.expect(2);
	  assert.strictEqual(typeof Player.prototype.overlay, 'function', 'videojs-overlay plugin was registered');
	  assert.ok(videojs__default["default"].getComponent('Overlay'), 'the Overlay component was registered');
	});
	QUnit__default["default"].test('does not display overlays when none are configured', function (assert) {
	  assert.expect(1);
	  this.player.overlay({
	    overlays: []
	  });
	  this.assertOverlayCount(assert, 0);
	});
	QUnit__default["default"].test('can be triggered and dismissed by events', function (assert) {
	  assert.expect(3);
	  this.player.overlay({
	    overlays: [{
	      start: 'custom-start',
	      end: 'custom-end'
	    }]
	  });
	  this.assertOverlayCount(assert, 0);
	  this.player.trigger('custom-start');
	  this.assertOverlayCount(assert, 1);
	  this.player.trigger('custom-end');
	  this.assertOverlayCount(assert, 0);
	});
	QUnit__default["default"].test('can be triggered for time intervals', function (assert) {
	  assert.expect(7);
	  this.player.overlay({
	    overlays: [{
	      start: 5,
	      end: 10
	    }]
	  });
	  this.updateTime(4);
	  this.assertOverlayCount(assert, 0);
	  this.updateTime(5);
	  this.assertOverlayCount(assert, 1);
	  this.updateTime(7.5);
	  this.assertOverlayCount(assert, 1);
	  this.updateTime(10);
	  this.assertOverlayCount(assert, 0);
	  this.updateTime(11);
	  this.assertOverlayCount(assert, 0);
	  this.updateTime(6);
	  this.assertOverlayCount(assert, 1);
	  this.updateTime(12);
	  this.assertOverlayCount(assert, 0);
	});
	QUnit__default["default"].test('shows multiple overlays simultaneously', function (assert) {
	  assert.expect(4);
	  this.player.overlay({
	    overlays: [{
	      start: 3,
	      end: 10
	    }, {
	      start: 'playing',
	      end: 'ended'
	    }]
	  });
	  this.updateTime(4);
	  this.assertOverlayCount(assert, 1);
	  this.player.trigger('playing');
	  this.assertOverlayCount(assert, 2);
	  this.player.trigger('ended');
	  this.assertOverlayCount(assert, 1);
	  this.updateTime(11);
	  this.assertOverlayCount(assert, 0);
	});
	QUnit__default["default"].test('the content of overlays can be specified as an HTML string', function (assert) {
	  assert.expect(1);
	  const innerHTML = '<p>overlay <a href="#">text</a></p>';
	  this.player.overlay({
	    content: innerHTML,
	    overlays: [{
	      start: 'playing',
	      end: 'ended'
	    }]
	  });
	  this.player.trigger('playing');
	  assert.strictEqual(this.player.$('.vjs-overlay').innerHTML, innerHTML, 'innerHTML matched');
	});
	QUnit__default["default"].test('an element can be used as the content of overlays', function (assert) {
	  assert.expect(1);
	  const content = document_1.createElement('p');
	  content.innerHTML = 'this is some text';
	  this.player.overlay({
	    content,
	    overlays: [{
	      start: 5,
	      end: 10
	    }]
	  });
	  this.updateTime(5);
	  assert.strictEqual(this.player.$('.vjs-overlay p'), content, 'sets the content element');
	});
	QUnit__default["default"].test('a DocumentFragment can be used as the content of overlays', function (assert) {
	  assert.expect(1);
	  const fragment = document_1.createDocumentFragment();
	  const br = document_1.createElement('br');
	  fragment.appendChild(br);
	  this.player.overlay({
	    content: fragment,
	    overlays: [{
	      start: 'showoverlay',
	      end: 'hideoverlay'
	    }]
	  });
	  this.player.trigger('showoverlay');
	  assert.strictEqual(this.player.$('.vjs-overlay br'), br, 'sets the content fragment');
	});
	QUnit__default["default"].test('allows content to be specified per overlay', function (assert) {
	  assert.expect(5);
	  const text = '<b>some text</b>';
	  const html = '<p>overlay <a href="#">text</a></p>';
	  const element = document_1.createElement('i');
	  const fragment = document_1.createDocumentFragment();
	  fragment.appendChild(document_1.createElement('img'));
	  this.player.overlay({
	    content: text,
	    overlays: [{
	      start: 0,
	      end: 1
	    }, {
	      content: html,
	      start: 0,
	      end: 1
	    }, {
	      content: element,
	      start: 0,
	      end: 1
	    }, {
	      content: fragment,
	      start: 0,
	      end: 1
	    }]
	  });
	  this.updateTime(0);
	  this.assertOverlayCount(assert, 4);
	  assert.strictEqual(this.player.$$('.vjs-overlay b').length, 1, 'shows a default overlay');
	  assert.strictEqual(this.player.$$('.vjs-overlay p').length, 1, 'shows an HTML string');
	  assert.strictEqual(this.player.$$('.vjs-overlay i').length, 1, 'shows a DOM element');
	  assert.strictEqual(this.player.$$('.vjs-overlay img').length, 1, 'shows a document fragment');
	});
	QUnit__default["default"].test('allows css class to be specified per overlay', function (assert) {
	  assert.expect(3);
	  const text = '<b>some text</b>';
	  const fragment = document_1.createDocumentFragment();
	  fragment.appendChild(document_1.createElement('img'));
	  this.player.overlay({
	    content: text,
	    overlays: [{
	      class: 'first-class-overlay',
	      start: 0,
	      end: 1
	    }, {
	      class: 'second-class-overlay',
	      start: 0,
	      end: 1
	    }, {
	      start: 0,
	      end: 1
	    }]
	  });
	  this.updateTime(0);
	  this.assertOverlayCount(assert, 3);
	  assert.strictEqual(this.player.$$('.first-class-overlay').length, 1, 'shows an overlay with a custom class');
	  assert.strictEqual(this.player.$$('.second-class-overlay').length, 1, 'shows an overlay with a different custom class');
	});
	QUnit__default["default"].test('does not double add overlays that are triggered twice', function (assert) {
	  assert.expect(1);
	  this.player.overlay({
	    overlays: [{
	      start: 'start',
	      end: 'end'
	    }]
	  });
	  this.player.trigger('start');
	  this.player.trigger('start');
	  this.assertOverlayCount(assert, 1);
	});
	QUnit__default["default"].test('does not double remove overlays that are triggered twice', function (assert) {
	  assert.expect(1);
	  this.player.overlay({
	    overlays: [{
	      start: 'start',
	      end: 'end'
	    }]
	  });
	  this.player.trigger('start');
	  this.player.trigger('end');
	  this.player.trigger('end');
	  this.assertOverlayCount(assert, 0);
	});
	QUnit__default["default"].test('displays overlays that mix event and playback time triggers', function (assert) {
	  assert.expect(4);
	  this.player.overlay({
	    overlays: [{
	      start: 'start',
	      end: 10
	    }, {
	      start: 5,
	      end: 'end'
	    }]
	  });
	  this.player.trigger('start');
	  this.assertOverlayCount(assert, 1);
	  this.updateTime(6);
	  this.assertOverlayCount(assert, 2);
	  this.updateTime(10);
	  this.assertOverlayCount(assert, 1);
	  this.player.trigger('end');
	  this.assertOverlayCount(assert, 0);
	});
	QUnit__default["default"].test('shows mixed trigger overlays once per seek', function (assert) {
	  assert.expect(6);
	  this.player.overlay({
	    overlays: [{
	      start: 1,
	      end: 'pause'
	    }]
	  });
	  this.updateTime(1);
	  this.assertOverlayCount(assert, 1);
	  this.player.trigger('pause');
	  this.assertOverlayCount(assert, 0);
	  this.updateTime(2);
	  this.assertOverlayCount(assert, 0);
	  this.updateTime(1);
	  this.assertOverlayCount(assert, 1);
	  this.player.trigger('pause');
	  this.assertOverlayCount(assert, 0);
	  this.updateTime(2);
	  this.assertOverlayCount(assert, 0);
	});
	QUnit__default["default"].test('applies simple alignment class names', function (assert) {
	  assert.expect(4);
	  this.player.overlay({
	    overlays: [{
	      start: 'start',
	      align: 'top'
	    }, {
	      start: 'start',
	      align: 'left'
	    }, {
	      start: 'start',
	      align: 'right'
	    }, {
	      start: 'start',
	      align: 'bottom'
	    }]
	  });
	  this.player.trigger('start');
	  assert.ok(this.player.$('.vjs-overlay.vjs-overlay-top'), 'applies top class');
	  assert.ok(this.player.$('.vjs-overlay.vjs-overlay-right'), 'applies right class');
	  assert.ok(this.player.$('.vjs-overlay.vjs-overlay-bottom'), 'applies bottom class');
	  assert.ok(this.player.$('.vjs-overlay.vjs-overlay-left'), 'applies left class');
	});
	QUnit__default["default"].test('applies compound alignment class names', function (assert) {
	  assert.expect(4);
	  this.player.overlay({
	    overlays: [{
	      start: 'start',
	      align: 'top-left'
	    }, {
	      start: 'start',
	      align: 'top-right'
	    }, {
	      start: 'start',
	      align: 'bottom-left'
	    }, {
	      start: 'start',
	      align: 'bottom-right'
	    }]
	  });
	  this.player.trigger('start');
	  assert.ok(this.player.$('.vjs-overlay.vjs-overlay-top-left'), 'applies top class');
	  assert.ok(this.player.$('.vjs-overlay.vjs-overlay-top-right'), 'applies right class');
	  assert.ok(this.player.$('.vjs-overlay.vjs-overlay-bottom-left'), 'applies bottom class');
	  assert.ok(this.player.$('.vjs-overlay.vjs-overlay-bottom-right'), 'applies left class');
	});
	QUnit__default["default"].test('removes time based overlays if the user seeks backward', function (assert) {
	  assert.expect(2);
	  this.player.overlay({
	    overlays: [{
	      start: 5,
	      end: 10
	    }]
	  });
	  this.updateTime(6);
	  this.assertOverlayCount(assert, 1);
	  this.updateTime(4);
	  this.assertOverlayCount(assert, 0);
	});
	QUnit__default["default"].test('applies background styling when showBackground is true', function (assert) {
	  assert.expect(1);
	  this.player.overlay({
	    overlays: [{
	      start: 'start',
	      showBackground: true
	    }]
	  });
	  this.player.trigger('start');
	  assert.ok(this.player.$('.vjs-overlay.vjs-overlay-background'), 'applies background styling');
	});
	QUnit__default["default"].test('doesn\'t apply background when showBackground is false', function (assert) {
	  assert.expect(1);
	  this.player.overlay({
	    overlays: [{
	      start: 'start',
	      showBackground: false
	    }]
	  });
	  this.player.trigger('start');
	  assert.notOk(this.player.$('.vjs-overlay.vjs-overlay-background'), 'does not apply background styling');
	});
	QUnit__default["default"].test('attaches bottom aligned overlays to the controlBar', function (assert) {
	  assert.expect(4);
	  this.player.overlay({
	    attachToControlBar: true,
	    overlays: [{
	      start: 'start',
	      align: 'bottom-left'
	    }, {
	      start: 'start',
	      align: 'bottom'
	    }, {
	      start: 'start',
	      align: 'bottom-right'
	    }, {
	      start: 'start',
	      align: 'top-right'
	    }]
	  });
	  this.player.trigger('start');
	  assert.ok(this.player.controlBar.$('.vjs-overlay.vjs-overlay-bottom-left'), 'bottom-left attaches to control bar');
	  assert.ok(this.player.controlBar.$('.vjs-overlay.vjs-overlay-bottom'), 'bottom attaches to control bar');
	  assert.ok(this.player.controlBar.$('.vjs-overlay.vjs-overlay-bottom-right'), 'bottom-right attaches to control bar');
	  assert.notOk(this.player.controlBar.$('.vjs-overlay.vjs-overlay-top-right'), 'top-right is not attached to control bar');
	});
	QUnit__default["default"].test('attach only to player when attachToControlbar is false', function (assert) {
	  assert.expect(2);
	  this.player.overlay({
	    attachToControlBar: false,
	    overlays: [{
	      start: 'start',
	      align: 'bottom-left'
	    }, {
	      start: 'start',
	      align: 'bottom'
	    }]
	  });
	  assert.notOk(this.player.controlBar.$('.vjs-overlay.vjs-overlay-bottom-left'), 'bottom-left is not attached to control bar');
	  assert.notOk(this.player.controlBar.$('.vjs-overlay.vjs-overlay-bottom'), 'bottom is not attached to control bar');
	});
	QUnit__default["default"].test('can deinitialize the plugin on reinitialization', function (assert) {
	  assert.expect(3);
	  this.player.overlay({
	    attachToControlBar: true,
	    overlays: [{
	      start: 'start',
	      align: 'bottom-left'
	    }, {
	      start: 'start',
	      align: 'top-right'
	    }]
	  });
	  this.player.overlay({
	    overlays: [{
	      start: 'start',
	      align: 'top-left'
	    }]
	  });
	  assert.notOk(this.player.$('.vjs-overlay.vjs-overlay-bottom-left'), 'previous bottom-left aligned overlay removed');
	  assert.notOk(this.player.$('.vjs-overlay.vjs-overlay-top-right'), 'previous top-right aligned overlay removed');
	  assert.ok(this.player.$('.vjs-overlay.vjs-overlay-top-left'), 'new top-left overlay added');
	});
	QUnit__default["default"].test('attach bottom overlay as first child when attachToControlBar is invalid component', function (assert) {
	  assert.expect(1);
	  this.player.overlay({
	    attachToControlBar: 'InvalidComponent',
	    overlays: [{
	      start: 'start',
	      align: 'bottom'
	    }]
	  });
	  this.player.trigger('start');
	  assert.equal(this.player.$('.vjs-overlay.vjs-overlay-bottom'), this.player.controlBar.el().firstChild, 'bottom attaches as first child of controlBar');
	});
	QUnit__default["default"].test('attach top overlay as previous sibling when attachToControlBar is invalid component', function (assert) {
	  assert.expect(1);
	  this.player.overlay({
	    attachToControlBar: 'InvalidComponent',
	    overlays: [{
	      start: 'start',
	      align: 'top'
	    }]
	  });
	  this.player.trigger('start');
	  assert.equal(this.player.$('.vjs-overlay.vjs-overlay-top'), this.player.controlBar.el().previousSibling, 'top attaches as previous sibiling of controlBar');
	});
	QUnit__default["default"].test('attach overlays when attachToControlBar is true', function (assert) {
	  assert.expect(4);
	  this.player.overlay({
	    attachToControlBar: true,
	    overlays: [{
	      start: 'start',
	      align: 'bottom'
	    }]
	  });
	  this.player.trigger('start');
	  assert.equal(this.player.controlBar.$('.vjs-overlay.vjs-overlay-bottom'), this.player.controlBar.el().firstChild, 'bottom attaches as first child of control bar');
	  this.player.overlay({
	    attachToControlBar: true,
	    overlays: [{
	      start: 'start',
	      align: 'top'
	    }]
	  });
	  this.player.trigger('start');
	  assert.equal(this.player.$('.vjs-overlay.vjs-overlay-top'), this.player.controlBar.el().previousSibling, 'top attaches as previous sibiling of controlBar');
	  this.player.overlay({
	    attachToControlBar: 'RemainingTimeDisplay',
	    overlays: [{
	      start: 'start',
	      align: 'bottom'
	    }]
	  });
	  this.player.trigger('start');
	  assert.equal(this.player.controlBar.$('.vjs-overlay.vjs-overlay-bottom'), this.player.controlBar.remainingTimeDisplay.el().previousSibling, 'bottom attaches as previous sibiling of attachToControlBar component');
	  this.player.overlay({
	    attachToControlBar: 'RemainingTimeDisplay',
	    overlays: [{
	      start: 'start',
	      align: 'top'
	    }]
	  });
	  this.player.trigger('start');
	  assert.equal(this.player.$('.vjs-overlay.vjs-overlay-top'), this.player.controlBar.el().previousSibling, 'top attaches as previous sibiling of controlBar when using attachToControlBar component');
	});
	QUnit__default["default"].test('attach overlays as last child when no controls are present', function (assert) {
	  assert.expect(2);
	  this.player.controls(false);
	  this.player.overlay({
	    overlays: [{
	      start: 'start',
	      align: 'bottom'
	    }]
	  });
	  this.player.trigger('start');
	  assert.equal(this.player.$('.vjs-overlay.vjs-overlay-bottom'), this.player.el().lastChild, 'bottom attaches as last child of player');
	  this.player.overlay({
	    overlays: [{
	      start: 'start',
	      align: 'top'
	    }]
	  });
	  this.player.trigger('start');
	  assert.equal(this.player.$('.vjs-overlay.vjs-overlay-top'), this.player.el().lastChild, 'top attaches as last child of player');
	});
	QUnit__default["default"].test('can get all existing overlays with the `get` fn', function (assert) {
	  assert.expect(1);
	  this.player.controls(false);
	  const overlay = this.player.overlay({
	    overlays: [{
	      content: 'this is the first overlay',
	      start: 'start',
	      align: 'bottom'
	    }]
	  });
	  this.player.trigger('start');
	  const overlays = overlay.get();
	  assert.equal(overlays[0].options_.content, 'this is the first overlay');
	});
	QUnit__default["default"].test('can add an individual overlay using the `add` fn', function (assert) {
	  assert.expect(3);
	  this.player.controls(false);
	  const overlay = this.player.overlay({
	    overlays: [{
	      start: 'start',
	      align: 'bottom'
	    }]
	  });
	  this.player.trigger('start');
	  assert.equal(this.player.$('.vjs-overlay.vjs-overlay-bottom'), this.player.el().lastChild, 'initial bottom overlay is attached as last child of player');
	  const addedOverlay = overlay.add({
	    content: 'newly added overlay',
	    start: 'start',
	    align: 'top'
	  });
	  assert.equal(addedOverlay[0].options_.content, 'newly added overlay', 'added overlay object is returned by `add` fn');
	  this.player.trigger('start');
	  assert.equal(this.player.$('.vjs-overlay.vjs-overlay-top'), this.player.el().lastChild, 'top gets added as last child of player');
	});
	QUnit__default["default"].test('can add a list of overlays using the `add` fn', function (assert) {
	  assert.expect(2);
	  this.player.controls(false);
	  const overlay = this.player.overlay();
	  overlay.add([{
	    start: 'start',
	    align: 'top'
	  }, {
	    start: 'start',
	    align: 'bottom'
	  }]);
	  this.player.trigger('start');
	  assert.equal(this.player.$('.vjs-overlay.vjs-overlay-bottom'), this.player.el().lastChild, 'bottom gets added as last child of player');
	  assert.equal(this.player.$('.vjs-overlay.vjs-overlay-top'), this.player.el().lastChild.previousSibling, 'top gets added as second last child of player');
	});
	QUnit__default["default"].test('can remove an overlay using the `remove` fn', function (assert) {
	  assert.expect(2);
	  this.player.controls(false);
	  const overlay = this.player.overlay({
	    overlays: [{
	      start: 'start',
	      align: 'bottom'
	    }, {
	      start: 'start',
	      align: 'top'
	    }]
	  });
	  assert.equal(this.player.$('.vjs-overlay.vjs-overlay-bottom'), this.player.el().lastChild.previousSibling, 'bottom gets added as second last child of player');
	  overlay.remove(overlay.get()[0]);
	  assert.notOk(this.player.$('.vjs-overlay.vjs-overlay-bottom'), 'bottom overlay has been removed');
	});
	QUnit__default["default"].test('`remove` fn does not remove anything if an invalid overlay is passed into it', function (assert) {
	  assert.expect(2);
	  this.player.controls(false);
	  const overlay = this.player.overlay({
	    overlays: [{
	      start: 'start',
	      align: 'bottom'
	    }]
	  });
	  assert.equal(this.player.$('.vjs-overlay.vjs-overlay-bottom'), this.player.el().lastChild, 'bottom gets added as last child of player');
	  overlay.remove(undefined);
	  assert.equal(this.player.$('.vjs-overlay.vjs-overlay-bottom'), this.player.el().lastChild, 'bottom is still last child of player');
	});

})(QUnit, sinon, videojs);
