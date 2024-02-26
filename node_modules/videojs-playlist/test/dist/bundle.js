/*! @name videojs-playlist @version 5.1.0 @license Apache-2.0 */
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

	function cov_1iwvftf2su() {
	  var path = "/Users/bclifford/Code/videojs-playlist/src/auto-advance.js";
	  var hash = "6d18dc67ced86940e573709df85590b78e79097c";
	  var global = new Function("return this")();
	  var gcv = "__coverage__";
	  var coverageData = {
	    path: "/Users/bclifford/Code/videojs-playlist/src/auto-advance.js",
	    statementMap: {
	      "0": {
	        start: {
	          line: 12,
	          column: 21
	        },
	        end: {
	          line: 13,
	          column: 62
	        }
	      },
	      "1": {
	        start: {
	          line: 13,
	          column: 2
	        },
	        end: {
	          line: 13,
	          column: 62
	        }
	      },
	      "2": {
	        start: {
	          line: 21,
	          column: 12
	        },
	        end: {
	          line: 34,
	          column: 1
	        }
	      },
	      "3": {
	        start: {
	          line: 22,
	          column: 13
	        },
	        end: {
	          line: 22,
	          column: 41
	        }
	      },
	      "4": {
	        start: {
	          line: 24,
	          column: 2
	        },
	        end: {
	          line: 26,
	          column: 3
	        }
	      },
	      "5": {
	        start: {
	          line: 25,
	          column: 4
	        },
	        end: {
	          line: 25,
	          column: 36
	        }
	      },
	      "6": {
	        start: {
	          line: 28,
	          column: 2
	        },
	        end: {
	          line: 30,
	          column: 3
	        }
	      },
	      "7": {
	        start: {
	          line: 29,
	          column: 4
	        },
	        end: {
	          line: 29,
	          column: 36
	        }
	      },
	      "8": {
	        start: {
	          line: 32,
	          column: 2
	        },
	        end: {
	          line: 32,
	          column: 20
	        }
	      },
	      "9": {
	        start: {
	          line: 33,
	          column: 2
	        },
	        end: {
	          line: 33,
	          column: 20
	        }
	      },
	      "10": {
	        start: {
	          line: 48,
	          column: 14
	        },
	        end: {
	          line: 80,
	          column: 1
	        }
	      },
	      "11": {
	        start: {
	          line: 49,
	          column: 2
	        },
	        end: {
	          line: 49,
	          column: 16
	        }
	      },
	      "12": {
	        start: {
	          line: 53,
	          column: 2
	        },
	        end: {
	          line: 56,
	          column: 3
	        }
	      },
	      "13": {
	        start: {
	          line: 54,
	          column: 4
	        },
	        end: {
	          line: 54,
	          column: 46
	        }
	      },
	      "14": {
	        start: {
	          line: 55,
	          column: 4
	        },
	        end: {
	          line: 55,
	          column: 11
	        }
	      },
	      "15": {
	        start: {
	          line: 58,
	          column: 2
	        },
	        end: {
	          line: 58,
	          column: 45
	        }
	      },
	      "16": {
	        start: {
	          line: 60,
	          column: 2
	        },
	        end: {
	          line: 77,
	          column: 4
	        }
	      },
	      "17": {
	        start: {
	          line: 64,
	          column: 25
	        },
	        end: {
	          line: 64,
	          column: 51
	        }
	      },
	      "18": {
	        start: {
	          line: 64,
	          column: 31
	        },
	        end: {
	          line: 64,
	          column: 51
	        }
	      },
	      "19": {
	        start: {
	          line: 70,
	          column: 4
	        },
	        end: {
	          line: 70,
	          column: 37
	        }
	      },
	      "20": {
	        start: {
	          line: 72,
	          column: 4
	        },
	        end: {
	          line: 76,
	          column: 21
	        }
	      },
	      "21": {
	        start: {
	          line: 73,
	          column: 6
	        },
	        end: {
	          line: 73,
	          column: 20
	        }
	      },
	      "22": {
	        start: {
	          line: 74,
	          column: 6
	        },
	        end: {
	          line: 74,
	          column: 39
	        }
	      },
	      "23": {
	        start: {
	          line: 75,
	          column: 6
	        },
	        end: {
	          line: 75,
	          column: 29
	        }
	      },
	      "24": {
	        start: {
	          line: 79,
	          column: 2
	        },
	        end: {
	          line: 79,
	          column: 60
	        }
	      },
	      "25": {
	        start: {
	          line: 89,
	          column: 18
	        },
	        end: {
	          line: 91,
	          column: 1
	        }
	      },
	      "26": {
	        start: {
	          line: 90,
	          column: 2
	        },
	        end: {
	          line: 90,
	          column: 13
	        }
	      }
	    },
	    fnMap: {
	      "0": {
	        name: "(anonymous_0)",
	        decl: {
	          start: {
	            line: 12,
	            column: 21
	          },
	          end: {
	            line: 12,
	            column: 22
	          }
	        },
	        loc: {
	          start: {
	            line: 13,
	            column: 2
	          },
	          end: {
	            line: 13,
	            column: 62
	          }
	        },
	        line: 13
	      },
	      "1": {
	        name: "(anonymous_1)",
	        decl: {
	          start: {
	            line: 21,
	            column: 12
	          },
	          end: {
	            line: 21,
	            column: 13
	          }
	        },
	        loc: {
	          start: {
	            line: 21,
	            column: 24
	          },
	          end: {
	            line: 34,
	            column: 1
	          }
	        },
	        line: 21
	      },
	      "2": {
	        name: "(anonymous_2)",
	        decl: {
	          start: {
	            line: 48,
	            column: 14
	          },
	          end: {
	            line: 48,
	            column: 15
	          }
	        },
	        loc: {
	          start: {
	            line: 48,
	            column: 33
	          },
	          end: {
	            line: 80,
	            column: 1
	          }
	        },
	        line: 48
	      },
	      "3": {
	        name: "(anonymous_3)",
	        decl: {
	          start: {
	            line: 60,
	            column: 41
	          },
	          end: {
	            line: 60,
	            column: 42
	          }
	        },
	        loc: {
	          start: {
	            line: 60,
	            column: 52
	          },
	          end: {
	            line: 77,
	            column: 3
	          }
	        },
	        line: 60
	      },
	      "4": {
	        name: "(anonymous_4)",
	        decl: {
	          start: {
	            line: 64,
	            column: 25
	          },
	          end: {
	            line: 64,
	            column: 26
	          }
	        },
	        loc: {
	          start: {
	            line: 64,
	            column: 31
	          },
	          end: {
	            line: 64,
	            column: 51
	          }
	        },
	        line: 64
	      },
	      "5": {
	        name: "(anonymous_5)",
	        decl: {
	          start: {
	            line: 72,
	            column: 61
	          },
	          end: {
	            line: 72,
	            column: 62
	          }
	        },
	        loc: {
	          start: {
	            line: 72,
	            column: 67
	          },
	          end: {
	            line: 76,
	            column: 5
	          }
	        },
	        line: 72
	      },
	      "6": {
	        name: "(anonymous_6)",
	        decl: {
	          start: {
	            line: 89,
	            column: 18
	          },
	          end: {
	            line: 89,
	            column: 19
	          }
	        },
	        loc: {
	          start: {
	            line: 89,
	            column: 26
	          },
	          end: {
	            line: 91,
	            column: 1
	          }
	        },
	        line: 89
	      }
	    },
	    branchMap: {
	      "0": {
	        loc: {
	          start: {
	            line: 13,
	            column: 2
	          },
	          end: {
	            line: 13,
	            column: 62
	          }
	        },
	        type: "binary-expr",
	        locations: [{
	          start: {
	            line: 13,
	            column: 2
	          },
	          end: {
	            line: 13,
	            column: 23
	          }
	        }, {
	          start: {
	            line: 13,
	            column: 27
	          },
	          end: {
	            line: 13,
	            column: 36
	          }
	        }, {
	          start: {
	            line: 13,
	            column: 40
	          },
	          end: {
	            line: 13,
	            column: 46
	          }
	        }, {
	          start: {
	            line: 13,
	            column: 50
	          },
	          end: {
	            line: 13,
	            column: 62
	          }
	        }],
	        line: 13
	      },
	      "1": {
	        loc: {
	          start: {
	            line: 24,
	            column: 2
	          },
	          end: {
	            line: 26,
	            column: 3
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 24,
	            column: 2
	          },
	          end: {
	            line: 26,
	            column: 3
	          }
	        }, {
	          start: {
	            line: 24,
	            column: 2
	          },
	          end: {
	            line: 26,
	            column: 3
	          }
	        }],
	        line: 24
	      },
	      "2": {
	        loc: {
	          start: {
	            line: 28,
	            column: 2
	          },
	          end: {
	            line: 30,
	            column: 3
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 28,
	            column: 2
	          },
	          end: {
	            line: 30,
	            column: 3
	          }
	        }, {
	          start: {
	            line: 28,
	            column: 2
	          },
	          end: {
	            line: 30,
	            column: 3
	          }
	        }],
	        line: 28
	      },
	      "3": {
	        loc: {
	          start: {
	            line: 53,
	            column: 2
	          },
	          end: {
	            line: 56,
	            column: 3
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 53,
	            column: 2
	          },
	          end: {
	            line: 56,
	            column: 3
	          }
	        }, {
	          start: {
	            line: 53,
	            column: 2
	          },
	          end: {
	            line: 56,
	            column: 3
	          }
	        }],
	        line: 53
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
	      "26": 0
	    },
	    f: {
	      "0": 0,
	      "1": 0,
	      "2": 0,
	      "3": 0,
	      "4": 0,
	      "5": 0,
	      "6": 0
	    },
	    b: {
	      "0": [0, 0, 0, 0],
	      "1": [0, 0],
	      "2": [0, 0],
	      "3": [0, 0]
	    },
	    _coverageSchema: "1a1c01bbd47fc00a2c39e90264f33305004495a9",
	    hash: "6d18dc67ced86940e573709df85590b78e79097c"
	  };
	  var coverage = global[gcv] || (global[gcv] = {});

	  if (!coverage[path] || coverage[path].hash !== hash) {
	    coverage[path] = coverageData;
	  }

	  var actualCoverage = coverage[path];
	  {
	    // @ts-ignore
	    cov_1iwvftf2su = function () {
	      return actualCoverage;
	    };
	  }
	  return actualCoverage;
	}

	cov_1iwvftf2su();
	cov_1iwvftf2su().s[0]++;
	/**
	* Validates a number of seconds to use as the auto-advance delay.
	*
	* @private
	* @param   {number} s
	*          The number to check
	*
	* @return  {boolean}
	*          Whether this is a valid second or not
	*/

	const validSeconds = s => {
	  cov_1iwvftf2su().f[0]++;
	  cov_1iwvftf2su().s[1]++;
	  return (cov_1iwvftf2su().b[0][0]++, typeof s === 'number') && (cov_1iwvftf2su().b[0][1]++, !isNaN(s)) && (cov_1iwvftf2su().b[0][2]++, s >= 0) && (cov_1iwvftf2su().b[0][3]++, s < Infinity);
	};
	/**
	* Resets the auto-advance behavior of a player.
	*
	* @param {Player} player
	*        The player to reset the behavior on
	*/


	cov_1iwvftf2su().s[2]++;

	let reset = player => {
	  cov_1iwvftf2su().f[1]++;
	  const aa = (cov_1iwvftf2su().s[3]++, player.playlist.autoadvance_);
	  cov_1iwvftf2su().s[4]++;

	  if (aa.timeout) {
	    cov_1iwvftf2su().b[1][0]++;
	    cov_1iwvftf2su().s[5]++;
	    player.clearTimeout(aa.timeout);
	  } else {
	    cov_1iwvftf2su().b[1][1]++;
	  }

	  cov_1iwvftf2su().s[6]++;

	  if (aa.trigger) {
	    cov_1iwvftf2su().b[2][0]++;
	    cov_1iwvftf2su().s[7]++;
	    player.off('ended', aa.trigger);
	  } else {
	    cov_1iwvftf2su().b[2][1]++;
	  }

	  cov_1iwvftf2su().s[8]++;
	  aa.timeout = null;
	  cov_1iwvftf2su().s[9]++;
	  aa.trigger = null;
	};
	/**
	* Sets up auto-advance behavior on a player.
	*
	* @param  {Player} player
	*         the current player
	*
	* @param  {number} delay
	*         The number of seconds to wait before each auto-advance.
	*
	* @return {undefined}
	*         Used to short circuit function logic
	*/


	cov_1iwvftf2su().s[10]++;

	const setup = (player, delay) => {
	  cov_1iwvftf2su().f[2]++;
	  cov_1iwvftf2su().s[11]++;
	  reset(player); // Before queuing up new auto-advance behavior, check if `seconds` was
	  // called with a valid value.

	  cov_1iwvftf2su().s[12]++;

	  if (!validSeconds(delay)) {
	    cov_1iwvftf2su().b[3][0]++;
	    cov_1iwvftf2su().s[13]++;
	    player.playlist.autoadvance_.delay = null;
	    cov_1iwvftf2su().s[14]++;
	    return;
	  } else {
	    cov_1iwvftf2su().b[3][1]++;
	  }

	  cov_1iwvftf2su().s[15]++;
	  player.playlist.autoadvance_.delay = delay;
	  cov_1iwvftf2su().s[16]++;

	  player.playlist.autoadvance_.trigger = function () {
	    cov_1iwvftf2su().f[3]++;
	    cov_1iwvftf2su().s[17]++; // This calls setup again, which will reset the existing auto-advance and
	    // set up another auto-advance for the next "ended" event.

	    const cancelOnPlay = () => {
	      cov_1iwvftf2su().f[4]++;
	      cov_1iwvftf2su().s[18]++;
	      return setup(player, delay);
	    }; // If there is a "play" event while we're waiting for an auto-advance,
	    // we need to cancel the auto-advance. This could mean the user seeked
	    // back into the content or restarted the content. This is reproducible
	    // with an auto-advance > 0.


	    cov_1iwvftf2su().s[19]++;
	    player.one('play', cancelOnPlay);
	    cov_1iwvftf2su().s[20]++;
	    player.playlist.autoadvance_.timeout = player.setTimeout(() => {
	      cov_1iwvftf2su().f[5]++;
	      cov_1iwvftf2su().s[21]++;
	      reset(player);
	      cov_1iwvftf2su().s[22]++;
	      player.off('play', cancelOnPlay);
	      cov_1iwvftf2su().s[23]++;
	      player.playlist.next();
	    }, delay * 1000);
	  };

	  cov_1iwvftf2su().s[24]++;
	  player.one('ended', player.playlist.autoadvance_.trigger);
	};
	/**
	* Used to change the reset function in this module at runtime
	* This should only be used in tests.
	*
	* @param {Function} fn
	*        The function to se the reset to
	*/


	cov_1iwvftf2su().s[25]++;

	const setReset_ = fn => {
	  cov_1iwvftf2su().f[6]++;
	  cov_1iwvftf2su().s[26]++;
	  reset = fn;
	};

	function _extends() {
	  _extends = Object.assign || function (target) {
	    for (var i = 1; i < arguments.length; i++) {
	      var source = arguments[i];

	      for (var key in source) {
	        if (Object.prototype.hasOwnProperty.call(source, key)) {
	          target[key] = source[key];
	        }
	      }
	    }

	    return target;
	  };

	  return _extends.apply(this, arguments);
	}

	const proxy = props => {
	  let poster_ = '';

	  const player = _extends({}, videojs__default["default"].EventTarget.prototype, {
	    play: () => {},
	    paused: () => {},
	    ended: () => {},
	    poster: src => {
	      if (src !== undefined) {
	        poster_ = src;
	      }

	      return poster_;
	    },
	    src: () => {},
	    currentSrc: () => {},
	    addRemoteTextTrack: () => {},
	    removeRemoteTextTrack: () => {},
	    remoteTextTracks: () => {},
	    playlist: () => [],
	    ready: cb => cb(),
	    setTimeout: (cb, wait) => window_1.setTimeout(cb, wait),
	    clearTimeout: id => window_1.clearTimeout(id)
	  }, props);

	  player.constructor = videojs__default["default"].getComponent('Player');
	  player.playlist.player_ = player;
	  player.playlist.autoadvance_ = {};
	  player.playlist.currentIndex_ = -1;

	  player.playlist.autoadvance = () => {};

	  player.playlist.contains = () => {};

	  player.playlist.currentItem = () => {};

	  player.playlist.first = () => {};

	  player.playlist.indexOf = () => {};

	  player.playlist.next = () => {};

	  player.playlist.previous = () => {};

	  return player;
	};

	QUnit__default["default"].module('auto-advance');
	QUnit__default["default"].test('set up ended listener if one does not exist yet', function (assert) {
	  const player = proxy();
	  const ones = [];

	  player.one = function (type) {
	    ones.push(type);
	  };

	  setup(player, 0);
	  assert.equal(ones.length, 1, 'there should have been only one one event added');
	  assert.equal(ones[0], 'ended', 'the event we want to one is "ended"');
	});
	QUnit__default["default"].test('off previous listener if exists before adding a new one', function (assert) {
	  const player = proxy();
	  const ones = [];
	  const offs = [];

	  player.one = function (type) {
	    ones.push(type);
	  };

	  player.off = function (type) {
	    offs.push(type);
	  };

	  setup(player, 0);
	  assert.equal(ones.length, 1, 'there should have been only one one event added');
	  assert.equal(ones[0], 'ended', 'the event we want to one is "ended"');
	  assert.equal(offs.length, 0, 'we should not have off-ed anything yet');
	  setup(player, 10);
	  assert.equal(ones.length, 2, 'there should have been only two one event added');
	  assert.equal(ones[0], 'ended', 'the event we want to one is "ended"');
	  assert.equal(ones[1], 'ended', 'the event we want to one is "ended"');
	  assert.equal(offs.length, 1, 'there should have been only one off event added');
	  assert.equal(offs[0], 'ended', 'the event we want to off is "ended"');
	});
	QUnit__default["default"].test('do nothing if timeout is weird', function (assert) {
	  const player = proxy();
	  const ones = [];
	  const offs = [];

	  player.one = function (type) {
	    ones.push(type);
	  };

	  player.off = function (type) {
	    offs.push(type);
	  };

	  setup(player, -1);
	  setup(player, -100);
	  setup(player, null);
	  setup(player, {});
	  setup(player, []);
	  assert.equal(offs.length, 0, 'we did nothing');
	  assert.equal(ones.length, 0, 'we did nothing');
	});
	QUnit__default["default"].test('reset if timeout is weird after we advance', function (assert) {
	  const player = proxy();
	  const ones = [];
	  const offs = [];

	  player.one = function (type) {
	    ones.push(type);
	  };

	  player.off = function (type) {
	    offs.push(type);
	  };

	  setup(player, 0);
	  setup(player, -1);
	  setup(player, 0);
	  setup(player, -100);
	  setup(player, 0);
	  setup(player, null);
	  setup(player, 0);
	  setup(player, {});
	  setup(player, 0);
	  setup(player, []);
	  setup(player, 0);
	  setup(player, NaN);
	  setup(player, 0);
	  setup(player, Infinity);
	  setup(player, 0);
	  setup(player, -Infinity);
	  assert.equal(offs.length, 8, 'we reset the advance 8 times');
	  assert.equal(ones.length, 8, 'we autoadvanced 8 times');
	});
	QUnit__default["default"].test('reset if we have already started advancing', function (assert) {
	  const player = proxy();
	  const oldClearTimeout = window_1.clearTimeout;
	  let clears = 0;

	  window_1.clearTimeout = function () {
	    clears++;
	  }; // pretend we started autoadvancing


	  player.playlist.autoadvance_.timeout = 1;
	  setup(player, 0);
	  assert.equal(clears, 1, 'we reset the auto advance');
	  window_1.clearTimeout = oldClearTimeout;
	});
	QUnit__default["default"].test('timeout is given in seconds', function (assert) {
	  const player = proxy();
	  const oldSetTimeout = window_1.setTimeout;
	  player.addEventListener = Function.prototype;

	  window_1.setTimeout = function (fn, timeout) {
	    assert.equal(timeout, 10 * 1000, 'timeout was given in seconds');
	  };

	  setup(player, 10);
	  player.trigger('ended');
	  window_1.setTimeout = oldSetTimeout;
	});
	QUnit__default["default"].test('cancel a pending auto-advance if play is requested', function (assert) {
	  const clock = sinon__default["default"].useFakeTimers();
	  const player = proxy();
	  sinon__default["default"].spy(player.playlist, 'next');
	  setup(player, 10);
	  player.trigger('ended');
	  clock.tick(10000);
	  assert.equal(player.playlist.next.callCount, 1, 'next was called');
	  setup(player, 10);
	  player.trigger('ended');
	  clock.tick(5000);
	  player.trigger('play');
	  clock.tick(5000);
	  assert.equal(player.playlist.next.callCount, 1, 'next was not called because a "play" occurred');
	  player.trigger('ended');
	  clock.tick(10000);
	  assert.equal(player.playlist.next.callCount, 2, 'next was called again because the content ended again and the appropriate wait time elapsed');
	});

	function cov_18lzcgzrqf() {
	  var path = "/Users/bclifford/Code/videojs-playlist/src/play-item.js";
	  var hash = "e6c76989b9464c7b9cab9c4ae4238e461a2feeec";
	  var global = new Function("return this")();
	  var gcv = "__coverage__";
	  var coverageData = {
	    path: "/Users/bclifford/Code/videojs-playlist/src/play-item.js",
	    statementMap: {
	      "0": {
	        start: {
	          line: 9,
	          column: 20
	        },
	        end: {
	          line: 18,
	          column: 1
	        }
	      },
	      "1": {
	        start: {
	          line: 10,
	          column: 17
	        },
	        end: {
	          line: 10,
	          column: 42
	        }
	      },
	      "2": {
	        start: {
	          line: 11,
	          column: 10
	        },
	        end: {
	          line: 11,
	          column: 38
	        }
	      },
	      "3": {
	        start: {
	          line: 15,
	          column: 2
	        },
	        end: {
	          line: 17,
	          column: 3
	        }
	      },
	      "4": {
	        start: {
	          line: 16,
	          column: 4
	        },
	        end: {
	          line: 16,
	          column: 44
	        }
	      },
	      "5": {
	        start: {
	          line: 32,
	          column: 17
	        },
	        end: {
	          line: 64,
	          column: 1
	        }
	      },
	      "6": {
	        start: {
	          line: 33,
	          column: 17
	        },
	        end: {
	          line: 33,
	          column: 51
	        }
	      },
	      "7": {
	        start: {
	          line: 35,
	          column: 2
	        },
	        end: {
	          line: 35,
	          column: 67
	        }
	      },
	      "8": {
	        start: {
	          line: 37,
	          column: 2
	        },
	        end: {
	          line: 39,
	          column: 3
	        }
	      },
	      "9": {
	        start: {
	          line: 38,
	          column: 4
	        },
	        end: {
	          line: 38,
	          column: 66
	        }
	      },
	      "10": {
	        start: {
	          line: 41,
	          column: 2
	        },
	        end: {
	          line: 41,
	          column: 35
	        }
	      },
	      "11": {
	        start: {
	          line: 42,
	          column: 2
	        },
	        end: {
	          line: 42,
	          column: 27
	        }
	      },
	      "12": {
	        start: {
	          line: 43,
	          column: 2
	        },
	        end: {
	          line: 43,
	          column: 22
	        }
	      },
	      "13": {
	        start: {
	          line: 45,
	          column: 2
	        },
	        end: {
	          line: 61,
	          column: 5
	        }
	      },
	      "14": {
	        start: {
	          line: 47,
	          column: 4
	        },
	        end: {
	          line: 47,
	          column: 76
	        }
	      },
	      "15": {
	        start: {
	          line: 48,
	          column: 4
	        },
	        end: {
	          line: 48,
	          column: 63
	        }
	      },
	      "16": {
	        start: {
	          line: 50,
	          column: 4
	        },
	        end: {
	          line: 58,
	          column: 5
	        }
	      },
	      "17": {
	        start: {
	          line: 51,
	          column: 26
	        },
	        end: {
	          line: 51,
	          column: 39
	        }
	      },
	      "18": {
	        start: {
	          line: 55,
	          column: 6
	        },
	        end: {
	          line: 57,
	          column: 7
	        }
	      },
	      "19": {
	        start: {
	          line: 56,
	          column: 8
	        },
	        end: {
	          line: 56,
	          column: 42
	        }
	      },
	      "20": {
	        start: {
	          line: 60,
	          column: 4
	        },
	        end: {
	          line: 60,
	          column: 54
	        }
	      },
	      "21": {
	        start: {
	          line: 63,
	          column: 2
	        },
	        end: {
	          line: 63,
	          column: 16
	        }
	      }
	    },
	    fnMap: {
	      "0": {
	        name: "(anonymous_0)",
	        decl: {
	          start: {
	            line: 9,
	            column: 20
	          },
	          end: {
	            line: 9,
	            column: 21
	          }
	        },
	        loc: {
	          start: {
	            line: 9,
	            column: 32
	          },
	          end: {
	            line: 18,
	            column: 1
	          }
	        },
	        line: 9
	      },
	      "1": {
	        name: "(anonymous_1)",
	        decl: {
	          start: {
	            line: 32,
	            column: 17
	          },
	          end: {
	            line: 32,
	            column: 18
	          }
	        },
	        loc: {
	          start: {
	            line: 32,
	            column: 35
	          },
	          end: {
	            line: 64,
	            column: 1
	          }
	        },
	        line: 32
	      },
	      "2": {
	        name: "(anonymous_2)",
	        decl: {
	          start: {
	            line: 45,
	            column: 15
	          },
	          end: {
	            line: 45,
	            column: 16
	          }
	        },
	        loc: {
	          start: {
	            line: 45,
	            column: 21
	          },
	          end: {
	            line: 61,
	            column: 3
	          }
	        },
	        line: 45
	      },
	      "3": {
	        name: "(anonymous_3)",
	        decl: {
	          start: {
	            line: 56,
	            column: 31
	          },
	          end: {
	            line: 56,
	            column: 32
	          }
	        },
	        loc: {
	          start: {
	            line: 56,
	            column: 38
	          },
	          end: {
	            line: 56,
	            column: 40
	          }
	        },
	        line: 56
	      }
	    },
	    branchMap: {
	      "0": {
	        loc: {
	          start: {
	            line: 11,
	            column: 10
	          },
	          end: {
	            line: 11,
	            column: 38
	          }
	        },
	        type: "binary-expr",
	        locations: [{
	          start: {
	            line: 11,
	            column: 10
	          },
	          end: {
	            line: 11,
	            column: 16
	          }
	        }, {
	          start: {
	            line: 11,
	            column: 20
	          },
	          end: {
	            line: 11,
	            column: 33
	          }
	        }, {
	          start: {
	            line: 11,
	            column: 37
	          },
	          end: {
	            line: 11,
	            column: 38
	          }
	        }],
	        line: 11
	      },
	      "1": {
	        loc: {
	          start: {
	            line: 33,
	            column: 17
	          },
	          end: {
	            line: 33,
	            column: 51
	          }
	        },
	        type: "binary-expr",
	        locations: [{
	          start: {
	            line: 33,
	            column: 17
	          },
	          end: {
	            line: 33,
	            column: 33
	          }
	        }, {
	          start: {
	            line: 33,
	            column: 37
	          },
	          end: {
	            line: 33,
	            column: 51
	          }
	        }],
	        line: 33
	      },
	      "2": {
	        loc: {
	          start: {
	            line: 35,
	            column: 39
	          },
	          end: {
	            line: 35,
	            column: 65
	          }
	        },
	        type: "binary-expr",
	        locations: [{
	          start: {
	            line: 35,
	            column: 39
	          },
	          end: {
	            line: 35,
	            column: 57
	          }
	        }, {
	          start: {
	            line: 35,
	            column: 61
	          },
	          end: {
	            line: 35,
	            column: 65
	          }
	        }],
	        line: 35
	      },
	      "3": {
	        loc: {
	          start: {
	            line: 37,
	            column: 2
	          },
	          end: {
	            line: 39,
	            column: 3
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 37,
	            column: 2
	          },
	          end: {
	            line: 39,
	            column: 3
	          }
	        }, {
	          start: {
	            line: 37,
	            column: 2
	          },
	          end: {
	            line: 39,
	            column: 3
	          }
	        }],
	        line: 37
	      },
	      "4": {
	        loc: {
	          start: {
	            line: 41,
	            column: 16
	          },
	          end: {
	            line: 41,
	            column: 33
	          }
	        },
	        type: "binary-expr",
	        locations: [{
	          start: {
	            line: 41,
	            column: 16
	          },
	          end: {
	            line: 41,
	            column: 27
	          }
	        }, {
	          start: {
	            line: 41,
	            column: 31
	          },
	          end: {
	            line: 41,
	            column: 33
	          }
	        }],
	        line: 41
	      },
	      "5": {
	        loc: {
	          start: {
	            line: 47,
	            column: 5
	          },
	          end: {
	            line: 47,
	            column: 26
	          }
	        },
	        type: "binary-expr",
	        locations: [{
	          start: {
	            line: 47,
	            column: 5
	          },
	          end: {
	            line: 47,
	            column: 20
	          }
	        }, {
	          start: {
	            line: 47,
	            column: 24
	          },
	          end: {
	            line: 47,
	            column: 26
	          }
	        }],
	        line: 47
	      },
	      "6": {
	        loc: {
	          start: {
	            line: 48,
	            column: 35
	          },
	          end: {
	            line: 48,
	            column: 61
	          }
	        },
	        type: "binary-expr",
	        locations: [{
	          start: {
	            line: 48,
	            column: 35
	          },
	          end: {
	            line: 48,
	            column: 53
	          }
	        }, {
	          start: {
	            line: 48,
	            column: 57
	          },
	          end: {
	            line: 48,
	            column: 61
	          }
	        }],
	        line: 48
	      },
	      "7": {
	        loc: {
	          start: {
	            line: 50,
	            column: 4
	          },
	          end: {
	            line: 58,
	            column: 5
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 50,
	            column: 4
	          },
	          end: {
	            line: 58,
	            column: 5
	          }
	        }, {
	          start: {
	            line: 50,
	            column: 4
	          },
	          end: {
	            line: 58,
	            column: 5
	          }
	        }],
	        line: 50
	      },
	      "8": {
	        loc: {
	          start: {
	            line: 55,
	            column: 6
	          },
	          end: {
	            line: 57,
	            column: 7
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 55,
	            column: 6
	          },
	          end: {
	            line: 57,
	            column: 7
	          }
	        }, {
	          start: {
	            line: 55,
	            column: 6
	          },
	          end: {
	            line: 57,
	            column: 7
	          }
	        }],
	        line: 55
	      },
	      "9": {
	        loc: {
	          start: {
	            line: 55,
	            column: 10
	          },
	          end: {
	            line: 55,
	            column: 86
	          }
	        },
	        type: "binary-expr",
	        locations: [{
	          start: {
	            line: 55,
	            column: 10
	          },
	          end: {
	            line: 55,
	            column: 44
	          }
	        }, {
	          start: {
	            line: 55,
	            column: 48
	          },
	          end: {
	            line: 55,
	            column: 86
	          }
	        }],
	        line: 55
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
	      "21": 0
	    },
	    f: {
	      "0": 0,
	      "1": 0,
	      "2": 0,
	      "3": 0
	    },
	    b: {
	      "0": [0, 0, 0],
	      "1": [0, 0],
	      "2": [0, 0],
	      "3": [0, 0],
	      "4": [0, 0],
	      "5": [0, 0],
	      "6": [0, 0],
	      "7": [0, 0],
	      "8": [0, 0],
	      "9": [0, 0]
	    },
	    _coverageSchema: "1a1c01bbd47fc00a2c39e90264f33305004495a9",
	    hash: "e6c76989b9464c7b9cab9c4ae4238e461a2feeec"
	  };
	  var coverage = global[gcv] || (global[gcv] = {});

	  if (!coverage[path] || coverage[path].hash !== hash) {
	    coverage[path] = coverageData;
	  }

	  var actualCoverage = coverage[path];
	  {
	    // @ts-ignore
	    cov_18lzcgzrqf = function () {
	      return actualCoverage;
	    };
	  }
	  return actualCoverage;
	}

	cov_18lzcgzrqf();
	/**
	* Removes all remote text tracks from a player.
	*
	* @param  {Player} player
	*         The player to clear tracks on
	*/

	cov_18lzcgzrqf().s[0]++;

	const clearTracks = player => {
	  cov_18lzcgzrqf().f[0]++;
	  const tracks = (cov_18lzcgzrqf().s[1]++, player.remoteTextTracks());
	  let i = (cov_18lzcgzrqf().s[2]++, (cov_18lzcgzrqf().b[0][0]++, tracks) && (cov_18lzcgzrqf().b[0][1]++, tracks.length) || (cov_18lzcgzrqf().b[0][2]++, 0)); // This uses a `while` loop rather than `forEach` because the
	  // `TextTrackList` object is a live DOM list (not an array).

	  cov_18lzcgzrqf().s[3]++;

	  while (i--) {
	    cov_18lzcgzrqf().s[4]++;
	    player.removeRemoteTextTrack(tracks[i]);
	  }
	};
	/**
	* Plays an item on a player's playlist.
	*
	* @param  {Player} player
	*         The player to play the item on
	*
	* @param  {Object} item
	*         A source from the playlist.
	*
	* @return {Player}
	*         The player that is now playing the item
	*/


	cov_18lzcgzrqf().s[5]++;

	const playItem = (player, item) => {
	  cov_18lzcgzrqf().f[1]++;
	  const replay = (cov_18lzcgzrqf().s[6]++, (cov_18lzcgzrqf().b[1][0]++, !player.paused()) || (cov_18lzcgzrqf().b[1][1]++, player.ended()));
	  cov_18lzcgzrqf().s[7]++;
	  player.trigger('beforeplaylistitem', (cov_18lzcgzrqf().b[2][0]++, item.originalValue) || (cov_18lzcgzrqf().b[2][1]++, item));
	  cov_18lzcgzrqf().s[8]++;

	  if (item.playlistItemId_) {
	    cov_18lzcgzrqf().b[3][0]++;
	    cov_18lzcgzrqf().s[9]++;
	    player.playlist.currentPlaylistItemId_ = item.playlistItemId_;
	  } else {
	    cov_18lzcgzrqf().b[3][1]++;
	  }

	  cov_18lzcgzrqf().s[10]++;
	  player.poster((cov_18lzcgzrqf().b[4][0]++, item.poster) || (cov_18lzcgzrqf().b[4][1]++, ''));
	  cov_18lzcgzrqf().s[11]++;
	  player.src(item.sources);
	  cov_18lzcgzrqf().s[12]++;
	  clearTracks(player);
	  cov_18lzcgzrqf().s[13]++;
	  player.ready(() => {
	    cov_18lzcgzrqf().f[2]++;
	    cov_18lzcgzrqf().s[14]++;
	    ((cov_18lzcgzrqf().b[5][0]++, item.textTracks) || (cov_18lzcgzrqf().b[5][1]++, [])).forEach(player.addRemoteTextTrack.bind(player));
	    cov_18lzcgzrqf().s[15]++;
	    player.trigger('playlistitem', (cov_18lzcgzrqf().b[6][0]++, item.originalValue) || (cov_18lzcgzrqf().b[6][1]++, item));
	    cov_18lzcgzrqf().s[16]++;

	    if (replay) {
	      cov_18lzcgzrqf().b[7][0]++;
	      const playPromise = (cov_18lzcgzrqf().s[17]++, player.play()); // silence error when a pause interrupts a play request
	      // on browsers which return a promise

	      cov_18lzcgzrqf().s[18]++;

	      if ((cov_18lzcgzrqf().b[9][0]++, typeof playPromise !== 'undefined') && (cov_18lzcgzrqf().b[9][1]++, typeof playPromise.then === 'function')) {
	        cov_18lzcgzrqf().b[8][0]++;
	        cov_18lzcgzrqf().s[19]++;
	        playPromise.then(null, e => {
	          cov_18lzcgzrqf().f[3]++;
	        });
	      } else {
	        cov_18lzcgzrqf().b[8][1]++;
	      }
	    } else {
	      cov_18lzcgzrqf().b[7][1]++;
	    }

	    cov_18lzcgzrqf().s[20]++;
	    setup(player, player.playlist.autoadvance_.delay);
	  });
	  cov_18lzcgzrqf().s[21]++;
	  return player;
	};

	function cov_spj91b6dj() {
	  var path = "/Users/bclifford/Code/videojs-playlist/src/playlist-maker.js";
	  var hash = "c0f838095342ad20c96402f38b283f968f259b04";
	  var global = new Function("return this")();
	  var gcv = "__coverage__";
	  var coverageData = {
	    path: "/Users/bclifford/Code/videojs-playlist/src/playlist-maker.js",
	    statementMap: {
	      "0": {
	        start: {
	          line: 6,
	          column: 11
	        },
	        end: {
	          line: 6,
	          column: 12
	        }
	      },
	      "1": {
	        start: {
	          line: 23,
	          column: 28
	        },
	        end: {
	          line: 37,
	          column: 1
	        }
	      },
	      "2": {
	        start: {
	          line: 24,
	          column: 13
	        },
	        end: {
	          line: 24,
	          column: 20
	        }
	      },
	      "3": {
	        start: {
	          line: 26,
	          column: 2
	        },
	        end: {
	          line: 32,
	          column: 3
	        }
	      },
	      "4": {
	        start: {
	          line: 30,
	          column: 4
	        },
	        end: {
	          line: 30,
	          column: 27
	        }
	      },
	      "5": {
	        start: {
	          line: 31,
	          column: 4
	        },
	        end: {
	          line: 31,
	          column: 33
	        }
	      },
	      "6": {
	        start: {
	          line: 34,
	          column: 2
	        },
	        end: {
	          line: 34,
	          column: 32
	        }
	      },
	      "7": {
	        start: {
	          line: 36,
	          column: 2
	        },
	        end: {
	          line: 36,
	          column: 14
	        }
	      },
	      "8": {
	        start: {
	          line: 51,
	          column: 29
	        },
	        end: {
	          line: 51,
	          column: 66
	        }
	      },
	      "9": {
	        start: {
	          line: 51,
	          column: 38
	        },
	        end: {
	          line: 51,
	          column: 66
	        }
	      },
	      "10": {
	        start: {
	          line: 66,
	          column: 31
	        },
	        end: {
	          line: 74,
	          column: 1
	        }
	      },
	      "11": {
	        start: {
	          line: 67,
	          column: 2
	        },
	        end: {
	          line: 71,
	          column: 3
	        }
	      },
	      "12": {
	        start: {
	          line: 67,
	          column: 15
	        },
	        end: {
	          line: 67,
	          column: 16
	        }
	      },
	      "13": {
	        start: {
	          line: 68,
	          column: 4
	        },
	        end: {
	          line: 70,
	          column: 5
	        }
	      },
	      "14": {
	        start: {
	          line: 69,
	          column: 6
	        },
	        end: {
	          line: 69,
	          column: 15
	        }
	      },
	      "15": {
	        start: {
	          line: 73,
	          column: 2
	        },
	        end: {
	          line: 73,
	          column: 12
	        }
	      },
	      "16": {
	        start: {
	          line: 91,
	          column: 21
	        },
	        end: {
	          line: 110,
	          column: 1
	        }
	      },
	      "17": {
	        start: {
	          line: 92,
	          column: 13
	        },
	        end: {
	          line: 92,
	          column: 20
	        }
	      },
	      "18": {
	        start: {
	          line: 93,
	          column: 13
	        },
	        end: {
	          line: 93,
	          column: 20
	        }
	      },
	      "19": {
	        start: {
	          line: 95,
	          column: 2
	        },
	        end: {
	          line: 97,
	          column: 3
	        }
	      },
	      "20": {
	        start: {
	          line: 96,
	          column: 4
	        },
	        end: {
	          line: 96,
	          column: 23
	        }
	      },
	      "21": {
	        start: {
	          line: 98,
	          column: 2
	        },
	        end: {
	          line: 100,
	          column: 3
	        }
	      },
	      "22": {
	        start: {
	          line: 99,
	          column: 4
	        },
	        end: {
	          line: 99,
	          column: 23
	        }
	      },
	      "23": {
	        start: {
	          line: 102,
	          column: 2
	        },
	        end: {
	          line: 104,
	          column: 3
	        }
	      },
	      "24": {
	        start: {
	          line: 103,
	          column: 4
	        },
	        end: {
	          line: 103,
	          column: 42
	        }
	      },
	      "25": {
	        start: {
	          line: 105,
	          column: 2
	        },
	        end: {
	          line: 107,
	          column: 3
	        }
	      },
	      "26": {
	        start: {
	          line: 106,
	          column: 4
	        },
	        end: {
	          line: 106,
	          column: 42
	        }
	      },
	      "27": {
	        start: {
	          line: 109,
	          column: 2
	        },
	        end: {
	          line: 109,
	          column: 23
	        }
	      },
	      "28": {
	        start: {
	          line: 127,
	          column: 23
	        },
	        end: {
	          line: 143,
	          column: 1
	        }
	      },
	      "29": {
	        start: {
	          line: 128,
	          column: 2
	        },
	        end: {
	          line: 140,
	          column: 3
	        }
	      },
	      "30": {
	        start: {
	          line: 128,
	          column: 15
	        },
	        end: {
	          line: 128,
	          column: 16
	        }
	      },
	      "31": {
	        start: {
	          line: 129,
	          column: 20
	        },
	        end: {
	          line: 129,
	          column: 34
	        }
	      },
	      "32": {
	        start: {
	          line: 131,
	          column: 4
	        },
	        end: {
	          line: 139,
	          column: 5
	        }
	      },
	      "33": {
	        start: {
	          line: 132,
	          column: 6
	        },
	        end: {
	          line: 138,
	          column: 7
	        }
	      },
	      "34": {
	        start: {
	          line: 132,
	          column: 19
	        },
	        end: {
	          line: 132,
	          column: 20
	        }
	      },
	      "35": {
	        start: {
	          line: 133,
	          column: 23
	        },
	        end: {
	          line: 133,
	          column: 33
	        }
	      },
	      "36": {
	        start: {
	          line: 135,
	          column: 8
	        },
	        end: {
	          line: 137,
	          column: 9
	        }
	      },
	      "37": {
	        start: {
	          line: 136,
	          column: 10
	        },
	        end: {
	          line: 136,
	          column: 19
	        }
	      },
	      "38": {
	        start: {
	          line: 142,
	          column: 2
	        },
	        end: {
	          line: 142,
	          column: 12
	        }
	      },
	      "39": {
	        start: {
	          line: 155,
	          column: 18
	        },
	        end: {
	          line: 168,
	          column: 1
	        }
	      },
	      "40": {
	        start: {
	          line: 156,
	          column: 14
	        },
	        end: {
	          line: 156,
	          column: 16
	        }
	      },
	      "41": {
	        start: {
	          line: 157,
	          column: 20
	        },
	        end: {
	          line: 157,
	          column: 34
	        }
	      },
	      "42": {
	        start: {
	          line: 159,
	          column: 2
	        },
	        end: {
	          line: 165,
	          column: 3
	        }
	      },
	      "43": {
	        start: {
	          line: 160,
	          column: 17
	        },
	        end: {
	          line: 160,
	          column: 76
	        }
	      },
	      "44": {
	        start: {
	          line: 161,
	          column: 18
	        },
	        end: {
	          line: 161,
	          column: 27
	        }
	      },
	      "45": {
	        start: {
	          line: 163,
	          column: 4
	        },
	        end: {
	          line: 163,
	          column: 27
	        }
	      },
	      "46": {
	        start: {
	          line: 164,
	          column: 4
	        },
	        end: {
	          line: 164,
	          column: 23
	        }
	      },
	      "47": {
	        start: {
	          line: 167,
	          column: 2
	        },
	        end: {
	          line: 167,
	          column: 13
	        }
	      },
	      "48": {
	        start: {
	          line: 202,
	          column: 13
	        },
	        end: {
	          line: 202,
	          column: 17
	        }
	      },
	      "49": {
	        start: {
	          line: 203,
	          column: 17
	        },
	        end: {
	          line: 203,
	          column: 22
	        }
	      },
	      "50": {
	        start: {
	          line: 223,
	          column: 19
	        },
	        end: {
	          line: 271,
	          column: 3
	        }
	      },
	      "51": {
	        start: {
	          line: 224,
	          column: 4
	        },
	        end: {
	          line: 226,
	          column: 5
	        }
	      },
	      "52": {
	        start: {
	          line: 225,
	          column: 6
	        },
	        end: {
	          line: 225,
	          column: 73
	        }
	      },
	      "53": {
	        start: {
	          line: 228,
	          column: 4
	        },
	        end: {
	          line: 266,
	          column: 5
	        }
	      },
	      "54": {
	        start: {
	          line: 231,
	          column: 31
	        },
	        end: {
	          line: 231,
	          column: 72
	        }
	      },
	      "55": {
	        start: {
	          line: 233,
	          column: 6
	        },
	        end: {
	          line: 233,
	          column: 48
	        }
	      },
	      "56": {
	        start: {
	          line: 236,
	          column: 6
	        },
	        end: {
	          line: 236,
	          column: 22
	        }
	      },
	      "57": {
	        start: {
	          line: 238,
	          column: 6
	        },
	        end: {
	          line: 246,
	          column: 9
	        }
	      },
	      "58": {
	        start: {
	          line: 248,
	          column: 6
	        },
	        end: {
	          line: 248,
	          column: 23
	        }
	      },
	      "59": {
	        start: {
	          line: 250,
	          column: 6
	        },
	        end: {
	          line: 252,
	          column: 7
	        }
	      },
	      "60": {
	        start: {
	          line: 251,
	          column: 8
	        },
	        end: {
	          line: 251,
	          column: 39
	        }
	      },
	      "61": {
	        start: {
	          line: 261,
	          column: 6
	        },
	        end: {
	          line: 265,
	          column: 7
	        }
	      },
	      "62": {
	        start: {
	          line: 262,
	          column: 8
	        },
	        end: {
	          line: 264,
	          column: 14
	        }
	      },
	      "63": {
	        start: {
	          line: 263,
	          column: 10
	        },
	        end: {
	          line: 263,
	          column: 69
	        }
	      },
	      "64": {
	        start: {
	          line: 270,
	          column: 4
	        },
	        end: {
	          line: 270,
	          column: 58
	        }
	      },
	      "65": {
	        start: {
	          line: 270,
	          column: 30
	        },
	        end: {
	          line: 270,
	          column: 56
	        }
	      },
	      "66": {
	        start: {
	          line: 274,
	          column: 2
	        },
	        end: {
	          line: 278,
	          column: 5
	        }
	      },
	      "67": {
	        start: {
	          line: 275,
	          column: 4
	        },
	        end: {
	          line: 277,
	          column: 5
	        }
	      },
	      "68": {
	        start: {
	          line: 276,
	          column: 6
	        },
	        end: {
	          line: 276,
	          column: 32
	        }
	      },
	      "69": {
	        start: {
	          line: 280,
	          column: 2
	        },
	        end: {
	          line: 280,
	          column: 30
	        }
	      },
	      "70": {
	        start: {
	          line: 281,
	          column: 2
	        },
	        end: {
	          line: 281,
	          column: 28
	        }
	      },
	      "71": {
	        start: {
	          line: 282,
	          column: 2
	        },
	        end: {
	          line: 282,
	          column: 29
	        }
	      },
	      "72": {
	        start: {
	          line: 283,
	          column: 2
	        },
	        end: {
	          line: 283,
	          column: 27
	        }
	      },
	      "73": {
	        start: {
	          line: 284,
	          column: 2
	        },
	        end: {
	          line: 284,
	          column: 41
	        }
	      },
	      "74": {
	        start: {
	          line: 297,
	          column: 2
	        },
	        end: {
	          line: 356,
	          column: 4
	        }
	      },
	      "75": {
	        start: {
	          line: 299,
	          column: 4
	        },
	        end: {
	          line: 301,
	          column: 5
	        }
	      },
	      "76": {
	        start: {
	          line: 300,
	          column: 6
	        },
	        end: {
	          line: 300,
	          column: 36
	        }
	      },
	      "77": {
	        start: {
	          line: 304,
	          column: 4
	        },
	        end: {
	          line: 327,
	          column: 5
	        }
	      },
	      "78": {
	        start: {
	          line: 310,
	          column: 6
	        },
	        end: {
	          line: 310,
	          column: 37
	        }
	      },
	      "79": {
	        start: {
	          line: 311,
	          column: 6
	        },
	        end: {
	          line: 314,
	          column: 8
	        }
	      },
	      "80": {
	        start: {
	          line: 322,
	          column: 6
	        },
	        end: {
	          line: 324,
	          column: 7
	        }
	      },
	      "81": {
	        start: {
	          line: 323,
	          column: 8
	        },
	        end: {
	          line: 323,
	          column: 26
	        }
	      },
	      "82": {
	        start: {
	          line: 326,
	          column: 6
	        },
	        end: {
	          line: 326,
	          column: 36
	        }
	      },
	      "83": {
	        start: {
	          line: 329,
	          column: 16
	        },
	        end: {
	          line: 329,
	          column: 51
	        }
	      },
	      "84": {
	        start: {
	          line: 336,
	          column: 4
	        },
	        end: {
	          line: 349,
	          column: 5
	        }
	      },
	      "85": {
	        start: {
	          line: 337,
	          column: 29
	        },
	        end: {
	          line: 337,
	          column: 90
	        }
	      },
	      "86": {
	        start: {
	          line: 338,
	          column: 19
	        },
	        end: {
	          line: 338,
	          column: 39
	        }
	      },
	      "87": {
	        start: {
	          line: 341,
	          column: 6
	        },
	        end: {
	          line: 344,
	          column: 7
	        }
	      },
	      "88": {
	        start: {
	          line: 342,
	          column: 8
	        },
	        end: {
	          line: 342,
	          column: 48
	        }
	      },
	      "89": {
	        start: {
	          line: 343,
	          column: 8
	        },
	        end: {
	          line: 343,
	          column: 38
	        }
	      },
	      "90": {
	        start: {
	          line: 348,
	          column: 6
	        },
	        end: {
	          line: 348,
	          column: 45
	        }
	      },
	      "91": {
	        start: {
	          line: 353,
	          column: 4
	        },
	        end: {
	          line: 353,
	          column: 51
	        }
	      },
	      "92": {
	        start: {
	          line: 355,
	          column: 4
	        },
	        end: {
	          line: 355,
	          column: 34
	        }
	      },
	      "93": {
	        start: {
	          line: 409,
	          column: 2
	        },
	        end: {
	          line: 425,
	          column: 4
	        }
	      },
	      "94": {
	        start: {
	          line: 410,
	          column: 4
	        },
	        end: {
	          line: 412,
	          column: 5
	        }
	      },
	      "95": {
	        start: {
	          line: 411,
	          column: 6
	        },
	        end: {
	          line: 411,
	          column: 77
	        }
	      },
	      "96": {
	        start: {
	          line: 413,
	          column: 4
	        },
	        end: {
	          line: 415,
	          column: 5
	        }
	      },
	      "97": {
	        start: {
	          line: 414,
	          column: 6
	        },
	        end: {
	          line: 414,
	          column: 26
	        }
	      },
	      "98": {
	        start: {
	          line: 416,
	          column: 4
	        },
	        end: {
	          line: 418,
	          column: 5
	        }
	      },
	      "99": {
	        start: {
	          line: 417,
	          column: 6
	        },
	        end: {
	          line: 417,
	          column: 22
	        }
	      },
	      "100": {
	        start: {
	          line: 419,
	          column: 4
	        },
	        end: {
	          line: 419,
	          column: 58
	        }
	      },
	      "101": {
	        start: {
	          line: 423,
	          column: 4
	        },
	        end: {
	          line: 423,
	          column: 60
	        }
	      },
	      "102": {
	        start: {
	          line: 424,
	          column: 4
	        },
	        end: {
	          line: 424,
	          column: 70
	        }
	      },
	      "103": {
	        start: {
	          line: 443,
	          column: 2
	        },
	        end: {
	          line: 456,
	          column: 4
	        }
	      },
	      "104": {
	        start: {
	          line: 444,
	          column: 4
	        },
	        end: {
	          line: 446,
	          column: 5
	        }
	      },
	      "105": {
	        start: {
	          line: 445,
	          column: 6
	        },
	        end: {
	          line: 445,
	          column: 77
	        }
	      },
	      "106": {
	        start: {
	          line: 447,
	          column: 4
	        },
	        end: {
	          line: 449,
	          column: 5
	        }
	      },
	      "107": {
	        start: {
	          line: 448,
	          column: 6
	        },
	        end: {
	          line: 448,
	          column: 13
	        }
	      },
	      "108": {
	        start: {
	          line: 450,
	          column: 4
	        },
	        end: {
	          line: 450,
	          column: 30
	        }
	      },
	      "109": {
	        start: {
	          line: 454,
	          column: 4
	        },
	        end: {
	          line: 454,
	          column: 63
	        }
	      },
	      "110": {
	        start: {
	          line: 455,
	          column: 4
	        },
	        end: {
	          line: 455,
	          column: 59
	        }
	      },
	      "111": {
	        start: {
	          line: 467,
	          column: 2
	        },
	        end: {
	          line: 469,
	          column: 4
	        }
	      },
	      "112": {
	        start: {
	          line: 468,
	          column: 4
	        },
	        end: {
	          line: 468,
	          column: 42
	        }
	      },
	      "113": {
	        start: {
	          line: 480,
	          column: 2
	        },
	        end: {
	          line: 498,
	          column: 4
	        }
	      },
	      "114": {
	        start: {
	          line: 481,
	          column: 4
	        },
	        end: {
	          line: 483,
	          column: 5
	        }
	      },
	      "115": {
	        start: {
	          line: 482,
	          column: 6
	        },
	        end: {
	          line: 482,
	          column: 41
	        }
	      },
	      "116": {
	        start: {
	          line: 485,
	          column: 20
	        },
	        end: {
	          line: 485,
	          column: 64
	        }
	      },
	      "117": {
	        start: {
	          line: 487,
	          column: 4
	        },
	        end: {
	          line: 495,
	          column: 5
	        }
	      },
	      "118": {
	        start: {
	          line: 487,
	          column: 17
	        },
	        end: {
	          line: 487,
	          column: 18
	        }
	      },
	      "119": {
	        start: {
	          line: 488,
	          column: 21
	        },
	        end: {
	          line: 488,
	          column: 31
	        }
	      },
	      "120": {
	        start: {
	          line: 490,
	          column: 6
	        },
	        end: {
	          line: 494,
	          column: 7
	        }
	      },
	      "121": {
	        start: {
	          line: 491,
	          column: 8
	        },
	        end: {
	          line: 491,
	          column: 44
	        }
	      },
	      "122": {
	        start: {
	          line: 492,
	          column: 13
	        },
	        end: {
	          line: 494,
	          column: 7
	        }
	      },
	      "123": {
	        start: {
	          line: 493,
	          column: 8
	        },
	        end: {
	          line: 493,
	          column: 48
	        }
	      },
	      "124": {
	        start: {
	          line: 497,
	          column: 4
	        },
	        end: {
	          line: 497,
	          column: 14
	        }
	      },
	      "125": {
	        start: {
	          line: 507,
	          column: 2
	        },
	        end: {
	          line: 507,
	          column: 55
	        }
	      },
	      "126": {
	        start: {
	          line: 507,
	          column: 32
	        },
	        end: {
	          line: 507,
	          column: 54
	        }
	      },
	      "127": {
	        start: {
	          line: 516,
	          column: 2
	        },
	        end: {
	          line: 516,
	          column: 45
	        }
	      },
	      "128": {
	        start: {
	          line: 516,
	          column: 29
	        },
	        end: {
	          line: 516,
	          column: 44
	        }
	      },
	      "129": {
	        start: {
	          line: 525,
	          column: 2
	        },
	        end: {
	          line: 541,
	          column: 4
	        }
	      },
	      "130": {
	        start: {
	          line: 526,
	          column: 20
	        },
	        end: {
	          line: 526,
	          column: 42
	        }
	      },
	      "131": {
	        start: {
	          line: 528,
	          column: 4
	        },
	        end: {
	          line: 530,
	          column: 5
	        }
	      },
	      "132": {
	        start: {
	          line: 529,
	          column: 6
	        },
	        end: {
	          line: 529,
	          column: 16
	        }
	      },
	      "133": {
	        start: {
	          line: 532,
	          column: 22
	        },
	        end: {
	          line: 532,
	          column: 42
	        }
	      },
	      "134": {
	        start: {
	          line: 535,
	          column: 4
	        },
	        end: {
	          line: 537,
	          column: 5
	        }
	      },
	      "135": {
	        start: {
	          line: 536,
	          column: 6
	        },
	        end: {
	          line: 536,
	          column: 15
	        }
	      },
	      "136": {
	        start: {
	          line: 540,
	          column: 4
	        },
	        end: {
	          line: 540,
	          column: 44
	        }
	      },
	      "137": {
	        start: {
	          line: 550,
	          column: 2
	        },
	        end: {
	          line: 564,
	          column: 4
	        }
	      },
	      "138": {
	        start: {
	          line: 551,
	          column: 20
	        },
	        end: {
	          line: 551,
	          column: 42
	        }
	      },
	      "139": {
	        start: {
	          line: 553,
	          column: 4
	        },
	        end: {
	          line: 555,
	          column: 5
	        }
	      },
	      "140": {
	        start: {
	          line: 554,
	          column: 6
	        },
	        end: {
	          line: 554,
	          column: 16
	        }
	      },
	      "141": {
	        start: {
	          line: 558,
	          column: 4
	        },
	        end: {
	          line: 560,
	          column: 5
	        }
	      },
	      "142": {
	        start: {
	          line: 559,
	          column: 6
	        },
	        end: {
	          line: 559,
	          column: 34
	        }
	      },
	      "143": {
	        start: {
	          line: 563,
	          column: 4
	        },
	        end: {
	          line: 563,
	          column: 36
	        }
	      },
	      "144": {
	        start: {
	          line: 572,
	          column: 2
	        },
	        end: {
	          line: 583,
	          column: 4
	        }
	      },
	      "145": {
	        start: {
	          line: 573,
	          column: 4
	        },
	        end: {
	          line: 575,
	          column: 5
	        }
	      },
	      "146": {
	        start: {
	          line: 574,
	          column: 6
	        },
	        end: {
	          line: 574,
	          column: 13
	        }
	      },
	      "147": {
	        start: {
	          line: 576,
	          column: 20
	        },
	        end: {
	          line: 576,
	          column: 43
	        }
	      },
	      "148": {
	        start: {
	          line: 578,
	          column: 4
	        },
	        end: {
	          line: 580,
	          column: 5
	        }
	      },
	      "149": {
	        start: {
	          line: 579,
	          column: 6
	        },
	        end: {
	          line: 579,
	          column: 58
	        }
	      },
	      "150": {
	        start: {
	          line: 582,
	          column: 4
	        },
	        end: {
	          line: 582,
	          column: 32
	        }
	      },
	      "151": {
	        start: {
	          line: 591,
	          column: 2
	        },
	        end: {
	          line: 602,
	          column: 4
	        }
	      },
	      "152": {
	        start: {
	          line: 592,
	          column: 4
	        },
	        end: {
	          line: 594,
	          column: 5
	        }
	      },
	      "153": {
	        start: {
	          line: 593,
	          column: 6
	        },
	        end: {
	          line: 593,
	          column: 13
	        }
	      },
	      "154": {
	        start: {
	          line: 595,
	          column: 20
	        },
	        end: {
	          line: 595,
	          column: 62
	        }
	      },
	      "155": {
	        start: {
	          line: 597,
	          column: 4
	        },
	        end: {
	          line: 599,
	          column: 5
	        }
	      },
	      "156": {
	        start: {
	          line: 598,
	          column: 6
	        },
	        end: {
	          line: 598,
	          column: 58
	        }
	      },
	      "157": {
	        start: {
	          line: 601,
	          column: 4
	        },
	        end: {
	          line: 601,
	          column: 32
	        }
	      },
	      "158": {
	        start: {
	          line: 610,
	          column: 2
	        },
	        end: {
	          line: 622,
	          column: 4
	        }
	      },
	      "159": {
	        start: {
	          line: 611,
	          column: 4
	        },
	        end: {
	          line: 613,
	          column: 5
	        }
	      },
	      "160": {
	        start: {
	          line: 612,
	          column: 6
	        },
	        end: {
	          line: 612,
	          column: 13
	        }
	      },
	      "161": {
	        start: {
	          line: 615,
	          column: 18
	        },
	        end: {
	          line: 615,
	          column: 38
	        }
	      },
	      "162": {
	        start: {
	          line: 617,
	          column: 4
	        },
	        end: {
	          line: 621,
	          column: 5
	        }
	      },
	      "163": {
	        start: {
	          line: 618,
	          column: 22
	        },
	        end: {
	          line: 618,
	          column: 49
	        }
	      },
	      "164": {
	        start: {
	          line: 620,
	          column: 6
	        },
	        end: {
	          line: 620,
	          column: 58
	        }
	      },
	      "165": {
	        start: {
	          line: 630,
	          column: 2
	        },
	        end: {
	          line: 642,
	          column: 4
	        }
	      },
	      "166": {
	        start: {
	          line: 631,
	          column: 4
	        },
	        end: {
	          line: 633,
	          column: 5
	        }
	      },
	      "167": {
	        start: {
	          line: 632,
	          column: 6
	        },
	        end: {
	          line: 632,
	          column: 13
	        }
	      },
	      "168": {
	        start: {
	          line: 635,
	          column: 18
	        },
	        end: {
	          line: 635,
	          column: 42
	        }
	      },
	      "169": {
	        start: {
	          line: 637,
	          column: 4
	        },
	        end: {
	          line: 641,
	          column: 5
	        }
	      },
	      "170": {
	        start: {
	          line: 638,
	          column: 22
	        },
	        end: {
	          line: 638,
	          column: 49
	        }
	      },
	      "171": {
	        start: {
	          line: 640,
	          column: 6
	        },
	        end: {
	          line: 640,
	          column: 58
	        }
	      },
	      "172": {
	        start: {
	          line: 650,
	          column: 2
	        },
	        end: {
	          line: 652,
	          column: 4
	        }
	      },
	      "173": {
	        start: {
	          line: 651,
	          column: 4
	        },
	        end: {
	          line: 651,
	          column: 47
	        }
	      },
	      "174": {
	        start: {
	          line: 664,
	          column: 2
	        },
	        end: {
	          line: 676,
	          column: 4
	        }
	      },
	      "175": {
	        start: {
	          line: 665,
	          column: 4
	        },
	        end: {
	          line: 667,
	          column: 5
	        }
	      },
	      "176": {
	        start: {
	          line: 666,
	          column: 6
	        },
	        end: {
	          line: 666,
	          column: 30
	        }
	      },
	      "177": {
	        start: {
	          line: 669,
	          column: 4
	        },
	        end: {
	          line: 672,
	          column: 5
	        }
	      },
	      "178": {
	        start: {
	          line: 670,
	          column: 6
	        },
	        end: {
	          line: 670,
	          column: 75
	        }
	      },
	      "179": {
	        start: {
	          line: 671,
	          column: 6
	        },
	        end: {
	          line: 671,
	          column: 13
	        }
	      },
	      "180": {
	        start: {
	          line: 674,
	          column: 4
	        },
	        end: {
	          line: 674,
	          column: 29
	        }
	      },
	      "181": {
	        start: {
	          line: 675,
	          column: 4
	        },
	        end: {
	          line: 675,
	          column: 28
	        }
	      },
	      "182": {
	        start: {
	          line: 687,
	          column: 2
	        },
	        end: {
	          line: 708,
	          column: 4
	        }
	      },
	      "183": {
	        start: {
	          line: 690,
	          column: 4
	        },
	        end: {
	          line: 692,
	          column: 5
	        }
	      },
	      "184": {
	        start: {
	          line: 691,
	          column: 6
	        },
	        end: {
	          line: 691,
	          column: 13
	        }
	      },
	      "185": {
	        start: {
	          line: 694,
	          column: 4
	        },
	        end: {
	          line: 694,
	          column: 23
	        }
	      },
	      "186": {
	        start: {
	          line: 697,
	          column: 4
	        },
	        end: {
	          line: 699,
	          column: 5
	        }
	      },
	      "187": {
	        start: {
	          line: 698,
	          column: 6
	        },
	        end: {
	          line: 698,
	          column: 13
	        }
	      },
	      "188": {
	        start: {
	          line: 707,
	          column: 4
	        },
	        end: {
	          line: 707,
	          column: 37
	        }
	      },
	      "189": {
	        start: {
	          line: 716,
	          column: 2
	        },
	        end: {
	          line: 737,
	          column: 4
	        }
	      },
	      "190": {
	        start: {
	          line: 719,
	          column: 4
	        },
	        end: {
	          line: 721,
	          column: 5
	        }
	      },
	      "191": {
	        start: {
	          line: 720,
	          column: 6
	        },
	        end: {
	          line: 720,
	          column: 13
	        }
	      },
	      "192": {
	        start: {
	          line: 723,
	          column: 4
	        },
	        end: {
	          line: 723,
	          column: 19
	        }
	      },
	      "193": {
	        start: {
	          line: 726,
	          column: 4
	        },
	        end: {
	          line: 728,
	          column: 5
	        }
	      },
	      "194": {
	        start: {
	          line: 727,
	          column: 6
	        },
	        end: {
	          line: 727,
	          column: 13
	        }
	      },
	      "195": {
	        start: {
	          line: 736,
	          column: 4
	        },
	        end: {
	          line: 736,
	          column: 37
	        }
	      },
	      "196": {
	        start: {
	          line: 757,
	          column: 2
	        },
	        end: {
	          line: 793,
	          column: 4
	        }
	      },
	      "197": {
	        start: {
	          line: 758,
	          column: 16
	        },
	        end: {
	          line: 758,
	          column: 17
	        }
	      },
	      "198": {
	        start: {
	          line: 759,
	          column: 14
	        },
	        end: {
	          line: 759,
	          column: 18
	        }
	      },
	      "199": {
	        start: {
	          line: 763,
	          column: 4
	        },
	        end: {
	          line: 766,
	          column: 5
	        }
	      },
	      "200": {
	        start: {
	          line: 764,
	          column: 6
	        },
	        end: {
	          line: 764,
	          column: 41
	        }
	      },
	      "201": {
	        start: {
	          line: 765,
	          column: 6
	        },
	        end: {
	          line: 765,
	          column: 30
	        }
	      },
	      "202": {
	        start: {
	          line: 769,
	          column: 4
	        },
	        end: {
	          line: 771,
	          column: 5
	        }
	      },
	      "203": {
	        start: {
	          line: 770,
	          column: 6
	        },
	        end: {
	          line: 770,
	          column: 13
	        }
	      },
	      "204": {
	        start: {
	          line: 773,
	          column: 4
	        },
	        end: {
	          line: 773,
	          column: 19
	        }
	      },
	      "205": {
	        start: {
	          line: 777,
	          column: 4
	        },
	        end: {
	          line: 779,
	          column: 5
	        }
	      },
	      "206": {
	        start: {
	          line: 778,
	          column: 6
	        },
	        end: {
	          line: 778,
	          column: 54
	        }
	      },
	      "207": {
	        start: {
	          line: 782,
	          column: 4
	        },
	        end: {
	          line: 784,
	          column: 5
	        }
	      },
	      "208": {
	        start: {
	          line: 783,
	          column: 6
	        },
	        end: {
	          line: 783,
	          column: 13
	        }
	      },
	      "209": {
	        start: {
	          line: 792,
	          column: 4
	        },
	        end: {
	          line: 792,
	          column: 37
	        }
	      },
	      "210": {
	        start: {
	          line: 796,
	          column: 2
	        },
	        end: {
	          line: 802,
	          column: 3
	        }
	      },
	      "211": {
	        start: {
	          line: 797,
	          column: 4
	        },
	        end: {
	          line: 797,
	          column: 40
	        }
	      },
	      "212": {
	        start: {
	          line: 801,
	          column: 4
	        },
	        end: {
	          line: 801,
	          column: 14
	        }
	      },
	      "213": {
	        start: {
	          line: 804,
	          column: 2
	        },
	        end: {
	          line: 804,
	          column: 18
	        }
	      }
	    },
	    fnMap: {
	      "0": {
	        name: "(anonymous_0)",
	        decl: {
	          start: {
	            line: 23,
	            column: 28
	          },
	          end: {
	            line: 23,
	            column: 29
	          }
	        },
	        loc: {
	          start: {
	            line: 23,
	            column: 41
	          },
	          end: {
	            line: 37,
	            column: 1
	          }
	        },
	        line: 23
	      },
	      "1": {
	        name: "(anonymous_1)",
	        decl: {
	          start: {
	            line: 51,
	            column: 29
	          },
	          end: {
	            line: 51,
	            column: 30
	          }
	        },
	        loc: {
	          start: {
	            line: 51,
	            column: 38
	          },
	          end: {
	            line: 51,
	            column: 66
	          }
	        },
	        line: 51
	      },
	      "2": {
	        name: "(anonymous_2)",
	        decl: {
	          start: {
	            line: 66,
	            column: 31
	          },
	          end: {
	            line: 66,
	            column: 32
	          }
	        },
	        loc: {
	          start: {
	            line: 66,
	            column: 56
	          },
	          end: {
	            line: 74,
	            column: 1
	          }
	        },
	        line: 66
	      },
	      "3": {
	        name: "(anonymous_3)",
	        decl: {
	          start: {
	            line: 91,
	            column: 21
	          },
	          end: {
	            line: 91,
	            column: 22
	          }
	        },
	        loc: {
	          start: {
	            line: 91,
	            column: 43
	          },
	          end: {
	            line: 110,
	            column: 1
	          }
	        },
	        line: 91
	      },
	      "4": {
	        name: "(anonymous_4)",
	        decl: {
	          start: {
	            line: 127,
	            column: 23
	          },
	          end: {
	            line: 127,
	            column: 24
	          }
	        },
	        loc: {
	          start: {
	            line: 127,
	            column: 37
	          },
	          end: {
	            line: 143,
	            column: 1
	          }
	        },
	        line: 127
	      },
	      "5": {
	        name: "(anonymous_5)",
	        decl: {
	          start: {
	            line: 155,
	            column: 18
	          },
	          end: {
	            line: 155,
	            column: 19
	          }
	        },
	        loc: {
	          start: {
	            line: 155,
	            column: 27
	          },
	          end: {
	            line: 168,
	            column: 1
	          }
	        },
	        line: 155
	      },
	      "6": {
	        name: "factory",
	        decl: {
	          start: {
	            line: 201,
	            column: 24
	          },
	          end: {
	            line: 201,
	            column: 31
	          }
	        },
	        loc: {
	          start: {
	            line: 201,
	            column: 71
	          },
	          end: {
	            line: 805,
	            column: 1
	          }
	        },
	        line: 201
	      },
	      "7": {
	        name: "(anonymous_7)",
	        decl: {
	          start: {
	            line: 223,
	            column: 37
	          },
	          end: {
	            line: 223,
	            column: 38
	          }
	        },
	        loc: {
	          start: {
	            line: 223,
	            column: 69
	          },
	          end: {
	            line: 271,
	            column: 3
	          }
	        },
	        line: 223
	      },
	      "8": {
	        name: "(anonymous_8)",
	        decl: {
	          start: {
	            line: 262,
	            column: 26
	          },
	          end: {
	            line: 262,
	            column: 27
	          }
	        },
	        loc: {
	          start: {
	            line: 262,
	            column: 32
	          },
	          end: {
	            line: 264,
	            column: 9
	          }
	        },
	        line: 262
	      },
	      "9": {
	        name: "(anonymous_9)",
	        decl: {
	          start: {
	            line: 270,
	            column: 20
	          },
	          end: {
	            line: 270,
	            column: 21
	          }
	        },
	        loc: {
	          start: {
	            line: 270,
	            column: 30
	          },
	          end: {
	            line: 270,
	            column: 56
	          }
	        },
	        line: 270
	      },
	      "10": {
	        name: "(anonymous_10)",
	        decl: {
	          start: {
	            line: 274,
	            column: 25
	          },
	          end: {
	            line: 274,
	            column: 26
	          }
	        },
	        loc: {
	          start: {
	            line: 274,
	            column: 31
	          },
	          end: {
	            line: 278,
	            column: 3
	          }
	        },
	        line: 274
	      },
	      "11": {
	        name: "(anonymous_11)",
	        decl: {
	          start: {
	            line: 297,
	            column: 25
	          },
	          end: {
	            line: 297,
	            column: 26
	          }
	        },
	        loc: {
	          start: {
	            line: 297,
	            column: 36
	          },
	          end: {
	            line: 356,
	            column: 3
	          }
	        },
	        line: 297
	      },
	      "12": {
	        name: "(anonymous_12)",
	        decl: {
	          start: {
	            line: 409,
	            column: 17
	          },
	          end: {
	            line: 409,
	            column: 18
	          }
	        },
	        loc: {
	          start: {
	            line: 409,
	            column: 35
	          },
	          end: {
	            line: 425,
	            column: 3
	          }
	        },
	        line: 409
	      },
	      "13": {
	        name: "(anonymous_13)",
	        decl: {
	          start: {
	            line: 443,
	            column: 20
	          },
	          end: {
	            line: 443,
	            column: 21
	          }
	        },
	        loc: {
	          start: {
	            line: 443,
	            column: 42
	          },
	          end: {
	            line: 456,
	            column: 3
	          }
	        },
	        line: 443
	      },
	      "14": {
	        name: "(anonymous_14)",
	        decl: {
	          start: {
	            line: 467,
	            column: 22
	          },
	          end: {
	            line: 467,
	            column: 23
	          }
	        },
	        loc: {
	          start: {
	            line: 467,
	            column: 33
	          },
	          end: {
	            line: 469,
	            column: 3
	          }
	        },
	        line: 467
	      },
	      "15": {
	        name: "(anonymous_15)",
	        decl: {
	          start: {
	            line: 480,
	            column: 21
	          },
	          end: {
	            line: 480,
	            column: 22
	          }
	        },
	        loc: {
	          start: {
	            line: 480,
	            column: 32
	          },
	          end: {
	            line: 498,
	            column: 3
	          }
	        },
	        line: 480
	      },
	      "16": {
	        name: "(anonymous_16)",
	        decl: {
	          start: {
	            line: 507,
	            column: 26
	          },
	          end: {
	            line: 507,
	            column: 27
	          }
	        },
	        loc: {
	          start: {
	            line: 507,
	            column: 32
	          },
	          end: {
	            line: 507,
	            column: 54
	          }
	        },
	        line: 507
	      },
	      "17": {
	        name: "(anonymous_17)",
	        decl: {
	          start: {
	            line: 516,
	            column: 23
	          },
	          end: {
	            line: 516,
	            column: 24
	          }
	        },
	        loc: {
	          start: {
	            line: 516,
	            column: 29
	          },
	          end: {
	            line: 516,
	            column: 44
	          }
	        },
	        line: 516
	      },
	      "18": {
	        name: "(anonymous_18)",
	        decl: {
	          start: {
	            line: 525,
	            column: 23
	          },
	          end: {
	            line: 525,
	            column: 24
	          }
	        },
	        loc: {
	          start: {
	            line: 525,
	            column: 29
	          },
	          end: {
	            line: 541,
	            column: 3
	          }
	        },
	        line: 525
	      },
	      "19": {
	        name: "(anonymous_19)",
	        decl: {
	          start: {
	            line: 550,
	            column: 27
	          },
	          end: {
	            line: 550,
	            column: 28
	          }
	        },
	        loc: {
	          start: {
	            line: 550,
	            column: 33
	          },
	          end: {
	            line: 564,
	            column: 3
	          }
	        },
	        line: 550
	      },
	      "20": {
	        name: "(anonymous_20)",
	        decl: {
	          start: {
	            line: 572,
	            column: 19
	          },
	          end: {
	            line: 572,
	            column: 20
	          }
	        },
	        loc: {
	          start: {
	            line: 572,
	            column: 25
	          },
	          end: {
	            line: 583,
	            column: 3
	          }
	        },
	        line: 572
	      },
	      "21": {
	        name: "(anonymous_21)",
	        decl: {
	          start: {
	            line: 591,
	            column: 18
	          },
	          end: {
	            line: 591,
	            column: 19
	          }
	        },
	        loc: {
	          start: {
	            line: 591,
	            column: 24
	          },
	          end: {
	            line: 602,
	            column: 3
	          }
	        },
	        line: 591
	      },
	      "22": {
	        name: "(anonymous_22)",
	        decl: {
	          start: {
	            line: 610,
	            column: 18
	          },
	          end: {
	            line: 610,
	            column: 19
	          }
	        },
	        loc: {
	          start: {
	            line: 610,
	            column: 24
	          },
	          end: {
	            line: 622,
	            column: 3
	          }
	        },
	        line: 610
	      },
	      "23": {
	        name: "(anonymous_23)",
	        decl: {
	          start: {
	            line: 630,
	            column: 22
	          },
	          end: {
	            line: 630,
	            column: 23
	          }
	        },
	        loc: {
	          start: {
	            line: 630,
	            column: 28
	          },
	          end: {
	            line: 642,
	            column: 3
	          }
	        },
	        line: 630
	      },
	      "24": {
	        name: "(anonymous_24)",
	        decl: {
	          start: {
	            line: 650,
	            column: 25
	          },
	          end: {
	            line: 650,
	            column: 26
	          }
	        },
	        loc: {
	          start: {
	            line: 650,
	            column: 36
	          },
	          end: {
	            line: 652,
	            column: 3
	          }
	        },
	        line: 650
	      },
	      "25": {
	        name: "(anonymous_25)",
	        decl: {
	          start: {
	            line: 664,
	            column: 20
	          },
	          end: {
	            line: 664,
	            column: 21
	          }
	        },
	        loc: {
	          start: {
	            line: 664,
	            column: 29
	          },
	          end: {
	            line: 676,
	            column: 3
	          }
	        },
	        line: 664
	      },
	      "26": {
	        name: "(anonymous_26)",
	        decl: {
	          start: {
	            line: 687,
	            column: 18
	          },
	          end: {
	            line: 687,
	            column: 19
	          }
	        },
	        loc: {
	          start: {
	            line: 687,
	            column: 31
	          },
	          end: {
	            line: 708,
	            column: 3
	          }
	        },
	        line: 687
	      },
	      "27": {
	        name: "(anonymous_27)",
	        decl: {
	          start: {
	            line: 716,
	            column: 21
	          },
	          end: {
	            line: 716,
	            column: 22
	          }
	        },
	        loc: {
	          start: {
	            line: 716,
	            column: 27
	          },
	          end: {
	            line: 737,
	            column: 3
	          }
	        },
	        line: 716
	      },
	      "28": {
	        name: "(anonymous_28)",
	        decl: {
	          start: {
	            line: 757,
	            column: 21
	          },
	          end: {
	            line: 757,
	            column: 22
	          }
	        },
	        loc: {
	          start: {
	            line: 757,
	            column: 38
	          },
	          end: {
	            line: 793,
	            column: 3
	          }
	        },
	        line: 757
	      }
	    },
	    branchMap: {
	      "0": {
	        loc: {
	          start: {
	            line: 26,
	            column: 2
	          },
	          end: {
	            line: 32,
	            column: 3
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 26,
	            column: 2
	          },
	          end: {
	            line: 32,
	            column: 3
	          }
	        }, {
	          start: {
	            line: 26,
	            column: 2
	          },
	          end: {
	            line: 32,
	            column: 3
	          }
	        }],
	        line: 26
	      },
	      "1": {
	        loc: {
	          start: {
	            line: 26,
	            column: 6
	          },
	          end: {
	            line: 26,
	            column: 45
	          }
	        },
	        type: "binary-expr",
	        locations: [{
	          start: {
	            line: 26,
	            column: 6
	          },
	          end: {
	            line: 26,
	            column: 14
	          }
	        }, {
	          start: {
	            line: 26,
	            column: 18
	          },
	          end: {
	            line: 26,
	            column: 45
	          }
	        }],
	        line: 26
	      },
	      "2": {
	        loc: {
	          start: {
	            line: 68,
	            column: 4
	          },
	          end: {
	            line: 70,
	            column: 5
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 68,
	            column: 4
	          },
	          end: {
	            line: 70,
	            column: 5
	          }
	        }, {
	          start: {
	            line: 68,
	            column: 4
	          },
	          end: {
	            line: 70,
	            column: 5
	          }
	        }],
	        line: 68
	      },
	      "3": {
	        loc: {
	          start: {
	            line: 95,
	            column: 2
	          },
	          end: {
	            line: 97,
	            column: 3
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 95,
	            column: 2
	          },
	          end: {
	            line: 97,
	            column: 3
	          }
	        }, {
	          start: {
	            line: 95,
	            column: 2
	          },
	          end: {
	            line: 97,
	            column: 3
	          }
	        }],
	        line: 95
	      },
	      "4": {
	        loc: {
	          start: {
	            line: 98,
	            column: 2
	          },
	          end: {
	            line: 100,
	            column: 3
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 98,
	            column: 2
	          },
	          end: {
	            line: 100,
	            column: 3
	          }
	        }, {
	          start: {
	            line: 98,
	            column: 2
	          },
	          end: {
	            line: 100,
	            column: 3
	          }
	        }],
	        line: 98
	      },
	      "5": {
	        loc: {
	          start: {
	            line: 102,
	            column: 2
	          },
	          end: {
	            line: 104,
	            column: 3
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 102,
	            column: 2
	          },
	          end: {
	            line: 104,
	            column: 3
	          }
	        }, {
	          start: {
	            line: 102,
	            column: 2
	          },
	          end: {
	            line: 104,
	            column: 3
	          }
	        }],
	        line: 102
	      },
	      "6": {
	        loc: {
	          start: {
	            line: 105,
	            column: 2
	          },
	          end: {
	            line: 107,
	            column: 3
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 105,
	            column: 2
	          },
	          end: {
	            line: 107,
	            column: 3
	          }
	        }, {
	          start: {
	            line: 105,
	            column: 2
	          },
	          end: {
	            line: 107,
	            column: 3
	          }
	        }],
	        line: 105
	      },
	      "7": {
	        loc: {
	          start: {
	            line: 131,
	            column: 4
	          },
	          end: {
	            line: 139,
	            column: 5
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 131,
	            column: 4
	          },
	          end: {
	            line: 139,
	            column: 5
	          }
	        }, {
	          start: {
	            line: 131,
	            column: 4
	          },
	          end: {
	            line: 139,
	            column: 5
	          }
	        }],
	        line: 131
	      },
	      "8": {
	        loc: {
	          start: {
	            line: 135,
	            column: 8
	          },
	          end: {
	            line: 137,
	            column: 9
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 135,
	            column: 8
	          },
	          end: {
	            line: 137,
	            column: 9
	          }
	        }, {
	          start: {
	            line: 135,
	            column: 8
	          },
	          end: {
	            line: 137,
	            column: 9
	          }
	        }],
	        line: 135
	      },
	      "9": {
	        loc: {
	          start: {
	            line: 135,
	            column: 12
	          },
	          end: {
	            line: 135,
	            column: 47
	          }
	        },
	        type: "binary-expr",
	        locations: [{
	          start: {
	            line: 135,
	            column: 12
	          },
	          end: {
	            line: 135,
	            column: 18
	          }
	        }, {
	          start: {
	            line: 135,
	            column: 22
	          },
	          end: {
	            line: 135,
	            column: 47
	          }
	        }],
	        line: 135
	      },
	      "10": {
	        loc: {
	          start: {
	            line: 201,
	            column: 53
	          },
	          end: {
	            line: 201,
	            column: 69
	          }
	        },
	        type: "default-arg",
	        locations: [{
	          start: {
	            line: 201,
	            column: 68
	          },
	          end: {
	            line: 201,
	            column: 69
	          }
	        }],
	        line: 201
	      },
	      "11": {
	        loc: {
	          start: {
	            line: 223,
	            column: 52
	          },
	          end: {
	            line: 223,
	            column: 64
	          }
	        },
	        type: "default-arg",
	        locations: [{
	          start: {
	            line: 223,
	            column: 63
	          },
	          end: {
	            line: 223,
	            column: 64
	          }
	        }],
	        line: 223
	      },
	      "12": {
	        loc: {
	          start: {
	            line: 224,
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
	            line: 224,
	            column: 4
	          },
	          end: {
	            line: 226,
	            column: 5
	          }
	        }, {
	          start: {
	            line: 224,
	            column: 4
	          },
	          end: {
	            line: 226,
	            column: 5
	          }
	        }],
	        line: 224
	      },
	      "13": {
	        loc: {
	          start: {
	            line: 228,
	            column: 4
	          },
	          end: {
	            line: 266,
	            column: 5
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 228,
	            column: 4
	          },
	          end: {
	            line: 266,
	            column: 5
	          }
	        }, {
	          start: {
	            line: 228,
	            column: 4
	          },
	          end: {
	            line: 266,
	            column: 5
	          }
	        }],
	        line: 228
	      },
	      "14": {
	        loc: {
	          start: {
	            line: 231,
	            column: 31
	          },
	          end: {
	            line: 231,
	            column: 72
	          }
	        },
	        type: "cond-expr",
	        locations: [{
	          start: {
	            line: 231,
	            column: 53
	          },
	          end: {
	            line: 231,
	            column: 65
	          }
	        }, {
	          start: {
	            line: 231,
	            column: 68
	          },
	          end: {
	            line: 231,
	            column: 72
	          }
	        }],
	        line: 231
	      },
	      "15": {
	        loc: {
	          start: {
	            line: 245,
	            column: 26
	          },
	          end: {
	            line: 245,
	            column: 48
	          }
	        },
	        type: "binary-expr",
	        locations: [{
	          start: {
	            line: 245,
	            column: 26
	          },
	          end: {
	            line: 245,
	            column: 42
	          }
	        }, {
	          start: {
	            line: 245,
	            column: 46
	          },
	          end: {
	            line: 245,
	            column: 48
	          }
	        }],
	        line: 245
	      },
	      "16": {
	        loc: {
	          start: {
	            line: 250,
	            column: 6
	          },
	          end: {
	            line: 252,
	            column: 7
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 250,
	            column: 6
	          },
	          end: {
	            line: 252,
	            column: 7
	          }
	        }, {
	          start: {
	            line: 250,
	            column: 6
	          },
	          end: {
	            line: 252,
	            column: 7
	          }
	        }],
	        line: 250
	      },
	      "17": {
	        loc: {
	          start: {
	            line: 261,
	            column: 6
	          },
	          end: {
	            line: 265,
	            column: 7
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 261,
	            column: 6
	          },
	          end: {
	            line: 265,
	            column: 7
	          }
	        }, {
	          start: {
	            line: 261,
	            column: 6
	          },
	          end: {
	            line: 265,
	            column: 7
	          }
	        }],
	        line: 261
	      },
	      "18": {
	        loc: {
	          start: {
	            line: 270,
	            column: 30
	          },
	          end: {
	            line: 270,
	            column: 56
	          }
	        },
	        type: "binary-expr",
	        locations: [{
	          start: {
	            line: 270,
	            column: 30
	          },
	          end: {
	            line: 270,
	            column: 48
	          }
	        }, {
	          start: {
	            line: 270,
	            column: 52
	          },
	          end: {
	            line: 270,
	            column: 56
	          }
	        }],
	        line: 270
	      },
	      "19": {
	        loc: {
	          start: {
	            line: 275,
	            column: 4
	          },
	          end: {
	            line: 277,
	            column: 5
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 275,
	            column: 4
	          },
	          end: {
	            line: 277,
	            column: 5
	          }
	        }, {
	          start: {
	            line: 275,
	            column: 4
	          },
	          end: {
	            line: 277,
	            column: 5
	          }
	        }],
	        line: 275
	      },
	      "20": {
	        loc: {
	          start: {
	            line: 299,
	            column: 4
	          },
	          end: {
	            line: 301,
	            column: 5
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 299,
	            column: 4
	          },
	          end: {
	            line: 301,
	            column: 5
	          }
	        }, {
	          start: {
	            line: 299,
	            column: 4
	          },
	          end: {
	            line: 301,
	            column: 5
	          }
	        }],
	        line: 299
	      },
	      "21": {
	        loc: {
	          start: {
	            line: 304,
	            column: 4
	          },
	          end: {
	            line: 327,
	            column: 5
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 304,
	            column: 4
	          },
	          end: {
	            line: 327,
	            column: 5
	          }
	        }, {
	          start: {
	            line: 304,
	            column: 4
	          },
	          end: {
	            line: 327,
	            column: 5
	          }
	        }],
	        line: 304
	      },
	      "22": {
	        loc: {
	          start: {
	            line: 305,
	            column: 6
	          },
	          end: {
	            line: 308,
	            column: 25
	          }
	        },
	        type: "binary-expr",
	        locations: [{
	          start: {
	            line: 305,
	            column: 6
	          },
	          end: {
	            line: 305,
	            column: 31
	          }
	        }, {
	          start: {
	            line: 306,
	            column: 6
	          },
	          end: {
	            line: 306,
	            column: 38
	          }
	        }, {
	          start: {
	            line: 307,
	            column: 6
	          },
	          end: {
	            line: 307,
	            column: 16
	          }
	        }, {
	          start: {
	            line: 308,
	            column: 6
	          },
	          end: {
	            line: 308,
	            column: 25
	          }
	        }],
	        line: 305
	      },
	      "23": {
	        loc: {
	          start: {
	            line: 322,
	            column: 6
	          },
	          end: {
	            line: 324,
	            column: 7
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 322,
	            column: 6
	          },
	          end: {
	            line: 324,
	            column: 7
	          }
	        }, {
	          start: {
	            line: 322,
	            column: 6
	          },
	          end: {
	            line: 324,
	            column: 7
	          }
	        }],
	        line: 322
	      },
	      "24": {
	        loc: {
	          start: {
	            line: 329,
	            column: 16
	          },
	          end: {
	            line: 329,
	            column: 51
	          }
	        },
	        type: "binary-expr",
	        locations: [{
	          start: {
	            line: 329,
	            column: 16
	          },
	          end: {
	            line: 329,
	            column: 45
	          }
	        }, {
	          start: {
	            line: 329,
	            column: 49
	          },
	          end: {
	            line: 329,
	            column: 51
	          }
	        }],
	        line: 329
	      },
	      "25": {
	        loc: {
	          start: {
	            line: 336,
	            column: 4
	          },
	          end: {
	            line: 349,
	            column: 5
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 336,
	            column: 4
	          },
	          end: {
	            line: 349,
	            column: 5
	          }
	        }, {
	          start: {
	            line: 336,
	            column: 4
	          },
	          end: {
	            line: 349,
	            column: 5
	          }
	        }],
	        line: 336
	      },
	      "26": {
	        loc: {
	          start: {
	            line: 341,
	            column: 6
	          },
	          end: {
	            line: 344,
	            column: 7
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 341,
	            column: 6
	          },
	          end: {
	            line: 344,
	            column: 7
	          }
	        }, {
	          start: {
	            line: 341,
	            column: 6
	          },
	          end: {
	            line: 344,
	            column: 7
	          }
	        }],
	        line: 341
	      },
	      "27": {
	        loc: {
	          start: {
	            line: 341,
	            column: 10
	          },
	          end: {
	            line: 341,
	            column: 81
	          }
	        },
	        type: "binary-expr",
	        locations: [{
	          start: {
	            line: 341,
	            column: 10
	          },
	          end: {
	            line: 341,
	            column: 14
	          }
	        }, {
	          start: {
	            line: 341,
	            column: 18
	          },
	          end: {
	            line: 341,
	            column: 45
	          }
	        }, {
	          start: {
	            line: 341,
	            column: 49
	          },
	          end: {
	            line: 341,
	            column: 81
	          }
	        }],
	        line: 341
	      },
	      "28": {
	        loc: {
	          start: {
	            line: 410,
	            column: 4
	          },
	          end: {
	            line: 412,
	            column: 5
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 410,
	            column: 4
	          },
	          end: {
	            line: 412,
	            column: 5
	          }
	        }, {
	          start: {
	            line: 410,
	            column: 4
	          },
	          end: {
	            line: 412,
	            column: 5
	          }
	        }],
	        line: 410
	      },
	      "29": {
	        loc: {
	          start: {
	            line: 413,
	            column: 4
	          },
	          end: {
	            line: 415,
	            column: 5
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 413,
	            column: 4
	          },
	          end: {
	            line: 415,
	            column: 5
	          }
	        }, {
	          start: {
	            line: 413,
	            column: 4
	          },
	          end: {
	            line: 415,
	            column: 5
	          }
	        }],
	        line: 413
	      },
	      "30": {
	        loc: {
	          start: {
	            line: 413,
	            column: 8
	          },
	          end: {
	            line: 413,
	            column: 69
	          }
	        },
	        type: "binary-expr",
	        locations: [{
	          start: {
	            line: 413,
	            column: 8
	          },
	          end: {
	            line: 413,
	            column: 33
	          }
	        }, {
	          start: {
	            line: 413,
	            column: 37
	          },
	          end: {
	            line: 413,
	            column: 46
	          }
	        }, {
	          start: {
	            line: 413,
	            column: 50
	          },
	          end: {
	            line: 413,
	            column: 69
	          }
	        }],
	        line: 413
	      },
	      "31": {
	        loc: {
	          start: {
	            line: 416,
	            column: 4
	          },
	          end: {
	            line: 418,
	            column: 5
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 416,
	            column: 4
	          },
	          end: {
	            line: 418,
	            column: 5
	          }
	        }, {
	          start: {
	            line: 416,
	            column: 4
	          },
	          end: {
	            line: 418,
	            column: 5
	          }
	        }],
	        line: 416
	      },
	      "32": {
	        loc: {
	          start: {
	            line: 443,
	            column: 28
	          },
	          end: {
	            line: 443,
	            column: 37
	          }
	        },
	        type: "default-arg",
	        locations: [{
	          start: {
	            line: 443,
	            column: 36
	          },
	          end: {
	            line: 443,
	            column: 37
	          }
	        }],
	        line: 443
	      },
	      "33": {
	        loc: {
	          start: {
	            line: 444,
	            column: 4
	          },
	          end: {
	            line: 446,
	            column: 5
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 444,
	            column: 4
	          },
	          end: {
	            line: 446,
	            column: 5
	          }
	        }, {
	          start: {
	            line: 444,
	            column: 4
	          },
	          end: {
	            line: 446,
	            column: 5
	          }
	        }],
	        line: 444
	      },
	      "34": {
	        loc: {
	          start: {
	            line: 447,
	            column: 4
	          },
	          end: {
	            line: 449,
	            column: 5
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 447,
	            column: 4
	          },
	          end: {
	            line: 449,
	            column: 5
	          }
	        }, {
	          start: {
	            line: 447,
	            column: 4
	          },
	          end: {
	            line: 449,
	            column: 5
	          }
	        }],
	        line: 447
	      },
	      "35": {
	        loc: {
	          start: {
	            line: 447,
	            column: 8
	          },
	          end: {
	            line: 447,
	            column: 69
	          }
	        },
	        type: "binary-expr",
	        locations: [{
	          start: {
	            line: 447,
	            column: 8
	          },
	          end: {
	            line: 447,
	            column: 33
	          }
	        }, {
	          start: {
	            line: 447,
	            column: 37
	          },
	          end: {
	            line: 447,
	            column: 46
	          }
	        }, {
	          start: {
	            line: 447,
	            column: 50
	          },
	          end: {
	            line: 447,
	            column: 69
	          }
	        }],
	        line: 447
	      },
	      "36": {
	        loc: {
	          start: {
	            line: 481,
	            column: 4
	          },
	          end: {
	            line: 483,
	            column: 5
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 481,
	            column: 4
	          },
	          end: {
	            line: 483,
	            column: 5
	          }
	        }, {
	          start: {
	            line: 481,
	            column: 4
	          },
	          end: {
	            line: 483,
	            column: 5
	          }
	        }],
	        line: 481
	      },
	      "37": {
	        loc: {
	          start: {
	            line: 485,
	            column: 20
	          },
	          end: {
	            line: 485,
	            column: 64
	          }
	        },
	        type: "cond-expr",
	        locations: [{
	          start: {
	            line: 485,
	            column: 43
	          },
	          end: {
	            line: 485,
	            column: 48
	          }
	        }, {
	          start: {
	            line: 485,
	            column: 51
	          },
	          end: {
	            line: 485,
	            column: 64
	          }
	        }],
	        line: 485
	      },
	      "38": {
	        loc: {
	          start: {
	            line: 490,
	            column: 6
	          },
	          end: {
	            line: 494,
	            column: 7
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 490,
	            column: 6
	          },
	          end: {
	            line: 494,
	            column: 7
	          }
	        }, {
	          start: {
	            line: 490,
	            column: 6
	          },
	          end: {
	            line: 494,
	            column: 7
	          }
	        }],
	        line: 490
	      },
	      "39": {
	        loc: {
	          start: {
	            line: 492,
	            column: 13
	          },
	          end: {
	            line: 494,
	            column: 7
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 492,
	            column: 13
	          },
	          end: {
	            line: 494,
	            column: 7
	          }
	        }, {
	          start: {
	            line: 492,
	            column: 13
	          },
	          end: {
	            line: 494,
	            column: 7
	          }
	        }],
	        line: 492
	      },
	      "40": {
	        loc: {
	          start: {
	            line: 528,
	            column: 4
	          },
	          end: {
	            line: 530,
	            column: 5
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 528,
	            column: 4
	          },
	          end: {
	            line: 530,
	            column: 5
	          }
	        }, {
	          start: {
	            line: 528,
	            column: 4
	          },
	          end: {
	            line: 530,
	            column: 5
	          }
	        }],
	        line: 528
	      },
	      "41": {
	        loc: {
	          start: {
	            line: 535,
	            column: 4
	          },
	          end: {
	            line: 537,
	            column: 5
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 535,
	            column: 4
	          },
	          end: {
	            line: 537,
	            column: 5
	          }
	        }, {
	          start: {
	            line: 535,
	            column: 4
	          },
	          end: {
	            line: 537,
	            column: 5
	          }
	        }],
	        line: 535
	      },
	      "42": {
	        loc: {
	          start: {
	            line: 535,
	            column: 8
	          },
	          end: {
	            line: 535,
	            column: 49
	          }
	        },
	        type: "binary-expr",
	        locations: [{
	          start: {
	            line: 535,
	            column: 8
	          },
	          end: {
	            line: 535,
	            column: 24
	          }
	        }, {
	          start: {
	            line: 535,
	            column: 28
	          },
	          end: {
	            line: 535,
	            column: 49
	          }
	        }],
	        line: 535
	      },
	      "43": {
	        loc: {
	          start: {
	            line: 553,
	            column: 4
	          },
	          end: {
	            line: 555,
	            column: 5
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 553,
	            column: 4
	          },
	          end: {
	            line: 555,
	            column: 5
	          }
	        }, {
	          start: {
	            line: 553,
	            column: 4
	          },
	          end: {
	            line: 555,
	            column: 5
	          }
	        }],
	        line: 553
	      },
	      "44": {
	        loc: {
	          start: {
	            line: 558,
	            column: 4
	          },
	          end: {
	            line: 560,
	            column: 5
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 558,
	            column: 4
	          },
	          end: {
	            line: 560,
	            column: 5
	          }
	        }, {
	          start: {
	            line: 558,
	            column: 4
	          },
	          end: {
	            line: 560,
	            column: 5
	          }
	        }],
	        line: 558
	      },
	      "45": {
	        loc: {
	          start: {
	            line: 558,
	            column: 8
	          },
	          end: {
	            line: 558,
	            column: 41
	          }
	        },
	        type: "binary-expr",
	        locations: [{
	          start: {
	            line: 558,
	            column: 8
	          },
	          end: {
	            line: 558,
	            column: 24
	          }
	        }, {
	          start: {
	            line: 558,
	            column: 28
	          },
	          end: {
	            line: 558,
	            column: 41
	          }
	        }],
	        line: 558
	      },
	      "46": {
	        loc: {
	          start: {
	            line: 573,
	            column: 4
	          },
	          end: {
	            line: 575,
	            column: 5
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 573,
	            column: 4
	          },
	          end: {
	            line: 575,
	            column: 5
	          }
	        }, {
	          start: {
	            line: 573,
	            column: 4
	          },
	          end: {
	            line: 575,
	            column: 5
	          }
	        }],
	        line: 573
	      },
	      "47": {
	        loc: {
	          start: {
	            line: 578,
	            column: 4
	          },
	          end: {
	            line: 580,
	            column: 5
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 578,
	            column: 4
	          },
	          end: {
	            line: 580,
	            column: 5
	          }
	        }, {
	          start: {
	            line: 578,
	            column: 4
	          },
	          end: {
	            line: 580,
	            column: 5
	          }
	        }],
	        line: 578
	      },
	      "48": {
	        loc: {
	          start: {
	            line: 579,
	            column: 13
	          },
	          end: {
	            line: 579,
	            column: 57
	          }
	        },
	        type: "binary-expr",
	        locations: [{
	          start: {
	            line: 579,
	            column: 13
	          },
	          end: {
	            line: 579,
	            column: 40
	          }
	        }, {
	          start: {
	            line: 579,
	            column: 44
	          },
	          end: {
	            line: 579,
	            column: 57
	          }
	        }],
	        line: 579
	      },
	      "49": {
	        loc: {
	          start: {
	            line: 592,
	            column: 4
	          },
	          end: {
	            line: 594,
	            column: 5
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 592,
	            column: 4
	          },
	          end: {
	            line: 594,
	            column: 5
	          }
	        }, {
	          start: {
	            line: 592,
	            column: 4
	          },
	          end: {
	            line: 594,
	            column: 5
	          }
	        }],
	        line: 592
	      },
	      "50": {
	        loc: {
	          start: {
	            line: 597,
	            column: 4
	          },
	          end: {
	            line: 599,
	            column: 5
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 597,
	            column: 4
	          },
	          end: {
	            line: 599,
	            column: 5
	          }
	        }, {
	          start: {
	            line: 597,
	            column: 4
	          },
	          end: {
	            line: 599,
	            column: 5
	          }
	        }],
	        line: 597
	      },
	      "51": {
	        loc: {
	          start: {
	            line: 598,
	            column: 13
	          },
	          end: {
	            line: 598,
	            column: 57
	          }
	        },
	        type: "binary-expr",
	        locations: [{
	          start: {
	            line: 598,
	            column: 13
	          },
	          end: {
	            line: 598,
	            column: 40
	          }
	        }, {
	          start: {
	            line: 598,
	            column: 44
	          },
	          end: {
	            line: 598,
	            column: 57
	          }
	        }],
	        line: 598
	      },
	      "52": {
	        loc: {
	          start: {
	            line: 611,
	            column: 4
	          },
	          end: {
	            line: 613,
	            column: 5
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 611,
	            column: 4
	          },
	          end: {
	            line: 613,
	            column: 5
	          }
	        }, {
	          start: {
	            line: 611,
	            column: 4
	          },
	          end: {
	            line: 613,
	            column: 5
	          }
	        }],
	        line: 611
	      },
	      "53": {
	        loc: {
	          start: {
	            line: 617,
	            column: 4
	          },
	          end: {
	            line: 621,
	            column: 5
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 617,
	            column: 4
	          },
	          end: {
	            line: 621,
	            column: 5
	          }
	        }, {
	          start: {
	            line: 617,
	            column: 4
	          },
	          end: {
	            line: 621,
	            column: 5
	          }
	        }],
	        line: 617
	      },
	      "54": {
	        loc: {
	          start: {
	            line: 620,
	            column: 13
	          },
	          end: {
	            line: 620,
	            column: 57
	          }
	        },
	        type: "binary-expr",
	        locations: [{
	          start: {
	            line: 620,
	            column: 13
	          },
	          end: {
	            line: 620,
	            column: 40
	          }
	        }, {
	          start: {
	            line: 620,
	            column: 44
	          },
	          end: {
	            line: 620,
	            column: 57
	          }
	        }],
	        line: 620
	      },
	      "55": {
	        loc: {
	          start: {
	            line: 631,
	            column: 4
	          },
	          end: {
	            line: 633,
	            column: 5
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 631,
	            column: 4
	          },
	          end: {
	            line: 633,
	            column: 5
	          }
	        }, {
	          start: {
	            line: 631,
	            column: 4
	          },
	          end: {
	            line: 633,
	            column: 5
	          }
	        }],
	        line: 631
	      },
	      "56": {
	        loc: {
	          start: {
	            line: 637,
	            column: 4
	          },
	          end: {
	            line: 641,
	            column: 5
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 637,
	            column: 4
	          },
	          end: {
	            line: 641,
	            column: 5
	          }
	        }, {
	          start: {
	            line: 637,
	            column: 4
	          },
	          end: {
	            line: 641,
	            column: 5
	          }
	        }],
	        line: 637
	      },
	      "57": {
	        loc: {
	          start: {
	            line: 640,
	            column: 13
	          },
	          end: {
	            line: 640,
	            column: 57
	          }
	        },
	        type: "binary-expr",
	        locations: [{
	          start: {
	            line: 640,
	            column: 13
	          },
	          end: {
	            line: 640,
	            column: 40
	          }
	        }, {
	          start: {
	            line: 640,
	            column: 44
	          },
	          end: {
	            line: 640,
	            column: 57
	          }
	        }],
	        line: 640
	      },
	      "58": {
	        loc: {
	          start: {
	            line: 665,
	            column: 4
	          },
	          end: {
	            line: 667,
	            column: 5
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 665,
	            column: 4
	          },
	          end: {
	            line: 667,
	            column: 5
	          }
	        }, {
	          start: {
	            line: 665,
	            column: 4
	          },
	          end: {
	            line: 667,
	            column: 5
	          }
	        }],
	        line: 665
	      },
	      "59": {
	        loc: {
	          start: {
	            line: 669,
	            column: 4
	          },
	          end: {
	            line: 672,
	            column: 5
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 669,
	            column: 4
	          },
	          end: {
	            line: 672,
	            column: 5
	          }
	        }, {
	          start: {
	            line: 669,
	            column: 4
	          },
	          end: {
	            line: 672,
	            column: 5
	          }
	        }],
	        line: 669
	      },
	      "60": {
	        loc: {
	          start: {
	            line: 690,
	            column: 4
	          },
	          end: {
	            line: 692,
	            column: 5
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 690,
	            column: 4
	          },
	          end: {
	            line: 692,
	            column: 5
	          }
	        }, {
	          start: {
	            line: 690,
	            column: 4
	          },
	          end: {
	            line: 692,
	            column: 5
	          }
	        }],
	        line: 690
	      },
	      "61": {
	        loc: {
	          start: {
	            line: 697,
	            column: 4
	          },
	          end: {
	            line: 699,
	            column: 5
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 697,
	            column: 4
	          },
	          end: {
	            line: 699,
	            column: 5
	          }
	        }, {
	          start: {
	            line: 697,
	            column: 4
	          },
	          end: {
	            line: 699,
	            column: 5
	          }
	        }],
	        line: 697
	      },
	      "62": {
	        loc: {
	          start: {
	            line: 719,
	            column: 4
	          },
	          end: {
	            line: 721,
	            column: 5
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 719,
	            column: 4
	          },
	          end: {
	            line: 721,
	            column: 5
	          }
	        }, {
	          start: {
	            line: 719,
	            column: 4
	          },
	          end: {
	            line: 721,
	            column: 5
	          }
	        }],
	        line: 719
	      },
	      "63": {
	        loc: {
	          start: {
	            line: 726,
	            column: 4
	          },
	          end: {
	            line: 728,
	            column: 5
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 726,
	            column: 4
	          },
	          end: {
	            line: 728,
	            column: 5
	          }
	        }, {
	          start: {
	            line: 726,
	            column: 4
	          },
	          end: {
	            line: 728,
	            column: 5
	          }
	        }],
	        line: 726
	      },
	      "64": {
	        loc: {
	          start: {
	            line: 757,
	            column: 22
	          },
	          end: {
	            line: 757,
	            column: 33
	          }
	        },
	        type: "default-arg",
	        locations: [{
	          start: {
	            line: 757,
	            column: 31
	          },
	          end: {
	            line: 757,
	            column: 33
	          }
	        }],
	        line: 757
	      },
	      "65": {
	        loc: {
	          start: {
	            line: 763,
	            column: 4
	          },
	          end: {
	            line: 766,
	            column: 5
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 763,
	            column: 4
	          },
	          end: {
	            line: 766,
	            column: 5
	          }
	        }, {
	          start: {
	            line: 763,
	            column: 4
	          },
	          end: {
	            line: 766,
	            column: 5
	          }
	        }],
	        line: 763
	      },
	      "66": {
	        loc: {
	          start: {
	            line: 769,
	            column: 4
	          },
	          end: {
	            line: 771,
	            column: 5
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 769,
	            column: 4
	          },
	          end: {
	            line: 771,
	            column: 5
	          }
	        }, {
	          start: {
	            line: 769,
	            column: 4
	          },
	          end: {
	            line: 771,
	            column: 5
	          }
	        }],
	        line: 769
	      },
	      "67": {
	        loc: {
	          start: {
	            line: 777,
	            column: 4
	          },
	          end: {
	            line: 779,
	            column: 5
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 777,
	            column: 4
	          },
	          end: {
	            line: 779,
	            column: 5
	          }
	        }, {
	          start: {
	            line: 777,
	            column: 4
	          },
	          end: {
	            line: 779,
	            column: 5
	          }
	        }],
	        line: 777
	      },
	      "68": {
	        loc: {
	          start: {
	            line: 782,
	            column: 4
	          },
	          end: {
	            line: 784,
	            column: 5
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 782,
	            column: 4
	          },
	          end: {
	            line: 784,
	            column: 5
	          }
	        }, {
	          start: {
	            line: 782,
	            column: 4
	          },
	          end: {
	            line: 784,
	            column: 5
	          }
	        }],
	        line: 782
	      },
	      "69": {
	        loc: {
	          start: {
	            line: 796,
	            column: 2
	          },
	          end: {
	            line: 802,
	            column: 3
	          }
	        },
	        type: "if",
	        locations: [{
	          start: {
	            line: 796,
	            column: 2
	          },
	          end: {
	            line: 802,
	            column: 3
	          }
	        }, {
	          start: {
	            line: 796,
	            column: 2
	          },
	          end: {
	            line: 802,
	            column: 3
	          }
	        }],
	        line: 796
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
	      "133": 0,
	      "134": 0,
	      "135": 0,
	      "136": 0,
	      "137": 0,
	      "138": 0,
	      "139": 0,
	      "140": 0,
	      "141": 0,
	      "142": 0,
	      "143": 0,
	      "144": 0,
	      "145": 0,
	      "146": 0,
	      "147": 0,
	      "148": 0,
	      "149": 0,
	      "150": 0,
	      "151": 0,
	      "152": 0,
	      "153": 0,
	      "154": 0,
	      "155": 0,
	      "156": 0,
	      "157": 0,
	      "158": 0,
	      "159": 0,
	      "160": 0,
	      "161": 0,
	      "162": 0,
	      "163": 0,
	      "164": 0,
	      "165": 0,
	      "166": 0,
	      "167": 0,
	      "168": 0,
	      "169": 0,
	      "170": 0,
	      "171": 0,
	      "172": 0,
	      "173": 0,
	      "174": 0,
	      "175": 0,
	      "176": 0,
	      "177": 0,
	      "178": 0,
	      "179": 0,
	      "180": 0,
	      "181": 0,
	      "182": 0,
	      "183": 0,
	      "184": 0,
	      "185": 0,
	      "186": 0,
	      "187": 0,
	      "188": 0,
	      "189": 0,
	      "190": 0,
	      "191": 0,
	      "192": 0,
	      "193": 0,
	      "194": 0,
	      "195": 0,
	      "196": 0,
	      "197": 0,
	      "198": 0,
	      "199": 0,
	      "200": 0,
	      "201": 0,
	      "202": 0,
	      "203": 0,
	      "204": 0,
	      "205": 0,
	      "206": 0,
	      "207": 0,
	      "208": 0,
	      "209": 0,
	      "210": 0,
	      "211": 0,
	      "212": 0,
	      "213": 0
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
	      "21": 0,
	      "22": 0,
	      "23": 0,
	      "24": 0,
	      "25": 0,
	      "26": 0,
	      "27": 0,
	      "28": 0
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
	      "10": [0],
	      "11": [0],
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
	      "22": [0, 0, 0, 0],
	      "23": [0, 0],
	      "24": [0, 0],
	      "25": [0, 0],
	      "26": [0, 0],
	      "27": [0, 0, 0],
	      "28": [0, 0],
	      "29": [0, 0],
	      "30": [0, 0, 0],
	      "31": [0, 0],
	      "32": [0],
	      "33": [0, 0],
	      "34": [0, 0],
	      "35": [0, 0, 0],
	      "36": [0, 0],
	      "37": [0, 0],
	      "38": [0, 0],
	      "39": [0, 0],
	      "40": [0, 0],
	      "41": [0, 0],
	      "42": [0, 0],
	      "43": [0, 0],
	      "44": [0, 0],
	      "45": [0, 0],
	      "46": [0, 0],
	      "47": [0, 0],
	      "48": [0, 0],
	      "49": [0, 0],
	      "50": [0, 0],
	      "51": [0, 0],
	      "52": [0, 0],
	      "53": [0, 0],
	      "54": [0, 0],
	      "55": [0, 0],
	      "56": [0, 0],
	      "57": [0, 0],
	      "58": [0, 0],
	      "59": [0, 0],
	      "60": [0, 0],
	      "61": [0, 0],
	      "62": [0, 0],
	      "63": [0, 0],
	      "64": [0],
	      "65": [0, 0],
	      "66": [0, 0],
	      "67": [0, 0],
	      "68": [0, 0],
	      "69": [0, 0]
	    },
	    _coverageSchema: "1a1c01bbd47fc00a2c39e90264f33305004495a9",
	    hash: "c0f838095342ad20c96402f38b283f968f259b04"
	  };
	  var coverage = global[gcv] || (global[gcv] = {});

	  if (!coverage[path] || coverage[path].hash !== hash) {
	    coverage[path] = coverageData;
	  }

	  var actualCoverage = coverage[path];
	  {
	    // @ts-ignore
	    cov_spj91b6dj = function () {
	      return actualCoverage;
	    };
	  }
	  return actualCoverage;
	}

	cov_spj91b6dj();

	let guid = (cov_spj91b6dj().s[0]++, 1);
	/**
	* Transform any primitive playlist item value into an object.
	*
	* For non-object values, adds a property to the transformed item containing
	* original value passed.
	*
	* For all items, add a unique ID to each playlist item object. This id is
	* used to determine the index of an item in the playlist array in cases where
	* there are multiple otherwise identical items.
	*
	* @param  {Object} newItem
	*         An playlist item object, but accepts any value.
	*
	* @return {Object}
	*/

	cov_spj91b6dj().s[1]++;

	const preparePlaylistItem = newItem => {
	  cov_spj91b6dj().f[0]++;
	  let item = (cov_spj91b6dj().s[2]++, newItem);
	  cov_spj91b6dj().s[3]++;

	  if ((cov_spj91b6dj().b[1][0]++, !newItem) || (cov_spj91b6dj().b[1][1]++, typeof newItem !== 'object')) {
	    cov_spj91b6dj().b[0][0]++;
	    cov_spj91b6dj().s[4]++; // Casting to an Object in this way allows primitives to retain their
	    // primitiveness (i.e. they will be cast back to primitives as needed).

	    item = Object(newItem);
	    cov_spj91b6dj().s[5]++;
	    item.originalValue = newItem;
	  } else {
	    cov_spj91b6dj().b[0][1]++;
	  }

	  cov_spj91b6dj().s[6]++;
	  item.playlistItemId_ = guid++;
	  cov_spj91b6dj().s[7]++;
	  return item;
	};
	/**
	* Look through an array of playlist items and passes them to
	* preparePlaylistItem.
	*
	* @private
	*
	* @param  {Array} arr
	*         An array of playlist items
	*
	* @return {Array}
	*         A new array with transformed items
	*/


	cov_spj91b6dj().s[8]++;

	const preparePlaylistItems = arr => {
	  cov_spj91b6dj().f[1]++;
	  cov_spj91b6dj().s[9]++;
	  return arr.map(preparePlaylistItem);
	};
	/**
	* Look through an array of playlist items for a specific playlist item id.
	*
	* @private
	* @param   {Array} list
	*          An array of playlist items to look through
	*
	* @param   {number} currentItemId
	*          The current item ID.
	*
	* @return  {number}
	*          The index of the playlist item or -1 if not found
	*/


	cov_spj91b6dj().s[10]++;

	const indexInPlaylistItemIds = (list, currentItemId) => {
	  cov_spj91b6dj().f[2]++;
	  cov_spj91b6dj().s[11]++;

	  for (let i = (cov_spj91b6dj().s[12]++, 0); i < list.length; i++) {
	    cov_spj91b6dj().s[13]++;

	    if (list[i].playlistItemId_ === currentItemId) {
	      cov_spj91b6dj().b[2][0]++;
	      cov_spj91b6dj().s[14]++;
	      return i;
	    } else {
	      cov_spj91b6dj().b[2][1]++;
	    }
	  }

	  cov_spj91b6dj().s[15]++;
	  return -1;
	};
	/**
	* Given two sources, check to see whether the two sources are equal.
	* If both source urls have a protocol, the protocols must match, otherwise, protocols
	* are ignored.
	*
	* @private
	* @param {string|Object} source1
	*        The first source
	*
	* @param {string|Object} source2
	*        The second source
	*
	* @return {boolean}
	*         The result
	*/


	cov_spj91b6dj().s[16]++;

	const sourceEquals = (source1, source2) => {
	  cov_spj91b6dj().f[3]++;
	  let src1 = (cov_spj91b6dj().s[17]++, source1);
	  let src2 = (cov_spj91b6dj().s[18]++, source2);
	  cov_spj91b6dj().s[19]++;

	  if (typeof source1 === 'object') {
	    cov_spj91b6dj().b[3][0]++;
	    cov_spj91b6dj().s[20]++;
	    src1 = source1.src;
	  } else {
	    cov_spj91b6dj().b[3][1]++;
	  }

	  cov_spj91b6dj().s[21]++;

	  if (typeof source2 === 'object') {
	    cov_spj91b6dj().b[4][0]++;
	    cov_spj91b6dj().s[22]++;
	    src2 = source2.src;
	  } else {
	    cov_spj91b6dj().b[4][1]++;
	  }

	  cov_spj91b6dj().s[23]++;

	  if (/^\/\//.test(src1)) {
	    cov_spj91b6dj().b[5][0]++;
	    cov_spj91b6dj().s[24]++;
	    src2 = src2.slice(src2.indexOf('//'));
	  } else {
	    cov_spj91b6dj().b[5][1]++;
	  }

	  cov_spj91b6dj().s[25]++;

	  if (/^\/\//.test(src2)) {
	    cov_spj91b6dj().b[6][0]++;
	    cov_spj91b6dj().s[26]++;
	    src1 = src1.slice(src1.indexOf('//'));
	  } else {
	    cov_spj91b6dj().b[6][1]++;
	  }

	  cov_spj91b6dj().s[27]++;
	  return src1 === src2;
	};
	/**
	* Look through an array of playlist items for a specific `source`;
	* checking both the value of elements and the value of their `src`
	* property.
	*
	* @private
	* @param   {Array} arr
	*          An array of playlist items to look through
	*
	* @param   {string} src
	*          The source to look for
	*
	* @return  {number}
	*          The index of that source or -1
	*/


	cov_spj91b6dj().s[28]++;

	const indexInSources = (arr, src) => {
	  cov_spj91b6dj().f[4]++;
	  cov_spj91b6dj().s[29]++;

	  for (let i = (cov_spj91b6dj().s[30]++, 0); i < arr.length; i++) {
	    const sources = (cov_spj91b6dj().s[31]++, arr[i].sources);
	    cov_spj91b6dj().s[32]++;

	    if (Array.isArray(sources)) {
	      cov_spj91b6dj().b[7][0]++;
	      cov_spj91b6dj().s[33]++;

	      for (let j = (cov_spj91b6dj().s[34]++, 0); j < sources.length; j++) {
	        const source = (cov_spj91b6dj().s[35]++, sources[j]);
	        cov_spj91b6dj().s[36]++;

	        if ((cov_spj91b6dj().b[9][0]++, source) && (cov_spj91b6dj().b[9][1]++, sourceEquals(source, src))) {
	          cov_spj91b6dj().b[8][0]++;
	          cov_spj91b6dj().s[37]++;
	          return i;
	        } else {
	          cov_spj91b6dj().b[8][1]++;
	        }
	      }
	    } else {
	      cov_spj91b6dj().b[7][1]++;
	    }
	  }

	  cov_spj91b6dj().s[38]++;
	  return -1;
	};
	/**
	* Randomize the contents of an array.
	*
	* @private
	* @param  {Array} arr
	*         An array.
	*
	* @return {Array}
	*         The same array that was passed in.
	*/


	cov_spj91b6dj().s[39]++;

	const randomize = arr => {
	  cov_spj91b6dj().f[5]++;
	  let index = (cov_spj91b6dj().s[40]++, -1);
	  const lastIndex = (cov_spj91b6dj().s[41]++, arr.length - 1);
	  cov_spj91b6dj().s[42]++;

	  while (++index < arr.length) {
	    const rand = (cov_spj91b6dj().s[43]++, index + Math.floor(Math.random() * (lastIndex - index + 1)));
	    const value = (cov_spj91b6dj().s[44]++, arr[rand]);
	    cov_spj91b6dj().s[45]++;
	    arr[rand] = arr[index];
	    cov_spj91b6dj().s[46]++;
	    arr[index] = value;
	  }

	  cov_spj91b6dj().s[47]++;
	  return arr;
	};
	/**
	* Factory function for creating new playlist implementation on the given player.
	*
	* API summary:
	*
	* playlist(['a', 'b', 'c']) // setter
	* playlist() // getter
	* playlist.currentItem() // getter, 0
	* playlist.currentItem(1) // setter, 1
	* playlist.next() // 'c'
	* playlist.previous() // 'b'
	* playlist.first() // 'a'
	* playlist.last() // 'c'
	* playlist.autoadvance(5) // 5 second delay
	* playlist.autoadvance() // cancel autoadvance
	*
	* @param  {Player} player
	*         The current player
	*
	* @param  {Array=} initialList
	*         If given, an initial list of sources with which to populate
	*         the playlist.
	*
	* @param  {number=}  initialIndex
	*         If given, the index of the item in the list that should
	*         be loaded first. If -1, no video is loaded. If omitted, The
	*         the first video is loaded.
	*
	* @return {Function}
	*         Returns the playlist function specific to the given player.
	*/


	function factory(player, initialList, initialIndex = (cov_spj91b6dj().b[10][0]++, 0)) {
	  cov_spj91b6dj().f[6]++;
	  let list = (cov_spj91b6dj().s[48]++, null);
	  let changing = (cov_spj91b6dj().s[49]++, false);
	  /**
	  * Get/set the playlist for a player.
	  *
	  * This function is added as an own property of the player and has its
	  * own methods which can be called to manipulate the internal state.
	  *
	  * @param  {Array} [newList]
	  *         If given, a new list of sources with which to populate the
	  *         playlist. Without this, the function acts as a getter.
	  *
	  * @param  {number}  [newIndex]
	  *         If given, the index of the item in the list that should
	  *         be loaded first. If -1, no video is loaded. If omitted, The
	  *         the first video is loaded.
	  *
	  * @return {Array}
	  *         The playlist
	  */

	  const playlist = (cov_spj91b6dj().s[50]++, player.playlist = (nextPlaylist, newIndex = (cov_spj91b6dj().b[11][0]++, 0)) => {
	    cov_spj91b6dj().f[7]++;
	    cov_spj91b6dj().s[51]++;

	    if (changing) {
	      cov_spj91b6dj().b[12][0]++;
	      cov_spj91b6dj().s[52]++;
	      throw new Error('do not call playlist() during a playlist change');
	    } else {
	      cov_spj91b6dj().b[12][1]++;
	    }

	    cov_spj91b6dj().s[53]++;

	    if (Array.isArray(nextPlaylist)) {
	      cov_spj91b6dj().b[13][0]++; // @todo - Simplify this to `list.slice()` for v5.

	      const previousPlaylist = (cov_spj91b6dj().s[54]++, Array.isArray(list) ? (cov_spj91b6dj().b[14][0]++, list.slice()) : (cov_spj91b6dj().b[14][1]++, null));
	      cov_spj91b6dj().s[55]++;
	      list = preparePlaylistItems(nextPlaylist); // Mark the playlist as changing during the duringplaylistchange lifecycle.

	      cov_spj91b6dj().s[56]++;
	      changing = true;
	      cov_spj91b6dj().s[57]++;
	      player.trigger({
	        type: 'duringplaylistchange',
	        nextIndex: newIndex,
	        nextPlaylist,
	        previousIndex: playlist.currentIndex_,
	        // @todo - Simplify this to simply pass along `previousPlaylist` for v5.
	        previousPlaylist: (cov_spj91b6dj().b[15][0]++, previousPlaylist) || (cov_spj91b6dj().b[15][1]++, [])
	      });
	      cov_spj91b6dj().s[58]++;
	      changing = false;
	      cov_spj91b6dj().s[59]++;

	      if (newIndex !== -1) {
	        cov_spj91b6dj().b[16][0]++;
	        cov_spj91b6dj().s[60]++;
	        playlist.currentItem(newIndex);
	      } else {
	        cov_spj91b6dj().b[16][1]++;
	      } // The only time the previous playlist is null is the first call to this
	      // function. This allows us to fire the `duringplaylistchange` event
	      // every time the playlist is populated and to maintain backward
	      // compatibility by not firing the `playlistchange` event on the initial
	      // population of the list.
	      //
	      // @todo - Remove this condition in preparation for v5.


	      cov_spj91b6dj().s[61]++;

	      if (previousPlaylist) {
	        cov_spj91b6dj().b[17][0]++;
	        cov_spj91b6dj().s[62]++;
	        player.setTimeout(() => {
	          cov_spj91b6dj().f[8]++;
	          cov_spj91b6dj().s[63]++;
	          player.trigger({
	            type: 'playlistchange',
	            action: 'change'
	          });
	        }, 0);
	      } else {
	        cov_spj91b6dj().b[17][1]++;
	      }
	    } else {
	      cov_spj91b6dj().b[13][1]++;
	    } // Always return a shallow clone of the playlist list.
	    // We also want to return originalValue if any item in the list has it.


	    cov_spj91b6dj().s[64]++;
	    return list.map(item => {
	      cov_spj91b6dj().f[9]++;
	      cov_spj91b6dj().s[65]++;
	      return (cov_spj91b6dj().b[18][0]++, item.originalValue) || (cov_spj91b6dj().b[18][1]++, item);
	    });
	  }); // On a new source, if there is no current item, disable auto-advance.

	  cov_spj91b6dj().s[66]++;
	  player.on('loadstart', () => {
	    cov_spj91b6dj().f[10]++;
	    cov_spj91b6dj().s[67]++;

	    if (playlist.currentItem() === -1) {
	      cov_spj91b6dj().b[19][0]++;
	      cov_spj91b6dj().s[68]++;
	      reset(player);
	    } else {
	      cov_spj91b6dj().b[19][1]++;
	    }
	  });
	  cov_spj91b6dj().s[69]++;
	  playlist.currentIndex_ = -1;
	  cov_spj91b6dj().s[70]++;
	  playlist.player_ = player;
	  cov_spj91b6dj().s[71]++;
	  playlist.autoadvance_ = {};
	  cov_spj91b6dj().s[72]++;
	  playlist.repeat_ = false;
	  cov_spj91b6dj().s[73]++;
	  playlist.currentPlaylistItemId_ = null;
	  /**
	  * Get or set the current item in the playlist.
	  *
	  * During the duringplaylistchange event, acts only as a getter.
	  *
	  * @param  {number} [index]
	  *         If given as a valid value, plays the playlist item at that index.
	  *
	  * @return {number}
	  *         The current item index.
	  */

	  cov_spj91b6dj().s[74]++;

	  playlist.currentItem = index => {
	    cov_spj91b6dj().f[11]++;
	    cov_spj91b6dj().s[75]++; // If the playlist is changing, only act as a getter.

	    if (changing) {
	      cov_spj91b6dj().b[20][0]++;
	      cov_spj91b6dj().s[76]++;
	      return playlist.currentIndex_;
	    } else {
	      cov_spj91b6dj().b[20][1]++;
	    } // Act as a setter when the index is given and is a valid number.


	    cov_spj91b6dj().s[77]++;

	    if ((cov_spj91b6dj().b[22][0]++, typeof index === 'number') && (cov_spj91b6dj().b[22][1]++, playlist.currentIndex_ !== index) && (cov_spj91b6dj().b[22][2]++, index >= 0) && (cov_spj91b6dj().b[22][3]++, index < list.length)) {
	      cov_spj91b6dj().b[21][0]++;
	      cov_spj91b6dj().s[78]++;
	      playlist.currentIndex_ = index;
	      cov_spj91b6dj().s[79]++;
	      playItem(playlist.player_, list[playlist.currentIndex_]); // When playing multiple videos in a playlist the videojs PosterImage
	      // will be hidden using CSS. However, in some browsers the native poster
	      // attribute will briefly appear while the new source loads. Prevent
	      // this by hiding every poster after the first play list item. This
	      // doesn't cover every use case for showing/hiding the poster, but
	      // it will significantly improve the user experience.

	      cov_spj91b6dj().s[80]++;

	      if (index > 0) {
	        cov_spj91b6dj().b[23][0]++;
	        cov_spj91b6dj().s[81]++;
	        player.poster('');
	      } else {
	        cov_spj91b6dj().b[23][1]++;
	      }

	      cov_spj91b6dj().s[82]++;
	      return playlist.currentIndex_;
	    } else {
	      cov_spj91b6dj().b[21][1]++;
	    }

	    const src = (cov_spj91b6dj().s[83]++, (cov_spj91b6dj().b[24][0]++, playlist.player_.currentSrc()) || (cov_spj91b6dj().b[24][1]++, '')); // If there is a currentPlaylistItemId_, validate that it matches the
	    // current source URL returned by the player. This is sufficient evidence
	    // to suggest that the source was set by the playlist plugin. This code
	    // exists primarily to deal with playlists where multiple items have the
	    // same source.

	    cov_spj91b6dj().s[84]++;

	    if (playlist.currentPlaylistItemId_) {
	      cov_spj91b6dj().b[25][0]++;
	      const indexInItemIds = (cov_spj91b6dj().s[85]++, indexInPlaylistItemIds(list, playlist.currentPlaylistItemId_));
	      const item = (cov_spj91b6dj().s[86]++, list[indexInItemIds]); // Found a match, this is our current index!

	      cov_spj91b6dj().s[87]++;

	      if ((cov_spj91b6dj().b[27][0]++, item) && (cov_spj91b6dj().b[27][1]++, Array.isArray(item.sources)) && (cov_spj91b6dj().b[27][2]++, indexInSources([item], src) > -1)) {
	        cov_spj91b6dj().b[26][0]++;
	        cov_spj91b6dj().s[88]++;
	        playlist.currentIndex_ = indexInItemIds;
	        cov_spj91b6dj().s[89]++;
	        return playlist.currentIndex_;
	      } else {
	        cov_spj91b6dj().b[26][1]++;
	      } // If this does not match the current source, null it out so subsequent
	      // calls can skip this step.


	      cov_spj91b6dj().s[90]++;
	      playlist.currentPlaylistItemId_ = null;
	    } else {
	      cov_spj91b6dj().b[25][1]++;
	    } // Finally, if we don't have a valid, current playlist item ID, we can
	    // auto-detect it based on the player's current source URL.


	    cov_spj91b6dj().s[91]++;
	    playlist.currentIndex_ = playlist.indexOf(src);
	    cov_spj91b6dj().s[92]++;
	    return playlist.currentIndex_;
	  };
	  /**
	  * A custom DOM event that is fired when new item(s) are added to the current
	  * playlist (rather than replacing the entire playlist).
	  *
	  * Unlike playlistchange, this is fired synchronously as it does not
	  * affect playback.
	  *
	  * @typedef  {Object} PlaylistAddEvent
	  * @see      [CustomEvent Properties]{@link https://developer.mozilla.org/en-US/docs/Web/API/CustomEvent}
	  * @property {string} type
	  *           Always "playlistadd"
	  *
	  * @property {number} count
	  *           The number of items that were added.
	  *
	  * @property {number} index
	  *           The starting index where item(s) were added.
	  */

	  /**
	  * A custom DOM event that is fired when new item(s) are removed from the
	  * current playlist (rather than replacing the entire playlist).
	  *
	  * This is fired synchronously as it does not affect playback.
	  *
	  * @typedef  {Object} PlaylistRemoveEvent
	  * @see      [CustomEvent Properties]{@link https://developer.mozilla.org/en-US/docs/Web/API/CustomEvent}
	  * @property {string} type
	  *           Always "playlistremove"
	  *
	  * @property {number} count
	  *           The number of items that were removed.
	  *
	  * @property {number} index
	  *           The starting index where item(s) were removed.
	  */

	  /**
	  * Add one or more items to the playlist.
	  *
	  * @fires  {PlaylistAddEvent}
	  * @throws {Error}
	  *         If called during the duringplaylistchange event, throws an error.
	  *
	  * @param  {string|Object|Array}  item
	  *         An item - or array of items - to be added to the playlist.
	  *
	  * @param  {number} [index]
	  *         If given as a valid value, injects the new playlist item(s)
	  *         starting from that index. Otherwise, the item(s) are appended.
	  */


	  cov_spj91b6dj().s[93]++;

	  playlist.add = (items, index) => {
	    cov_spj91b6dj().f[12]++;
	    cov_spj91b6dj().s[94]++;

	    if (changing) {
	      cov_spj91b6dj().b[28][0]++;
	      cov_spj91b6dj().s[95]++;
	      throw new Error('cannot modify a playlist that is currently changing');
	    } else {
	      cov_spj91b6dj().b[28][1]++;
	    }

	    cov_spj91b6dj().s[96]++;

	    if ((cov_spj91b6dj().b[30][0]++, typeof index !== 'number') || (cov_spj91b6dj().b[30][1]++, index < 0) || (cov_spj91b6dj().b[30][2]++, index > list.length)) {
	      cov_spj91b6dj().b[29][0]++;
	      cov_spj91b6dj().s[97]++;
	      index = list.length;
	    } else {
	      cov_spj91b6dj().b[29][1]++;
	    }

	    cov_spj91b6dj().s[98]++;

	    if (!Array.isArray(items)) {
	      cov_spj91b6dj().b[31][0]++;
	      cov_spj91b6dj().s[99]++;
	      items = [items];
	    } else {
	      cov_spj91b6dj().b[31][1]++;
	    }

	    cov_spj91b6dj().s[100]++;
	    list.splice(index, 0, ...preparePlaylistItems(items)); // playlistchange is triggered synchronously in this case because it does
	    // not change the current media source

	    cov_spj91b6dj().s[101]++;
	    player.trigger({
	      type: 'playlistchange',
	      action: 'add'
	    });
	    cov_spj91b6dj().s[102]++;
	    player.trigger({
	      type: 'playlistadd',
	      count: items.length,
	      index
	    });
	  };
	  /**
	  * Remove one or more items from the playlist.
	  *
	  * @fires  {PlaylistRemoveEvent}
	  * @throws {Error}
	  *         If called during the duringplaylistchange event, throws an error.
	  *
	  * @param  {number} index
	  *         If a valid index in the current playlist, removes the item at that
	  *         index from the playlist.
	  *
	  *         If no valid index is given, nothing is removed from the playlist.
	  *
	  * @param  {number} [count=1]
	  *         The number of items to remove from the playlist.
	  */


	  cov_spj91b6dj().s[103]++;

	  playlist.remove = (index, count = (cov_spj91b6dj().b[32][0]++, 1)) => {
	    cov_spj91b6dj().f[13]++;
	    cov_spj91b6dj().s[104]++;

	    if (changing) {
	      cov_spj91b6dj().b[33][0]++;
	      cov_spj91b6dj().s[105]++;
	      throw new Error('cannot modify a playlist that is currently changing');
	    } else {
	      cov_spj91b6dj().b[33][1]++;
	    }

	    cov_spj91b6dj().s[106]++;

	    if ((cov_spj91b6dj().b[35][0]++, typeof index !== 'number') || (cov_spj91b6dj().b[35][1]++, index < 0) || (cov_spj91b6dj().b[35][2]++, index > list.length)) {
	      cov_spj91b6dj().b[34][0]++;
	      cov_spj91b6dj().s[107]++;
	      return;
	    } else {
	      cov_spj91b6dj().b[34][1]++;
	    }

	    cov_spj91b6dj().s[108]++;
	    list.splice(index, count); // playlistchange is triggered synchronously in this case because it does
	    // not change the current media source

	    cov_spj91b6dj().s[109]++;
	    player.trigger({
	      type: 'playlistchange',
	      action: 'remove'
	    });
	    cov_spj91b6dj().s[110]++;
	    player.trigger({
	      type: 'playlistremove',
	      count,
	      index
	    });
	  };
	  /**
	  * Checks if the playlist contains a value.
	  *
	  * @param  {string|Object|Array} value
	  *         The value to check
	  *
	  * @return {boolean}
	  *         The result
	  */


	  cov_spj91b6dj().s[111]++;

	  playlist.contains = value => {
	    cov_spj91b6dj().f[14]++;
	    cov_spj91b6dj().s[112]++;
	    return playlist.indexOf(value) !== -1;
	  };
	  /**
	  * Gets the index of a value in the playlist or -1 if not found.
	  *
	  * @param  {string|Object|Array} value
	  *         The value to find the index of
	  *
	  * @return {number}
	  *         The index or -1
	  */


	  cov_spj91b6dj().s[113]++;

	  playlist.indexOf = value => {
	    cov_spj91b6dj().f[15]++;
	    cov_spj91b6dj().s[114]++;

	    if (typeof value === 'string') {
	      cov_spj91b6dj().b[36][0]++;
	      cov_spj91b6dj().s[115]++;
	      return indexInSources(list, value);
	    } else {
	      cov_spj91b6dj().b[36][1]++;
	    }

	    const sources = (cov_spj91b6dj().s[116]++, Array.isArray(value) ? (cov_spj91b6dj().b[37][0]++, value) : (cov_spj91b6dj().b[37][1]++, value.sources));
	    cov_spj91b6dj().s[117]++;

	    for (let i = (cov_spj91b6dj().s[118]++, 0); i < sources.length; i++) {
	      const source = (cov_spj91b6dj().s[119]++, sources[i]);
	      cov_spj91b6dj().s[120]++;

	      if (typeof source === 'string') {
	        cov_spj91b6dj().b[38][0]++;
	        cov_spj91b6dj().s[121]++;
	        return indexInSources(list, source);
	      } else {
	        cov_spj91b6dj().b[38][1]++;
	        cov_spj91b6dj().s[122]++;

	        if (source.src) {
	          cov_spj91b6dj().b[39][0]++;
	          cov_spj91b6dj().s[123]++;
	          return indexInSources(list, source.src);
	        } else {
	          cov_spj91b6dj().b[39][1]++;
	        }
	      }
	    }

	    cov_spj91b6dj().s[124]++;
	    return -1;
	  };
	  /**
	  * Get the index of the current item in the playlist. This is identical to
	  * calling `currentItem()` with no arguments.
	  *
	  * @return {number}
	  *         The current item index.
	  */


	  cov_spj91b6dj().s[125]++;

	  playlist.currentIndex = () => {
	    cov_spj91b6dj().f[16]++;
	    cov_spj91b6dj().s[126]++;
	    return playlist.currentItem();
	  };
	  /**
	  * Get the index of the last item in the playlist.
	  *
	  * @return {number}
	  *         The index of the last item in the playlist or -1 if there are no
	  *         items.
	  */


	  cov_spj91b6dj().s[127]++;

	  playlist.lastIndex = () => {
	    cov_spj91b6dj().f[17]++;
	    cov_spj91b6dj().s[128]++;
	    return list.length - 1;
	  };
	  /**
	  * Get the index of the next item in the playlist.
	  *
	  * @return {number}
	  *         The index of the next item in the playlist or -1 if there is no
	  *         current item.
	  */


	  cov_spj91b6dj().s[129]++;

	  playlist.nextIndex = () => {
	    cov_spj91b6dj().f[18]++;
	    const current = (cov_spj91b6dj().s[130]++, playlist.currentItem());
	    cov_spj91b6dj().s[131]++;

	    if (current === -1) {
	      cov_spj91b6dj().b[40][0]++;
	      cov_spj91b6dj().s[132]++;
	      return -1;
	    } else {
	      cov_spj91b6dj().b[40][1]++;
	    }

	    const lastIndex = (cov_spj91b6dj().s[133]++, playlist.lastIndex()); // When repeating, loop back to the beginning on the last item.

	    cov_spj91b6dj().s[134]++;

	    if ((cov_spj91b6dj().b[42][0]++, playlist.repeat_) && (cov_spj91b6dj().b[42][1]++, current === lastIndex)) {
	      cov_spj91b6dj().b[41][0]++;
	      cov_spj91b6dj().s[135]++;
	      return 0;
	    } else {
	      cov_spj91b6dj().b[41][1]++;
	    } // Don't go past the end of the playlist.


	    cov_spj91b6dj().s[136]++;
	    return Math.min(current + 1, lastIndex);
	  };
	  /**
	  * Get the index of the previous item in the playlist.
	  *
	  * @return {number}
	  *         The index of the previous item in the playlist or -1 if there is
	  *         no current item.
	  */


	  cov_spj91b6dj().s[137]++;

	  playlist.previousIndex = () => {
	    cov_spj91b6dj().f[19]++;
	    const current = (cov_spj91b6dj().s[138]++, playlist.currentItem());
	    cov_spj91b6dj().s[139]++;

	    if (current === -1) {
	      cov_spj91b6dj().b[43][0]++;
	      cov_spj91b6dj().s[140]++;
	      return -1;
	    } else {
	      cov_spj91b6dj().b[43][1]++;
	    } // When repeating, loop back to the end of the playlist.


	    cov_spj91b6dj().s[141]++;

	    if ((cov_spj91b6dj().b[45][0]++, playlist.repeat_) && (cov_spj91b6dj().b[45][1]++, current === 0)) {
	      cov_spj91b6dj().b[44][0]++;
	      cov_spj91b6dj().s[142]++;
	      return playlist.lastIndex();
	    } else {
	      cov_spj91b6dj().b[44][1]++;
	    } // Don't go past the beginning of the playlist.


	    cov_spj91b6dj().s[143]++;
	    return Math.max(current - 1, 0);
	  };
	  /**
	  * Plays the first item in the playlist.
	  *
	  * @return {Object|undefined}
	  *         Returns undefined and has no side effects if the list is empty.
	  */


	  cov_spj91b6dj().s[144]++;

	  playlist.first = () => {
	    cov_spj91b6dj().f[20]++;
	    cov_spj91b6dj().s[145]++;

	    if (changing) {
	      cov_spj91b6dj().b[46][0]++;
	      cov_spj91b6dj().s[146]++;
	      return;
	    } else {
	      cov_spj91b6dj().b[46][1]++;
	    }

	    const newItem = (cov_spj91b6dj().s[147]++, playlist.currentItem(0));
	    cov_spj91b6dj().s[148]++;

	    if (list.length) {
	      cov_spj91b6dj().b[47][0]++;
	      cov_spj91b6dj().s[149]++;
	      return (cov_spj91b6dj().b[48][0]++, list[newItem].originalValue) || (cov_spj91b6dj().b[48][1]++, list[newItem]);
	    } else {
	      cov_spj91b6dj().b[47][1]++;
	    }

	    cov_spj91b6dj().s[150]++;
	    playlist.currentIndex_ = -1;
	  };
	  /**
	  * Plays the last item in the playlist.
	  *
	  * @return {Object|undefined}
	  *         Returns undefined and has no side effects if the list is empty.
	  */


	  cov_spj91b6dj().s[151]++;

	  playlist.last = () => {
	    cov_spj91b6dj().f[21]++;
	    cov_spj91b6dj().s[152]++;

	    if (changing) {
	      cov_spj91b6dj().b[49][0]++;
	      cov_spj91b6dj().s[153]++;
	      return;
	    } else {
	      cov_spj91b6dj().b[49][1]++;
	    }

	    const newItem = (cov_spj91b6dj().s[154]++, playlist.currentItem(playlist.lastIndex()));
	    cov_spj91b6dj().s[155]++;

	    if (list.length) {
	      cov_spj91b6dj().b[50][0]++;
	      cov_spj91b6dj().s[156]++;
	      return (cov_spj91b6dj().b[51][0]++, list[newItem].originalValue) || (cov_spj91b6dj().b[51][1]++, list[newItem]);
	    } else {
	      cov_spj91b6dj().b[50][1]++;
	    }

	    cov_spj91b6dj().s[157]++;
	    playlist.currentIndex_ = -1;
	  };
	  /**
	  * Plays the next item in the playlist.
	  *
	  * @return {Object|undefined}
	  *         Returns undefined and has no side effects if on last item.
	  */


	  cov_spj91b6dj().s[158]++;

	  playlist.next = () => {
	    cov_spj91b6dj().f[22]++;
	    cov_spj91b6dj().s[159]++;

	    if (changing) {
	      cov_spj91b6dj().b[52][0]++;
	      cov_spj91b6dj().s[160]++;
	      return;
	    } else {
	      cov_spj91b6dj().b[52][1]++;
	    }

	    const index = (cov_spj91b6dj().s[161]++, playlist.nextIndex());
	    cov_spj91b6dj().s[162]++;

	    if (index !== playlist.currentIndex_) {
	      cov_spj91b6dj().b[53][0]++;
	      const newItem = (cov_spj91b6dj().s[163]++, playlist.currentItem(index));
	      cov_spj91b6dj().s[164]++;
	      return (cov_spj91b6dj().b[54][0]++, list[newItem].originalValue) || (cov_spj91b6dj().b[54][1]++, list[newItem]);
	    } else {
	      cov_spj91b6dj().b[53][1]++;
	    }
	  };
	  /**
	  * Plays the previous item in the playlist.
	  *
	  * @return {Object|undefined}
	  *         Returns undefined and has no side effects if on first item.
	  */


	  cov_spj91b6dj().s[165]++;

	  playlist.previous = () => {
	    cov_spj91b6dj().f[23]++;
	    cov_spj91b6dj().s[166]++;

	    if (changing) {
	      cov_spj91b6dj().b[55][0]++;
	      cov_spj91b6dj().s[167]++;
	      return;
	    } else {
	      cov_spj91b6dj().b[55][1]++;
	    }

	    const index = (cov_spj91b6dj().s[168]++, playlist.previousIndex());
	    cov_spj91b6dj().s[169]++;

	    if (index !== playlist.currentIndex_) {
	      cov_spj91b6dj().b[56][0]++;
	      const newItem = (cov_spj91b6dj().s[170]++, playlist.currentItem(index));
	      cov_spj91b6dj().s[171]++;
	      return (cov_spj91b6dj().b[57][0]++, list[newItem].originalValue) || (cov_spj91b6dj().b[57][1]++, list[newItem]);
	    } else {
	      cov_spj91b6dj().b[56][1]++;
	    }
	  };
	  /**
	  * Set up auto-advance on the playlist.
	  *
	  * @param  {number} [delay]
	  *         The number of seconds to wait before each auto-advance.
	  */


	  cov_spj91b6dj().s[172]++;

	  playlist.autoadvance = delay => {
	    cov_spj91b6dj().f[24]++;
	    cov_spj91b6dj().s[173]++;
	    setup(playlist.player_, delay);
	  };
	  /**
	  * Sets `repeat` option, which makes the "next" video of the last video in
	  * the playlist be the first video in the playlist.
	  *
	  * @param  {boolean} [val]
	  *         The value to set repeat to
	  *
	  * @return {boolean}
	  *         The current value of repeat
	  */


	  cov_spj91b6dj().s[174]++;

	  playlist.repeat = val => {
	    cov_spj91b6dj().f[25]++;
	    cov_spj91b6dj().s[175]++;

	    if (val === undefined) {
	      cov_spj91b6dj().b[58][0]++;
	      cov_spj91b6dj().s[176]++;
	      return playlist.repeat_;
	    } else {
	      cov_spj91b6dj().b[58][1]++;
	    }

	    cov_spj91b6dj().s[177]++;

	    if (typeof val !== 'boolean') {
	      cov_spj91b6dj().b[59][0]++;
	      cov_spj91b6dj().s[178]++;
	      videojs__default["default"].log.error('videojs-playlist: Invalid value for repeat', val);
	      cov_spj91b6dj().s[179]++;
	      return;
	    } else {
	      cov_spj91b6dj().b[59][1]++;
	    }

	    cov_spj91b6dj().s[180]++;
	    playlist.repeat_ = !!val;
	    cov_spj91b6dj().s[181]++;
	    return playlist.repeat_;
	  };
	  /**
	  * Sorts the playlist array.
	  *
	  * @see {@link https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/sort}
	  * @fires playlistsorted
	  *
	  * @param {Function} compare
	  *        A comparator function as per the native Array method.
	  */


	  cov_spj91b6dj().s[182]++;

	  playlist.sort = compare => {
	    cov_spj91b6dj().f[26]++;
	    cov_spj91b6dj().s[183]++; // Bail if the array is empty.

	    if (!list.length) {
	      cov_spj91b6dj().b[60][0]++;
	      cov_spj91b6dj().s[184]++;
	      return;
	    } else {
	      cov_spj91b6dj().b[60][1]++;
	    }

	    cov_spj91b6dj().s[185]++;
	    list.sort(compare); // If the playlist is changing, don't trigger events.

	    cov_spj91b6dj().s[186]++;

	    if (changing) {
	      cov_spj91b6dj().b[61][0]++;
	      cov_spj91b6dj().s[187]++;
	      return;
	    } else {
	      cov_spj91b6dj().b[61][1]++;
	    }
	    /**
	    * Triggered after the playlist is sorted internally.
	    *
	    * @event playlistsorted
	    * @type {Object}
	    */


	    cov_spj91b6dj().s[188]++;
	    player.trigger('playlistsorted');
	  };
	  /**
	  * Reverses the playlist array.
	  *
	  * @see {@link https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/reverse}
	  * @fires playlistsorted
	  */


	  cov_spj91b6dj().s[189]++;

	  playlist.reverse = () => {
	    cov_spj91b6dj().f[27]++;
	    cov_spj91b6dj().s[190]++; // Bail if the array is empty.

	    if (!list.length) {
	      cov_spj91b6dj().b[62][0]++;
	      cov_spj91b6dj().s[191]++;
	      return;
	    } else {
	      cov_spj91b6dj().b[62][1]++;
	    }

	    cov_spj91b6dj().s[192]++;
	    list.reverse(); // If the playlist is changing, don't trigger events.

	    cov_spj91b6dj().s[193]++;

	    if (changing) {
	      cov_spj91b6dj().b[63][0]++;
	      cov_spj91b6dj().s[194]++;
	      return;
	    } else {
	      cov_spj91b6dj().b[63][1]++;
	    }
	    /**
	    * Triggered after the playlist is sorted internally.
	    *
	    * @event playlistsorted
	    * @type {Object}
	    */


	    cov_spj91b6dj().s[195]++;
	    player.trigger('playlistsorted');
	  };
	  /**
	  * Shuffle the contents of the list randomly.
	  *
	  * @see   {@link https://github.com/lodash/lodash/blob/40e096b6d5291a025e365a0f4c010d9a0efb9a69/shuffle.js}
	  * @fires playlistsorted
	  * @todo  Make the `rest` option default to `true` in v5.0.0.
	  * @param {Object} [options]
	  *        An object containing shuffle options.
	  *
	  * @param {boolean} [options.rest = false]
	  *        By default, the entire playlist is randomized. However, this may
	  *        not be desirable in all cases, such as when a user is already
	  *        watching a video.
	  *
	  *        When `true` is passed for this option, it will only shuffle
	  *        playlist items after the current item. For example, when on the
	  *        first item, will shuffle the second item and beyond.
	  */


	  cov_spj91b6dj().s[196]++;

	  playlist.shuffle = ({
	    rest
	  } = (cov_spj91b6dj().b[64][0]++, {})) => {
	    cov_spj91b6dj().f[28]++;
	    let index = (cov_spj91b6dj().s[197]++, 0);
	    let arr = (cov_spj91b6dj().s[198]++, list); // When options.rest is true, start randomization at the item after the
	    // current item.

	    cov_spj91b6dj().s[199]++;

	    if (rest) {
	      cov_spj91b6dj().b[65][0]++;
	      cov_spj91b6dj().s[200]++;
	      index = playlist.currentIndex_ + 1;
	      cov_spj91b6dj().s[201]++;
	      arr = list.slice(index);
	    } else {
	      cov_spj91b6dj().b[65][1]++;
	    } // Bail if the array is empty or too short to shuffle.


	    cov_spj91b6dj().s[202]++;

	    if (arr.length <= 1) {
	      cov_spj91b6dj().b[66][0]++;
	      cov_spj91b6dj().s[203]++;
	      return;
	    } else {
	      cov_spj91b6dj().b[66][1]++;
	    }

	    cov_spj91b6dj().s[204]++;
	    randomize(arr); // When options.rest is true, splice the randomized sub-array back into
	    // the original array.

	    cov_spj91b6dj().s[205]++;

	    if (rest) {
	      cov_spj91b6dj().b[67][0]++;
	      cov_spj91b6dj().s[206]++;
	      list.splice(...[index, arr.length].concat(arr));
	    } else {
	      cov_spj91b6dj().b[67][1]++;
	    } // If the playlist is changing, don't trigger events.


	    cov_spj91b6dj().s[207]++;

	    if (changing) {
	      cov_spj91b6dj().b[68][0]++;
	      cov_spj91b6dj().s[208]++;
	      return;
	    } else {
	      cov_spj91b6dj().b[68][1]++;
	    }
	    /**
	    * Triggered after the playlist is sorted internally.
	    *
	    * @event playlistsorted
	    * @type {Object}
	    */


	    cov_spj91b6dj().s[209]++;
	    player.trigger('playlistsorted');
	  }; // If an initial list was given, populate the playlist with it.


	  cov_spj91b6dj().s[210]++;

	  if (Array.isArray(initialList)) {
	    cov_spj91b6dj().b[69][0]++;
	    cov_spj91b6dj().s[211]++;
	    playlist(initialList, initialIndex); // If there is no initial list given, silently set an empty array.
	  } else {
	    cov_spj91b6dj().b[69][1]++;
	    cov_spj91b6dj().s[212]++;
	    list = [];
	  }

	  cov_spj91b6dj().s[213]++;
	  return playlist;
	}

	var version = "5.1.0";

	function cov_d7t40zke3() {
	  var path = "/Users/bclifford/Code/videojs-playlist/src/plugin.js";
	  var hash = "86f3dd7eb7d6aac3026b62f2267de1cdde2a9aca";
	  var global = new Function("return this")();
	  var gcv = "__coverage__";
	  var coverageData = {
	    path: "/Users/bclifford/Code/videojs-playlist/src/plugin.js",
	    statementMap: {
	      "0": {
	        start: {
	          line: 6,
	          column: 23
	        },
	        end: {
	          line: 6,
	          column: 63
	        }
	      },
	      "1": {
	        start: {
	          line: 18,
	          column: 15
	        },
	        end: {
	          line: 20,
	          column: 1
	        }
	      },
	      "2": {
	        start: {
	          line: 19,
	          column: 2
	        },
	        end: {
	          line: 19,
	          column: 34
	        }
	      },
	      "3": {
	        start: {
	          line: 22,
	          column: 0
	        },
	        end: {
	          line: 22,
	          column: 35
	        }
	      },
	      "4": {
	        start: {
	          line: 24,
	          column: 0
	        },
	        end: {
	          line: 24,
	          column: 25
	        }
	      }
	    },
	    fnMap: {
	      "0": {
	        name: "(anonymous_0)",
	        decl: {
	          start: {
	            line: 18,
	            column: 15
	          },
	          end: {
	            line: 18,
	            column: 16
	          }
	        },
	        loc: {
	          start: {
	            line: 18,
	            column: 36
	          },
	          end: {
	            line: 20,
	            column: 1
	          }
	        },
	        line: 18
	      }
	    },
	    branchMap: {
	      "0": {
	        loc: {
	          start: {
	            line: 6,
	            column: 23
	          },
	          end: {
	            line: 6,
	            column: 63
	          }
	        },
	        type: "binary-expr",
	        locations: [{
	          start: {
	            line: 6,
	            column: 23
	          },
	          end: {
	            line: 6,
	            column: 45
	          }
	        }, {
	          start: {
	            line: 6,
	            column: 49
	          },
	          end: {
	            line: 6,
	            column: 63
	          }
	        }],
	        line: 6
	      }
	    },
	    s: {
	      "0": 0,
	      "1": 0,
	      "2": 0,
	      "3": 0,
	      "4": 0
	    },
	    f: {
	      "0": 0
	    },
	    b: {
	      "0": [0, 0]
	    },
	    _coverageSchema: "1a1c01bbd47fc00a2c39e90264f33305004495a9",
	    hash: "86f3dd7eb7d6aac3026b62f2267de1cdde2a9aca"
	  };
	  var coverage = global[gcv] || (global[gcv] = {});

	  if (!coverage[path] || coverage[path].hash !== hash) {
	    coverage[path] = coverageData;
	  }

	  var actualCoverage = coverage[path];
	  {
	    // @ts-ignore
	    cov_d7t40zke3 = function () {
	      return actualCoverage;
	    };
	  }
	  return actualCoverage;
	}

	cov_d7t40zke3();

	const registerPlugin = (cov_d7t40zke3().s[0]++, (cov_d7t40zke3().b[0][0]++, videojs__default["default"].registerPlugin) || (cov_d7t40zke3().b[0][1]++, videojs__default["default"].plugin));
	/**
	* The video.js playlist plugin. Invokes the playlist-maker to create a
	* playlist function on the specific player.
	*
	* @param {Array} list
	*        a list of sources
	*
	* @param {number} item
	*        The index to start at
	*/

	cov_d7t40zke3().s[1]++;

	const plugin = function (list, item) {
	  cov_d7t40zke3().f[0]++;
	  cov_d7t40zke3().s[2]++;
	  factory(this, list, item);
	};

	cov_d7t40zke3().s[3]++;
	registerPlugin('playlist', plugin);
	cov_d7t40zke3().s[4]++;
	plugin.VERSION = version;

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

	/**
	 * Destroy a fixture player.
	 *
	 * @param  {Object} context
	 *         A testing context.
	 */

	function destroyFixturePlayer(context) {
	  context.player.dispose();
	}
	/**
	 * Create a fixture player.
	 *
	 * @param  {Object} context
	 *         A testing context.
	 */

	function createFixturePlayer(context) {
	  context.video = document_1.createElement('video');
	  context.fixture = document_1.querySelector('#qunit-fixture');
	  context.fixture.appendChild(context.video);
	  context.playerIsReady = false;
	  context.player = videojs__default["default"](context.video, {}, () => {
	    context.playerIsReady = true;
	  });
	  context.player.playlist();
	}

	const samplePlaylist = [{
	  sources: [{
	    src: 'http://media.w3.org/2010/05/sintel/trailer.mp4',
	    type: 'video/mp4'
	  }],
	  poster: 'http://media.w3.org/2010/05/sintel/poster.png'
	}, {
	  sources: [{
	    src: 'http://media.w3.org/2010/05/bunny/trailer.mp4',
	    type: 'video/mp4'
	  }],
	  poster: 'http://media.w3.org/2010/05/bunny/poster.png'
	}, {
	  sources: [{
	    src: 'http://vjs.zencdn.net/v/oceans.mp4',
	    type: 'video/mp4'
	  }],
	  poster: 'http://www.videojs.com/img/poster.jpg'
	}];
	QUnit__default["default"].module('current-item', {
	  beforeEach() {
	    this.clock = sinon__default["default"].useFakeTimers();
	    createFixturePlayer(this);
	  },

	  afterEach() {
	    destroyFixturePlayer(this);
	    this.clock.restore();
	  }

	}, function () {
	  QUnit__default["default"].module('without a playlist', function () {
	    QUnit__default["default"].test('player without a source', function (assert) {
	      assert.strictEqual(this.player.playlist.currentItem(), -1, 'currentItem() before tech ready'); // Tick forward to ready the playback tech.

	      this.clock.tick(1);
	      assert.strictEqual(this.player.playlist.currentItem(), -1, 'currentItem() after tech ready');
	    });
	    QUnit__default["default"].test('player with a source', function (assert) {
	      assert.strictEqual(this.player.playlist.currentItem(), -1, 'currentItem() before tech ready'); // Tick forward to ready the playback tech.

	      this.clock.tick(1);
	      this.player.src({
	        src: 'http://vjs.zencdn.net/v/oceans.mp4',
	        type: 'video/mp4'
	      });
	      assert.strictEqual(this.player.playlist.currentItem(), -1, 'currentItem() after tech ready');
	    });
	  });
	  QUnit__default["default"].module('with a playlist', function () {
	    QUnit__default["default"].test('set new source by calling currentItem()', function (assert) {
	      this.player.playlist(samplePlaylist);
	      assert.strictEqual(this.player.playlist.currentItem(), 0, 'currentItem() before tech ready'); // Tick forward to ready the playback tech.

	      this.clock.tick(1);
	      assert.strictEqual(this.player.playlist.currentItem(), 0, 'currentItem() after tech ready');
	      this.player.playlist.currentItem(1);
	      assert.strictEqual(this.player.playlist.currentItem(), 1, 'currentItem() changes the current item');
	    });
	    QUnit__default["default"].test('set a new source via src()', function (assert) {
	      this.player.playlist(samplePlaylist);
	      assert.strictEqual(this.player.playlist.currentItem(), 0, 'currentItem() before tech ready'); // Tick forward to ready the playback tech.

	      this.clock.tick(1);
	      assert.strictEqual(this.player.playlist.currentItem(), 0, 'currentItem() after tech ready');
	      this.player.src({
	        src: 'http://vjs.zencdn.net/v/oceans.mp4',
	        type: 'video/mp4'
	      });
	      assert.strictEqual(this.player.playlist.currentItem(), 2, 'src() changes the current item');
	    });
	    QUnit__default["default"].test('set a new source via src() - source is NOT in the playlist', function (assert) {
	      // Populate the player with a playlist without oceans.mp4
	      this.player.playlist(samplePlaylist.slice(0, 2));
	      assert.strictEqual(this.player.playlist.currentItem(), 0, 'currentItem() before tech ready'); // Tick forward to ready the playback tech.

	      this.clock.tick(1);
	      assert.strictEqual(this.player.playlist.currentItem(), 0, 'currentItem() after tech ready');
	      this.player.src({
	        src: 'http://vjs.zencdn.net/v/oceans.mp4',
	        type: 'video/mp4'
	      });
	      assert.strictEqual(this.player.playlist.currentItem(), -1, 'src() changes the current item');
	    });
	  });
	  QUnit__default["default"].module('duplicate sources playlist', function () {
	    QUnit__default["default"].test('set new sources by calling currentItem()', function (assert) {
	      // Populate the player with a playlist with another sintel on the end.
	      this.player.playlist(samplePlaylist.concat([{
	        sources: [{
	          src: 'http://media.w3.org/2010/05/sintel/trailer.mp4',
	          type: 'video/mp4'
	        }],
	        poster: 'http://media.w3.org/2010/05/sintel/poster.png'
	      }]));
	      assert.strictEqual(this.player.playlist.currentItem(), 0, 'currentItem() before tech ready'); // Tick forward to ready the playback tech.

	      this.clock.tick(1);
	      assert.strictEqual(this.player.playlist.currentItem(), 0, 'currentItem() after tech ready'); // Set the playlist to the last item.

	      this.player.playlist.currentItem(3);
	      assert.strictEqual(this.player.playlist.currentItem(), 3, 'currentItem() matches the duplicated item that was actually selected'); // Set the playlist back to the first item (also sintel).

	      this.player.playlist.currentItem(0);
	      assert.strictEqual(this.player.playlist.currentItem(), 0, 'currentItem() matches the duplicated item that was actually selected'); // Set the playlist to the second item (NOT sintel).

	      this.player.playlist.currentItem(1);
	      assert.strictEqual(this.player.playlist.currentItem(), 1, 'currentItem() is correct');
	    });
	    QUnit__default["default"].test('set new source by calling src()', function (assert) {
	      // Populate the player with a playlist with another sintel on the end.
	      this.player.playlist(samplePlaylist.concat([{
	        sources: [{
	          src: 'http://media.w3.org/2010/05/sintel/trailer.mp4',
	          type: 'video/mp4'
	        }],
	        poster: 'http://media.w3.org/2010/05/sintel/poster.png'
	      }]));
	      assert.strictEqual(this.player.playlist.currentItem(), 0, 'currentItem() before tech ready'); // Tick forward to ready the playback tech.

	      this.clock.tick(1);
	      assert.strictEqual(this.player.playlist.currentItem(), 0, 'currentItem() after tech ready'); // Set the playlist to the second item (NOT sintel).

	      this.player.playlist.currentItem(1);
	      assert.strictEqual(this.player.playlist.currentItem(), 1, 'currentItem() acted as a setter');
	      this.player.src({
	        src: 'http://media.w3.org/2010/05/sintel/trailer.mp4',
	        type: 'video/mp4'
	      });
	      assert.strictEqual(this.player.playlist.currentItem(), 0, 'currentItem() defaults to the first playlist item that matches the current source');
	    });
	  });
	});

	QUnit__default["default"].module('play-item');
	QUnit__default["default"].test('clearTracks will try and remove all tracks', function (assert) {
	  const player = proxy();
	  const remoteTracks = [1, 2, 3];
	  const removedTracks = [];

	  player.remoteTextTracks = function () {
	    return remoteTracks;
	  };

	  player.removeRemoteTextTrack = function (tt) {
	    removedTracks.push(tt);
	  };

	  clearTracks(player);
	  assert.deepEqual(removedTracks.sort(), remoteTracks.sort(), 'the removed tracks are equivalent to our remote tracks');
	});
	QUnit__default["default"].test('will not try to play if paused', function (assert) {
	  const player = proxy();
	  let tryPlay = false;

	  player.paused = function () {
	    return true;
	  };

	  player.play = function () {
	    tryPlay = true;
	  };

	  playItem(player, {
	    sources: [1, 2, 3],
	    textTracks: [4, 5, 6],
	    poster: 'http://example.com/poster.png'
	  });
	  assert.ok(!tryPlay, 'we did not reply on paused');
	});
	QUnit__default["default"].test('will try to play if not paused', function (assert) {
	  const player = proxy();
	  let tryPlay = false;

	  player.paused = function () {
	    return false;
	  };

	  player.play = function () {
	    tryPlay = true;
	  };

	  playItem(player, {
	    sources: [1, 2, 3],
	    textTracks: [4, 5, 6],
	    poster: 'http://example.com/poster.png'
	  });
	  assert.ok(tryPlay, 'we replayed on not-paused');
	});
	QUnit__default["default"].test('will not try to play if paused and not ended', function (assert) {
	  const player = proxy();
	  let tryPlay = false;

	  player.paused = function () {
	    return true;
	  };

	  player.ended = function () {
	    return false;
	  };

	  player.play = function () {
	    tryPlay = true;
	  };

	  playItem(player, {
	    sources: [1, 2, 3],
	    textTracks: [4, 5, 6],
	    poster: 'http://example.com/poster.png'
	  });
	  assert.ok(!tryPlay, 'we did not replaye on paused and not ended');
	});
	QUnit__default["default"].test('will try to play if paused and ended', function (assert) {
	  const player = proxy();
	  let tryPlay = false;

	  player.paused = function () {
	    return true;
	  };

	  player.ended = function () {
	    return true;
	  };

	  player.play = function () {
	    tryPlay = true;
	  };

	  playItem(player, {
	    sources: [1, 2, 3],
	    poster: 'http://example.com/poster.png'
	  });
	  assert.ok(tryPlay, 'we replayed on not-paused');
	});
	QUnit__default["default"].test('fires "beforeplaylistitem" and "playlistitem"', function (assert) {
	  const player = proxy();
	  const beforeSpy = sinon__default["default"].spy();
	  const spy = sinon__default["default"].spy();
	  player.on('beforeplaylistitem', beforeSpy);
	  player.on('playlistitem', spy);
	  playItem(player, {
	    sources: [1, 2, 3],
	    poster: 'http://example.com/poster.png'
	  });
	  assert.strictEqual(beforeSpy.callCount, 1);
	  assert.strictEqual(spy.callCount, 1);
	});

	QUnit__default["default"].test('the environment is sane', function (assert) {
	  assert.strictEqual(typeof Array.isArray, 'function', 'es5 exists');
	  assert.strictEqual(typeof sinon__default["default"], 'object', 'sinon exists');
	  assert.strictEqual(typeof videojs__default["default"], 'function', 'videojs exists');
	  assert.strictEqual(typeof plugin, 'function', 'plugin is a function');
	});
	QUnit__default["default"].test('registers itself with video.js', function (assert) {
	  assert.expect(1);
	  assert.strictEqual(typeof videojs__default["default"].getComponent('Player').prototype.playlist, 'function', 'videojs-playlist plugin was registered');
	});

	const videoList = [{
	  sources: [{
	    src: 'http://media.w3.org/2010/05/sintel/trailer.mp4',
	    type: 'video/mp4'
	  }],
	  poster: 'http://media.w3.org/2010/05/sintel/poster.png'
	}, {
	  sources: [{
	    src: 'http://media.w3.org/2010/05/bunny/trailer.mp4',
	    type: 'video/mp4'
	  }],
	  poster: 'http://media.w3.org/2010/05/bunny/poster.png'
	}, {
	  sources: [{
	    src: 'http://vjs.zencdn.net/v/oceans.mp4',
	    type: 'video/mp4'
	  }],
	  poster: 'http://www.videojs.com/img/poster.jpg'
	}, {
	  sources: [{
	    src: 'http://media.w3.org/2010/05/bunny/movie.mp4',
	    type: 'video/mp4'
	  }],
	  poster: 'http://media.w3.org/2010/05/bunny/poster.png'
	}, {
	  sources: [{
	    src: 'http://media.w3.org/2010/05/video/movie_300.mp4',
	    type: 'video/mp4'
	  }],
	  poster: 'http://media.w3.org/2010/05/video/poster.png'
	}];
	QUnit__default["default"].module('playlist-maker', {
	  beforeEach() {
	    this.clock = sinon__default["default"].useFakeTimers();
	  },

	  afterEach() {
	    this.clock.restore();
	  }

	});
	QUnit__default["default"].test('playlistMaker takes a player and a list and returns a playlist', function (assert) {
	  const playlist = factory(proxy(), []);
	  assert.equal(typeof playlist, 'function', 'playlist is a function');
	  assert.equal(typeof playlist.autoadvance, 'function', 'we have a autoadvance function');
	  assert.equal(typeof playlist.currentItem, 'function', 'we have a currentItem function');
	  assert.equal(typeof playlist.first, 'function', 'we have a first function');
	  assert.equal(typeof playlist.indexOf, 'function', 'we have a indexOf function');
	  assert.equal(typeof playlist.next, 'function', 'we have a next function');
	  assert.equal(typeof playlist.previous, 'function', 'we have a previous function');
	});
	QUnit__default["default"].test('playlistMaker can either take nothing or an Array as its first argument', function (assert) {
	  const playlist1 = factory(proxy());
	  const playlist2 = factory(proxy(), 'foo');
	  const playlist3 = factory(proxy(), {
	    foo: [1, 2, 3]
	  });
	  assert.deepEqual(playlist1(), [], 'if given no initial array, default to an empty array');
	  assert.deepEqual(playlist2(), [], 'if given no initial array, default to an empty array');
	  assert.deepEqual(playlist3(), [], 'if given no initial array, default to an empty array');
	});
	QUnit__default["default"].test('playlist() is a getter and setter for the list', function (assert) {
	  const playlist = factory(proxy(), [1, 2, 3]);
	  assert.deepEqual(playlist(), [1, 2, 3], 'equal to input list');
	  assert.deepEqual(playlist([1, 2, 3, 4, 5]), [1, 2, 3, 4, 5], 'equal to input list, arguments ignored');
	  assert.deepEqual(playlist(), [1, 2, 3, 4, 5], 'equal to input list');
	  const list = playlist();
	  list.unshift(10);
	  assert.deepEqual(playlist(), [1, 2, 3, 4, 5], 'changing the list did not affect the playlist');
	  assert.notDeepEqual(playlist(), [10, 1, 2, 3, 4, 5], 'changing the list did not affect the playlist');
	});
	QUnit__default["default"].test('playlist() should only accept an Array as a new playlist', function (assert) {
	  const playlist = factory(proxy(), [1, 2, 3]);
	  assert.deepEqual(playlist('foo'), [1, 2, 3], 'when given "foo", it should be treated as a getter');
	  assert.deepEqual(playlist({
	    foo: [1, 2, 3]
	  }), [1, 2, 3], 'when given {foo: [1,2,3]}, it should be treated as a getter');
	});
	QUnit__default["default"].test('playlist.currentItem() works as expected', function (assert) {
	  const player = proxy();
	  const playlist = factory(player, videoList);
	  let src;

	  player.src = function (s) {
	    if (s) {
	      if (typeof s === 'string') {
	        src = s;
	      } else if (Array.isArray(s)) {
	        return player.src(s[0]);
	      } else {
	        return player.src(s.src);
	      }
	    }
	  };

	  player.currentSrc = function () {
	    return src;
	  };

	  src = videoList[0].sources[0].src;
	  assert.equal(playlist.currentItem(), 0, 'begin at the first item, item 0');
	  assert.equal(playlist.currentItem(2), 2, 'setting to item 2 gives us back the new item index');
	  assert.equal(playlist.currentItem(), 2, 'the current item is now 2');
	  assert.equal(playlist.currentItem(5), 2, 'cannot change to an out-of-bounds item');
	  assert.equal(playlist.currentItem(-1), 2, 'cannot change to an out-of-bounds item');
	  assert.equal(playlist.currentItem(null), 2, 'cannot change to an invalid item');
	  assert.equal(playlist.currentItem(NaN), 2, 'cannot change to an invalid item');
	  assert.equal(playlist.currentItem(Infinity), 2, 'cannot change to an invalid item');
	  assert.equal(playlist.currentItem(-Infinity), 2, 'cannot change to an invalid item');
	});
	QUnit__default["default"].test('playlist.currentItem() shows the poster for the first video', function (assert) {
	  const player = proxy();
	  const playlist = factory(player, videoList);
	  playlist.currentItem(0);
	  assert.notEqual(player.poster(), '', 'poster is shown for playlist index 0');
	});
	QUnit__default["default"].test('playlist.currentItem() hides the poster for all videos after the first', function (assert) {
	  const player = proxy();
	  const playlist = factory(player, videoList);

	  for (let i = 1; i <= playlist.lastIndex(); i++) {
	    playlist.currentItem(i);
	    assert.equal(player.poster(), '', 'poster is hidden for playlist index ' + i);
	  }
	});
	QUnit__default["default"].test('playlist.currentItem() returns -1 with an empty playlist', function (assert) {
	  const playlist = factory(proxy(), []);
	  assert.equal(playlist.currentItem(), -1, 'we should get a -1 with an empty playlist');
	});
	QUnit__default["default"].test('playlist.currentItem() does not change items if same index is given', function (assert) {
	  const player = proxy();
	  let sources = 0;
	  let src;

	  player.src = function (s) {
	    if (s) {
	      if (typeof s === 'string') {
	        src = s;
	      } else if (Array.isArray(s)) {
	        return player.src(s[0]);
	      } else {
	        return player.src(s.src);
	      }
	    }

	    sources++;
	  };

	  player.currentSrc = function () {
	    return src;
	  };

	  const playlist = factory(player, videoList);
	  assert.equal(sources, 1, 'we switched to the first playlist item');
	  sources = 0;
	  assert.equal(playlist.currentItem(), 0, 'we start at index 0');
	  playlist.currentItem(0);
	  assert.equal(sources, 0, 'we did not try to set sources');
	  playlist.currentItem(1);
	  assert.equal(sources, 1, 'we did try to set sources');
	  playlist.currentItem(1);
	  assert.equal(sources, 1, 'we did not try to set sources');
	});
	QUnit__default["default"].test('playlistMaker accepts a starting index', function (assert) {
	  const player = proxy();
	  let src;

	  player.src = function (s) {
	    if (s) {
	      if (typeof s === 'string') {
	        src = s;
	      } else if (Array.isArray(s)) {
	        return player.src(s[0]);
	      } else {
	        return player.src(s.src);
	      }
	    }
	  };

	  player.currentSrc = function () {
	    return src;
	  };

	  const playlist = factory(player, videoList, 1);
	  assert.equal(playlist.currentItem(), 1, 'if given an initial index, load that video');
	});
	QUnit__default["default"].test('playlistMaker accepts a starting index', function (assert) {
	  const player = proxy();
	  let src;

	  player.src = function (s) {
	    if (s) {
	      if (typeof s === 'string') {
	        src = s;
	      } else if (Array.isArray(s)) {
	        return player.src(s[0]);
	      } else {
	        return player.src(s.src);
	      }
	    }
	  };

	  player.currentSrc = function () {
	    return src;
	  };

	  const playlist = factory(player, videoList, -1);
	  assert.equal(playlist.currentItem(), -1, 'if given -1 as initial index, load no video');
	});
	QUnit__default["default"].test('playlist.contains() works as expected', function (assert) {
	  const player = proxy();
	  const playlist = factory(player, videoList);
	  player.playlist = playlist;
	  assert.ok(playlist.contains('http://media.w3.org/2010/05/sintel/trailer.mp4'), 'we can ask whether it contains a source string');
	  assert.ok(playlist.contains(['http://media.w3.org/2010/05/sintel/trailer.mp4']), 'we can ask whether it contains a sources list of strings');
	  assert.ok(playlist.contains([{
	    src: 'http://media.w3.org/2010/05/sintel/trailer.mp4',
	    type: 'video/mp4'
	  }]), 'we can ask whether it contains a sources list of objects');
	  assert.ok(playlist.contains({
	    sources: ['http://media.w3.org/2010/05/sintel/trailer.mp4']
	  }), 'we can ask whether it contains a playlist item');
	  assert.ok(playlist.contains({
	    sources: [{
	      src: 'http://media.w3.org/2010/05/sintel/trailer.mp4',
	      type: 'video/mp4'
	    }]
	  }), 'we can ask whether it contains a playlist item');
	  assert.ok(!playlist.contains('http://media.w3.org/2010/05/sintel/poster.png'), 'we get false for a non-existent source string');
	  assert.ok(!playlist.contains(['http://media.w3.org/2010/05/sintel/poster.png']), 'we get false for a non-existent source list of strings');
	  assert.ok(!playlist.contains([{
	    src: 'http://media.w3.org/2010/05/sintel/poster.png',
	    type: 'video/mp4'
	  }]), 'we get false for a non-existent source list of objects');
	  assert.ok(!playlist.contains({
	    sources: ['http://media.w3.org/2010/05/sintel/poster.png']
	  }), 'we can ask whether it contains a playlist item');
	  assert.ok(!playlist.contains({
	    sources: [{
	      src: 'http://media.w3.org/2010/05/sintel/poster.png',
	      type: 'video/mp4'
	    }]
	  }), 'we get false for a non-existent playlist item');
	});
	QUnit__default["default"].test('playlist.indexOf() works as expected', function (assert) {
	  const player = proxy();
	  const playlist = factory(player, videoList);
	  const mixedSourcesPlaylist = factory(player, [{
	    sources: [{
	      src: 'http://media.w3.org/2010/05/sintel/trailer.mp4',
	      type: 'video/mp4'
	    }, {
	      app_name: 'rtmp://example.com/sintel/trailer',
	      // eslint-disable-line
	      avg_bitrate: 4255000,
	      // eslint-disable-line
	      codec: 'H264',
	      container: 'MP4'
	    }],
	    poster: 'http://media.w3.org/2010/05/sintel/poster.png'
	  }]);
	  player.playlist = playlist;
	  assert.equal(playlist.indexOf('http://media.w3.org/2010/05/sintel/trailer.mp4'), 0, 'sintel trailer is first item');
	  assert.equal(playlist.indexOf('//media.w3.org/2010/05/sintel/trailer.mp4'), 0, 'sintel trailer is first item, protocol-relative url considered equal');
	  assert.equal(playlist.indexOf(['http://media.w3.org/2010/05/bunny/trailer.mp4']), 1, 'bunny trailer is second item');
	  assert.equal(playlist.indexOf([{
	    src: 'http://vjs.zencdn.net/v/oceans.mp4',
	    type: 'video/mp4'
	  }]), 2, 'oceans is third item');
	  assert.equal(playlist.indexOf({
	    sources: ['http://media.w3.org/2010/05/bunny/movie.mp4']
	  }), 3, 'bunny movie is fourth item');
	  assert.equal(playlist.indexOf({
	    sources: [{
	      src: 'http://media.w3.org/2010/05/video/movie_300.mp4',
	      type: 'video/mp4'
	    }]
	  }), 4, 'timer video is fifth item');
	  assert.equal(playlist.indexOf('http://media.w3.org/2010/05/sintel/poster.png'), -1, 'poster.png does not exist');
	  assert.equal(playlist.indexOf(['http://media.w3.org/2010/05/sintel/poster.png']), -1, 'poster.png does not exist');
	  assert.equal(playlist.indexOf([{
	    src: 'http://media.w3.org/2010/05/sintel/poster.png',
	    type: 'video/mp4'
	  }]), -1, 'poster.png does not exist');
	  assert.equal(playlist.indexOf({
	    sources: ['http://media.w3.org/2010/05/sintel/poster.png']
	  }), -1, 'poster.png does not exist');
	  assert.equal(playlist.indexOf({
	    sources: [{
	      src: 'http://media.w3.org/2010/05/sintel/poster.png',
	      type: 'video/mp4'
	    }]
	  }), -1, 'poster.png does not exist');
	  assert.equal(mixedSourcesPlaylist.indexOf({
	    sources: [{
	      src: 'http://media.w3.org/2010/05/bunny/movie.mp4',
	      type: 'video/mp4'
	    }, {
	      app_name: 'rtmp://example.com/bunny/movie',
	      // eslint-disable-line
	      avg_bitrate: 4255000,
	      // eslint-disable-line
	      codec: 'H264',
	      container: 'MP4'
	    }],
	    poster: 'http://media.w3.org/2010/05/sintel/poster.png'
	  }), -1, 'bunny movie does not exist');
	  assert.equal(mixedSourcesPlaylist.indexOf({
	    sources: [{
	      src: 'http://media.w3.org/2010/05/sintel/trailer.mp4',
	      type: 'video/mp4'
	    }, {
	      app_name: 'rtmp://example.com/sintel/trailer',
	      // eslint-disable-line
	      avg_bitrate: 4255000,
	      // eslint-disable-line
	      codec: 'H264',
	      container: 'MP4'
	    }],
	    poster: 'http://media.w3.org/2010/05/sintel/poster.png'
	  }), 0, 'sintel trailer does exist');
	});
	QUnit__default["default"].test('playlist.nextIndex() works as expected', function (assert) {
	  const playlist = factory(proxy(), []);
	  assert.equal(playlist.nextIndex(), -1, 'the next index was -1 for an empty list');
	  playlist([1, 2, 3]);

	  playlist.currentItem = () => 0;

	  assert.equal(playlist.nextIndex(), 1, 'the next index was 1');

	  playlist.currentItem = () => 1;

	  assert.equal(playlist.nextIndex(), 2, 'the next index was 2');

	  playlist.currentItem = () => 2;

	  assert.equal(playlist.nextIndex(), 2, 'the next index did not change because the playlist does not repeat');
	  playlist.repeat(true);
	  assert.equal(playlist.nextIndex(), 0, 'the next index was now 0 because the playlist repeats');
	});
	QUnit__default["default"].test('playlist.previousIndex() works as expected', function (assert) {
	  const playlist = factory(proxy(), []);
	  assert.equal(playlist.previousIndex(), -1, 'the previous index was -1 for an empty list');
	  playlist([1, 2, 3]);

	  playlist.currentItem = () => 2;

	  assert.equal(playlist.previousIndex(), 1, 'the previous index was 1');

	  playlist.currentItem = () => 1;

	  assert.equal(playlist.previousIndex(), 0, 'the previous index was 0');

	  playlist.currentItem = () => 0;

	  assert.equal(playlist.previousIndex(), 0, 'the previous index did not change because the playlist does not repeat');
	  playlist.repeat(true);
	  assert.equal(playlist.previousIndex(), 2, 'the previous index was now 2 because the playlist repeats');
	});
	QUnit__default["default"].test('playlist.lastIndex() works as expected', function (assert) {
	  const playlist = factory(proxy(), []);
	  assert.equal(playlist.lastIndex(), -1, 'the last index was -1 for an empty list');
	  playlist([1, 2, 3]);
	  assert.equal(playlist.lastIndex(), 2, 'the last index was 2');
	});
	QUnit__default["default"].test('playlist.next() works as expected', function (assert) {
	  const player = proxy();
	  const playlist = factory(player, videoList);
	  let src;

	  player.currentSrc = function () {
	    return src;
	  };

	  src = videoList[0].sources[0].src;
	  assert.equal(playlist.currentItem(), 0, 'we start on item 0');
	  assert.deepEqual(playlist.next(), videoList[1], 'we get back the value of currentItem 2');
	  src = videoList[1].sources[0].src;
	  assert.equal(playlist.currentItem(), 1, 'we are now on item 1');
	  assert.deepEqual(playlist.next(), videoList[2], 'we get back the value of currentItem 3');
	  src = videoList[2].sources[0].src;
	  assert.equal(playlist.currentItem(), 2, 'we are now on item 2');
	  src = videoList[4].sources[0].src;
	  assert.equal(playlist.currentItem(4), 4, 'we are now on item 4');
	  assert.equal(typeof playlist.next(), 'undefined', 'we get nothing back if we try to go out of bounds');
	});
	QUnit__default["default"].test('playlist.previous() works as expected', function (assert) {
	  const player = proxy();
	  const playlist = factory(player, videoList);
	  let src;

	  player.currentSrc = function () {
	    return src;
	  };

	  src = videoList[0].sources[0].src;
	  assert.equal(playlist.currentItem(), 0, 'we start on item 0');
	  assert.equal(typeof playlist.previous(), 'undefined', 'we get nothing back if we try to go out of bounds');
	  src = videoList[2].sources[0].src;
	  assert.equal(playlist.currentItem(), 2, 'we are on item 2');
	  assert.deepEqual(playlist.previous(), videoList[1], 'we get back value of currentItem 1');
	  src = videoList[1].sources[0].src;
	  assert.equal(playlist.currentItem(), 1, 'we are on item 1');
	  assert.deepEqual(playlist.previous(), videoList[0], 'we get back value of currentItem 0');
	  src = videoList[0].sources[0].src;
	  assert.equal(playlist.currentItem(), 0, 'we are on item 0');
	  assert.equal(typeof playlist.previous(), 'undefined', 'we get nothing back if we try to go out of bounds');
	});
	QUnit__default["default"].test('loading a non-playlist video will cancel autoadvance and set index of -1', function (assert) {
	  const oldReset = reset;
	  const player = proxy();
	  const playlist = factory(player, [{
	    sources: [{
	      src: 'http://media.w3.org/2010/05/sintel/trailer.mp4',
	      type: 'video/mp4'
	    }],
	    poster: 'http://media.w3.org/2010/05/sintel/poster.png'
	  }, {
	    sources: [{
	      src: 'http://media.w3.org/2010/05/bunny/trailer.mp4',
	      type: 'video/mp4'
	    }],
	    poster: 'http://media.w3.org/2010/05/bunny/poster.png'
	  }]);

	  player.currentSrc = function () {
	    return 'http://vjs.zencdn.net/v/oceans.mp4';
	  };

	  setReset_(function () {
	    assert.ok(true, 'autoadvance.reset was called');
	  });
	  player.trigger('loadstart');
	  assert.equal(playlist.currentItem(), -1, 'new currentItem is -1');

	  player.currentSrc = function () {
	    return 'http://media.w3.org/2010/05/sintel/trailer.mp4';
	  };

	  setReset_(function () {
	    assert.ok(false, 'autoadvance.reset should not be called');
	  });
	  player.trigger('loadstart');
	  setReset_(oldReset);
	});
	QUnit__default["default"].test('when loading a new playlist, trigger "duringplaylistchange" on the player', function (assert) {
	  const done = assert.async();
	  const player = proxy();
	  const playlist = factory(player, [1, 2, 3], 1);
	  player.on('duringplaylistchange', e => {
	    assert.strictEqual(e.type, 'duringplaylistchange', 'the event object had the correct "type" property');
	    assert.strictEqual(e.previousIndex, 1, 'the event object had the correct "previousIndex" property');
	    assert.deepEqual(e.previousPlaylist, [1, 2, 3], 'the event object had the correct "previousPlaylist" property');
	    assert.strictEqual(e.nextIndex, 0, 'the event object had the correct "nextIndex" property');
	    assert.deepEqual(e.nextPlaylist, [4, 5, 6], 'the event object had the correct "nextPlaylist" property');
	    assert.throws(() => {
	      playlist([1, 2, 3]);
	    }, Error, 'cannot set a new playlist during a change');
	    const spy = sinon__default["default"].spy();
	    player.on('playlistsorted', spy);
	    playlist.sort();
	    playlist.reverse();
	    playlist.shuffle();
	    assert.strictEqual(spy.callCount, 0, 'the "playlistsorted" event never fired');
	    playlist.currentItem(2);
	    assert.strictEqual(playlist.currentItem(), 1, 'the playlist current item could not be changed');
	    playlist.next();
	    assert.strictEqual(playlist.currentItem(), 1, 'the playlist current item could not be changed');
	    playlist.previous();
	    assert.strictEqual(playlist.currentItem(), 1, 'the playlist current item could not be changed');
	    playlist.first();
	    assert.strictEqual(playlist.currentItem(), 1, 'the playlist current item could not be changed');
	    playlist.last();
	    assert.strictEqual(playlist.currentItem(), 1, 'the playlist current item could not be changed');
	    done();
	  });
	  playlist([4, 5, 6]);
	});
	QUnit__default["default"].test('when loading a new playlist, trigger "playlistchange" on the player', function (assert) {
	  const spy = sinon__default["default"].spy();
	  const player = proxy();
	  player.on('playlistchange', spy);
	  const playlist = factory(player, [1, 2, 3]);
	  playlist([4, 5, 6]);
	  this.clock.tick(1);
	  assert.strictEqual(spy.callCount, 1);
	  assert.strictEqual(spy.firstCall.args[0].type, 'playlistchange');
	  assert.strictEqual(spy.firstCall.args[0].action, 'change');
	});
	QUnit__default["default"].test('"duringplaylistchange" and "playlistchange" on first call without an initial list', function (assert) {
	  const changeSpy = sinon__default["default"].spy();
	  const duringSpy = sinon__default["default"].spy();
	  const player = proxy();
	  player.on('playlistchange', changeSpy);
	  player.on('duringplaylistchange', duringSpy);
	  const playlist = factory(player);
	  this.clock.tick(1);
	  assert.strictEqual(changeSpy.callCount, 0, 'on initial call, the "playlistchange" event did not fire');
	  assert.strictEqual(duringSpy.callCount, 0, 'on initial call, the "duringplaylistchange" event did not fire');
	  playlist([1]);
	  this.clock.tick(1);
	  assert.strictEqual(changeSpy.callCount, 1, 'on second call, the "playlistchange" event did fire');
	  assert.strictEqual(duringSpy.callCount, 1, 'on second call, the "duringplaylistchange" event did fire');
	  playlist([2]);
	  this.clock.tick(1);
	  assert.strictEqual(changeSpy.callCount, 2, 'on third call, the "playlistchange" event did fire');
	  assert.strictEqual(duringSpy.callCount, 2, 'on third call, the "duringplaylistchange" event did fire');
	});
	QUnit__default["default"].test('"duringplaylistchange" and "playlistchange" on first call with an initial list', function (assert) {
	  const changeSpy = sinon__default["default"].spy();
	  const duringSpy = sinon__default["default"].spy();
	  const player = proxy();
	  player.on('playlistchange', changeSpy);
	  player.on('duringplaylistchange', duringSpy);
	  const playlist = factory(player, [1]);
	  this.clock.tick(1);
	  assert.strictEqual(changeSpy.callCount, 0, 'on initial call, the "playlistchange" event did not fire');
	  assert.strictEqual(duringSpy.callCount, 1, 'on initial call, the "duringplaylistchange" event did fire');
	  playlist([2]);
	  this.clock.tick(1);
	  assert.strictEqual(changeSpy.callCount, 1, 'on second call, the "playlistchange" event did fire');
	  assert.strictEqual(duringSpy.callCount, 2, 'on second call, the "duringplaylistchange" event did fire');
	  playlist([3]);
	  this.clock.tick(1);
	  assert.strictEqual(changeSpy.callCount, 2, 'on third call, the "playlistchange" event did fire');
	  assert.strictEqual(duringSpy.callCount, 3, 'on third call, the "duringplaylistchange" event did fire');
	});
	QUnit__default["default"].test('playlist.sort() works as expected', function (assert) {
	  const player = proxy();
	  const spy = sinon__default["default"].spy();
	  player.on('playlistsorted', spy);
	  const playlist = factory(player, []);
	  playlist.sort();
	  assert.deepEqual(playlist(), [], 'playlist did not change because it is empty');
	  assert.strictEqual(spy.callCount, 0, 'the "playlistsorted" event did not trigger');
	  playlist([4, 2, 1, 3]);
	  playlist.sort();
	  assert.deepEqual(playlist(), [1, 2, 3, 4], 'playlist is sorted per default sort behavior');
	  assert.strictEqual(spy.callCount, 1, 'the "playlistsorted" event triggered');
	  playlist.sort((a, b) => b - a);
	  assert.deepEqual(playlist(), [4, 3, 2, 1], 'playlist is sorted per default sort behavior');
	  assert.strictEqual(spy.callCount, 2, 'the "playlistsorted" event triggered');
	});
	QUnit__default["default"].test('playlist.reverse() works as expected', function (assert) {
	  const player = proxy();
	  const spy = sinon__default["default"].spy();
	  player.on('playlistsorted', spy);
	  const playlist = factory(player, []);
	  playlist.reverse();
	  assert.deepEqual(playlist(), [], 'playlist did not change because it is empty');
	  assert.strictEqual(spy.callCount, 0, 'the "playlistsorted" event did not trigger');
	  playlist([1, 2, 3, 4]);
	  playlist.reverse();
	  assert.deepEqual(playlist(), [4, 3, 2, 1], 'playlist is reversed');
	  assert.strictEqual(spy.callCount, 1, 'the "playlistsorted" event triggered');
	});
	QUnit__default["default"].test('playlist.shuffle() works as expected', function (assert) {
	  const player = proxy();
	  const spy = sinon__default["default"].spy();
	  player.on('playlistsorted', spy);
	  const playlist = factory(player, []);
	  playlist.shuffle();
	  assert.deepEqual(playlist(), [], 'playlist did not change because it is empty');
	  assert.strictEqual(spy.callCount, 0, 'the "playlistsorted" event did not trigger');
	  playlist([1, 2, 3, 4]);
	  playlist.shuffle();
	  const list = playlist();
	  assert.strictEqual(list.length, 4, 'playlist is the correct length');
	  assert.notStrictEqual(list.indexOf(1), -1, '1 is in the list');
	  assert.notStrictEqual(list.indexOf(2), -1, '2 is in the list');
	  assert.notStrictEqual(list.indexOf(3), -1, '3 is in the list');
	  assert.notStrictEqual(list.indexOf(4), -1, '4 is in the list');
	  assert.strictEqual(spy.callCount, 1, 'the "playlistsorted" event triggered');
	});
	QUnit__default["default"].test('playlist.shuffle({rest: true}) works as expected', function (assert) {
	  const player = proxy();
	  const spy = sinon__default["default"].spy();
	  player.on('playlistsorted', spy);
	  const playlist = factory(player, [1, 2, 3, 4]);
	  playlist.currentIndex_ = 3;
	  playlist.shuffle({
	    rest: true
	  });
	  let list = playlist();
	  assert.deepEqual(list, [1, 2, 3, 4], 'playlist is unchanged because the last item is selected');
	  assert.strictEqual(spy.callCount, 0, 'the "playlistsorted" event was not triggered');
	  playlist.currentIndex_ = 2;
	  playlist.shuffle({
	    rest: true
	  });
	  list = playlist();
	  assert.deepEqual(list, [1, 2, 3, 4], 'playlist is unchanged because the second-to-last item is selected');
	  assert.strictEqual(spy.callCount, 0, 'the "playlistsorted" event was not triggered');
	  playlist.currentIndex_ = 1;
	  playlist.shuffle({
	    rest: true
	  });
	  list = playlist();
	  assert.strictEqual(list.length, 4, 'playlist is the correct length');
	  assert.strictEqual(list.indexOf(1), 0, '1 is the first item in the list');
	  assert.strictEqual(list.indexOf(2), 1, '2 is the second item in the list');
	  assert.notStrictEqual(list.indexOf(3), -1, '3 is in the list');
	  assert.notStrictEqual(list.indexOf(4), -1, '4 is in the list');
	  assert.strictEqual(spy.callCount, 1, 'the "playlistsorted" event triggered');
	  playlist.currentIndex_ = 0;
	  playlist.shuffle({
	    rest: true
	  });
	  list = playlist();
	  assert.strictEqual(list.length, 4, 'playlist is the correct length');
	  assert.strictEqual(list.indexOf(1), 0, '1 is the first item in the list');
	  assert.notStrictEqual(list.indexOf(2), -1, '2 is in the list');
	  assert.notStrictEqual(list.indexOf(3), -1, '3 is in the list');
	  assert.notStrictEqual(list.indexOf(4), -1, '4 is in the list');
	  assert.strictEqual(spy.callCount, 2, 'the "playlistsorted" event triggered');
	  playlist.currentIndex_ = -1;
	  playlist.shuffle({
	    rest: true
	  });
	  list = playlist();
	  assert.strictEqual(list.length, 4, 'playlist is the correct length');
	  assert.notStrictEqual(list.indexOf(1), -1, '1 is in the list');
	  assert.notStrictEqual(list.indexOf(2), -1, '2 is in the list');
	  assert.notStrictEqual(list.indexOf(3), -1, '3 is in the list');
	  assert.notStrictEqual(list.indexOf(4), -1, '4 is in the list');
	  assert.strictEqual(spy.callCount, 3, 'the "playlistsorted" event triggered');
	});
	QUnit__default["default"].test('playlist.add will append an item by default', function (assert) {
	  const player = proxy();
	  const playlist = factory(player, [1, 2, 3]);
	  const spy = sinon__default["default"].spy();
	  this.clock.tick(1);
	  player.on(['playlistchange', 'playlistadd'], spy);
	  playlist.add(4);
	  assert.deepEqual(playlist(), [1, 2, 3, 4]);
	  assert.strictEqual(spy.callCount, 2);
	  assert.strictEqual(spy.firstCall.args[0].type, 'playlistchange');
	  assert.strictEqual(spy.firstCall.args[0].action, 'add');
	  assert.strictEqual(spy.secondCall.args[0].type, 'playlistadd');
	  assert.strictEqual(spy.secondCall.args[0].index, 3);
	  assert.strictEqual(spy.secondCall.args[0].count, 1);
	});
	QUnit__default["default"].test('playlist.add can insert an item at a specific index', function (assert) {
	  const player = proxy();
	  const playlist = factory(player, [1, 2, 3]);
	  const spy = sinon__default["default"].spy();
	  this.clock.tick(1);
	  player.on(['playlistchange', 'playlistadd'], spy);
	  playlist.add(4, 1);
	  assert.deepEqual(playlist(), [1, 4, 2, 3]);
	  assert.strictEqual(spy.callCount, 2);
	  assert.strictEqual(spy.firstCall.args[0].type, 'playlistchange');
	  assert.strictEqual(spy.firstCall.args[0].action, 'add');
	  assert.strictEqual(spy.secondCall.args[0].type, 'playlistadd');
	  assert.strictEqual(spy.secondCall.args[0].index, 1);
	  assert.strictEqual(spy.secondCall.args[0].count, 1);
	});
	QUnit__default["default"].test('playlist.add appends when specified index is out of bounds', function (assert) {
	  const player = proxy();
	  const playlist = factory(player, [1, 2, 3]);
	  const spy = sinon__default["default"].spy();
	  this.clock.tick(1);
	  player.on(['playlistchange', 'playlistadd'], spy);
	  playlist.add(4, 10);
	  assert.deepEqual(playlist(), [1, 2, 3, 4]);
	  assert.strictEqual(spy.callCount, 2);
	  assert.strictEqual(spy.firstCall.args[0].type, 'playlistchange');
	  assert.strictEqual(spy.firstCall.args[0].action, 'add');
	  assert.strictEqual(spy.secondCall.args[0].type, 'playlistadd');
	  assert.strictEqual(spy.secondCall.args[0].index, 3);
	  assert.strictEqual(spy.secondCall.args[0].count, 1);
	});
	QUnit__default["default"].test('playlist.add can append multiple items', function (assert) {
	  const player = proxy();
	  const playlist = factory(player, [1, 2, 3]);
	  const spy = sinon__default["default"].spy();
	  this.clock.tick(1);
	  player.on(['playlistchange', 'playlistadd'], spy);
	  playlist.add([4, 5, 6]);
	  assert.deepEqual(playlist(), [1, 2, 3, 4, 5, 6]);
	  assert.strictEqual(spy.callCount, 2);
	  assert.strictEqual(spy.firstCall.args[0].type, 'playlistchange');
	  assert.strictEqual(spy.firstCall.args[0].action, 'add');
	  assert.strictEqual(spy.secondCall.args[0].type, 'playlistadd');
	  assert.strictEqual(spy.secondCall.args[0].index, 3);
	  assert.strictEqual(spy.secondCall.args[0].count, 3);
	});
	QUnit__default["default"].test('playlist.add can insert multiple items at a specific index', function (assert) {
	  const player = proxy();
	  const playlist = factory(player, [1, 2, 3]);
	  const spy = sinon__default["default"].spy();
	  this.clock.tick(1);
	  player.on(['playlistchange', 'playlistadd'], spy);
	  playlist.add([4, 5, 6, 7], 1);
	  assert.deepEqual(playlist(), [1, 4, 5, 6, 7, 2, 3]);
	  assert.strictEqual(spy.callCount, 2);
	  assert.strictEqual(spy.firstCall.args[0].type, 'playlistchange');
	  assert.strictEqual(spy.firstCall.args[0].action, 'add');
	  assert.strictEqual(spy.secondCall.args[0].type, 'playlistadd');
	  assert.strictEqual(spy.secondCall.args[0].index, 1);
	  assert.strictEqual(spy.secondCall.args[0].count, 4);
	});
	QUnit__default["default"].test('playlist.add throws an error duringplaylistchange', function (assert) {
	  const done = assert.async();
	  const player = proxy();
	  const playlist = factory(player, [1, 2, 3]);
	  player.on('duringplaylistchange', e => {
	    assert.throws(() => playlist.add(4));
	    done();
	  });
	  playlist([4, 5, 6]);
	});
	QUnit__default["default"].test('playlist.remove can remove an item at an index', function (assert) {
	  const player = proxy();
	  const playlist = factory(player, [1, 2, 3]);
	  const spy = sinon__default["default"].spy();
	  this.clock.tick(1);
	  player.on(['playlistchange', 'playlistremove'], spy);
	  playlist.remove(1);
	  assert.deepEqual(playlist(), [1, 3]);
	  assert.strictEqual(spy.callCount, 2);
	  assert.strictEqual(spy.firstCall.args[0].type, 'playlistchange');
	  assert.strictEqual(spy.firstCall.args[0].action, 'remove');
	  assert.strictEqual(spy.secondCall.args[0].type, 'playlistremove');
	  assert.strictEqual(spy.secondCall.args[0].index, 1);
	  assert.strictEqual(spy.secondCall.args[0].count, 1);
	});
	QUnit__default["default"].test('playlist.remove does nothing when index is out of range', function (assert) {
	  const player = proxy();
	  const playlist = factory(player, [1, 2, 3]);
	  const spy = sinon__default["default"].spy();
	  this.clock.tick(1);
	  player.on(['playlistchange', 'playlistremove'], spy);
	  playlist.remove(4);
	  assert.deepEqual(playlist(), [1, 2, 3]);
	  assert.strictEqual(spy.callCount, 0);
	});
	QUnit__default["default"].test('playlist.remove can remove multiple items at an index', function (assert) {
	  const player = proxy();
	  const playlist = factory(player, [1, 2, 3]);
	  const spy = sinon__default["default"].spy();
	  this.clock.tick(1);
	  player.on(['playlistchange', 'playlistremove'], spy);
	  playlist.remove(1, 2);
	  assert.deepEqual(playlist(), [1]);
	  assert.strictEqual(spy.callCount, 2);
	  assert.strictEqual(spy.firstCall.args[0].type, 'playlistchange');
	  assert.strictEqual(spy.firstCall.args[0].action, 'remove');
	  assert.strictEqual(spy.secondCall.args[0].type, 'playlistremove');
	  assert.strictEqual(spy.secondCall.args[0].index, 1);
	  assert.strictEqual(spy.secondCall.args[0].count, 2);
	});
	QUnit__default["default"].test('playlist.remove throws an error duringplaylistchange', function (assert) {
	  const done = assert.async();
	  const player = proxy();
	  const playlist = factory(player, [1, 2, 3]);
	  player.on('duringplaylistchange', e => {
	    assert.throws(() => playlist.remove(0));
	    done();
	  });
	  playlist([4, 5, 6]);
	});

})(QUnit, sinon, videojs);
