/*! @name videojs-overlay @version 4.0.0 @license Apache-2.0 */
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

	function cov_1eue1x6p4a() {
	  var path = "/Users/wseymour/Desktop/devOS/videojs-overlay/src/overlay-component.js";
	  var hash = "5bb093f3651781fad28e03be6e55ae8e9b7b478f";
	  var global = new Function("return this")();
	  var gcv = "__coverage__";
	  var coverageData = {
	    path: "/Users/wseymour/Desktop/devOS/videojs-overlay/src/overlay-component.js",
	    statementMap: {
	      "0": {
	        start: {
	          line: 3,
	          column: 29
	        },
	        end: {
	          line: 284,
	          column: 1
	        }
	      },
	      "1": {
	        start: {
	          line: 4,
	          column: 20
	        },
	        end: {
	          line: 4,
	          column: 53
	        }
	      },
	      "2": {
	        start: {
	          line: 6,
	          column: 14
	        },
	        end: {
	          line: 6,
	          column: 36
	        }
	      },
	      "3": {
	        start: {
	          line: 18,
	          column: 19
	        },
	        end: {
	          line: 18,
	          column: 56
	        }
	      },
	      "4": {
	        start: {
	          line: 18,
	          column: 24
	        },
	        end: {
	          line: 18,
	          column: 56
	        }
	      },
	      "5": {
	        start: {
	          line: 27,
	          column: 26
	        },
	        end: {
	          line: 27,
	          column: 73
	        }
	      },
	      "6": {
	        start: {
	          line: 27,
	          column: 31
	        },
	        end: {
	          line: 27,
	          column: 73
	        }
	      },
	      "7": {
	        start: {
	          line: 38,
	          column: 6
	        },
	        end: {
	          line: 38,
	          column: 29
	        }
	      },
	      "8": {
	        start: {
	          line: 40,
	          column: 6
	        },
	        end: {
	          line: 52,
	          column: 9
	        }
	      },
	      "9": {
	        start: {
	          line: 41,
	          column: 22
	        },
	        end: {
	          line: 41,
	          column: 40
	        }
	      },
	      "10": {
	        start: {
	          line: 43,
	          column: 8
	        },
	        end: {
	          line: 51,
	          column: 9
	        }
	      },
	      "11": {
	        start: {
	          line: 44,
	          column: 10
	        },
	        end: {
	          line: 44,
	          column: 46
	        }
	      },
	      "12": {
	        start: {
	          line: 45,
	          column: 15
	        },
	        end: {
	          line: 51,
	          column: 9
	        }
	      },
	      "13": {
	        start: {
	          line: 46,
	          column: 10
	        },
	        end: {
	          line: 46,
	          column: 39
	        }
	      },
	      "14": {
	        start: {
	          line: 49,
	          column: 15
	        },
	        end: {
	          line: 51,
	          column: 9
	        }
	      },
	      "15": {
	        start: {
	          line: 50,
	          column: 10
	        },
	        end: {
	          line: 50,
	          column: 79
	        }
	      },
	      "16": {
	        start: {
	          line: 61,
	          column: 6
	        },
	        end: {
	          line: 63,
	          column: 9
	        }
	      },
	      "17": {
	        start: {
	          line: 62,
	          column: 8
	        },
	        end: {
	          line: 62,
	          column: 66
	        }
	      },
	      "18": {
	        start: {
	          line: 62,
	          column: 28
	        },
	        end: {
	          line: 62,
	          column: 65
	        }
	      },
	      "19": {
	        start: {
	          line: 67,
	          column: 6
	        },
	        end: {
	          line: 69,
	          column: 7
	        }
	      },
	      "20": {
	        start: {
	          line: 68,
	          column: 8
	        },
	        end: {
	          line: 68,
	          column: 60
	        }
	      },
	      "21": {
	        start: {
	          line: 71,
	          column: 6
	        },
	        end: {
	          line: 71,
	          column: 123
	        }
	      },
	      "22": {
	        start: {
	          line: 73,
	          column: 6
	        },
	        end: {
	          line: 73,
	          column: 18
	        }
	      },
	      "23": {
	        start: {
	          line: 77,
	          column: 22
	        },
	        end: {
	          line: 77,
	          column: 35
	        }
	      },
	      "24": {
	        start: {
	          line: 78,
	          column: 22
	        },
	        end: {
	          line: 78,
	          column: 37
	        }
	      },
	      "25": {
	        start: {
	          line: 80,
	          column: 25
	        },
	        end: {
	          line: 80,
	          column: 104
	        }
	      },
	      "26": {
	        start: {
	          line: 81,
	          column: 17
	        },
	        end: {
	          line: 89,
	          column: 8
	        }
	      },
	      "27": {
	        start: {
	          line: 91,
	          column: 6
	        },
	        end: {
	          line: 97,
	          column: 7
	        }
	      },
	      "28": {
	        start: {
	          line: 92,
	          column: 8
	        },
	        end: {
	          line: 92,
	          column: 31
	        }
	      },
	      "29": {
	        start: {
	          line: 93,
	          column: 13
	        },
	        end: {
	          line: 97,
	          column: 7
	        }
	      },
	      "30": {
	        start: {
	          line: 94,
	          column: 8
	        },
	        end: {
	          line: 94,
	          column: 32
	        }
	      },
	      "31": {
	        start: {
	          line: 96,
	          column: 8
	        },
	        end: {
	          line: 96,
	          column: 39
	        }
	      },
	      "32": {
	        start: {
	          line: 99,
	          column: 6
	        },
	        end: {
	          line: 99,
	          column: 16
	        }
	      },
	      "33": {
	        start: {
	          line: 109,
	          column: 6
	        },
	        end: {
	          line: 111,
	          column: 7
	        }
	      },
	      "34": {
	        start: {
	          line: 110,
	          column: 8
	        },
	        end: {
	          line: 110,
	          column: 15
	        }
	      },
	      "35": {
	        start: {
	          line: 113,
	          column: 18
	        },
	        end: {
	          line: 113,
	          column: 29
	        }
	      },
	      "36": {
	        start: {
	          line: 114,
	          column: 15
	        },
	        end: {
	          line: 114,
	          column: 18
	        }
	      },
	      "37": {
	        start: {
	          line: 117,
	          column: 6
	        },
	        end: {
	          line: 119,
	          column: 7
	        }
	      },
	      "38": {
	        start: {
	          line: 118,
	          column: 8
	        },
	        end: {
	          line: 118,
	          column: 31
	        }
	      },
	      "39": {
	        start: {
	          line: 121,
	          column: 6
	        },
	        end: {
	          line: 121,
	          column: 49
	        }
	      },
	      "40": {
	        start: {
	          line: 130,
	          column: 6
	        },
	        end: {
	          line: 130,
	          column: 19
	        }
	      },
	      "41": {
	        start: {
	          line: 132,
	          column: 6
	        },
	        end: {
	          line: 132,
	          column: 27
	        }
	      },
	      "42": {
	        start: {
	          line: 133,
	          column: 6
	        },
	        end: {
	          line: 133,
	          column: 70
	        }
	      },
	      "43": {
	        start: {
	          line: 136,
	          column: 6
	        },
	        end: {
	          line: 139,
	          column: 7
	        }
	      },
	      "44": {
	        start: {
	          line: 137,
	          column: 8
	        },
	        end: {
	          line: 137,
	          column: 72
	        }
	      },
	      "45": {
	        start: {
	          line: 138,
	          column: 8
	        },
	        end: {
	          line: 138,
	          column: 67
	        }
	      },
	      "46": {
	        start: {
	          line: 141,
	          column: 6
	        },
	        end: {
	          line: 141,
	          column: 68
	        }
	      },
	      "47": {
	        start: {
	          line: 143,
	          column: 6
	        },
	        end: {
	          line: 143,
	          column: 18
	        }
	      },
	      "48": {
	        start: {
	          line: 156,
	          column: 18
	        },
	        end: {
	          line: 156,
	          column: 35
	        }
	      },
	      "49": {
	        start: {
	          line: 158,
	          column: 6
	        },
	        end: {
	          line: 158,
	          column: 58
	        }
	      },
	      "50": {
	        start: {
	          line: 167,
	          column: 6
	        },
	        end: {
	          line: 167,
	          column: 19
	        }
	      },
	      "51": {
	        start: {
	          line: 168,
	          column: 6
	        },
	        end: {
	          line: 168,
	          column: 69
	        }
	      },
	      "52": {
	        start: {
	          line: 169,
	          column: 6
	        },
	        end: {
	          line: 169,
	          column: 26
	        }
	      },
	      "53": {
	        start: {
	          line: 170,
	          column: 6
	        },
	        end: {
	          line: 170,
	          column: 74
	        }
	      },
	      "54": {
	        start: {
	          line: 173,
	          column: 6
	        },
	        end: {
	          line: 176,
	          column: 7
	        }
	      },
	      "55": {
	        start: {
	          line: 174,
	          column: 8
	        },
	        end: {
	          line: 174,
	          column: 68
	        }
	      },
	      "56": {
	        start: {
	          line: 175,
	          column: 8
	        },
	        end: {
	          line: 175,
	          column: 66
	        }
	      },
	      "57": {
	        start: {
	          line: 178,
	          column: 6
	        },
	        end: {
	          line: 178,
	          column: 18
	        }
	      },
	      "58": {
	        start: {
	          line: 191,
	          column: 20
	        },
	        end: {
	          line: 191,
	          column: 39
	        }
	      },
	      "59": {
	        start: {
	          line: 192,
	          column: 18
	        },
	        end: {
	          line: 192,
	          column: 35
	        }
	      },
	      "60": {
	        start: {
	          line: 194,
	          column: 6
	        },
	        end: {
	          line: 211,
	          column: 7
	        }
	      },
	      "61": {
	        start: {
	          line: 196,
	          column: 8
	        },
	        end: {
	          line: 204,
	          column: 9
	        }
	      },
	      "62": {
	        start: {
	          line: 197,
	          column: 10
	        },
	        end: {
	          line: 197,
	          column: 45
	        }
	      },
	      "63": {
	        start: {
	          line: 201,
	          column: 15
	        },
	        end: {
	          line: 204,
	          column: 9
	        }
	      },
	      "64": {
	        start: {
	          line: 202,
	          column: 10
	        },
	        end: {
	          line: 202,
	          column: 41
	        }
	      },
	      "65": {
	        start: {
	          line: 203,
	          column: 10
	        },
	        end: {
	          line: 203,
	          column: 31
	        }
	      },
	      "66": {
	        start: {
	          line: 210,
	          column: 8
	        },
	        end: {
	          line: 210,
	          column: 42
	        }
	      },
	      "67": {
	        start: {
	          line: 213,
	          column: 6
	        },
	        end: {
	          line: 213,
	          column: 28
	        }
	      },
	      "68": {
	        start: {
	          line: 222,
	          column: 19
	        },
	        end: {
	          line: 222,
	          column: 46
	        }
	      },
	      "69": {
	        start: {
	          line: 224,
	          column: 6
	        },
	        end: {
	          line: 226,
	          column: 7
	        }
	      },
	      "70": {
	        start: {
	          line: 225,
	          column: 8
	        },
	        end: {
	          line: 225,
	          column: 20
	        }
	      },
	      "71": {
	        start: {
	          line: 235,
	          column: 19
	        },
	        end: {
	          line: 235,
	          column: 46
	        }
	      },
	      "72": {
	        start: {
	          line: 237,
	          column: 6
	        },
	        end: {
	          line: 239,
	          column: 7
	        }
	      },
	      "73": {
	        start: {
	          line: 238,
	          column: 8
	        },
	        end: {
	          line: 238,
	          column: 20
	        }
	      },
	      "74": {
	        start: {
	          line: 249,
	          column: 19
	        },
	        end: {
	          line: 249,
	          column: 46
	        }
	      },
	      "75": {
	        start: {
	          line: 250,
	          column: 23
	        },
	        end: {
	          line: 250,
	          column: 41
	        }
	      },
	      "76": {
	        start: {
	          line: 251,
	          column: 20
	        },
	        end: {
	          line: 251,
	          column: 39
	        }
	      },
	      "77": {
	        start: {
	          line: 252,
	          column: 18
	        },
	        end: {
	          line: 252,
	          column: 35
	        }
	      },
	      "78": {
	        start: {
	          line: 255,
	          column: 6
	        },
	        end: {
	          line: 275,
	          column: 7
	        }
	      },
	      "79": {
	        start: {
	          line: 256,
	          column: 8
	        },
	        end: {
	          line: 256,
	          column: 38
	        }
	      },
	      "80": {
	        start: {
	          line: 261,
	          column: 8
	        },
	        end: {
	          line: 274,
	          column: 9
	        }
	      },
	      "81": {
	        start: {
	          line: 262,
	          column: 10
	        },
	        end: {
	          line: 262,
	          column: 94
	        }
	      },
	      "82": {
	        start: {
	          line: 263,
	          column: 10
	        },
	        end: {
	          line: 263,
	          column: 42
	        }
	      },
	      "83": {
	        start: {
	          line: 264,
	          column: 10
	        },
	        end: {
	          line: 264,
	          column: 22
	        }
	      },
	      "84": {
	        start: {
	          line: 270,
	          column: 15
	        },
	        end: {
	          line: 274,
	          column: 9
	        }
	      },
	      "85": {
	        start: {
	          line: 271,
	          column: 10
	        },
	        end: {
	          line: 271,
	          column: 113
	        }
	      },
	      "86": {
	        start: {
	          line: 272,
	          column: 10
	        },
	        end: {
	          line: 272,
	          column: 42
	        }
	      },
	      "87": {
	        start: {
	          line: 273,
	          column: 10
	        },
	        end: {
	          line: 273,
	          column: 22
	        }
	      },
	      "88": {
	        start: {
	          line: 277,
	          column: 6
	        },
	        end: {
	          line: 277,
	          column: 32
	        }
	      },
	      "89": {
	        start: {
	          line: 281,
	          column: 2
	        },
	        end: {
	          line: 281,
	          column: 48
	        }
	      },
	      "90": {
	        start: {
	          line: 283,
	          column: 2
	        },
	        end: {
	          line: 283,
	          column: 17
	        }
	      }
	    },
	    fnMap: {
	      "0": {
	        name: "(anonymous_0)",
	        decl: {
	          start: {
	            line: 3,
	            column: 29
	          },
	          end: {
	            line: 3,
	            column: 30
	          }
	        },
	        loc: {
	          start: {
	            line: 3,
	            column: 42
	          },
	          end: {
	            line: 284,
	            column: 1
	          }
	        },
	        line: 3
	      },
	      "1": {
	        name: "(anonymous_1)",
	        decl: {
	          start: {
	            line: 18,
	            column: 19
	          },
	          end: {
	            line: 18,
	            column: 20
	          }
	        },
	        loc: {
	          start: {
	            line: 18,
	            column: 24
	          },
	          end: {
	            line: 18,
	            column: 56
	          }
	        },
	        line: 18
	      },
	      "2": {
	        name: "(anonymous_2)",
	        decl: {
	          start: {
	            line: 27,
	            column: 26
	          },
	          end: {
	            line: 27,
	            column: 27
	          }
	        },
	        loc: {
	          start: {
	            line: 27,
	            column: 31
	          },
	          end: {
	            line: 27,
	            column: 73
	          }
	        },
	        line: 27
	      },
	      "3": {
	        name: "(anonymous_3)",
	        decl: {
	          start: {
	            line: 37,
	            column: 4
	          },
	          end: {
	            line: 37,
	            column: 5
	          }
	        },
	        loc: {
	          start: {
	            line: 37,
	            column: 33
	          },
	          end: {
	            line: 74,
	            column: 5
	          }
	        },
	        line: 37
	      },
	      "4": {
	        name: "(anonymous_4)",
	        decl: {
	          start: {
	            line: 40,
	            column: 31
	          },
	          end: {
	            line: 40,
	            column: 32
	          }
	        },
	        loc: {
	          start: {
	            line: 40,
	            column: 38
	          },
	          end: {
	            line: 52,
	            column: 7
	          }
	        },
	        line: 40
	      },
	      "5": {
	        name: "(anonymous_5)",
	        decl: {
	          start: {
	            line: 61,
	            column: 68
	          },
	          end: {
	            line: 61,
	            column: 69
	          }
	        },
	        loc: {
	          start: {
	            line: 61,
	            column: 76
	          },
	          end: {
	            line: 63,
	            column: 7
	          }
	        },
	        line: 61
	      },
	      "6": {
	        name: "(anonymous_6)",
	        decl: {
	          start: {
	            line: 62,
	            column: 21
	          },
	          end: {
	            line: 62,
	            column: 22
	          }
	        },
	        loc: {
	          start: {
	            line: 62,
	            column: 28
	          },
	          end: {
	            line: 62,
	            column: 65
	          }
	        },
	        line: 62
	      },
	      "7": {
	        name: "(anonymous_7)",
	        decl: {
	          start: {
	            line: 76,
	            column: 4
	          },
	          end: {
	            line: 76,
	            column: 5
	          }
	        },
	        loc: {
	          start: {
	            line: 76,
	            column: 15
	          },
	          end: {
	            line: 100,
	            column: 5
	          }
	        },
	        line: 76
	      },
	      "8": {
	        name: "(anonymous_8)",
	        decl: {
	          start: {
	            line: 108,
	            column: 4
	          },
	          end: {
	            line: 108,
	            column: 5
	          }
	        },
	        loc: {
	          start: {
	            line: 108,
	            column: 19
	          },
	          end: {
	            line: 122,
	            column: 5
	          }
	        },
	        line: 108
	      },
	      "9": {
	        name: "(anonymous_9)",
	        decl: {
	          start: {
	            line: 129,
	            column: 4
	          },
	          end: {
	            line: 129,
	            column: 5
	          }
	        },
	        loc: {
	          start: {
	            line: 129,
	            column: 11
	          },
	          end: {
	            line: 144,
	            column: 5
	          }
	        },
	        line: 129
	      },
	      "10": {
	        name: "(anonymous_10)",
	        decl: {
	          start: {
	            line: 155,
	            column: 4
	          },
	          end: {
	            line: 155,
	            column: 5
	          }
	        },
	        loc: {
	          start: {
	            line: 155,
	            column: 28
	          },
	          end: {
	            line: 159,
	            column: 5
	          }
	        },
	        line: 155
	      },
	      "11": {
	        name: "(anonymous_11)",
	        decl: {
	          start: {
	            line: 166,
	            column: 4
	          },
	          end: {
	            line: 166,
	            column: 5
	          }
	        },
	        loc: {
	          start: {
	            line: 166,
	            column: 11
	          },
	          end: {
	            line: 179,
	            column: 5
	          }
	        },
	        line: 166
	      },
	      "12": {
	        name: "(anonymous_12)",
	        decl: {
	          start: {
	            line: 190,
	            column: 4
	          },
	          end: {
	            line: 190,
	            column: 5
	          }
	        },
	        loc: {
	          start: {
	            line: 190,
	            column: 28
	          },
	          end: {
	            line: 214,
	            column: 5
	          }
	        },
	        line: 190
	      },
	      "13": {
	        name: "(anonymous_13)",
	        decl: {
	          start: {
	            line: 221,
	            column: 4
	          },
	          end: {
	            line: 221,
	            column: 5
	          }
	        },
	        loc: {
	          start: {
	            line: 221,
	            column: 22
	          },
	          end: {
	            line: 227,
	            column: 5
	          }
	        },
	        line: 221
	      },
	      "14": {
	        name: "(anonymous_14)",
	        decl: {
	          start: {
	            line: 234,
	            column: 4
	          },
	          end: {
	            line: 234,
	            column: 5
	          }
	        },
	        loc: {
	          start: {
	            line: 234,
	            column: 20
	          },
	          end: {
	            line: 240,
	            column: 5
	          }
	        },
	        line: 234
	      },
	      "15": {
	        name: "(anonymous_15)",
	        decl: {
	          start: {
	            line: 248,
	            column: 4
	          },
	          end: {
	            line: 248,
	            column: 5
	          }
	        },
	        loc: {
	          start: {
	            line: 248,
	            column: 23
	          },
	          end: {
	            line: 278,
	            column: 5
	          }
	        },
	        line: 248
	      }
	    },
	    branchMap: {
	      "0": {
	        loc: {
	          start: {
	            line: 6,
	            column: 14
	          },
	          end: {
	            line: 6,
	            column: 36
	          }
	        },
	        type: "binary-expr",
	        locations: [{
	          start: {
	            line: 6,
	            column: 14
	          },
	          end: {
	            line: 6,
	            column: 25
	          }
	        }, {
	          start: {
	            line: 6,
	            column: 29
	          },
	          end: {
	            line: 6,
	            column: 36
	          }
	        }],
	        line: 6
	      },
	      "1": {
	        loc: {
	          start: {
	            line: 18,
	            column: 24
	          },
	          end: {
	            line: 18,
	            column: 56
	          }
	        },
	        type: "binary-expr",
	        locations: [{
	          start: {
	            line: 18,
	            column: 24
	          },
	          end: {
	            line: 18,
	            column: 45
	          }
	        }, {
	          start: {
	            line: 18,
	            column: 49
	          },
	          end: {
	            line: 18,
	            column: 56
	          }
	        }],
	        line: 18
	      },
	      "2": {
	        loc: {
	          start: {
	            line: 27,
	            column: 31
	          },
	          end: {
	            line: 27,
	            column: 73
	          }
	        },
	        type: "binary-expr",
	        locations: [{
	          start: {
	            line: 27,
	            column: 31
	          },
	          end: {
	            line: 27,
	            column: 52
	          }
	        }, {
	          start: {
	            line: 27,
	            column: 56
	          },
	          end: {
	            line: 27,
	            column: 73
	          }
	        }],
	        line: 27
	      },
	      "3": {
	        loc: {
	          start: {
	            line: 43,
	            column: 8
	          },
	          end: {
	            line: 51,
	            column: 9
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 43,
	            column: 8
	          },
	          end: {
	            line: 51,
	            column: 9
	          }
	        }, {
	          start: {
	            line: 43,
	            column: 8
	          },
	          end: {
	            line: 51,
	            column: 9
	          }
	        }],
	        line: 43
	      },
	      "4": {
	        loc: {
	          start: {
	            line: 45,
	            column: 15
	          },
	          end: {
	            line: 51,
	            column: 9
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 45,
	            column: 15
	          },
	          end: {
	            line: 51,
	            column: 9
	          }
	        }, {
	          start: {
	            line: 45,
	            column: 15
	          },
	          end: {
	            line: 51,
	            column: 9
	          }
	        }],
	        line: 45
	      },
	      "5": {
	        loc: {
	          start: {
	            line: 49,
	            column: 15
	          },
	          end: {
	            line: 51,
	            column: 9
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 49,
	            column: 15
	          },
	          end: {
	            line: 51,
	            column: 9
	          }
	        }, {
	          start: {
	            line: 49,
	            column: 15
	          },
	          end: {
	            line: 51,
	            column: 9
	          }
	        }],
	        line: 49
	      },
	      "6": {
	        loc: {
	          start: {
	            line: 67,
	            column: 6
	          },
	          end: {
	            line: 69,
	            column: 7
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 67,
	            column: 6
	          },
	          end: {
	            line: 69,
	            column: 7
	          }
	        }, {
	          start: {
	            line: 67,
	            column: 6
	          },
	          end: {
	            line: 69,
	            column: 7
	          }
	        }],
	        line: 67
	      },
	      "7": {
	        loc: {
	          start: {
	            line: 71,
	            column: 81
	          },
	          end: {
	            line: 71,
	            column: 108
	          }
	        },
	        type: "binary-expr",
	        locations: [{
	          start: {
	            line: 71,
	            column: 81
	          },
	          end: {
	            line: 71,
	            column: 95
	          }
	        }, {
	          start: {
	            line: 71,
	            column: 99
	          },
	          end: {
	            line: 71,
	            column: 108
	          }
	        }],
	        line: 71
	      },
	      "8": {
	        loc: {
	          start: {
	            line: 80,
	            column: 25
	          },
	          end: {
	            line: 80,
	            column: 104
	          }
	        },
	        type: "cond-expr",
	        locations: [{
	          start: {
	            line: 80,
	            column: 50
	          },
	          end: {
	            line: 80,
	            column: 74
	          }
	        }, {
	          start: {
	            line: 80,
	            column: 77
	          },
	          end: {
	            line: 80,
	            column: 104
	          }
	        }],
	        line: 80
	      },
	      "9": {
	        loc: {
	          start: {
	            line: 91,
	            column: 6
	          },
	          end: {
	            line: 97,
	            column: 7
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 91,
	            column: 6
	          },
	          end: {
	            line: 97,
	            column: 7
	          }
	        }, {
	          start: {
	            line: 91,
	            column: 6
	          },
	          end: {
	            line: 97,
	            column: 7
	          }
	        }],
	        line: 91
	      },
	      "10": {
	        loc: {
	          start: {
	            line: 93,
	            column: 13
	          },
	          end: {
	            line: 97,
	            column: 7
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 93,
	            column: 13
	          },
	          end: {
	            line: 97,
	            column: 7
	          }
	        }, {
	          start: {
	            line: 93,
	            column: 13
	          },
	          end: {
	            line: 97,
	            column: 7
	          }
	        }],
	        line: 93
	      },
	      "11": {
	        loc: {
	          start: {
	            line: 109,
	            column: 6
	          },
	          end: {
	            line: 111,
	            column: 7
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 109,
	            column: 6
	          },
	          end: {
	            line: 111,
	            column: 7
	          }
	        }, {
	          start: {
	            line: 109,
	            column: 6
	          },
	          end: {
	            line: 111,
	            column: 7
	          }
	        }],
	        line: 109
	      },
	      "12": {
	        loc: {
	          start: {
	            line: 117,
	            column: 6
	          },
	          end: {
	            line: 119,
	            column: 7
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 117,
	            column: 6
	          },
	          end: {
	            line: 119,
	            column: 7
	          }
	        }, {
	          start: {
	            line: 117,
	            column: 6
	          },
	          end: {
	            line: 119,
	            column: 7
	          }
	        }],
	        line: 117
	      },
	      "13": {
	        loc: {
	          start: {
	            line: 117,
	            column: 10
	          },
	          end: {
	            line: 117,
	            column: 75
	          }
	        },
	        type: "binary-expr",
	        locations: [{
	          start: {
	            line: 117,
	            column: 10
	          },
	          end: {
	            line: 117,
	            column: 37
	          }
	        }, {
	          start: {
	            line: 117,
	            column: 41
	          },
	          end: {
	            line: 117,
	            column: 75
	          }
	        }],
	        line: 117
	      },
	      "14": {
	        loc: {
	          start: {
	            line: 136,
	            column: 6
	          },
	          end: {
	            line: 139,
	            column: 7
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 136,
	            column: 6
	          },
	          end: {
	            line: 139,
	            column: 7
	          }
	        }, {
	          start: {
	            line: 136,
	            column: 6
	          },
	          end: {
	            line: 139,
	            column: 7
	          }
	        }],
	        line: 136
	      },
	      "15": {
	        loc: {
	          start: {
	            line: 158,
	            column: 13
	          },
	          end: {
	            line: 158,
	            column: 57
	          }
	        },
	        type: "cond-expr",
	        locations: [{
	          start: {
	            line: 158,
	            column: 30
	          },
	          end: {
	            line: 158,
	            column: 41
	          }
	        }, {
	          start: {
	            line: 158,
	            column: 45
	          },
	          end: {
	            line: 158,
	            column: 57
	          }
	        }],
	        line: 158
	      },
	      "16": {
	        loc: {
	          start: {
	            line: 173,
	            column: 6
	          },
	          end: {
	            line: 176,
	            column: 7
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 173,
	            column: 6
	          },
	          end: {
	            line: 176,
	            column: 7
	          }
	        }, {
	          start: {
	            line: 173,
	            column: 6
	          },
	          end: {
	            line: 176,
	            column: 7
	          }
	        }],
	        line: 173
	      },
	      "17": {
	        loc: {
	          start: {
	            line: 194,
	            column: 6
	          },
	          end: {
	            line: 211,
	            column: 7
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 194,
	            column: 6
	          },
	          end: {
	            line: 211,
	            column: 7
	          }
	        }, {
	          start: {
	            line: 194,
	            column: 6
	          },
	          end: {
	            line: 211,
	            column: 7
	          }
	        }],
	        line: 194
	      },
	      "18": {
	        loc: {
	          start: {
	            line: 196,
	            column: 8
	          },
	          end: {
	            line: 204,
	            column: 9
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 196,
	            column: 8
	          },
	          end: {
	            line: 204,
	            column: 9
	          }
	        }, {
	          start: {
	            line: 196,
	            column: 8
	          },
	          end: {
	            line: 204,
	            column: 9
	          }
	        }],
	        line: 196
	      },
	      "19": {
	        loc: {
	          start: {
	            line: 197,
	            column: 17
	          },
	          end: {
	            line: 197,
	            column: 44
	          }
	        },
	        type: "binary-expr",
	        locations: [{
	          start: {
	            line: 197,
	            column: 17
	          },
	          end: {
	            line: 197,
	            column: 30
	          }
	        }, {
	          start: {
	            line: 197,
	            column: 34
	          },
	          end: {
	            line: 197,
	            column: 44
	          }
	        }],
	        line: 197
	      },
	      "20": {
	        loc: {
	          start: {
	            line: 201,
	            column: 15
	          },
	          end: {
	            line: 204,
	            column: 9
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 201,
	            column: 15
	          },
	          end: {
	            line: 204,
	            column: 9
	          }
	        }, {
	          start: {
	            line: 201,
	            column: 15
	          },
	          end: {
	            line: 204,
	            column: 9
	          }
	        }],
	        line: 201
	      },
	      "21": {
	        loc: {
	          start: {
	            line: 224,
	            column: 6
	          },
	          end: {
	            line: 226,
	            column: 7
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 224,
	            column: 6
	          },
	          end: {
	            line: 226,
	            column: 7
	          }
	        }, {
	          start: {
	            line: 224,
	            column: 6
	          },
	          end: {
	            line: 226,
	            column: 7
	          }
	        }],
	        line: 224
	      },
	      "22": {
	        loc: {
	          start: {
	            line: 237,
	            column: 6
	          },
	          end: {
	            line: 239,
	            column: 7
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 237,
	            column: 6
	          },
	          end: {
	            line: 239,
	            column: 7
	          }
	        }, {
	          start: {
	            line: 237,
	            column: 6
	          },
	          end: {
	            line: 239,
	            column: 7
	          }
	        }],
	        line: 237
	      },
	      "23": {
	        loc: {
	          start: {
	            line: 255,
	            column: 6
	          },
	          end: {
	            line: 275,
	            column: 7
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 255,
	            column: 6
	          },
	          end: {
	            line: 275,
	            column: 7
	          }
	        }, {
	          start: {
	            line: 255,
	            column: 6
	          },
	          end: {
	            line: 275,
	            column: 7
	          }
	        }],
	        line: 255
	      },
	      "24": {
	        loc: {
	          start: {
	            line: 261,
	            column: 8
	          },
	          end: {
	            line: 274,
	            column: 9
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 261,
	            column: 8
	          },
	          end: {
	            line: 274,
	            column: 9
	          }
	        }, {
	          start: {
	            line: 261,
	            column: 8
	          },
	          end: {
	            line: 274,
	            column: 9
	          }
	        }],
	        line: 261
	      },
	      "25": {
	        loc: {
	          start: {
	            line: 261,
	            column: 12
	          },
	          end: {
	            line: 261,
	            column: 52
	          }
	        },
	        type: "binary-expr",
	        locations: [{
	          start: {
	            line: 261,
	            column: 12
	          },
	          end: {
	            line: 261,
	            column: 25
	          }
	        }, {
	          start: {
	            line: 261,
	            column: 29
	          },
	          end: {
	            line: 261,
	            column: 52
	          }
	        }],
	        line: 261
	      },
	      "26": {
	        loc: {
	          start: {
	            line: 270,
	            column: 15
	          },
	          end: {
	            line: 274,
	            column: 9
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 270,
	            column: 15
	          },
	          end: {
	            line: 274,
	            column: 9
	          }
	        }, {
	          start: {
	            line: 270,
	            column: 15
	          },
	          end: {
	            line: 274,
	            column: 9
	          }
	        }],
	        line: 270
	      },
	      "27": {
	        loc: {
	          start: {
	            line: 270,
	            column: 19
	          },
	          end: {
	            line: 270,
	            column: 55
	          }
	        },
	        type: "binary-expr",
	        locations: [{
	          start: {
	            line: 270,
	            column: 19
	          },
	          end: {
	            line: 270,
	            column: 39
	          }
	        }, {
	          start: {
	            line: 270,
	            column: 43
	          },
	          end: {
	            line: 270,
	            column: 55
	          }
	        }],
	        line: 270
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
	      "90": 0
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
	      "15": 0
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
	      "27": [0, 0]
	    },
	    _coverageSchema: "1a1c01bbd47fc00a2c39e90264f33305004495a9",
	    hash: "5bb093f3651781fad28e03be6e55ae8e9b7b478f"
	  };
	  var coverage = global[gcv] || (global[gcv] = {});
	  if (!coverage[path] || coverage[path].hash !== hash) {
	    coverage[path] = coverageData;
	  }
	  var actualCoverage = coverage[path];
	  {
	    // @ts-ignore
	    cov_1eue1x6p4a = function () {
	      return actualCoverage;
	    };
	  }
	  return actualCoverage;
	}
	cov_1eue1x6p4a();
	cov_1eue1x6p4a().s[0]++;
	const initOverlayComponent = videojs => {
	  cov_1eue1x6p4a().f[0]++;
	  const Component = (cov_1eue1x6p4a().s[1]++, videojs.getComponent('Component'));
	  const dom = (cov_1eue1x6p4a().s[2]++, (cov_1eue1x6p4a().b[0][0]++, videojs.dom) || (cov_1eue1x6p4a().b[0][1]++, videojs)); /**
	                                                                                                                             * Whether the value is a `Number`.
	                                                                                                                             *
	                                                                                                                             * Both `Infinity` and `-Infinity` are accepted, but `NaN` is not.
	                                                                                                                             *
	                                                                                                                             * @param  {Number} n
	                                                                                                                             * @return {Boolean}
	                                                                                                                             */ /* eslint-disable no-self-compare */
	  cov_1eue1x6p4a().s[3]++;
	  const isNumber = n => {
	    cov_1eue1x6p4a().f[1]++;
	    cov_1eue1x6p4a().s[4]++;
	    return (cov_1eue1x6p4a().b[1][0]++, typeof n === 'number') && (cov_1eue1x6p4a().b[1][1]++, n === n);
	  }; /* eslint-enable no-self-compare */ /**
	                                         * Whether a value is a string with no whitespace.
	                                         *
	                                         * @param  {string} s
	                                         * @return {boolean}
	                                         */
	  cov_1eue1x6p4a().s[5]++;
	  const hasNoWhitespace = s => {
	    cov_1eue1x6p4a().f[2]++;
	    cov_1eue1x6p4a().s[6]++;
	    return (cov_1eue1x6p4a().b[2][0]++, typeof s === 'string') && (cov_1eue1x6p4a().b[2][1]++, /^\S+$/.test(s));
	  }; /**
	     * Overlay component.
	     *
	     * @class   Overlay
	     * @extends {videojs.Component}
	     */
	  class Overlay extends Component {
	    constructor(player, options) {
	      cov_1eue1x6p4a().f[3]++;
	      cov_1eue1x6p4a().s[7]++;
	      super(player, options);
	      cov_1eue1x6p4a().s[8]++;
	      ['start', 'end'].forEach(key => {
	        cov_1eue1x6p4a().f[4]++;
	        const value = (cov_1eue1x6p4a().s[9]++, this.options_[key]);
	        cov_1eue1x6p4a().s[10]++;
	        if (isNumber(value)) {
	          cov_1eue1x6p4a().b[3][0]++;
	          cov_1eue1x6p4a().s[11]++;
	          this[key + 'Event_'] = 'timeupdate';
	        } else {
	          cov_1eue1x6p4a().b[3][1]++;
	          cov_1eue1x6p4a().s[12]++;
	          if (hasNoWhitespace(value)) {
	            cov_1eue1x6p4a().b[4][0]++;
	            cov_1eue1x6p4a().s[13]++;
	            this[key + 'Event_'] = value; // An overlay MUST have a start option. Otherwise, it's pointless.
	          } else {
	            cov_1eue1x6p4a().b[4][1]++;
	            cov_1eue1x6p4a().s[14]++;
	            if (key === 'start') {
	              cov_1eue1x6p4a().b[5][0]++;
	              cov_1eue1x6p4a().s[15]++;
	              throw new Error('invalid "start" option; expected number or string');
	            } else {
	              cov_1eue1x6p4a().b[5][1]++;
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
	      cov_1eue1x6p4a().s[16]++;
	      ['endListener_', 'rewindListener_', 'startListener_'].forEach(name => {
	        cov_1eue1x6p4a().f[5]++;
	        cov_1eue1x6p4a().s[17]++;
	        this[name] = e => {
	          cov_1eue1x6p4a().f[6]++;
	          cov_1eue1x6p4a().s[18]++;
	          return Overlay.prototype[name].call(this, e);
	        };
	      }); // If the start event is a timeupdate, we need to watch for rewinds (i.e.,
	      // when the user seeks backward).
	      cov_1eue1x6p4a().s[19]++;
	      if (this.startEvent_ === 'timeupdate') {
	        cov_1eue1x6p4a().b[6][0]++;
	        cov_1eue1x6p4a().s[20]++;
	        this.on(player, 'timeupdate', this.rewindListener_);
	      } else {
	        cov_1eue1x6p4a().b[6][1]++;
	      }
	      cov_1eue1x6p4a().s[21]++;
	      this.debug(`created, listening to "${this.startEvent_}" for "start" and "${(cov_1eue1x6p4a().b[7][0]++, this.endEvent_) || (cov_1eue1x6p4a().b[7][1]++, 'nothing')}" for "end"`);
	      cov_1eue1x6p4a().s[22]++;
	      this.hide();
	    }
	    createEl() {
	      cov_1eue1x6p4a().f[7]++;
	      const options = (cov_1eue1x6p4a().s[23]++, this.options_);
	      const content = (cov_1eue1x6p4a().s[24]++, options.content);
	      const background = (cov_1eue1x6p4a().s[25]++, options.showBackground ? (cov_1eue1x6p4a().b[8][0]++, 'vjs-overlay-background') : (cov_1eue1x6p4a().b[8][1]++, 'vjs-overlay-no-background'));
	      const el = (cov_1eue1x6p4a().s[26]++, dom.createEl('div', {
	        className: `
          vjs-overlay
          vjs-overlay-${options.align}
          ${options.class}
          ${background}
          vjs-hidden
        `
	      }));
	      cov_1eue1x6p4a().s[27]++;
	      if (typeof content === 'string') {
	        cov_1eue1x6p4a().b[9][0]++;
	        cov_1eue1x6p4a().s[28]++;
	        el.innerHTML = content;
	      } else {
	        cov_1eue1x6p4a().b[9][1]++;
	        cov_1eue1x6p4a().s[29]++;
	        if (content instanceof window_1.DocumentFragment) {
	          cov_1eue1x6p4a().b[10][0]++;
	          cov_1eue1x6p4a().s[30]++;
	          el.appendChild(content);
	        } else {
	          cov_1eue1x6p4a().b[10][1]++;
	          cov_1eue1x6p4a().s[31]++;
	          dom.appendContent(el, content);
	        }
	      }
	      cov_1eue1x6p4a().s[32]++;
	      return el;
	    } /**
	      * Logs debug errors
	      *
	      * @param  {...[type]} args [description]
	      * @return {[type]}         [description]
	      */
	    debug(...args) {
	      cov_1eue1x6p4a().f[8]++;
	      cov_1eue1x6p4a().s[33]++;
	      if (!this.options_.debug) {
	        cov_1eue1x6p4a().b[11][0]++;
	        cov_1eue1x6p4a().s[34]++;
	        return;
	      } else {
	        cov_1eue1x6p4a().b[11][1]++;
	      }
	      const log = (cov_1eue1x6p4a().s[35]++, videojs.log);
	      let fn = (cov_1eue1x6p4a().s[36]++, log); // Support `videojs.log.foo` calls.
	      cov_1eue1x6p4a().s[37]++;
	      if ((cov_1eue1x6p4a().b[13][0]++, log.hasOwnProperty(args[0])) && (cov_1eue1x6p4a().b[13][1]++, typeof log[args[0]] === 'function')) {
	        cov_1eue1x6p4a().b[12][0]++;
	        cov_1eue1x6p4a().s[38]++;
	        fn = log[args.shift()];
	      } else {
	        cov_1eue1x6p4a().b[12][1]++;
	      }
	      cov_1eue1x6p4a().s[39]++;
	      fn(...[`overlay#${this.id()}: `, ...args]);
	    } /**
	      * Overrides the inherited method to perform some event binding
	      *
	      * @return {Overlay}
	      */
	    hide() {
	      cov_1eue1x6p4a().f[9]++;
	      cov_1eue1x6p4a().s[40]++;
	      super.hide();
	      cov_1eue1x6p4a().s[41]++;
	      this.debug('hidden');
	      cov_1eue1x6p4a().s[42]++;
	      this.debug(`bound \`startListener_\` to "${this.startEvent_}"`); // Overlays without an "end" are valid.
	      cov_1eue1x6p4a().s[43]++;
	      if (this.endEvent_) {
	        cov_1eue1x6p4a().b[14][0]++;
	        cov_1eue1x6p4a().s[44]++;
	        this.debug(`unbound \`endListener_\` from "${this.endEvent_}"`);
	        cov_1eue1x6p4a().s[45]++;
	        this.off(this.player(), this.endEvent_, this.endListener_);
	      } else {
	        cov_1eue1x6p4a().b[14][1]++;
	      }
	      cov_1eue1x6p4a().s[46]++;
	      this.on(this.player(), this.startEvent_, this.startListener_);
	      cov_1eue1x6p4a().s[47]++;
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
	      cov_1eue1x6p4a().f[10]++;
	      const end = (cov_1eue1x6p4a().s[48]++, this.options_.end);
	      cov_1eue1x6p4a().s[49]++;
	      return isNumber(end) ? (cov_1eue1x6p4a().b[15][0]++, time >= end) : (cov_1eue1x6p4a().b[15][1]++, end === type);
	    } /**
	      * Overrides the inherited method to perform some event binding
	      *
	      * @return {Overlay}
	      */
	    show() {
	      cov_1eue1x6p4a().f[11]++;
	      cov_1eue1x6p4a().s[50]++;
	      super.show();
	      cov_1eue1x6p4a().s[51]++;
	      this.off(this.player(), this.startEvent_, this.startListener_);
	      cov_1eue1x6p4a().s[52]++;
	      this.debug('shown');
	      cov_1eue1x6p4a().s[53]++;
	      this.debug(`unbound \`startListener_\` from "${this.startEvent_}"`); // Overlays without an "end" are valid.
	      cov_1eue1x6p4a().s[54]++;
	      if (this.endEvent_) {
	        cov_1eue1x6p4a().b[16][0]++;
	        cov_1eue1x6p4a().s[55]++;
	        this.debug(`bound \`endListener_\` to "${this.endEvent_}"`);
	        cov_1eue1x6p4a().s[56]++;
	        this.on(this.player(), this.endEvent_, this.endListener_);
	      } else {
	        cov_1eue1x6p4a().b[16][1]++;
	      }
	      cov_1eue1x6p4a().s[57]++;
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
	      cov_1eue1x6p4a().f[12]++;
	      const start = (cov_1eue1x6p4a().s[58]++, this.options_.start);
	      const end = (cov_1eue1x6p4a().s[59]++, this.options_.end);
	      cov_1eue1x6p4a().s[60]++;
	      if (isNumber(start)) {
	        cov_1eue1x6p4a().b[17][0]++;
	        cov_1eue1x6p4a().s[61]++;
	        if (isNumber(end)) {
	          cov_1eue1x6p4a().b[18][0]++;
	          cov_1eue1x6p4a().s[62]++;
	          return (cov_1eue1x6p4a().b[19][0]++, time >= start) && (cov_1eue1x6p4a().b[19][1]++, time < end); // In this case, the start is a number and the end is a string. We need
	          // to check whether or not the overlay has shown since the last seek.
	        } else {
	          cov_1eue1x6p4a().b[18][1]++;
	          cov_1eue1x6p4a().s[63]++;
	          if (!this.hasShownSinceSeek_) {
	            cov_1eue1x6p4a().b[20][0]++;
	            cov_1eue1x6p4a().s[64]++;
	            this.hasShownSinceSeek_ = true;
	            cov_1eue1x6p4a().s[65]++;
	            return time >= start;
	          } else {
	            cov_1eue1x6p4a().b[20][1]++;
	          }
	        } // In this case, the start is a number and the end is a string, but
	        // the overlay has shown since the last seek. This means that we need
	        // to be sure we aren't re-showing it at a later time than it is
	        // scheduled to appear.
	        cov_1eue1x6p4a().s[66]++;
	        return Math.floor(time) === start;
	      } else {
	        cov_1eue1x6p4a().b[17][1]++;
	      }
	      cov_1eue1x6p4a().s[67]++;
	      return start === type;
	    } /**
	      * Event listener that can trigger the overlay to show.
	      *
	      * @param  {Event} e
	      */
	    startListener_(e) {
	      cov_1eue1x6p4a().f[13]++;
	      const time = (cov_1eue1x6p4a().s[68]++, this.player().currentTime());
	      cov_1eue1x6p4a().s[69]++;
	      if (this.shouldShow_(time, e.type)) {
	        cov_1eue1x6p4a().b[21][0]++;
	        cov_1eue1x6p4a().s[70]++;
	        this.show();
	      } else {
	        cov_1eue1x6p4a().b[21][1]++;
	      }
	    } /**
	      * Event listener that can trigger the overlay to show.
	      *
	      * @param  {Event} e
	      */
	    endListener_(e) {
	      cov_1eue1x6p4a().f[14]++;
	      const time = (cov_1eue1x6p4a().s[71]++, this.player().currentTime());
	      cov_1eue1x6p4a().s[72]++;
	      if (this.shouldHide_(time, e.type)) {
	        cov_1eue1x6p4a().b[22][0]++;
	        cov_1eue1x6p4a().s[73]++;
	        this.hide();
	      } else {
	        cov_1eue1x6p4a().b[22][1]++;
	      }
	    } /**
	      * Event listener that can looks for rewinds - that is, backward seeks
	      * and may hide the overlay as needed.
	      *
	      * @param  {Event} e
	      */
	    rewindListener_(e) {
	      cov_1eue1x6p4a().f[15]++;
	      const time = (cov_1eue1x6p4a().s[74]++, this.player().currentTime());
	      const previous = (cov_1eue1x6p4a().s[75]++, this.previousTime_);
	      const start = (cov_1eue1x6p4a().s[76]++, this.options_.start);
	      const end = (cov_1eue1x6p4a().s[77]++, this.options_.end); // Did we seek backward?
	      cov_1eue1x6p4a().s[78]++;
	      if (time < previous) {
	        cov_1eue1x6p4a().b[23][0]++;
	        cov_1eue1x6p4a().s[79]++;
	        this.debug('rewind detected'); // The overlay remains visible if two conditions are met: the end value
	        // MUST be an integer and the the current time indicates that the
	        // overlay should NOT be visible.
	        cov_1eue1x6p4a().s[80]++;
	        if ((cov_1eue1x6p4a().b[25][0]++, isNumber(end)) && (cov_1eue1x6p4a().b[25][1]++, !this.shouldShow_(time))) {
	          cov_1eue1x6p4a().b[24][0]++;
	          cov_1eue1x6p4a().s[81]++;
	          this.debug(`hiding; ${end} is an integer and overlay should not show at this time`);
	          cov_1eue1x6p4a().s[82]++;
	          this.hasShownSinceSeek_ = false;
	          cov_1eue1x6p4a().s[83]++;
	          this.hide(); // If the end value is an event name, we cannot reliably decide if the
	          // overlay should still be displayed based solely on time; so, we can
	          // only queue it up for showing if the seek took us to a point before
	          // the start time.
	        } else {
	          cov_1eue1x6p4a().b[24][1]++;
	          cov_1eue1x6p4a().s[84]++;
	          if ((cov_1eue1x6p4a().b[27][0]++, hasNoWhitespace(end)) && (cov_1eue1x6p4a().b[27][1]++, time < start)) {
	            cov_1eue1x6p4a().b[26][0]++;
	            cov_1eue1x6p4a().s[85]++;
	            this.debug(`hiding; show point (${start}) is before now (${time}) and end point (${end}) is an event`);
	            cov_1eue1x6p4a().s[86]++;
	            this.hasShownSinceSeek_ = false;
	            cov_1eue1x6p4a().s[87]++;
	            this.hide();
	          } else {
	            cov_1eue1x6p4a().b[26][1]++;
	          }
	        }
	      } else {
	        cov_1eue1x6p4a().b[23][1]++;
	      }
	      cov_1eue1x6p4a().s[88]++;
	      this.previousTime_ = time;
	    }
	  }
	  cov_1eue1x6p4a().s[89]++;
	  videojs.registerComponent('Overlay', Overlay);
	  cov_1eue1x6p4a().s[90]++;
	  return Overlay;
	};

	function cov_1r1i8ni59x() {
	  var path = "/Users/wseymour/Desktop/devOS/videojs-overlay/src/plugin.js";
	  var hash = "43387c04a2b5ce0b5c9b11301e44f7603e5d2757";
	  var global = new Function("return this")();
	  var gcv = "__coverage__";
	  var coverageData = {
	    path: "/Users/wseymour/Desktop/devOS/videojs-overlay/src/plugin.js",
	    statementMap: {
	      "0": {
	        start: {
	          line: 4,
	          column: 15
	        },
	        end: {
	          line: 4,
	          column: 42
	        }
	      },
	      "1": {
	        start: {
	          line: 6,
	          column: 17
	        },
	        end: {
	          line: 17,
	          column: 1
	        }
	      },
	      "2": {
	        start: {
	          line: 33,
	          column: 4
	        },
	        end: {
	          line: 33,
	          column: 18
	        }
	      },
	      "3": {
	        start: {
	          line: 35,
	          column: 4
	        },
	        end: {
	          line: 35,
	          column: 24
	        }
	      },
	      "4": {
	        start: {
	          line: 48,
	          column: 4
	        },
	        end: {
	          line: 50,
	          column: 5
	        }
	      },
	      "5": {
	        start: {
	          line: 49,
	          column: 6
	        },
	        end: {
	          line: 49,
	          column: 20
	        }
	      },
	      "6": {
	        start: {
	          line: 52,
	          column: 26
	        },
	        end: {
	          line: 52,
	          column: 49
	        }
	      },
	      "7": {
	        start: {
	          line: 54,
	          column: 4
	        },
	        end: {
	          line: 54,
	          column: 72
	        }
	      },
	      "8": {
	        start: {
	          line: 56,
	          column: 4
	        },
	        end: {
	          line: 56,
	          column: 25
	        }
	      },
	      "9": {
	        start: {
	          line: 69,
	          column: 18
	        },
	        end: {
	          line: 69,
	          column: 53
	        }
	      },
	      "10": {
	        start: {
	          line: 71,
	          column: 4
	        },
	        end: {
	          line: 76,
	          column: 5
	        }
	      },
	      "11": {
	        start: {
	          line: 72,
	          column: 6
	        },
	        end: {
	          line: 72,
	          column: 50
	        }
	      },
	      "12": {
	        start: {
	          line: 73,
	          column: 6
	        },
	        end: {
	          line: 73,
	          column: 45
	        }
	      },
	      "13": {
	        start: {
	          line: 75,
	          column: 6
	        },
	        end: {
	          line: 75,
	          column: 75
	        }
	      },
	      "14": {
	        start: {
	          line: 85,
	          column: 4
	        },
	        end: {
	          line: 85,
	          column: 33
	        }
	      },
	      "15": {
	        start: {
	          line: 95,
	          column: 4
	        },
	        end: {
	          line: 95,
	          column: 26
	        }
	      },
	      "16": {
	        start: {
	          line: 98,
	          column: 18
	        },
	        end: {
	          line: 98,
	          column: 74
	        }
	      },
	      "17": {
	        start: {
	          line: 100,
	          column: 4
	        },
	        end: {
	          line: 100,
	          column: 44
	        }
	      },
	      "18": {
	        start: {
	          line: 102,
	          column: 21
	        },
	        end: {
	          line: 102,
	          column: 42
	        }
	      },
	      "19": {
	        start: {
	          line: 106,
	          column: 4
	        },
	        end: {
	          line: 106,
	          column: 33
	        }
	      },
	      "20": {
	        start: {
	          line: 108,
	          column: 4
	        },
	        end: {
	          line: 108,
	          column: 56
	        }
	      },
	      "21": {
	        start: {
	          line: 115,
	          column: 4
	        },
	        end: {
	          line: 115,
	          column: 26
	        }
	      },
	      "22": {
	        start: {
	          line: 117,
	          column: 4
	        },
	        end: {
	          line: 117,
	          column: 33
	        }
	      },
	      "23": {
	        start: {
	          line: 118,
	          column: 4
	        },
	        end: {
	          line: 118,
	          column: 20
	        }
	      },
	      "24": {
	        start: {
	          line: 123,
	          column: 4
	        },
	        end: {
	          line: 130,
	          column: 5
	        }
	      },
	      "25": {
	        start: {
	          line: 124,
	          column: 6
	        },
	        end: {
	          line: 129,
	          column: 9
	        }
	      },
	      "26": {
	        start: {
	          line: 125,
	          column: 8
	        },
	        end: {
	          line: 125,
	          column: 41
	        }
	      },
	      "27": {
	        start: {
	          line: 126,
	          column: 8
	        },
	        end: {
	          line: 128,
	          column: 9
	        }
	      },
	      "28": {
	        start: {
	          line: 127,
	          column: 10
	        },
	        end: {
	          line: 127,
	          column: 54
	        }
	      },
	      "29": {
	        start: {
	          line: 134,
	          column: 4
	        },
	        end: {
	          line: 165,
	          column: 7
	        }
	      },
	      "30": {
	        start: {
	          line: 135,
	          column: 27
	        },
	        end: {
	          line: 135,
	          column: 64
	        }
	      },
	      "31": {
	        start: {
	          line: 136,
	          column: 33
	        },
	        end: {
	          line: 136,
	          column: 128
	        }
	      },
	      "32": {
	        start: {
	          line: 138,
	          column: 6
	        },
	        end: {
	          line: 140,
	          column: 7
	        }
	      },
	      "33": {
	        start: {
	          line: 139,
	          column: 8
	        },
	        end: {
	          line: 139,
	          column: 61
	        }
	      },
	      "34": {
	        start: {
	          line: 142,
	          column: 6
	        },
	        end: {
	          line: 155,
	          column: 7
	        }
	      },
	      "35": {
	        start: {
	          line: 143,
	          column: 29
	        },
	        end: {
	          line: 143,
	          column: 65
	        }
	      },
	      "36": {
	        start: {
	          line: 145,
	          column: 8
	        },
	        end: {
	          line: 147,
	          column: 9
	        }
	      },
	      "37": {
	        start: {
	          line: 146,
	          column: 10
	        },
	        end: {
	          line: 146,
	          column: 92
	        }
	      },
	      "38": {
	        start: {
	          line: 149,
	          column: 8
	        },
	        end: {
	          line: 154,
	          column: 9
	        }
	      },
	      "39": {
	        start: {
	          line: 150,
	          column: 38
	        },
	        end: {
	          line: 150,
	          column: 95
	        }
	      },
	      "40": {
	        start: {
	          line: 151,
	          column: 34
	        },
	        end: {
	          line: 151,
	          column: 111
	        }
	      },
	      "41": {
	        start: {
	          line: 153,
	          column: 10
	        },
	        end: {
	          line: 153,
	          column: 33
	        }
	      },
	      "42": {
	        start: {
	          line: 157,
	          column: 26
	        },
	        end: {
	          line: 157,
	          column: 71
	        }
	      },
	      "43": {
	        start: {
	          line: 159,
	          column: 6
	        },
	        end: {
	          line: 162,
	          column: 8
	        }
	      },
	      "44": {
	        start: {
	          line: 164,
	          column: 6
	        },
	        end: {
	          line: 164,
	          column: 25
	        }
	      }
	    },
	    fnMap: {
	      "0": {
	        name: "(anonymous_0)",
	        decl: {
	          start: {
	            line: 32,
	            column: 2
	          },
	          end: {
	            line: 32,
	            column: 3
	          }
	        },
	        loc: {
	          start: {
	            line: 32,
	            column: 31
	          },
	          end: {
	            line: 36,
	            column: 3
	          }
	        },
	        line: 32
	      },
	      "1": {
	        name: "(anonymous_1)",
	        decl: {
	          start: {
	            line: 47,
	            column: 2
	          },
	          end: {
	            line: 47,
	            column: 3
	          }
	        },
	        loc: {
	          start: {
	            line: 47,
	            column: 12
	          },
	          end: {
	            line: 57,
	            column: 3
	          }
	        },
	        line: 47
	      },
	      "2": {
	        name: "(anonymous_2)",
	        decl: {
	          start: {
	            line: 68,
	            column: 2
	          },
	          end: {
	            line: 68,
	            column: 3
	          }
	        },
	        loc: {
	          start: {
	            line: 68,
	            column: 15
	          },
	          end: {
	            line: 77,
	            column: 3
	          }
	        },
	        line: 68
	      },
	      "3": {
	        name: "(anonymous_3)",
	        decl: {
	          start: {
	            line: 84,
	            column: 2
	          },
	          end: {
	            line: 84,
	            column: 3
	          }
	        },
	        loc: {
	          start: {
	            line: 84,
	            column: 8
	          },
	          end: {
	            line: 86,
	            column: 3
	          }
	        },
	        line: 84
	      },
	      "4": {
	        name: "(anonymous_4)",
	        decl: {
	          start: {
	            line: 94,
	            column: 2
	          },
	          end: {
	            line: 94,
	            column: 3
	          }
	        },
	        loc: {
	          start: {
	            line: 94,
	            column: 17
	          },
	          end: {
	            line: 109,
	            column: 3
	          }
	        },
	        line: 94
	      },
	      "5": {
	        name: "(anonymous_5)",
	        decl: {
	          start: {
	            line: 114,
	            column: 2
	          },
	          end: {
	            line: 114,
	            column: 3
	          }
	        },
	        loc: {
	          start: {
	            line: 114,
	            column: 12
	          },
	          end: {
	            line: 119,
	            column: 3
	          }
	        },
	        line: 114
	      },
	      "6": {
	        name: "(anonymous_6)",
	        decl: {
	          start: {
	            line: 121,
	            column: 2
	          },
	          end: {
	            line: 121,
	            column: 3
	          }
	        },
	        loc: {
	          start: {
	            line: 121,
	            column: 19
	          },
	          end: {
	            line: 131,
	            column: 3
	          }
	        },
	        line: 121
	      },
	      "7": {
	        name: "(anonymous_7)",
	        decl: {
	          start: {
	            line: 124,
	            column: 36
	          },
	          end: {
	            line: 124,
	            column: 37
	          }
	        },
	        loc: {
	          start: {
	            line: 124,
	            column: 47
	          },
	          end: {
	            line: 129,
	            column: 7
	          }
	        },
	        line: 124
	      },
	      "8": {
	        name: "(anonymous_8)",
	        decl: {
	          start: {
	            line: 133,
	            column: 2
	          },
	          end: {
	            line: 133,
	            column: 3
	          }
	        },
	        loc: {
	          start: {
	            line: 133,
	            column: 22
	          },
	          end: {
	            line: 166,
	            column: 3
	          }
	        },
	        line: 133
	      },
	      "9": {
	        name: "(anonymous_9)",
	        decl: {
	          start: {
	            line: 134,
	            column: 21
	          },
	          end: {
	            line: 134,
	            column: 22
	          }
	        },
	        loc: {
	          start: {
	            line: 134,
	            column: 26
	          },
	          end: {
	            line: 165,
	            column: 5
	          }
	        },
	        line: 134
	      }
	    },
	    branchMap: {
	      "0": {
	        loc: {
	          start: {
	            line: 48,
	            column: 4
	          },
	          end: {
	            line: 50,
	            column: 5
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 48,
	            column: 4
	          },
	          end: {
	            line: 50,
	            column: 5
	          }
	        }, {
	          start: {
	            line: 48,
	            column: 4
	          },
	          end: {
	            line: 50,
	            column: 5
	          }
	        }],
	        line: 48
	      },
	      "1": {
	        loc: {
	          start: {
	            line: 71,
	            column: 4
	          },
	          end: {
	            line: 76,
	            column: 5
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 71,
	            column: 4
	          },
	          end: {
	            line: 76,
	            column: 5
	          }
	        }, {
	          start: {
	            line: 71,
	            column: 4
	          },
	          end: {
	            line: 76,
	            column: 5
	          }
	        }],
	        line: 71
	      },
	      "2": {
	        loc: {
	          start: {
	            line: 98,
	            column: 18
	          },
	          end: {
	            line: 98,
	            column: 74
	          }
	        },
	        type: "binary-expr",
	        locations: [{
	          start: {
	            line: 98,
	            column: 18
	          },
	          end: {
	            line: 98,
	            column: 29
	          }
	        }, {
	          start: {
	            line: 98,
	            column: 33
	          },
	          end: {
	            line: 98,
	            column: 50
	          }
	        }, {
	          start: {
	            line: 98,
	            column: 54
	          },
	          end: {
	            line: 98,
	            column: 74
	          }
	        }],
	        line: 98
	      },
	      "3": {
	        loc: {
	          start: {
	            line: 123,
	            column: 4
	          },
	          end: {
	            line: 130,
	            column: 5
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 123,
	            column: 4
	          },
	          end: {
	            line: 130,
	            column: 5
	          }
	        }, {
	          start: {
	            line: 123,
	            column: 4
	          },
	          end: {
	            line: 130,
	            column: 5
	          }
	        }],
	        line: 123
	      },
	      "4": {
	        loc: {
	          start: {
	            line: 126,
	            column: 8
	          },
	          end: {
	            line: 128,
	            column: 9
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 126,
	            column: 8
	          },
	          end: {
	            line: 128,
	            column: 9
	          }
	        }, {
	          start: {
	            line: 126,
	            column: 8
	          },
	          end: {
	            line: 128,
	            column: 9
	          }
	        }],
	        line: 126
	      },
	      "5": {
	        loc: {
	          start: {
	            line: 136,
	            column: 33
	          },
	          end: {
	            line: 136,
	            column: 128
	          }
	        },
	        type: "binary-expr",
	        locations: [{
	          start: {
	            line: 136,
	            column: 33
	          },
	          end: {
	            line: 136,
	            column: 84
	          }
	        }, {
	          start: {
	            line: 136,
	            column: 88
	          },
	          end: {
	            line: 136,
	            column: 128
	          }
	        }],
	        line: 136
	      },
	      "6": {
	        loc: {
	          start: {
	            line: 138,
	            column: 6
	          },
	          end: {
	            line: 140,
	            column: 7
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 138,
	            column: 6
	          },
	          end: {
	            line: 140,
	            column: 7
	          }
	        }, {
	          start: {
	            line: 138,
	            column: 6
	          },
	          end: {
	            line: 140,
	            column: 7
	          }
	        }],
	        line: 138
	      },
	      "7": {
	        loc: {
	          start: {
	            line: 138,
	            column: 10
	          },
	          end: {
	            line: 138,
	            column: 60
	          }
	        },
	        type: "binary-expr",
	        locations: [{
	          start: {
	            line: 138,
	            column: 10
	          },
	          end: {
	            line: 138,
	            column: 33
	          }
	        }, {
	          start: {
	            line: 138,
	            column: 37
	          },
	          end: {
	            line: 138,
	            column: 60
	          }
	        }],
	        line: 138
	      },
	      "8": {
	        loc: {
	          start: {
	            line: 142,
	            column: 6
	          },
	          end: {
	            line: 155,
	            column: 7
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 142,
	            column: 6
	          },
	          end: {
	            line: 155,
	            column: 7
	          }
	        }, {
	          start: {
	            line: 142,
	            column: 6
	          },
	          end: {
	            line: 155,
	            column: 7
	          }
	        }],
	        line: 142
	      },
	      "9": {
	        loc: {
	          start: {
	            line: 142,
	            column: 10
	          },
	          end: {
	            line: 142,
	            column: 75
	          }
	        },
	        type: "binary-expr",
	        locations: [{
	          start: {
	            line: 142,
	            column: 10
	          },
	          end: {
	            line: 142,
	            column: 28
	          }
	        }, {
	          start: {
	            line: 142,
	            column: 32
	          },
	          end: {
	            line: 142,
	            column: 75
	          }
	        }],
	        line: 142
	      },
	      "10": {
	        loc: {
	          start: {
	            line: 145,
	            column: 8
	          },
	          end: {
	            line: 147,
	            column: 9
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 145,
	            column: 8
	          },
	          end: {
	            line: 147,
	            column: 9
	          }
	        }, {
	          start: {
	            line: 145,
	            column: 8
	          },
	          end: {
	            line: 147,
	            column: 9
	          }
	        }],
	        line: 145
	      },
	      "11": {
	        loc: {
	          start: {
	            line: 149,
	            column: 8
	          },
	          end: {
	            line: 154,
	            column: 9
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 149,
	            column: 8
	          },
	          end: {
	            line: 154,
	            column: 9
	          }
	        }, {
	          start: {
	            line: 149,
	            column: 8
	          },
	          end: {
	            line: 154,
	            column: 9
	          }
	        }],
	        line: 149
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
	      "44": 0
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
	      "9": 0
	    },
	    b: {
	      "0": [0, 0],
	      "1": [0, 0],
	      "2": [0, 0, 0],
	      "3": [0, 0],
	      "4": [0, 0],
	      "5": [0, 0],
	      "6": [0, 0],
	      "7": [0, 0],
	      "8": [0, 0],
	      "9": [0, 0],
	      "10": [0, 0],
	      "11": [0, 0]
	    },
	    _coverageSchema: "1a1c01bbd47fc00a2c39e90264f33305004495a9",
	    hash: "43387c04a2b5ce0b5c9b11301e44f7603e5d2757"
	  };
	  var coverage = global[gcv] || (global[gcv] = {});
	  if (!coverage[path] || coverage[path].hash !== hash) {
	    coverage[path] = coverageData;
	  }
	  var actualCoverage = coverage[path];
	  {
	    // @ts-ignore
	    cov_1r1i8ni59x = function () {
	      return actualCoverage;
	    };
	  }
	  return actualCoverage;
	}
	cov_1r1i8ni59x();
	const Plugin = (cov_1r1i8ni59x().s[0]++, videojs__default["default"].getPlugin('plugin'));
	const defaults = (cov_1r1i8ni59x().s[1]++, {
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
	}); /**
	    * A plugin for handling overlays in the Brightcove Player.
	    */
	class OverlayPlugin extends Plugin {
	  /**
	  * Create an Overlay Plugin instance.
	  *
	  * @param  {Player} player
	  *         A Video.js Player instance.
	  *
	  * @param  {Object} [options]
	  *         An options object.
	  */
	  constructor(player, options) {
	    cov_1r1i8ni59x().f[0]++;
	    cov_1r1i8ni59x().s[2]++;
	    super(player);
	    cov_1r1i8ni59x().s[3]++;
	    this.reset(options);
	  } /**
	    * Adds one or more items to the existing list of overlays.
	    *
	    * @param {Object|Array} item
	    *        An item (or an array of items) to be added as overlay/s
	    *
	    * @return {Array[Overlay]}
	    *         The array of overlay objects that were added
	    */
	  add(item) {
	    cov_1r1i8ni59x().f[1]++;
	    cov_1r1i8ni59x().s[4]++;
	    if (!Array.isArray(item)) {
	      cov_1r1i8ni59x().b[0][0]++;
	      cov_1r1i8ni59x().s[5]++;
	      item = [item];
	    } else {
	      cov_1r1i8ni59x().b[0][1]++;
	    }
	    const addedOverlays = (cov_1r1i8ni59x().s[6]++, this.mapOverlays_(item));
	    cov_1r1i8ni59x().s[7]++;
	    this.player.overlays_ = this.player.overlays_.concat(addedOverlays);
	    cov_1r1i8ni59x().s[8]++;
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
	  remove(item) {
	    cov_1r1i8ni59x().f[2]++;
	    const index = (cov_1r1i8ni59x().s[9]++, this.player.overlays_.indexOf(item));
	    cov_1r1i8ni59x().s[10]++;
	    if (index !== -1) {
	      cov_1r1i8ni59x().b[1][0]++;
	      cov_1r1i8ni59x().s[11]++;
	      item.el().parentNode.removeChild(item.el());
	      cov_1r1i8ni59x().s[12]++;
	      this.player.overlays_.splice(index, 1);
	    } else {
	      cov_1r1i8ni59x().b[1][1]++;
	      cov_1r1i8ni59x().s[13]++;
	      this.player.log.warn('overlay does not exist and cannot be removed');
	    }
	  } /**
	    * Gets the array of overlays used for the current video
	    *
	    * @return The array of overlay objects currently used by the plugin
	    */
	  get() {
	    cov_1r1i8ni59x().f[3]++;
	    cov_1r1i8ni59x().s[14]++;
	    return this.player.overlays_;
	  } /**
	    * Updates the overlay options
	    *
	    * @param  {Object} [options]
	    *         An options object.
	    */
	  reset(options) {
	    cov_1r1i8ni59x().f[4]++;
	    cov_1r1i8ni59x().s[15]++;
	    this.clearOverlays_(); // Use merge function based on video.js version.
	    const merge = (cov_1r1i8ni59x().s[16]++, (cov_1r1i8ni59x().b[2][0]++, videojs__default["default"].obj) && (cov_1r1i8ni59x().b[2][1]++, videojs__default["default"].obj.merge) || (cov_1r1i8ni59x().b[2][2]++, videojs__default["default"].mergeOptions));
	    cov_1r1i8ni59x().s[17]++;
	    this.options = merge(defaults, options);
	    const overlays = (cov_1r1i8ni59x().s[18]++, this.options.overlays); // We don't want to keep the original array of overlay options around
	    // because it doesn't make sense to pass it to each Overlay component.
	    cov_1r1i8ni59x().s[19]++;
	    delete this.options.overlays;
	    cov_1r1i8ni59x().s[20]++;
	    this.player.overlays_ = this.mapOverlays_(overlays);
	  } /**
	    * Disposes the plugin
	    */
	  dispose() {
	    cov_1r1i8ni59x().f[5]++;
	    cov_1r1i8ni59x().s[21]++;
	    this.clearOverlays_();
	    cov_1r1i8ni59x().s[22]++;
	    delete this.player.overlays_;
	    cov_1r1i8ni59x().s[23]++;
	    super.dispose();
	  }
	  clearOverlays_() {
	    cov_1r1i8ni59x().f[6]++;
	    cov_1r1i8ni59x().s[24]++; // Remove child components
	    if (Array.isArray(this.player.overlays_)) {
	      cov_1r1i8ni59x().b[3][0]++;
	      cov_1r1i8ni59x().s[25]++;
	      this.player.overlays_.forEach(overlay => {
	        cov_1r1i8ni59x().f[7]++;
	        cov_1r1i8ni59x().s[26]++;
	        this.player.removeChild(overlay);
	        cov_1r1i8ni59x().s[27]++;
	        if (this.player.controlBar) {
	          cov_1r1i8ni59x().b[4][0]++;
	          cov_1r1i8ni59x().s[28]++;
	          this.player.controlBar.removeChild(overlay);
	        } else {
	          cov_1r1i8ni59x().b[4][1]++;
	        }
	      });
	    } else {
	      cov_1r1i8ni59x().b[3][1]++;
	    }
	  }
	  mapOverlays_(items) {
	    cov_1r1i8ni59x().f[8]++;
	    cov_1r1i8ni59x().s[29]++;
	    return items.map(o => {
	      cov_1r1i8ni59x().f[9]++;
	      const mergeOptions = (cov_1r1i8ni59x().s[30]++, videojs__default["default"].mergeOptions(this.options, o));
	      const attachToControlBar = (cov_1r1i8ni59x().s[31]++, (cov_1r1i8ni59x().b[5][0]++, typeof mergeOptions.attachToControlBar === 'string') || (cov_1r1i8ni59x().b[5][1]++, mergeOptions.attachToControlBar === true));
	      cov_1r1i8ni59x().s[32]++;
	      if ((cov_1r1i8ni59x().b[7][0]++, !this.player.controls()) || (cov_1r1i8ni59x().b[7][1]++, !this.player.controlBar)) {
	        cov_1r1i8ni59x().b[6][0]++;
	        cov_1r1i8ni59x().s[33]++;
	        return this.player.addChild('overlay', mergeOptions);
	      } else {
	        cov_1r1i8ni59x().b[6][1]++;
	      }
	      cov_1r1i8ni59x().s[34]++;
	      if ((cov_1r1i8ni59x().b[9][0]++, attachToControlBar) && (cov_1r1i8ni59x().b[9][1]++, mergeOptions.align.indexOf('bottom') !== -1)) {
	        cov_1r1i8ni59x().b[8][0]++;
	        let referenceChild = (cov_1r1i8ni59x().s[35]++, this.player.controlBar.children()[0]);
	        cov_1r1i8ni59x().s[36]++;
	        if (this.player.controlBar.getChild(mergeOptions.attachToControlBar) !== undefined) {
	          cov_1r1i8ni59x().b[10][0]++;
	          cov_1r1i8ni59x().s[37]++;
	          referenceChild = this.player.controlBar.getChild(mergeOptions.attachToControlBar);
	        } else {
	          cov_1r1i8ni59x().b[10][1]++;
	        }
	        cov_1r1i8ni59x().s[38]++;
	        if (referenceChild) {
	          cov_1r1i8ni59x().b[11][0]++;
	          const referenceChildIndex = (cov_1r1i8ni59x().s[39]++, this.player.controlBar.children().indexOf(referenceChild));
	          const controlBarChild = (cov_1r1i8ni59x().s[40]++, this.player.controlBar.addChild('overlay', mergeOptions, referenceChildIndex));
	          cov_1r1i8ni59x().s[41]++;
	          return controlBarChild;
	        } else {
	          cov_1r1i8ni59x().b[11][1]++;
	        }
	      } else {
	        cov_1r1i8ni59x().b[8][1]++;
	      }
	      const playerChild = (cov_1r1i8ni59x().s[42]++, this.player.addChild('overlay', mergeOptions));
	      cov_1r1i8ni59x().s[43]++;
	      this.player.el().insertBefore(playerChild.el(), this.player.controlBar.el());
	      cov_1r1i8ni59x().s[44]++;
	      return playerChild;
	    });
	  }
	}

	var version = "4.0.0";

	function cov_29pbfi7s38() {
	  var path = "/Users/wseymour/Desktop/devOS/videojs-overlay/src/index.js";
	  var hash = "a5065635dbe16261079b680cca138c6206a32808";
	  var global = new Function("return this")();
	  var gcv = "__coverage__";
	  var coverageData = {
	    path: "/Users/wseymour/Desktop/devOS/videojs-overlay/src/index.js",
	    statementMap: {
	      "0": {
	        start: {
	          line: 6,
	          column: 0
	        },
	        end: {
	          line: 6,
	          column: 30
	        }
	      },
	      "1": {
	        start: {
	          line: 8,
	          column: 0
	        },
	        end: {
	          line: 8,
	          column: 32
	        }
	      },
	      "2": {
	        start: {
	          line: 10,
	          column: 0
	        },
	        end: {
	          line: 10,
	          column: 49
	        }
	      }
	    },
	    fnMap: {},
	    branchMap: {},
	    s: {
	      "0": 0,
	      "1": 0,
	      "2": 0
	    },
	    f: {},
	    b: {},
	    _coverageSchema: "1a1c01bbd47fc00a2c39e90264f33305004495a9",
	    hash: "a5065635dbe16261079b680cca138c6206a32808"
	  };
	  var coverage = global[gcv] || (global[gcv] = {});
	  if (!coverage[path] || coverage[path].hash !== hash) {
	    coverage[path] = coverageData;
	  }
	  var actualCoverage = coverage[path];
	  {
	    // @ts-ignore
	    cov_29pbfi7s38 = function () {
	      return actualCoverage;
	    };
	  }
	  return actualCoverage;
	}
	cov_29pbfi7s38();
	cov_29pbfi7s38().s[0]++;
	initOverlayComponent(videojs__default["default"]);
	cov_29pbfi7s38().s[1]++;
	OverlayPlugin.VERSION = version;
	cov_29pbfi7s38().s[2]++;
	videojs__default["default"].registerPlugin('overlay', OverlayPlugin);

	const Player = videojs__default["default"].getComponent('Player');
	const dom = videojs__default["default"].dom || videojs__default["default"];
	QUnit__default["default"].test('the environment is sane', function (assert) {
	  assert.strictEqual(typeof Array.isArray, 'function', 'es5 exists');
	  assert.strictEqual(typeof sinon__default["default"], 'object', 'sinon exists');
	  assert.strictEqual(typeof videojs__default["default"], 'function', 'videojs exists');
	  assert.strictEqual(typeof OverlayPlugin, 'function', 'plugin is a function');
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
	QUnit__default["default"].test('can reinitialize the plugin on reset', function (assert) {
	  assert.expect(3);
	  const overlayPlugin = this.player.overlay({
	    attachToControlBar: true,
	    overlays: [{
	      start: 'start',
	      align: 'bottom-left'
	    }, {
	      start: 'start',
	      align: 'top-right'
	    }]
	  });
	  overlayPlugin.reset({
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
	  const overlayPlugin = this.player.overlay({
	    attachToControlBar: true,
	    overlays: [{
	      start: 'start',
	      align: 'bottom'
	    }]
	  });
	  this.player.trigger('start');
	  assert.equal(this.player.controlBar.$('.vjs-overlay.vjs-overlay-bottom'), this.player.controlBar.el().firstChild, 'bottom attaches as first child of control bar');
	  overlayPlugin.reset({
	    attachToControlBar: true,
	    overlays: [{
	      start: 'start',
	      align: 'top'
	    }]
	  });
	  this.player.trigger('start');
	  assert.equal(this.player.$('.vjs-overlay.vjs-overlay-top'), this.player.controlBar.el().previousSibling, 'top attaches as previous sibiling of controlBar');
	  overlayPlugin.reset({
	    attachToControlBar: 'RemainingTimeDisplay',
	    overlays: [{
	      start: 'start',
	      align: 'bottom'
	    }]
	  });
	  this.player.trigger('start');
	  assert.equal(this.player.controlBar.$('.vjs-overlay.vjs-overlay-bottom'), this.player.controlBar.remainingTimeDisplay.el().previousSibling, 'bottom attaches as previous sibiling of attachToControlBar component');
	  overlayPlugin.reset({
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
	  const overlayPlugin = this.player.overlay({
	    overlays: [{
	      start: 'start',
	      align: 'bottom'
	    }]
	  });
	  this.player.trigger('start');
	  assert.equal(this.player.$('.vjs-overlay.vjs-overlay-bottom'), this.player.el().lastChild, 'bottom attaches as last child of player');
	  overlayPlugin.reset({
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
