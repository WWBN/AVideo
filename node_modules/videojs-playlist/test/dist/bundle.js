/*! @name videojs-playlist @version 5.2.0 @license Apache-2.0 */
(function (QUnit, sinon, videojs) {
  'use strict';

  function _interopDefaultLegacy (e) { return e && typeof e === 'object' && 'default' in e ? e : { 'default': e }; }

  var QUnit__default = /*#__PURE__*/_interopDefaultLegacy(QUnit);
  var sinon__default = /*#__PURE__*/_interopDefaultLegacy(sinon);
  var videojs__default = /*#__PURE__*/_interopDefaultLegacy(videojs);

  function cov_2hh5kxylu4() {
    var path = "/Users/bzizmond/Documents/projects/bc/videojs-playlist/src/auto-advance.js";
    var hash = "aa91c92b29ff730bd2c5c93c0bee707f838b0b75";
    var global = new Function("return this")();
    var gcv = "__coverage__";
    var coverageData = {
      path: "/Users/bzizmond/Documents/projects/bc/videojs-playlist/src/auto-advance.js",
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
            line: 39,
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
            line: 34,
            column: 3
          }
        },
        "9": {
          start: {
            line: 33,
            column: 4
          },
          end: {
            line: 33,
            column: 59
          }
        },
        "10": {
          start: {
            line: 36,
            column: 2
          },
          end: {
            line: 36,
            column: 20
          }
        },
        "11": {
          start: {
            line: 37,
            column: 2
          },
          end: {
            line: 37,
            column: 20
          }
        },
        "12": {
          start: {
            line: 38,
            column: 2
          },
          end: {
            line: 38,
            column: 32
          }
        },
        "13": {
          start: {
            line: 53,
            column: 14
          },
          end: {
            line: 94,
            column: 1
          }
        },
        "14": {
          start: {
            line: 54,
            column: 2
          },
          end: {
            line: 54,
            column: 16
          }
        },
        "15": {
          start: {
            line: 58,
            column: 2
          },
          end: {
            line: 61,
            column: 3
          }
        },
        "16": {
          start: {
            line: 59,
            column: 4
          },
          end: {
            line: 59,
            column: 46
          }
        },
        "17": {
          start: {
            line: 60,
            column: 4
          },
          end: {
            line: 60,
            column: 11
          }
        },
        "18": {
          start: {
            line: 63,
            column: 2
          },
          end: {
            line: 63,
            column: 45
          }
        },
        "19": {
          start: {
            line: 65,
            column: 2
          },
          end: {
            line: 86,
            column: 4
          }
        },
        "20": {
          start: {
            line: 69,
            column: 25
          },
          end: {
            line: 69,
            column: 51
          }
        },
        "21": {
          start: {
            line: 69,
            column: 31
          },
          end: {
            line: 69,
            column: 51
          }
        },
        "22": {
          start: {
            line: 75,
            column: 4
          },
          end: {
            line: 75,
            column: 37
          }
        },
        "23": {
          start: {
            line: 77,
            column: 4
          },
          end: {
            line: 85,
            column: 21
          }
        },
        "24": {
          start: {
            line: 78,
            column: 6
          },
          end: {
            line: 78,
            column: 20
          }
        },
        "25": {
          start: {
            line: 79,
            column: 6
          },
          end: {
            line: 79,
            column: 39
          }
        },
        "26": {
          start: {
            line: 80,
            column: 6
          },
          end: {
            line: 82,
            column: 9
          }
        },
        "27": {
          start: {
            line: 81,
            column: 8
          },
          end: {
            line: 81,
            column: 47
          }
        },
        "28": {
          start: {
            line: 84,
            column: 6
          },
          end: {
            line: 84,
            column: 33
          }
        },
        "29": {
          start: {
            line: 88,
            column: 2
          },
          end: {
            line: 90,
            column: 4
          }
        },
        "30": {
          start: {
            line: 89,
            column: 4
          },
          end: {
            line: 89,
            column: 44
          }
        },
        "31": {
          start: {
            line: 92,
            column: 2
          },
          end: {
            line: 92,
            column: 60
          }
        },
        "32": {
          start: {
            line: 93,
            column: 2
          },
          end: {
            line: 93,
            column: 83
          }
        },
        "33": {
          start: {
            line: 103,
            column: 18
          },
          end: {
            line: 105,
            column: 1
          }
        },
        "34": {
          start: {
            line: 104,
            column: 2
          },
          end: {
            line: 104,
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
              line: 39,
              column: 1
            }
          },
          line: 21
        },
        "2": {
          name: "(anonymous_2)",
          decl: {
            start: {
              line: 53,
              column: 14
            },
            end: {
              line: 53,
              column: 15
            }
          },
          loc: {
            start: {
              line: 53,
              column: 33
            },
            end: {
              line: 94,
              column: 1
            }
          },
          line: 53
        },
        "3": {
          name: "(anonymous_3)",
          decl: {
            start: {
              line: 65,
              column: 41
            },
            end: {
              line: 65,
              column: 42
            }
          },
          loc: {
            start: {
              line: 65,
              column: 52
            },
            end: {
              line: 86,
              column: 3
            }
          },
          line: 65
        },
        "4": {
          name: "(anonymous_4)",
          decl: {
            start: {
              line: 69,
              column: 25
            },
            end: {
              line: 69,
              column: 26
            }
          },
          loc: {
            start: {
              line: 69,
              column: 31
            },
            end: {
              line: 69,
              column: 51
            }
          },
          line: 69
        },
        "5": {
          name: "(anonymous_5)",
          decl: {
            start: {
              line: 77,
              column: 61
            },
            end: {
              line: 77,
              column: 62
            }
          },
          loc: {
            start: {
              line: 77,
              column: 67
            },
            end: {
              line: 85,
              column: 5
            }
          },
          line: 77
        },
        "6": {
          name: "(anonymous_6)",
          decl: {
            start: {
              line: 80,
              column: 30
            },
            end: {
              line: 80,
              column: 31
            }
          },
          loc: {
            start: {
              line: 80,
              column: 41
            },
            end: {
              line: 82,
              column: 7
            }
          },
          line: 80
        },
        "7": {
          name: "(anonymous_7)",
          decl: {
            start: {
              line: 88,
              column: 53
            },
            end: {
              line: 88,
              column: 54
            }
          },
          loc: {
            start: {
              line: 88,
              column: 64
            },
            end: {
              line: 90,
              column: 3
            }
          },
          line: 88
        },
        "8": {
          name: "(anonymous_8)",
          decl: {
            start: {
              line: 103,
              column: 18
            },
            end: {
              line: 103,
              column: 19
            }
          },
          loc: {
            start: {
              line: 103,
              column: 26
            },
            end: {
              line: 105,
              column: 1
            }
          },
          line: 103
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
              line: 32,
              column: 2
            },
            end: {
              line: 34,
              column: 3
            }
          },
          type: "if",
          locations: [{
            start: {
              line: 32,
              column: 2
            },
            end: {
              line: 34,
              column: 3
            }
          }, {
            start: {
              line: 32,
              column: 2
            },
            end: {
              line: 34,
              column: 3
            }
          }],
          line: 32
        },
        "4": {
          loc: {
            start: {
              line: 58,
              column: 2
            },
            end: {
              line: 61,
              column: 3
            }
          },
          type: "if",
          locations: [{
            start: {
              line: 58,
              column: 2
            },
            end: {
              line: 61,
              column: 3
            }
          }, {
            start: {
              line: 58,
              column: 2
            },
            end: {
              line: 61,
              column: 3
            }
          }],
          line: 58
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
        "34": 0
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
        "8": 0
      },
      b: {
        "0": [0, 0, 0, 0],
        "1": [0, 0],
        "2": [0, 0],
        "3": [0, 0],
        "4": [0, 0]
      },
      _coverageSchema: "1a1c01bbd47fc00a2c39e90264f33305004495a9",
      hash: "aa91c92b29ff730bd2c5c93c0bee707f838b0b75"
    };
    var coverage = global[gcv] || (global[gcv] = {});

    if (!coverage[path] || coverage[path].hash !== hash) {
      coverage[path] = coverageData;
    }

    var actualCoverage = coverage[path];
    {
      // @ts-ignore
      cov_2hh5kxylu4 = function () {
        return actualCoverage;
      };
    }
    return actualCoverage;
  }

  cov_2hh5kxylu4();
  cov_2hh5kxylu4().s[0]++;
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
    cov_2hh5kxylu4().f[0]++;
    cov_2hh5kxylu4().s[1]++;
    return (cov_2hh5kxylu4().b[0][0]++, typeof s === 'number') && (cov_2hh5kxylu4().b[0][1]++, !isNaN(s)) && (cov_2hh5kxylu4().b[0][2]++, s >= 0) && (cov_2hh5kxylu4().b[0][3]++, s < Infinity);
  };
  /**
  * Resets the auto-advance behavior of a player.
  *
  * @param {Player} player
  *        The player to reset the behavior on
  */


  cov_2hh5kxylu4().s[2]++;

  let reset = player => {
    cov_2hh5kxylu4().f[1]++;
    const aa = (cov_2hh5kxylu4().s[3]++, player.playlist.autoadvance_);
    cov_2hh5kxylu4().s[4]++;

    if (aa.timeout) {
      cov_2hh5kxylu4().b[1][0]++;
      cov_2hh5kxylu4().s[5]++;
      player.clearTimeout(aa.timeout);
    } else {
      cov_2hh5kxylu4().b[1][1]++;
    }

    cov_2hh5kxylu4().s[6]++;

    if (aa.trigger) {
      cov_2hh5kxylu4().b[2][0]++;
      cov_2hh5kxylu4().s[7]++;
      player.off('ended', aa.trigger);
    } else {
      cov_2hh5kxylu4().b[2][1]++;
    }

    cov_2hh5kxylu4().s[8]++;

    if (aa.abortOrErrorHandler) {
      cov_2hh5kxylu4().b[3][0]++;
      cov_2hh5kxylu4().s[9]++;
      player.off(['abort', 'error'], aa.abortOrErrorHandler);
    } else {
      cov_2hh5kxylu4().b[3][1]++;
    }

    cov_2hh5kxylu4().s[10]++;
    aa.timeout = null;
    cov_2hh5kxylu4().s[11]++;
    aa.trigger = null;
    cov_2hh5kxylu4().s[12]++;
    aa.abortOrErrorHandler = null;
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


  cov_2hh5kxylu4().s[13]++;

  const setup = (player, delay) => {
    cov_2hh5kxylu4().f[2]++;
    cov_2hh5kxylu4().s[14]++;
    reset(player); // Before queuing up new auto-advance behavior, check if `seconds` was
    // called with a valid value.

    cov_2hh5kxylu4().s[15]++;

    if (!validSeconds(delay)) {
      cov_2hh5kxylu4().b[4][0]++;
      cov_2hh5kxylu4().s[16]++;
      player.playlist.autoadvance_.delay = null;
      cov_2hh5kxylu4().s[17]++;
      return;
    } else {
      cov_2hh5kxylu4().b[4][1]++;
    }

    cov_2hh5kxylu4().s[18]++;
    player.playlist.autoadvance_.delay = delay;
    cov_2hh5kxylu4().s[19]++;

    player.playlist.autoadvance_.trigger = function () {
      cov_2hh5kxylu4().f[3]++;
      cov_2hh5kxylu4().s[20]++; // This calls setup again, which will reset the existing auto-advance and
      // set up another auto-advance for the next "ended" event.

      const cancelOnPlay = () => {
        cov_2hh5kxylu4().f[4]++;
        cov_2hh5kxylu4().s[21]++;
        return setup(player, delay);
      }; // If there is a "play" event while we're waiting for an auto-advance,
      // we need to cancel the auto-advance. This could mean the user seeked
      // back into the content or restarted the content. This is reproducible
      // with an auto-advance > 0.


      cov_2hh5kxylu4().s[22]++;
      player.one('play', cancelOnPlay);
      cov_2hh5kxylu4().s[23]++;
      player.playlist.autoadvance_.timeout = player.setTimeout(() => {
        cov_2hh5kxylu4().f[5]++;
        cov_2hh5kxylu4().s[24]++;
        reset(player);
        cov_2hh5kxylu4().s[25]++;
        player.off('play', cancelOnPlay);
        cov_2hh5kxylu4().s[26]++;
        player.one('loadstart', function () {
          cov_2hh5kxylu4().f[6]++;
          cov_2hh5kxylu4().s[27]++;
          player.playlist.isAutoadvancing = true;
        }); // Poster should be suppressed when auto-advancing

        cov_2hh5kxylu4().s[28]++;
        player.playlist.next(true);
      }, delay * 1000);
    };

    cov_2hh5kxylu4().s[29]++;

    player.playlist.autoadvance_.abortOrErrorHandler = function () {
      cov_2hh5kxylu4().f[7]++;
      cov_2hh5kxylu4().s[30]++;
      player.playlist.isAutoadvancing = false;
    };

    cov_2hh5kxylu4().s[31]++;
    player.one('ended', player.playlist.autoadvance_.trigger);
    cov_2hh5kxylu4().s[32]++;
    player.one(['abort', 'error'], player.playlist.autoadvance_.abortOrErrorHandler);
  };
  /**
  * Used to change the reset function in this module at runtime
  * This should only be used in tests.
  *
  * @param {Function} fn
  *        The function to se the reset to
  */


  cov_2hh5kxylu4().s[33]++;

  const setReset_ = fn => {
    cov_2hh5kxylu4().f[8]++;
    cov_2hh5kxylu4().s[34]++;
    reset = fn;
  };

  function cov_1sxhcryusd() {
    var path = "/Users/bzizmond/Documents/projects/bc/videojs-playlist/src/play-item.js";
    var hash = "a3724ae16d6170869795c08496bf5979f490cde5";
    var global = new Function("return this")();
    var gcv = "__coverage__";
    var coverageData = {
      path: "/Users/bzizmond/Documents/projects/bc/videojs-playlist/src/play-item.js",
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
            line: 34,
            column: 17
          },
          end: {
            line: 75,
            column: 1
          }
        },
        "6": {
          start: {
            line: 35,
            column: 17
          },
          end: {
            line: 35,
            column: 51
          }
        },
        "7": {
          start: {
            line: 36,
            column: 24
          },
          end: {
            line: 40,
            column: 3
          }
        },
        "8": {
          start: {
            line: 37,
            column: 4
          },
          end: {
            line: 39,
            column: 5
          }
        },
        "9": {
          start: {
            line: 38,
            column: 6
          },
          end: {
            line: 38,
            column: 39
          }
        },
        "10": {
          start: {
            line: 42,
            column: 2
          },
          end: {
            line: 42,
            column: 67
          }
        },
        "11": {
          start: {
            line: 44,
            column: 2
          },
          end: {
            line: 46,
            column: 3
          }
        },
        "12": {
          start: {
            line: 45,
            column: 4
          },
          end: {
            line: 45,
            column: 66
          }
        },
        "13": {
          start: {
            line: 48,
            column: 2
          },
          end: {
            line: 48,
            column: 57
          }
        },
        "14": {
          start: {
            line: 50,
            column: 2
          },
          end: {
            line: 50,
            column: 53
          }
        },
        "15": {
          start: {
            line: 51,
            column: 2
          },
          end: {
            line: 51,
            column: 53
          }
        },
        "16": {
          start: {
            line: 53,
            column: 2
          },
          end: {
            line: 53,
            column: 27
          }
        },
        "17": {
          start: {
            line: 54,
            column: 2
          },
          end: {
            line: 54,
            column: 22
          }
        },
        "18": {
          start: {
            line: 56,
            column: 2
          },
          end: {
            line: 72,
            column: 5
          }
        },
        "19": {
          start: {
            line: 58,
            column: 4
          },
          end: {
            line: 58,
            column: 76
          }
        },
        "20": {
          start: {
            line: 59,
            column: 4
          },
          end: {
            line: 59,
            column: 63
          }
        },
        "21": {
          start: {
            line: 61,
            column: 4
          },
          end: {
            line: 69,
            column: 5
          }
        },
        "22": {
          start: {
            line: 62,
            column: 26
          },
          end: {
            line: 62,
            column: 39
          }
        },
        "23": {
          start: {
            line: 66,
            column: 6
          },
          end: {
            line: 68,
            column: 7
          }
        },
        "24": {
          start: {
            line: 67,
            column: 8
          },
          end: {
            line: 67,
            column: 42
          }
        },
        "25": {
          start: {
            line: 71,
            column: 4
          },
          end: {
            line: 71,
            column: 54
          }
        },
        "26": {
          start: {
            line: 74,
            column: 2
          },
          end: {
            line: 74,
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
              line: 34,
              column: 17
            },
            end: {
              line: 34,
              column: 18
            }
          },
          loc: {
            start: {
              line: 34,
              column: 59
            },
            end: {
              line: 75,
              column: 1
            }
          },
          line: 34
        },
        "2": {
          name: "(anonymous_2)",
          decl: {
            start: {
              line: 36,
              column: 24
            },
            end: {
              line: 36,
              column: 25
            }
          },
          loc: {
            start: {
              line: 36,
              column: 30
            },
            end: {
              line: 40,
              column: 3
            }
          },
          line: 36
        },
        "3": {
          name: "(anonymous_3)",
          decl: {
            start: {
              line: 56,
              column: 15
            },
            end: {
              line: 56,
              column: 16
            }
          },
          loc: {
            start: {
              line: 56,
              column: 21
            },
            end: {
              line: 72,
              column: 3
            }
          },
          line: 56
        },
        "4": {
          name: "(anonymous_4)",
          decl: {
            start: {
              line: 67,
              column: 31
            },
            end: {
              line: 67,
              column: 32
            }
          },
          loc: {
            start: {
              line: 67,
              column: 38
            },
            end: {
              line: 67,
              column: 40
            }
          },
          line: 67
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
              line: 34,
              column: 32
            },
            end: {
              line: 34,
              column: 54
            }
          },
          type: "default-arg",
          locations: [{
            start: {
              line: 34,
              column: 49
            },
            end: {
              line: 34,
              column: 54
            }
          }],
          line: 34
        },
        "2": {
          loc: {
            start: {
              line: 35,
              column: 17
            },
            end: {
              line: 35,
              column: 51
            }
          },
          type: "binary-expr",
          locations: [{
            start: {
              line: 35,
              column: 17
            },
            end: {
              line: 35,
              column: 33
            }
          }, {
            start: {
              line: 35,
              column: 37
            },
            end: {
              line: 35,
              column: 51
            }
          }],
          line: 35
        },
        "3": {
          loc: {
            start: {
              line: 37,
              column: 4
            },
            end: {
              line: 39,
              column: 5
            }
          },
          type: "if",
          locations: [{
            start: {
              line: 37,
              column: 4
            },
            end: {
              line: 39,
              column: 5
            }
          }, {
            start: {
              line: 37,
              column: 4
            },
            end: {
              line: 39,
              column: 5
            }
          }],
          line: 37
        },
        "4": {
          loc: {
            start: {
              line: 38,
              column: 20
            },
            end: {
              line: 38,
              column: 37
            }
          },
          type: "binary-expr",
          locations: [{
            start: {
              line: 38,
              column: 20
            },
            end: {
              line: 38,
              column: 31
            }
          }, {
            start: {
              line: 38,
              column: 35
            },
            end: {
              line: 38,
              column: 37
            }
          }],
          line: 38
        },
        "5": {
          loc: {
            start: {
              line: 42,
              column: 39
            },
            end: {
              line: 42,
              column: 65
            }
          },
          type: "binary-expr",
          locations: [{
            start: {
              line: 42,
              column: 39
            },
            end: {
              line: 42,
              column: 57
            }
          }, {
            start: {
              line: 42,
              column: 61
            },
            end: {
              line: 42,
              column: 65
            }
          }],
          line: 42
        },
        "6": {
          loc: {
            start: {
              line: 44,
              column: 2
            },
            end: {
              line: 46,
              column: 3
            }
          },
          type: "if",
          locations: [{
            start: {
              line: 44,
              column: 2
            },
            end: {
              line: 46,
              column: 3
            }
          }, {
            start: {
              line: 44,
              column: 2
            },
            end: {
              line: 46,
              column: 3
            }
          }],
          line: 44
        },
        "7": {
          loc: {
            start: {
              line: 48,
              column: 16
            },
            end: {
              line: 48,
              column: 55
            }
          },
          type: "cond-expr",
          locations: [{
            start: {
              line: 48,
              column: 33
            },
            end: {
              line: 48,
              column: 35
            }
          }, {
            start: {
              line: 48,
              column: 38
            },
            end: {
              line: 48,
              column: 55
            }
          }],
          line: 48
        },
        "8": {
          loc: {
            start: {
              line: 48,
              column: 38
            },
            end: {
              line: 48,
              column: 55
            }
          },
          type: "binary-expr",
          locations: [{
            start: {
              line: 48,
              column: 38
            },
            end: {
              line: 48,
              column: 49
            }
          }, {
            start: {
              line: 48,
              column: 53
            },
            end: {
              line: 48,
              column: 55
            }
          }],
          line: 48
        },
        "9": {
          loc: {
            start: {
              line: 58,
              column: 5
            },
            end: {
              line: 58,
              column: 26
            }
          },
          type: "binary-expr",
          locations: [{
            start: {
              line: 58,
              column: 5
            },
            end: {
              line: 58,
              column: 20
            }
          }, {
            start: {
              line: 58,
              column: 24
            },
            end: {
              line: 58,
              column: 26
            }
          }],
          line: 58
        },
        "10": {
          loc: {
            start: {
              line: 59,
              column: 35
            },
            end: {
              line: 59,
              column: 61
            }
          },
          type: "binary-expr",
          locations: [{
            start: {
              line: 59,
              column: 35
            },
            end: {
              line: 59,
              column: 53
            }
          }, {
            start: {
              line: 59,
              column: 57
            },
            end: {
              line: 59,
              column: 61
            }
          }],
          line: 59
        },
        "11": {
          loc: {
            start: {
              line: 61,
              column: 4
            },
            end: {
              line: 69,
              column: 5
            }
          },
          type: "if",
          locations: [{
            start: {
              line: 61,
              column: 4
            },
            end: {
              line: 69,
              column: 5
            }
          }, {
            start: {
              line: 61,
              column: 4
            },
            end: {
              line: 69,
              column: 5
            }
          }],
          line: 61
        },
        "12": {
          loc: {
            start: {
              line: 66,
              column: 6
            },
            end: {
              line: 68,
              column: 7
            }
          },
          type: "if",
          locations: [{
            start: {
              line: 66,
              column: 6
            },
            end: {
              line: 68,
              column: 7
            }
          }, {
            start: {
              line: 66,
              column: 6
            },
            end: {
              line: 68,
              column: 7
            }
          }],
          line: 66
        },
        "13": {
          loc: {
            start: {
              line: 66,
              column: 10
            },
            end: {
              line: 66,
              column: 86
            }
          },
          type: "binary-expr",
          locations: [{
            start: {
              line: 66,
              column: 10
            },
            end: {
              line: 66,
              column: 44
            }
          }, {
            start: {
              line: 66,
              column: 48
            },
            end: {
              line: 66,
              column: 86
            }
          }],
          line: 66
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
        "4": 0
      },
      b: {
        "0": [0, 0, 0],
        "1": [0],
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
        "13": [0, 0]
      },
      _coverageSchema: "1a1c01bbd47fc00a2c39e90264f33305004495a9",
      hash: "a3724ae16d6170869795c08496bf5979f490cde5"
    };
    var coverage = global[gcv] || (global[gcv] = {});

    if (!coverage[path] || coverage[path].hash !== hash) {
      coverage[path] = coverageData;
    }

    var actualCoverage = coverage[path];
    {
      // @ts-ignore
      cov_1sxhcryusd = function () {
        return actualCoverage;
      };
    }
    return actualCoverage;
  }

  cov_1sxhcryusd();
  /**
  * Removes all remote text tracks from a player.
  *
  * @param  {Player} player
  *         The player to clear tracks on
  */

  cov_1sxhcryusd().s[0]++;

  const clearTracks = player => {
    cov_1sxhcryusd().f[0]++;
    const tracks = (cov_1sxhcryusd().s[1]++, player.remoteTextTracks());
    let i = (cov_1sxhcryusd().s[2]++, (cov_1sxhcryusd().b[0][0]++, tracks) && (cov_1sxhcryusd().b[0][1]++, tracks.length) || (cov_1sxhcryusd().b[0][2]++, 0)); // This uses a `while` loop rather than `forEach` because the
    // `TextTrackList` object is a live DOM list (not an array).

    cov_1sxhcryusd().s[3]++;

    while (i--) {
      cov_1sxhcryusd().s[4]++;
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
  * @param {boolean} [suppressPoster]
  *         Should the native poster be suppressed? Defaults to false.
  *
  * @return {Player}
  *         The player that is now playing the item
  */


  cov_1sxhcryusd().s[5]++;

  const playItem = (player, item, suppressPoster = (cov_1sxhcryusd().b[1][0]++, false)) => {
    cov_1sxhcryusd().f[1]++;
    const replay = (cov_1sxhcryusd().s[6]++, (cov_1sxhcryusd().b[2][0]++, !player.paused()) || (cov_1sxhcryusd().b[2][1]++, player.ended()));
    cov_1sxhcryusd().s[7]++;

    const displayPoster = () => {
      cov_1sxhcryusd().f[2]++;
      cov_1sxhcryusd().s[8]++;

      if (player.audioPosterMode()) {
        cov_1sxhcryusd().b[3][0]++;
        cov_1sxhcryusd().s[9]++;
        player.poster((cov_1sxhcryusd().b[4][0]++, item.poster) || (cov_1sxhcryusd().b[4][1]++, ''));
      } else {
        cov_1sxhcryusd().b[3][1]++;
      }
    };

    cov_1sxhcryusd().s[10]++;
    player.trigger('beforeplaylistitem', (cov_1sxhcryusd().b[5][0]++, item.originalValue) || (cov_1sxhcryusd().b[5][1]++, item));
    cov_1sxhcryusd().s[11]++;

    if (item.playlistItemId_) {
      cov_1sxhcryusd().b[6][0]++;
      cov_1sxhcryusd().s[12]++;
      player.playlist.currentPlaylistItemId_ = item.playlistItemId_;
    } else {
      cov_1sxhcryusd().b[6][1]++;
    }

    cov_1sxhcryusd().s[13]++;
    player.poster(suppressPoster ? (cov_1sxhcryusd().b[7][0]++, '') : (cov_1sxhcryusd().b[7][1]++, (cov_1sxhcryusd().b[8][0]++, item.poster) || (cov_1sxhcryusd().b[8][1]++, '')));
    cov_1sxhcryusd().s[14]++;
    player.off('audiopostermodechange', displayPoster);
    cov_1sxhcryusd().s[15]++;
    player.one('audiopostermodechange', displayPoster);
    cov_1sxhcryusd().s[16]++;
    player.src(item.sources);
    cov_1sxhcryusd().s[17]++;
    clearTracks(player);
    cov_1sxhcryusd().s[18]++;
    player.ready(() => {
      cov_1sxhcryusd().f[3]++;
      cov_1sxhcryusd().s[19]++;
      ((cov_1sxhcryusd().b[9][0]++, item.textTracks) || (cov_1sxhcryusd().b[9][1]++, [])).forEach(player.addRemoteTextTrack.bind(player));
      cov_1sxhcryusd().s[20]++;
      player.trigger('playlistitem', (cov_1sxhcryusd().b[10][0]++, item.originalValue) || (cov_1sxhcryusd().b[10][1]++, item));
      cov_1sxhcryusd().s[21]++;

      if (replay) {
        cov_1sxhcryusd().b[11][0]++;
        const playPromise = (cov_1sxhcryusd().s[22]++, player.play()); // silence error when a pause interrupts a play request
        // on browsers which return a promise

        cov_1sxhcryusd().s[23]++;

        if ((cov_1sxhcryusd().b[13][0]++, typeof playPromise !== 'undefined') && (cov_1sxhcryusd().b[13][1]++, typeof playPromise.then === 'function')) {
          cov_1sxhcryusd().b[12][0]++;
          cov_1sxhcryusd().s[24]++;
          playPromise.then(null, e => {
            cov_1sxhcryusd().f[4]++;
          });
        } else {
          cov_1sxhcryusd().b[12][1]++;
        }
      } else {
        cov_1sxhcryusd().b[11][1]++;
      }

      cov_1sxhcryusd().s[25]++;
      setup(player, player.playlist.autoadvance_.delay);
    });
    cov_1sxhcryusd().s[26]++;
    return player;
  };

  function cov_3xfj46l7x() {
    var path = "/Users/bzizmond/Documents/projects/bc/videojs-playlist/src/playlist-maker.js";
    var hash = "629936ec446d2feec4ce23e4f10f0a278477c5dd";
    var global = new Function("return this")();
    var gcv = "__coverage__";
    var coverageData = {
      path: "/Users/bzizmond/Documents/projects/bc/videojs-playlist/src/playlist-maker.js",
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
            line: 299,
            column: 2
          },
          end: {
            line: 348,
            column: 4
          }
        },
        "75": {
          start: {
            line: 301,
            column: 4
          },
          end: {
            line: 303,
            column: 5
          }
        },
        "76": {
          start: {
            line: 302,
            column: 6
          },
          end: {
            line: 302,
            column: 36
          }
        },
        "77": {
          start: {
            line: 306,
            column: 4
          },
          end: {
            line: 319,
            column: 5
          }
        },
        "78": {
          start: {
            line: 312,
            column: 6
          },
          end: {
            line: 312,
            column: 37
          }
        },
        "79": {
          start: {
            line: 313,
            column: 6
          },
          end: {
            line: 317,
            column: 8
          }
        },
        "80": {
          start: {
            line: 318,
            column: 6
          },
          end: {
            line: 318,
            column: 36
          }
        },
        "81": {
          start: {
            line: 321,
            column: 16
          },
          end: {
            line: 321,
            column: 51
          }
        },
        "82": {
          start: {
            line: 328,
            column: 4
          },
          end: {
            line: 341,
            column: 5
          }
        },
        "83": {
          start: {
            line: 329,
            column: 29
          },
          end: {
            line: 329,
            column: 90
          }
        },
        "84": {
          start: {
            line: 330,
            column: 19
          },
          end: {
            line: 330,
            column: 39
          }
        },
        "85": {
          start: {
            line: 333,
            column: 6
          },
          end: {
            line: 336,
            column: 7
          }
        },
        "86": {
          start: {
            line: 334,
            column: 8
          },
          end: {
            line: 334,
            column: 48
          }
        },
        "87": {
          start: {
            line: 335,
            column: 8
          },
          end: {
            line: 335,
            column: 38
          }
        },
        "88": {
          start: {
            line: 340,
            column: 6
          },
          end: {
            line: 340,
            column: 45
          }
        },
        "89": {
          start: {
            line: 345,
            column: 4
          },
          end: {
            line: 345,
            column: 51
          }
        },
        "90": {
          start: {
            line: 347,
            column: 4
          },
          end: {
            line: 347,
            column: 34
          }
        },
        "91": {
          start: {
            line: 401,
            column: 2
          },
          end: {
            line: 417,
            column: 4
          }
        },
        "92": {
          start: {
            line: 402,
            column: 4
          },
          end: {
            line: 404,
            column: 5
          }
        },
        "93": {
          start: {
            line: 403,
            column: 6
          },
          end: {
            line: 403,
            column: 77
          }
        },
        "94": {
          start: {
            line: 405,
            column: 4
          },
          end: {
            line: 407,
            column: 5
          }
        },
        "95": {
          start: {
            line: 406,
            column: 6
          },
          end: {
            line: 406,
            column: 26
          }
        },
        "96": {
          start: {
            line: 408,
            column: 4
          },
          end: {
            line: 410,
            column: 5
          }
        },
        "97": {
          start: {
            line: 409,
            column: 6
          },
          end: {
            line: 409,
            column: 22
          }
        },
        "98": {
          start: {
            line: 411,
            column: 4
          },
          end: {
            line: 411,
            column: 58
          }
        },
        "99": {
          start: {
            line: 415,
            column: 4
          },
          end: {
            line: 415,
            column: 60
          }
        },
        "100": {
          start: {
            line: 416,
            column: 4
          },
          end: {
            line: 416,
            column: 70
          }
        },
        "101": {
          start: {
            line: 435,
            column: 2
          },
          end: {
            line: 448,
            column: 4
          }
        },
        "102": {
          start: {
            line: 436,
            column: 4
          },
          end: {
            line: 438,
            column: 5
          }
        },
        "103": {
          start: {
            line: 437,
            column: 6
          },
          end: {
            line: 437,
            column: 77
          }
        },
        "104": {
          start: {
            line: 439,
            column: 4
          },
          end: {
            line: 441,
            column: 5
          }
        },
        "105": {
          start: {
            line: 440,
            column: 6
          },
          end: {
            line: 440,
            column: 13
          }
        },
        "106": {
          start: {
            line: 442,
            column: 4
          },
          end: {
            line: 442,
            column: 30
          }
        },
        "107": {
          start: {
            line: 446,
            column: 4
          },
          end: {
            line: 446,
            column: 63
          }
        },
        "108": {
          start: {
            line: 447,
            column: 4
          },
          end: {
            line: 447,
            column: 59
          }
        },
        "109": {
          start: {
            line: 459,
            column: 2
          },
          end: {
            line: 461,
            column: 4
          }
        },
        "110": {
          start: {
            line: 460,
            column: 4
          },
          end: {
            line: 460,
            column: 42
          }
        },
        "111": {
          start: {
            line: 472,
            column: 2
          },
          end: {
            line: 490,
            column: 4
          }
        },
        "112": {
          start: {
            line: 473,
            column: 4
          },
          end: {
            line: 475,
            column: 5
          }
        },
        "113": {
          start: {
            line: 474,
            column: 6
          },
          end: {
            line: 474,
            column: 41
          }
        },
        "114": {
          start: {
            line: 477,
            column: 20
          },
          end: {
            line: 477,
            column: 64
          }
        },
        "115": {
          start: {
            line: 479,
            column: 4
          },
          end: {
            line: 487,
            column: 5
          }
        },
        "116": {
          start: {
            line: 479,
            column: 17
          },
          end: {
            line: 479,
            column: 18
          }
        },
        "117": {
          start: {
            line: 480,
            column: 21
          },
          end: {
            line: 480,
            column: 31
          }
        },
        "118": {
          start: {
            line: 482,
            column: 6
          },
          end: {
            line: 486,
            column: 7
          }
        },
        "119": {
          start: {
            line: 483,
            column: 8
          },
          end: {
            line: 483,
            column: 44
          }
        },
        "120": {
          start: {
            line: 484,
            column: 13
          },
          end: {
            line: 486,
            column: 7
          }
        },
        "121": {
          start: {
            line: 485,
            column: 8
          },
          end: {
            line: 485,
            column: 48
          }
        },
        "122": {
          start: {
            line: 489,
            column: 4
          },
          end: {
            line: 489,
            column: 14
          }
        },
        "123": {
          start: {
            line: 499,
            column: 2
          },
          end: {
            line: 499,
            column: 55
          }
        },
        "124": {
          start: {
            line: 499,
            column: 32
          },
          end: {
            line: 499,
            column: 54
          }
        },
        "125": {
          start: {
            line: 508,
            column: 2
          },
          end: {
            line: 508,
            column: 45
          }
        },
        "126": {
          start: {
            line: 508,
            column: 29
          },
          end: {
            line: 508,
            column: 44
          }
        },
        "127": {
          start: {
            line: 517,
            column: 2
          },
          end: {
            line: 533,
            column: 4
          }
        },
        "128": {
          start: {
            line: 518,
            column: 20
          },
          end: {
            line: 518,
            column: 42
          }
        },
        "129": {
          start: {
            line: 520,
            column: 4
          },
          end: {
            line: 522,
            column: 5
          }
        },
        "130": {
          start: {
            line: 521,
            column: 6
          },
          end: {
            line: 521,
            column: 16
          }
        },
        "131": {
          start: {
            line: 524,
            column: 22
          },
          end: {
            line: 524,
            column: 42
          }
        },
        "132": {
          start: {
            line: 527,
            column: 4
          },
          end: {
            line: 529,
            column: 5
          }
        },
        "133": {
          start: {
            line: 528,
            column: 6
          },
          end: {
            line: 528,
            column: 15
          }
        },
        "134": {
          start: {
            line: 532,
            column: 4
          },
          end: {
            line: 532,
            column: 44
          }
        },
        "135": {
          start: {
            line: 542,
            column: 2
          },
          end: {
            line: 556,
            column: 4
          }
        },
        "136": {
          start: {
            line: 543,
            column: 20
          },
          end: {
            line: 543,
            column: 42
          }
        },
        "137": {
          start: {
            line: 545,
            column: 4
          },
          end: {
            line: 547,
            column: 5
          }
        },
        "138": {
          start: {
            line: 546,
            column: 6
          },
          end: {
            line: 546,
            column: 16
          }
        },
        "139": {
          start: {
            line: 550,
            column: 4
          },
          end: {
            line: 552,
            column: 5
          }
        },
        "140": {
          start: {
            line: 551,
            column: 6
          },
          end: {
            line: 551,
            column: 34
          }
        },
        "141": {
          start: {
            line: 555,
            column: 4
          },
          end: {
            line: 555,
            column: 36
          }
        },
        "142": {
          start: {
            line: 564,
            column: 2
          },
          end: {
            line: 575,
            column: 4
          }
        },
        "143": {
          start: {
            line: 565,
            column: 4
          },
          end: {
            line: 567,
            column: 5
          }
        },
        "144": {
          start: {
            line: 566,
            column: 6
          },
          end: {
            line: 566,
            column: 13
          }
        },
        "145": {
          start: {
            line: 568,
            column: 20
          },
          end: {
            line: 568,
            column: 43
          }
        },
        "146": {
          start: {
            line: 570,
            column: 4
          },
          end: {
            line: 572,
            column: 5
          }
        },
        "147": {
          start: {
            line: 571,
            column: 6
          },
          end: {
            line: 571,
            column: 58
          }
        },
        "148": {
          start: {
            line: 574,
            column: 4
          },
          end: {
            line: 574,
            column: 32
          }
        },
        "149": {
          start: {
            line: 583,
            column: 2
          },
          end: {
            line: 594,
            column: 4
          }
        },
        "150": {
          start: {
            line: 584,
            column: 4
          },
          end: {
            line: 586,
            column: 5
          }
        },
        "151": {
          start: {
            line: 585,
            column: 6
          },
          end: {
            line: 585,
            column: 13
          }
        },
        "152": {
          start: {
            line: 587,
            column: 20
          },
          end: {
            line: 587,
            column: 62
          }
        },
        "153": {
          start: {
            line: 589,
            column: 4
          },
          end: {
            line: 591,
            column: 5
          }
        },
        "154": {
          start: {
            line: 590,
            column: 6
          },
          end: {
            line: 590,
            column: 58
          }
        },
        "155": {
          start: {
            line: 593,
            column: 4
          },
          end: {
            line: 593,
            column: 32
          }
        },
        "156": {
          start: {
            line: 604,
            column: 2
          },
          end: {
            line: 616,
            column: 4
          }
        },
        "157": {
          start: {
            line: 605,
            column: 4
          },
          end: {
            line: 607,
            column: 5
          }
        },
        "158": {
          start: {
            line: 606,
            column: 6
          },
          end: {
            line: 606,
            column: 13
          }
        },
        "159": {
          start: {
            line: 609,
            column: 18
          },
          end: {
            line: 609,
            column: 38
          }
        },
        "160": {
          start: {
            line: 611,
            column: 4
          },
          end: {
            line: 615,
            column: 5
          }
        },
        "161": {
          start: {
            line: 612,
            column: 22
          },
          end: {
            line: 612,
            column: 65
          }
        },
        "162": {
          start: {
            line: 614,
            column: 6
          },
          end: {
            line: 614,
            column: 58
          }
        },
        "163": {
          start: {
            line: 624,
            column: 2
          },
          end: {
            line: 636,
            column: 4
          }
        },
        "164": {
          start: {
            line: 625,
            column: 4
          },
          end: {
            line: 627,
            column: 5
          }
        },
        "165": {
          start: {
            line: 626,
            column: 6
          },
          end: {
            line: 626,
            column: 13
          }
        },
        "166": {
          start: {
            line: 629,
            column: 18
          },
          end: {
            line: 629,
            column: 42
          }
        },
        "167": {
          start: {
            line: 631,
            column: 4
          },
          end: {
            line: 635,
            column: 5
          }
        },
        "168": {
          start: {
            line: 632,
            column: 22
          },
          end: {
            line: 632,
            column: 49
          }
        },
        "169": {
          start: {
            line: 634,
            column: 6
          },
          end: {
            line: 634,
            column: 58
          }
        },
        "170": {
          start: {
            line: 644,
            column: 2
          },
          end: {
            line: 646,
            column: 4
          }
        },
        "171": {
          start: {
            line: 645,
            column: 4
          },
          end: {
            line: 645,
            column: 47
          }
        },
        "172": {
          start: {
            line: 658,
            column: 2
          },
          end: {
            line: 670,
            column: 4
          }
        },
        "173": {
          start: {
            line: 659,
            column: 4
          },
          end: {
            line: 661,
            column: 5
          }
        },
        "174": {
          start: {
            line: 660,
            column: 6
          },
          end: {
            line: 660,
            column: 30
          }
        },
        "175": {
          start: {
            line: 663,
            column: 4
          },
          end: {
            line: 666,
            column: 5
          }
        },
        "176": {
          start: {
            line: 664,
            column: 6
          },
          end: {
            line: 664,
            column: 75
          }
        },
        "177": {
          start: {
            line: 665,
            column: 6
          },
          end: {
            line: 665,
            column: 13
          }
        },
        "178": {
          start: {
            line: 668,
            column: 4
          },
          end: {
            line: 668,
            column: 29
          }
        },
        "179": {
          start: {
            line: 669,
            column: 4
          },
          end: {
            line: 669,
            column: 28
          }
        },
        "180": {
          start: {
            line: 681,
            column: 2
          },
          end: {
            line: 702,
            column: 4
          }
        },
        "181": {
          start: {
            line: 684,
            column: 4
          },
          end: {
            line: 686,
            column: 5
          }
        },
        "182": {
          start: {
            line: 685,
            column: 6
          },
          end: {
            line: 685,
            column: 13
          }
        },
        "183": {
          start: {
            line: 688,
            column: 4
          },
          end: {
            line: 688,
            column: 23
          }
        },
        "184": {
          start: {
            line: 691,
            column: 4
          },
          end: {
            line: 693,
            column: 5
          }
        },
        "185": {
          start: {
            line: 692,
            column: 6
          },
          end: {
            line: 692,
            column: 13
          }
        },
        "186": {
          start: {
            line: 701,
            column: 4
          },
          end: {
            line: 701,
            column: 37
          }
        },
        "187": {
          start: {
            line: 710,
            column: 2
          },
          end: {
            line: 731,
            column: 4
          }
        },
        "188": {
          start: {
            line: 713,
            column: 4
          },
          end: {
            line: 715,
            column: 5
          }
        },
        "189": {
          start: {
            line: 714,
            column: 6
          },
          end: {
            line: 714,
            column: 13
          }
        },
        "190": {
          start: {
            line: 717,
            column: 4
          },
          end: {
            line: 717,
            column: 19
          }
        },
        "191": {
          start: {
            line: 720,
            column: 4
          },
          end: {
            line: 722,
            column: 5
          }
        },
        "192": {
          start: {
            line: 721,
            column: 6
          },
          end: {
            line: 721,
            column: 13
          }
        },
        "193": {
          start: {
            line: 730,
            column: 4
          },
          end: {
            line: 730,
            column: 37
          }
        },
        "194": {
          start: {
            line: 751,
            column: 2
          },
          end: {
            line: 787,
            column: 4
          }
        },
        "195": {
          start: {
            line: 752,
            column: 16
          },
          end: {
            line: 752,
            column: 17
          }
        },
        "196": {
          start: {
            line: 753,
            column: 14
          },
          end: {
            line: 753,
            column: 18
          }
        },
        "197": {
          start: {
            line: 757,
            column: 4
          },
          end: {
            line: 760,
            column: 5
          }
        },
        "198": {
          start: {
            line: 758,
            column: 6
          },
          end: {
            line: 758,
            column: 41
          }
        },
        "199": {
          start: {
            line: 759,
            column: 6
          },
          end: {
            line: 759,
            column: 30
          }
        },
        "200": {
          start: {
            line: 763,
            column: 4
          },
          end: {
            line: 765,
            column: 5
          }
        },
        "201": {
          start: {
            line: 764,
            column: 6
          },
          end: {
            line: 764,
            column: 13
          }
        },
        "202": {
          start: {
            line: 767,
            column: 4
          },
          end: {
            line: 767,
            column: 19
          }
        },
        "203": {
          start: {
            line: 771,
            column: 4
          },
          end: {
            line: 773,
            column: 5
          }
        },
        "204": {
          start: {
            line: 772,
            column: 6
          },
          end: {
            line: 772,
            column: 54
          }
        },
        "205": {
          start: {
            line: 776,
            column: 4
          },
          end: {
            line: 778,
            column: 5
          }
        },
        "206": {
          start: {
            line: 777,
            column: 6
          },
          end: {
            line: 777,
            column: 13
          }
        },
        "207": {
          start: {
            line: 786,
            column: 4
          },
          end: {
            line: 786,
            column: 37
          }
        },
        "208": {
          start: {
            line: 790,
            column: 2
          },
          end: {
            line: 796,
            column: 3
          }
        },
        "209": {
          start: {
            line: 791,
            column: 4
          },
          end: {
            line: 791,
            column: 40
          }
        },
        "210": {
          start: {
            line: 795,
            column: 4
          },
          end: {
            line: 795,
            column: 14
          }
        },
        "211": {
          start: {
            line: 798,
            column: 2
          },
          end: {
            line: 798,
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
              line: 799,
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
              line: 299,
              column: 25
            },
            end: {
              line: 299,
              column: 26
            }
          },
          loc: {
            start: {
              line: 299,
              column: 52
            },
            end: {
              line: 348,
              column: 3
            }
          },
          line: 299
        },
        "12": {
          name: "(anonymous_12)",
          decl: {
            start: {
              line: 401,
              column: 17
            },
            end: {
              line: 401,
              column: 18
            }
          },
          loc: {
            start: {
              line: 401,
              column: 35
            },
            end: {
              line: 417,
              column: 3
            }
          },
          line: 401
        },
        "13": {
          name: "(anonymous_13)",
          decl: {
            start: {
              line: 435,
              column: 20
            },
            end: {
              line: 435,
              column: 21
            }
          },
          loc: {
            start: {
              line: 435,
              column: 42
            },
            end: {
              line: 448,
              column: 3
            }
          },
          line: 435
        },
        "14": {
          name: "(anonymous_14)",
          decl: {
            start: {
              line: 459,
              column: 22
            },
            end: {
              line: 459,
              column: 23
            }
          },
          loc: {
            start: {
              line: 459,
              column: 33
            },
            end: {
              line: 461,
              column: 3
            }
          },
          line: 459
        },
        "15": {
          name: "(anonymous_15)",
          decl: {
            start: {
              line: 472,
              column: 21
            },
            end: {
              line: 472,
              column: 22
            }
          },
          loc: {
            start: {
              line: 472,
              column: 32
            },
            end: {
              line: 490,
              column: 3
            }
          },
          line: 472
        },
        "16": {
          name: "(anonymous_16)",
          decl: {
            start: {
              line: 499,
              column: 26
            },
            end: {
              line: 499,
              column: 27
            }
          },
          loc: {
            start: {
              line: 499,
              column: 32
            },
            end: {
              line: 499,
              column: 54
            }
          },
          line: 499
        },
        "17": {
          name: "(anonymous_17)",
          decl: {
            start: {
              line: 508,
              column: 23
            },
            end: {
              line: 508,
              column: 24
            }
          },
          loc: {
            start: {
              line: 508,
              column: 29
            },
            end: {
              line: 508,
              column: 44
            }
          },
          line: 508
        },
        "18": {
          name: "(anonymous_18)",
          decl: {
            start: {
              line: 517,
              column: 23
            },
            end: {
              line: 517,
              column: 24
            }
          },
          loc: {
            start: {
              line: 517,
              column: 29
            },
            end: {
              line: 533,
              column: 3
            }
          },
          line: 517
        },
        "19": {
          name: "(anonymous_19)",
          decl: {
            start: {
              line: 542,
              column: 27
            },
            end: {
              line: 542,
              column: 28
            }
          },
          loc: {
            start: {
              line: 542,
              column: 33
            },
            end: {
              line: 556,
              column: 3
            }
          },
          line: 542
        },
        "20": {
          name: "(anonymous_20)",
          decl: {
            start: {
              line: 564,
              column: 19
            },
            end: {
              line: 564,
              column: 20
            }
          },
          loc: {
            start: {
              line: 564,
              column: 25
            },
            end: {
              line: 575,
              column: 3
            }
          },
          line: 564
        },
        "21": {
          name: "(anonymous_21)",
          decl: {
            start: {
              line: 583,
              column: 18
            },
            end: {
              line: 583,
              column: 19
            }
          },
          loc: {
            start: {
              line: 583,
              column: 24
            },
            end: {
              line: 594,
              column: 3
            }
          },
          line: 583
        },
        "22": {
          name: "(anonymous_22)",
          decl: {
            start: {
              line: 604,
              column: 18
            },
            end: {
              line: 604,
              column: 19
            }
          },
          loc: {
            start: {
              line: 604,
              column: 46
            },
            end: {
              line: 616,
              column: 3
            }
          },
          line: 604
        },
        "23": {
          name: "(anonymous_23)",
          decl: {
            start: {
              line: 624,
              column: 22
            },
            end: {
              line: 624,
              column: 23
            }
          },
          loc: {
            start: {
              line: 624,
              column: 28
            },
            end: {
              line: 636,
              column: 3
            }
          },
          line: 624
        },
        "24": {
          name: "(anonymous_24)",
          decl: {
            start: {
              line: 644,
              column: 25
            },
            end: {
              line: 644,
              column: 26
            }
          },
          loc: {
            start: {
              line: 644,
              column: 36
            },
            end: {
              line: 646,
              column: 3
            }
          },
          line: 644
        },
        "25": {
          name: "(anonymous_25)",
          decl: {
            start: {
              line: 658,
              column: 20
            },
            end: {
              line: 658,
              column: 21
            }
          },
          loc: {
            start: {
              line: 658,
              column: 29
            },
            end: {
              line: 670,
              column: 3
            }
          },
          line: 658
        },
        "26": {
          name: "(anonymous_26)",
          decl: {
            start: {
              line: 681,
              column: 18
            },
            end: {
              line: 681,
              column: 19
            }
          },
          loc: {
            start: {
              line: 681,
              column: 31
            },
            end: {
              line: 702,
              column: 3
            }
          },
          line: 681
        },
        "27": {
          name: "(anonymous_27)",
          decl: {
            start: {
              line: 710,
              column: 21
            },
            end: {
              line: 710,
              column: 22
            }
          },
          loc: {
            start: {
              line: 710,
              column: 27
            },
            end: {
              line: 731,
              column: 3
            }
          },
          line: 710
        },
        "28": {
          name: "(anonymous_28)",
          decl: {
            start: {
              line: 751,
              column: 21
            },
            end: {
              line: 751,
              column: 22
            }
          },
          loc: {
            start: {
              line: 751,
              column: 38
            },
            end: {
              line: 787,
              column: 3
            }
          },
          line: 751
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
              line: 301,
              column: 4
            },
            end: {
              line: 303,
              column: 5
            }
          },
          type: "if",
          locations: [{
            start: {
              line: 301,
              column: 4
            },
            end: {
              line: 303,
              column: 5
            }
          }, {
            start: {
              line: 301,
              column: 4
            },
            end: {
              line: 303,
              column: 5
            }
          }],
          line: 301
        },
        "21": {
          loc: {
            start: {
              line: 306,
              column: 4
            },
            end: {
              line: 319,
              column: 5
            }
          },
          type: "if",
          locations: [{
            start: {
              line: 306,
              column: 4
            },
            end: {
              line: 319,
              column: 5
            }
          }, {
            start: {
              line: 306,
              column: 4
            },
            end: {
              line: 319,
              column: 5
            }
          }],
          line: 306
        },
        "22": {
          loc: {
            start: {
              line: 307,
              column: 6
            },
            end: {
              line: 310,
              column: 25
            }
          },
          type: "binary-expr",
          locations: [{
            start: {
              line: 307,
              column: 6
            },
            end: {
              line: 307,
              column: 31
            }
          }, {
            start: {
              line: 308,
              column: 6
            },
            end: {
              line: 308,
              column: 38
            }
          }, {
            start: {
              line: 309,
              column: 6
            },
            end: {
              line: 309,
              column: 16
            }
          }, {
            start: {
              line: 310,
              column: 6
            },
            end: {
              line: 310,
              column: 25
            }
          }],
          line: 307
        },
        "23": {
          loc: {
            start: {
              line: 321,
              column: 16
            },
            end: {
              line: 321,
              column: 51
            }
          },
          type: "binary-expr",
          locations: [{
            start: {
              line: 321,
              column: 16
            },
            end: {
              line: 321,
              column: 45
            }
          }, {
            start: {
              line: 321,
              column: 49
            },
            end: {
              line: 321,
              column: 51
            }
          }],
          line: 321
        },
        "24": {
          loc: {
            start: {
              line: 328,
              column: 4
            },
            end: {
              line: 341,
              column: 5
            }
          },
          type: "if",
          locations: [{
            start: {
              line: 328,
              column: 4
            },
            end: {
              line: 341,
              column: 5
            }
          }, {
            start: {
              line: 328,
              column: 4
            },
            end: {
              line: 341,
              column: 5
            }
          }],
          line: 328
        },
        "25": {
          loc: {
            start: {
              line: 333,
              column: 6
            },
            end: {
              line: 336,
              column: 7
            }
          },
          type: "if",
          locations: [{
            start: {
              line: 333,
              column: 6
            },
            end: {
              line: 336,
              column: 7
            }
          }, {
            start: {
              line: 333,
              column: 6
            },
            end: {
              line: 336,
              column: 7
            }
          }],
          line: 333
        },
        "26": {
          loc: {
            start: {
              line: 333,
              column: 10
            },
            end: {
              line: 333,
              column: 81
            }
          },
          type: "binary-expr",
          locations: [{
            start: {
              line: 333,
              column: 10
            },
            end: {
              line: 333,
              column: 14
            }
          }, {
            start: {
              line: 333,
              column: 18
            },
            end: {
              line: 333,
              column: 45
            }
          }, {
            start: {
              line: 333,
              column: 49
            },
            end: {
              line: 333,
              column: 81
            }
          }],
          line: 333
        },
        "27": {
          loc: {
            start: {
              line: 402,
              column: 4
            },
            end: {
              line: 404,
              column: 5
            }
          },
          type: "if",
          locations: [{
            start: {
              line: 402,
              column: 4
            },
            end: {
              line: 404,
              column: 5
            }
          }, {
            start: {
              line: 402,
              column: 4
            },
            end: {
              line: 404,
              column: 5
            }
          }],
          line: 402
        },
        "28": {
          loc: {
            start: {
              line: 405,
              column: 4
            },
            end: {
              line: 407,
              column: 5
            }
          },
          type: "if",
          locations: [{
            start: {
              line: 405,
              column: 4
            },
            end: {
              line: 407,
              column: 5
            }
          }, {
            start: {
              line: 405,
              column: 4
            },
            end: {
              line: 407,
              column: 5
            }
          }],
          line: 405
        },
        "29": {
          loc: {
            start: {
              line: 405,
              column: 8
            },
            end: {
              line: 405,
              column: 69
            }
          },
          type: "binary-expr",
          locations: [{
            start: {
              line: 405,
              column: 8
            },
            end: {
              line: 405,
              column: 33
            }
          }, {
            start: {
              line: 405,
              column: 37
            },
            end: {
              line: 405,
              column: 46
            }
          }, {
            start: {
              line: 405,
              column: 50
            },
            end: {
              line: 405,
              column: 69
            }
          }],
          line: 405
        },
        "30": {
          loc: {
            start: {
              line: 408,
              column: 4
            },
            end: {
              line: 410,
              column: 5
            }
          },
          type: "if",
          locations: [{
            start: {
              line: 408,
              column: 4
            },
            end: {
              line: 410,
              column: 5
            }
          }, {
            start: {
              line: 408,
              column: 4
            },
            end: {
              line: 410,
              column: 5
            }
          }],
          line: 408
        },
        "31": {
          loc: {
            start: {
              line: 435,
              column: 28
            },
            end: {
              line: 435,
              column: 37
            }
          },
          type: "default-arg",
          locations: [{
            start: {
              line: 435,
              column: 36
            },
            end: {
              line: 435,
              column: 37
            }
          }],
          line: 435
        },
        "32": {
          loc: {
            start: {
              line: 436,
              column: 4
            },
            end: {
              line: 438,
              column: 5
            }
          },
          type: "if",
          locations: [{
            start: {
              line: 436,
              column: 4
            },
            end: {
              line: 438,
              column: 5
            }
          }, {
            start: {
              line: 436,
              column: 4
            },
            end: {
              line: 438,
              column: 5
            }
          }],
          line: 436
        },
        "33": {
          loc: {
            start: {
              line: 439,
              column: 4
            },
            end: {
              line: 441,
              column: 5
            }
          },
          type: "if",
          locations: [{
            start: {
              line: 439,
              column: 4
            },
            end: {
              line: 441,
              column: 5
            }
          }, {
            start: {
              line: 439,
              column: 4
            },
            end: {
              line: 441,
              column: 5
            }
          }],
          line: 439
        },
        "34": {
          loc: {
            start: {
              line: 439,
              column: 8
            },
            end: {
              line: 439,
              column: 69
            }
          },
          type: "binary-expr",
          locations: [{
            start: {
              line: 439,
              column: 8
            },
            end: {
              line: 439,
              column: 33
            }
          }, {
            start: {
              line: 439,
              column: 37
            },
            end: {
              line: 439,
              column: 46
            }
          }, {
            start: {
              line: 439,
              column: 50
            },
            end: {
              line: 439,
              column: 69
            }
          }],
          line: 439
        },
        "35": {
          loc: {
            start: {
              line: 473,
              column: 4
            },
            end: {
              line: 475,
              column: 5
            }
          },
          type: "if",
          locations: [{
            start: {
              line: 473,
              column: 4
            },
            end: {
              line: 475,
              column: 5
            }
          }, {
            start: {
              line: 473,
              column: 4
            },
            end: {
              line: 475,
              column: 5
            }
          }],
          line: 473
        },
        "36": {
          loc: {
            start: {
              line: 477,
              column: 20
            },
            end: {
              line: 477,
              column: 64
            }
          },
          type: "cond-expr",
          locations: [{
            start: {
              line: 477,
              column: 43
            },
            end: {
              line: 477,
              column: 48
            }
          }, {
            start: {
              line: 477,
              column: 51
            },
            end: {
              line: 477,
              column: 64
            }
          }],
          line: 477
        },
        "37": {
          loc: {
            start: {
              line: 482,
              column: 6
            },
            end: {
              line: 486,
              column: 7
            }
          },
          type: "if",
          locations: [{
            start: {
              line: 482,
              column: 6
            },
            end: {
              line: 486,
              column: 7
            }
          }, {
            start: {
              line: 482,
              column: 6
            },
            end: {
              line: 486,
              column: 7
            }
          }],
          line: 482
        },
        "38": {
          loc: {
            start: {
              line: 484,
              column: 13
            },
            end: {
              line: 486,
              column: 7
            }
          },
          type: "if",
          locations: [{
            start: {
              line: 484,
              column: 13
            },
            end: {
              line: 486,
              column: 7
            }
          }, {
            start: {
              line: 484,
              column: 13
            },
            end: {
              line: 486,
              column: 7
            }
          }],
          line: 484
        },
        "39": {
          loc: {
            start: {
              line: 520,
              column: 4
            },
            end: {
              line: 522,
              column: 5
            }
          },
          type: "if",
          locations: [{
            start: {
              line: 520,
              column: 4
            },
            end: {
              line: 522,
              column: 5
            }
          }, {
            start: {
              line: 520,
              column: 4
            },
            end: {
              line: 522,
              column: 5
            }
          }],
          line: 520
        },
        "40": {
          loc: {
            start: {
              line: 527,
              column: 4
            },
            end: {
              line: 529,
              column: 5
            }
          },
          type: "if",
          locations: [{
            start: {
              line: 527,
              column: 4
            },
            end: {
              line: 529,
              column: 5
            }
          }, {
            start: {
              line: 527,
              column: 4
            },
            end: {
              line: 529,
              column: 5
            }
          }],
          line: 527
        },
        "41": {
          loc: {
            start: {
              line: 527,
              column: 8
            },
            end: {
              line: 527,
              column: 49
            }
          },
          type: "binary-expr",
          locations: [{
            start: {
              line: 527,
              column: 8
            },
            end: {
              line: 527,
              column: 24
            }
          }, {
            start: {
              line: 527,
              column: 28
            },
            end: {
              line: 527,
              column: 49
            }
          }],
          line: 527
        },
        "42": {
          loc: {
            start: {
              line: 545,
              column: 4
            },
            end: {
              line: 547,
              column: 5
            }
          },
          type: "if",
          locations: [{
            start: {
              line: 545,
              column: 4
            },
            end: {
              line: 547,
              column: 5
            }
          }, {
            start: {
              line: 545,
              column: 4
            },
            end: {
              line: 547,
              column: 5
            }
          }],
          line: 545
        },
        "43": {
          loc: {
            start: {
              line: 550,
              column: 4
            },
            end: {
              line: 552,
              column: 5
            }
          },
          type: "if",
          locations: [{
            start: {
              line: 550,
              column: 4
            },
            end: {
              line: 552,
              column: 5
            }
          }, {
            start: {
              line: 550,
              column: 4
            },
            end: {
              line: 552,
              column: 5
            }
          }],
          line: 550
        },
        "44": {
          loc: {
            start: {
              line: 550,
              column: 8
            },
            end: {
              line: 550,
              column: 41
            }
          },
          type: "binary-expr",
          locations: [{
            start: {
              line: 550,
              column: 8
            },
            end: {
              line: 550,
              column: 24
            }
          }, {
            start: {
              line: 550,
              column: 28
            },
            end: {
              line: 550,
              column: 41
            }
          }],
          line: 550
        },
        "45": {
          loc: {
            start: {
              line: 565,
              column: 4
            },
            end: {
              line: 567,
              column: 5
            }
          },
          type: "if",
          locations: [{
            start: {
              line: 565,
              column: 4
            },
            end: {
              line: 567,
              column: 5
            }
          }, {
            start: {
              line: 565,
              column: 4
            },
            end: {
              line: 567,
              column: 5
            }
          }],
          line: 565
        },
        "46": {
          loc: {
            start: {
              line: 570,
              column: 4
            },
            end: {
              line: 572,
              column: 5
            }
          },
          type: "if",
          locations: [{
            start: {
              line: 570,
              column: 4
            },
            end: {
              line: 572,
              column: 5
            }
          }, {
            start: {
              line: 570,
              column: 4
            },
            end: {
              line: 572,
              column: 5
            }
          }],
          line: 570
        },
        "47": {
          loc: {
            start: {
              line: 571,
              column: 13
            },
            end: {
              line: 571,
              column: 57
            }
          },
          type: "binary-expr",
          locations: [{
            start: {
              line: 571,
              column: 13
            },
            end: {
              line: 571,
              column: 40
            }
          }, {
            start: {
              line: 571,
              column: 44
            },
            end: {
              line: 571,
              column: 57
            }
          }],
          line: 571
        },
        "48": {
          loc: {
            start: {
              line: 584,
              column: 4
            },
            end: {
              line: 586,
              column: 5
            }
          },
          type: "if",
          locations: [{
            start: {
              line: 584,
              column: 4
            },
            end: {
              line: 586,
              column: 5
            }
          }, {
            start: {
              line: 584,
              column: 4
            },
            end: {
              line: 586,
              column: 5
            }
          }],
          line: 584
        },
        "49": {
          loc: {
            start: {
              line: 589,
              column: 4
            },
            end: {
              line: 591,
              column: 5
            }
          },
          type: "if",
          locations: [{
            start: {
              line: 589,
              column: 4
            },
            end: {
              line: 591,
              column: 5
            }
          }, {
            start: {
              line: 589,
              column: 4
            },
            end: {
              line: 591,
              column: 5
            }
          }],
          line: 589
        },
        "50": {
          loc: {
            start: {
              line: 590,
              column: 13
            },
            end: {
              line: 590,
              column: 57
            }
          },
          type: "binary-expr",
          locations: [{
            start: {
              line: 590,
              column: 13
            },
            end: {
              line: 590,
              column: 40
            }
          }, {
            start: {
              line: 590,
              column: 44
            },
            end: {
              line: 590,
              column: 57
            }
          }],
          line: 590
        },
        "51": {
          loc: {
            start: {
              line: 604,
              column: 19
            },
            end: {
              line: 604,
              column: 41
            }
          },
          type: "default-arg",
          locations: [{
            start: {
              line: 604,
              column: 36
            },
            end: {
              line: 604,
              column: 41
            }
          }],
          line: 604
        },
        "52": {
          loc: {
            start: {
              line: 605,
              column: 4
            },
            end: {
              line: 607,
              column: 5
            }
          },
          type: "if",
          locations: [{
            start: {
              line: 605,
              column: 4
            },
            end: {
              line: 607,
              column: 5
            }
          }, {
            start: {
              line: 605,
              column: 4
            },
            end: {
              line: 607,
              column: 5
            }
          }],
          line: 605
        },
        "53": {
          loc: {
            start: {
              line: 611,
              column: 4
            },
            end: {
              line: 615,
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
              line: 615,
              column: 5
            }
          }, {
            start: {
              line: 611,
              column: 4
            },
            end: {
              line: 615,
              column: 5
            }
          }],
          line: 611
        },
        "54": {
          loc: {
            start: {
              line: 614,
              column: 13
            },
            end: {
              line: 614,
              column: 57
            }
          },
          type: "binary-expr",
          locations: [{
            start: {
              line: 614,
              column: 13
            },
            end: {
              line: 614,
              column: 40
            }
          }, {
            start: {
              line: 614,
              column: 44
            },
            end: {
              line: 614,
              column: 57
            }
          }],
          line: 614
        },
        "55": {
          loc: {
            start: {
              line: 625,
              column: 4
            },
            end: {
              line: 627,
              column: 5
            }
          },
          type: "if",
          locations: [{
            start: {
              line: 625,
              column: 4
            },
            end: {
              line: 627,
              column: 5
            }
          }, {
            start: {
              line: 625,
              column: 4
            },
            end: {
              line: 627,
              column: 5
            }
          }],
          line: 625
        },
        "56": {
          loc: {
            start: {
              line: 631,
              column: 4
            },
            end: {
              line: 635,
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
              line: 635,
              column: 5
            }
          }, {
            start: {
              line: 631,
              column: 4
            },
            end: {
              line: 635,
              column: 5
            }
          }],
          line: 631
        },
        "57": {
          loc: {
            start: {
              line: 634,
              column: 13
            },
            end: {
              line: 634,
              column: 57
            }
          },
          type: "binary-expr",
          locations: [{
            start: {
              line: 634,
              column: 13
            },
            end: {
              line: 634,
              column: 40
            }
          }, {
            start: {
              line: 634,
              column: 44
            },
            end: {
              line: 634,
              column: 57
            }
          }],
          line: 634
        },
        "58": {
          loc: {
            start: {
              line: 659,
              column: 4
            },
            end: {
              line: 661,
              column: 5
            }
          },
          type: "if",
          locations: [{
            start: {
              line: 659,
              column: 4
            },
            end: {
              line: 661,
              column: 5
            }
          }, {
            start: {
              line: 659,
              column: 4
            },
            end: {
              line: 661,
              column: 5
            }
          }],
          line: 659
        },
        "59": {
          loc: {
            start: {
              line: 663,
              column: 4
            },
            end: {
              line: 666,
              column: 5
            }
          },
          type: "if",
          locations: [{
            start: {
              line: 663,
              column: 4
            },
            end: {
              line: 666,
              column: 5
            }
          }, {
            start: {
              line: 663,
              column: 4
            },
            end: {
              line: 666,
              column: 5
            }
          }],
          line: 663
        },
        "60": {
          loc: {
            start: {
              line: 684,
              column: 4
            },
            end: {
              line: 686,
              column: 5
            }
          },
          type: "if",
          locations: [{
            start: {
              line: 684,
              column: 4
            },
            end: {
              line: 686,
              column: 5
            }
          }, {
            start: {
              line: 684,
              column: 4
            },
            end: {
              line: 686,
              column: 5
            }
          }],
          line: 684
        },
        "61": {
          loc: {
            start: {
              line: 691,
              column: 4
            },
            end: {
              line: 693,
              column: 5
            }
          },
          type: "if",
          locations: [{
            start: {
              line: 691,
              column: 4
            },
            end: {
              line: 693,
              column: 5
            }
          }, {
            start: {
              line: 691,
              column: 4
            },
            end: {
              line: 693,
              column: 5
            }
          }],
          line: 691
        },
        "62": {
          loc: {
            start: {
              line: 713,
              column: 4
            },
            end: {
              line: 715,
              column: 5
            }
          },
          type: "if",
          locations: [{
            start: {
              line: 713,
              column: 4
            },
            end: {
              line: 715,
              column: 5
            }
          }, {
            start: {
              line: 713,
              column: 4
            },
            end: {
              line: 715,
              column: 5
            }
          }],
          line: 713
        },
        "63": {
          loc: {
            start: {
              line: 720,
              column: 4
            },
            end: {
              line: 722,
              column: 5
            }
          },
          type: "if",
          locations: [{
            start: {
              line: 720,
              column: 4
            },
            end: {
              line: 722,
              column: 5
            }
          }, {
            start: {
              line: 720,
              column: 4
            },
            end: {
              line: 722,
              column: 5
            }
          }],
          line: 720
        },
        "64": {
          loc: {
            start: {
              line: 751,
              column: 22
            },
            end: {
              line: 751,
              column: 33
            }
          },
          type: "default-arg",
          locations: [{
            start: {
              line: 751,
              column: 31
            },
            end: {
              line: 751,
              column: 33
            }
          }],
          line: 751
        },
        "65": {
          loc: {
            start: {
              line: 757,
              column: 4
            },
            end: {
              line: 760,
              column: 5
            }
          },
          type: "if",
          locations: [{
            start: {
              line: 757,
              column: 4
            },
            end: {
              line: 760,
              column: 5
            }
          }, {
            start: {
              line: 757,
              column: 4
            },
            end: {
              line: 760,
              column: 5
            }
          }],
          line: 757
        },
        "66": {
          loc: {
            start: {
              line: 763,
              column: 4
            },
            end: {
              line: 765,
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
              line: 765,
              column: 5
            }
          }, {
            start: {
              line: 763,
              column: 4
            },
            end: {
              line: 765,
              column: 5
            }
          }],
          line: 763
        },
        "67": {
          loc: {
            start: {
              line: 771,
              column: 4
            },
            end: {
              line: 773,
              column: 5
            }
          },
          type: "if",
          locations: [{
            start: {
              line: 771,
              column: 4
            },
            end: {
              line: 773,
              column: 5
            }
          }, {
            start: {
              line: 771,
              column: 4
            },
            end: {
              line: 773,
              column: 5
            }
          }],
          line: 771
        },
        "68": {
          loc: {
            start: {
              line: 776,
              column: 4
            },
            end: {
              line: 778,
              column: 5
            }
          },
          type: "if",
          locations: [{
            start: {
              line: 776,
              column: 4
            },
            end: {
              line: 778,
              column: 5
            }
          }, {
            start: {
              line: 776,
              column: 4
            },
            end: {
              line: 778,
              column: 5
            }
          }],
          line: 776
        },
        "69": {
          loc: {
            start: {
              line: 790,
              column: 2
            },
            end: {
              line: 796,
              column: 3
            }
          },
          type: "if",
          locations: [{
            start: {
              line: 790,
              column: 2
            },
            end: {
              line: 796,
              column: 3
            }
          }, {
            start: {
              line: 790,
              column: 2
            },
            end: {
              line: 796,
              column: 3
            }
          }],
          line: 790
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
        "211": 0
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
        "26": [0, 0, 0],
        "27": [0, 0],
        "28": [0, 0],
        "29": [0, 0, 0],
        "30": [0, 0],
        "31": [0],
        "32": [0, 0],
        "33": [0, 0],
        "34": [0, 0, 0],
        "35": [0, 0],
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
        "51": [0],
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
      hash: "629936ec446d2feec4ce23e4f10f0a278477c5dd"
    };
    var coverage = global[gcv] || (global[gcv] = {});

    if (!coverage[path] || coverage[path].hash !== hash) {
      coverage[path] = coverageData;
    }

    var actualCoverage = coverage[path];
    {
      // @ts-ignore
      cov_3xfj46l7x = function () {
        return actualCoverage;
      };
    }
    return actualCoverage;
  }

  cov_3xfj46l7x();

  let guid = (cov_3xfj46l7x().s[0]++, 1);
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

  cov_3xfj46l7x().s[1]++;

  const preparePlaylistItem = newItem => {
    cov_3xfj46l7x().f[0]++;
    let item = (cov_3xfj46l7x().s[2]++, newItem);
    cov_3xfj46l7x().s[3]++;

    if ((cov_3xfj46l7x().b[1][0]++, !newItem) || (cov_3xfj46l7x().b[1][1]++, typeof newItem !== 'object')) {
      cov_3xfj46l7x().b[0][0]++;
      cov_3xfj46l7x().s[4]++; // Casting to an Object in this way allows primitives to retain their
      // primitiveness (i.e. they will be cast back to primitives as needed).

      item = Object(newItem);
      cov_3xfj46l7x().s[5]++;
      item.originalValue = newItem;
    } else {
      cov_3xfj46l7x().b[0][1]++;
    }

    cov_3xfj46l7x().s[6]++;
    item.playlistItemId_ = guid++;
    cov_3xfj46l7x().s[7]++;
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


  cov_3xfj46l7x().s[8]++;

  const preparePlaylistItems = arr => {
    cov_3xfj46l7x().f[1]++;
    cov_3xfj46l7x().s[9]++;
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


  cov_3xfj46l7x().s[10]++;

  const indexInPlaylistItemIds = (list, currentItemId) => {
    cov_3xfj46l7x().f[2]++;
    cov_3xfj46l7x().s[11]++;

    for (let i = (cov_3xfj46l7x().s[12]++, 0); i < list.length; i++) {
      cov_3xfj46l7x().s[13]++;

      if (list[i].playlistItemId_ === currentItemId) {
        cov_3xfj46l7x().b[2][0]++;
        cov_3xfj46l7x().s[14]++;
        return i;
      } else {
        cov_3xfj46l7x().b[2][1]++;
      }
    }

    cov_3xfj46l7x().s[15]++;
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


  cov_3xfj46l7x().s[16]++;

  const sourceEquals = (source1, source2) => {
    cov_3xfj46l7x().f[3]++;
    let src1 = (cov_3xfj46l7x().s[17]++, source1);
    let src2 = (cov_3xfj46l7x().s[18]++, source2);
    cov_3xfj46l7x().s[19]++;

    if (typeof source1 === 'object') {
      cov_3xfj46l7x().b[3][0]++;
      cov_3xfj46l7x().s[20]++;
      src1 = source1.src;
    } else {
      cov_3xfj46l7x().b[3][1]++;
    }

    cov_3xfj46l7x().s[21]++;

    if (typeof source2 === 'object') {
      cov_3xfj46l7x().b[4][0]++;
      cov_3xfj46l7x().s[22]++;
      src2 = source2.src;
    } else {
      cov_3xfj46l7x().b[4][1]++;
    }

    cov_3xfj46l7x().s[23]++;

    if (/^\/\//.test(src1)) {
      cov_3xfj46l7x().b[5][0]++;
      cov_3xfj46l7x().s[24]++;
      src2 = src2.slice(src2.indexOf('//'));
    } else {
      cov_3xfj46l7x().b[5][1]++;
    }

    cov_3xfj46l7x().s[25]++;

    if (/^\/\//.test(src2)) {
      cov_3xfj46l7x().b[6][0]++;
      cov_3xfj46l7x().s[26]++;
      src1 = src1.slice(src1.indexOf('//'));
    } else {
      cov_3xfj46l7x().b[6][1]++;
    }

    cov_3xfj46l7x().s[27]++;
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


  cov_3xfj46l7x().s[28]++;

  const indexInSources = (arr, src) => {
    cov_3xfj46l7x().f[4]++;
    cov_3xfj46l7x().s[29]++;

    for (let i = (cov_3xfj46l7x().s[30]++, 0); i < arr.length; i++) {
      const sources = (cov_3xfj46l7x().s[31]++, arr[i].sources);
      cov_3xfj46l7x().s[32]++;

      if (Array.isArray(sources)) {
        cov_3xfj46l7x().b[7][0]++;
        cov_3xfj46l7x().s[33]++;

        for (let j = (cov_3xfj46l7x().s[34]++, 0); j < sources.length; j++) {
          const source = (cov_3xfj46l7x().s[35]++, sources[j]);
          cov_3xfj46l7x().s[36]++;

          if ((cov_3xfj46l7x().b[9][0]++, source) && (cov_3xfj46l7x().b[9][1]++, sourceEquals(source, src))) {
            cov_3xfj46l7x().b[8][0]++;
            cov_3xfj46l7x().s[37]++;
            return i;
          } else {
            cov_3xfj46l7x().b[8][1]++;
          }
        }
      } else {
        cov_3xfj46l7x().b[7][1]++;
      }
    }

    cov_3xfj46l7x().s[38]++;
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


  cov_3xfj46l7x().s[39]++;

  const randomize = arr => {
    cov_3xfj46l7x().f[5]++;
    let index = (cov_3xfj46l7x().s[40]++, -1);
    const lastIndex = (cov_3xfj46l7x().s[41]++, arr.length - 1);
    cov_3xfj46l7x().s[42]++;

    while (++index < arr.length) {
      const rand = (cov_3xfj46l7x().s[43]++, index + Math.floor(Math.random() * (lastIndex - index + 1)));
      const value = (cov_3xfj46l7x().s[44]++, arr[rand]);
      cov_3xfj46l7x().s[45]++;
      arr[rand] = arr[index];
      cov_3xfj46l7x().s[46]++;
      arr[index] = value;
    }

    cov_3xfj46l7x().s[47]++;
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


  function factory(player, initialList, initialIndex = (cov_3xfj46l7x().b[10][0]++, 0)) {
    cov_3xfj46l7x().f[6]++;
    let list = (cov_3xfj46l7x().s[48]++, null);
    let changing = (cov_3xfj46l7x().s[49]++, false);
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

    const playlist = (cov_3xfj46l7x().s[50]++, player.playlist = (nextPlaylist, newIndex = (cov_3xfj46l7x().b[11][0]++, 0)) => {
      cov_3xfj46l7x().f[7]++;
      cov_3xfj46l7x().s[51]++;

      if (changing) {
        cov_3xfj46l7x().b[12][0]++;
        cov_3xfj46l7x().s[52]++;
        throw new Error('do not call playlist() during a playlist change');
      } else {
        cov_3xfj46l7x().b[12][1]++;
      }

      cov_3xfj46l7x().s[53]++;

      if (Array.isArray(nextPlaylist)) {
        cov_3xfj46l7x().b[13][0]++; // @todo - Simplify this to `list.slice()` for v5.

        const previousPlaylist = (cov_3xfj46l7x().s[54]++, Array.isArray(list) ? (cov_3xfj46l7x().b[14][0]++, list.slice()) : (cov_3xfj46l7x().b[14][1]++, null));
        cov_3xfj46l7x().s[55]++;
        list = preparePlaylistItems(nextPlaylist); // Mark the playlist as changing during the duringplaylistchange lifecycle.

        cov_3xfj46l7x().s[56]++;
        changing = true;
        cov_3xfj46l7x().s[57]++;
        player.trigger({
          type: 'duringplaylistchange',
          nextIndex: newIndex,
          nextPlaylist,
          previousIndex: playlist.currentIndex_,
          // @todo - Simplify this to simply pass along `previousPlaylist` for v5.
          previousPlaylist: (cov_3xfj46l7x().b[15][0]++, previousPlaylist) || (cov_3xfj46l7x().b[15][1]++, [])
        });
        cov_3xfj46l7x().s[58]++;
        changing = false;
        cov_3xfj46l7x().s[59]++;

        if (newIndex !== -1) {
          cov_3xfj46l7x().b[16][0]++;
          cov_3xfj46l7x().s[60]++;
          playlist.currentItem(newIndex);
        } else {
          cov_3xfj46l7x().b[16][1]++;
        } // The only time the previous playlist is null is the first call to this
        // function. This allows us to fire the `duringplaylistchange` event
        // every time the playlist is populated and to maintain backward
        // compatibility by not firing the `playlistchange` event on the initial
        // population of the list.
        //
        // @todo - Remove this condition in preparation for v5.


        cov_3xfj46l7x().s[61]++;

        if (previousPlaylist) {
          cov_3xfj46l7x().b[17][0]++;
          cov_3xfj46l7x().s[62]++;
          player.setTimeout(() => {
            cov_3xfj46l7x().f[8]++;
            cov_3xfj46l7x().s[63]++;
            player.trigger({
              type: 'playlistchange',
              action: 'change'
            });
          }, 0);
        } else {
          cov_3xfj46l7x().b[17][1]++;
        }
      } else {
        cov_3xfj46l7x().b[13][1]++;
      } // Always return a shallow clone of the playlist list.
      // We also want to return originalValue if any item in the list has it.


      cov_3xfj46l7x().s[64]++;
      return list.map(item => {
        cov_3xfj46l7x().f[9]++;
        cov_3xfj46l7x().s[65]++;
        return (cov_3xfj46l7x().b[18][0]++, item.originalValue) || (cov_3xfj46l7x().b[18][1]++, item);
      });
    }); // On a new source, if there is no current item, disable auto-advance.

    cov_3xfj46l7x().s[66]++;
    player.on('loadstart', () => {
      cov_3xfj46l7x().f[10]++;
      cov_3xfj46l7x().s[67]++;

      if (playlist.currentItem() === -1) {
        cov_3xfj46l7x().b[19][0]++;
        cov_3xfj46l7x().s[68]++;
        reset(player);
      } else {
        cov_3xfj46l7x().b[19][1]++;
      }
    });
    cov_3xfj46l7x().s[69]++;
    playlist.currentIndex_ = -1;
    cov_3xfj46l7x().s[70]++;
    playlist.player_ = player;
    cov_3xfj46l7x().s[71]++;
    playlist.autoadvance_ = {};
    cov_3xfj46l7x().s[72]++;
    playlist.repeat_ = false;
    cov_3xfj46l7x().s[73]++;
    playlist.currentPlaylistItemId_ = null;
    /**
    * Get or set the current item in the playlist.
    *
    * During the duringplaylistchange event, acts only as a getter.
    *
    * @param  {number} [index]
    *         If given as a valid value, plays the playlist item at that index.
    * @param {boolean} [suppressPoster]
    *         Should the native poster be suppressed? Defaults to false.
    *
    * @return {number}
    *         The current item index.
    */

    cov_3xfj46l7x().s[74]++;

    playlist.currentItem = (index, suppressPoster) => {
      cov_3xfj46l7x().f[11]++;
      cov_3xfj46l7x().s[75]++; // If the playlist is changing, only act as a getter.

      if (changing) {
        cov_3xfj46l7x().b[20][0]++;
        cov_3xfj46l7x().s[76]++;
        return playlist.currentIndex_;
      } else {
        cov_3xfj46l7x().b[20][1]++;
      } // Act as a setter when the index is given and is a valid number.


      cov_3xfj46l7x().s[77]++;

      if ((cov_3xfj46l7x().b[22][0]++, typeof index === 'number') && (cov_3xfj46l7x().b[22][1]++, playlist.currentIndex_ !== index) && (cov_3xfj46l7x().b[22][2]++, index >= 0) && (cov_3xfj46l7x().b[22][3]++, index < list.length)) {
        cov_3xfj46l7x().b[21][0]++;
        cov_3xfj46l7x().s[78]++;
        playlist.currentIndex_ = index;
        cov_3xfj46l7x().s[79]++;
        playItem(playlist.player_, list[playlist.currentIndex_], suppressPoster);
        cov_3xfj46l7x().s[80]++;
        return playlist.currentIndex_;
      } else {
        cov_3xfj46l7x().b[21][1]++;
      }

      const src = (cov_3xfj46l7x().s[81]++, (cov_3xfj46l7x().b[23][0]++, playlist.player_.currentSrc()) || (cov_3xfj46l7x().b[23][1]++, '')); // If there is a currentPlaylistItemId_, validate that it matches the
      // current source URL returned by the player. This is sufficient evidence
      // to suggest that the source was set by the playlist plugin. This code
      // exists primarily to deal with playlists where multiple items have the
      // same source.

      cov_3xfj46l7x().s[82]++;

      if (playlist.currentPlaylistItemId_) {
        cov_3xfj46l7x().b[24][0]++;
        const indexInItemIds = (cov_3xfj46l7x().s[83]++, indexInPlaylistItemIds(list, playlist.currentPlaylistItemId_));
        const item = (cov_3xfj46l7x().s[84]++, list[indexInItemIds]); // Found a match, this is our current index!

        cov_3xfj46l7x().s[85]++;

        if ((cov_3xfj46l7x().b[26][0]++, item) && (cov_3xfj46l7x().b[26][1]++, Array.isArray(item.sources)) && (cov_3xfj46l7x().b[26][2]++, indexInSources([item], src) > -1)) {
          cov_3xfj46l7x().b[25][0]++;
          cov_3xfj46l7x().s[86]++;
          playlist.currentIndex_ = indexInItemIds;
          cov_3xfj46l7x().s[87]++;
          return playlist.currentIndex_;
        } else {
          cov_3xfj46l7x().b[25][1]++;
        } // If this does not match the current source, null it out so subsequent
        // calls can skip this step.


        cov_3xfj46l7x().s[88]++;
        playlist.currentPlaylistItemId_ = null;
      } else {
        cov_3xfj46l7x().b[24][1]++;
      } // Finally, if we don't have a valid, current playlist item ID, we can
      // auto-detect it based on the player's current source URL.


      cov_3xfj46l7x().s[89]++;
      playlist.currentIndex_ = playlist.indexOf(src);
      cov_3xfj46l7x().s[90]++;
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


    cov_3xfj46l7x().s[91]++;

    playlist.add = (items, index) => {
      cov_3xfj46l7x().f[12]++;
      cov_3xfj46l7x().s[92]++;

      if (changing) {
        cov_3xfj46l7x().b[27][0]++;
        cov_3xfj46l7x().s[93]++;
        throw new Error('cannot modify a playlist that is currently changing');
      } else {
        cov_3xfj46l7x().b[27][1]++;
      }

      cov_3xfj46l7x().s[94]++;

      if ((cov_3xfj46l7x().b[29][0]++, typeof index !== 'number') || (cov_3xfj46l7x().b[29][1]++, index < 0) || (cov_3xfj46l7x().b[29][2]++, index > list.length)) {
        cov_3xfj46l7x().b[28][0]++;
        cov_3xfj46l7x().s[95]++;
        index = list.length;
      } else {
        cov_3xfj46l7x().b[28][1]++;
      }

      cov_3xfj46l7x().s[96]++;

      if (!Array.isArray(items)) {
        cov_3xfj46l7x().b[30][0]++;
        cov_3xfj46l7x().s[97]++;
        items = [items];
      } else {
        cov_3xfj46l7x().b[30][1]++;
      }

      cov_3xfj46l7x().s[98]++;
      list.splice(index, 0, ...preparePlaylistItems(items)); // playlistchange is triggered synchronously in this case because it does
      // not change the current media source

      cov_3xfj46l7x().s[99]++;
      player.trigger({
        type: 'playlistchange',
        action: 'add'
      });
      cov_3xfj46l7x().s[100]++;
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


    cov_3xfj46l7x().s[101]++;

    playlist.remove = (index, count = (cov_3xfj46l7x().b[31][0]++, 1)) => {
      cov_3xfj46l7x().f[13]++;
      cov_3xfj46l7x().s[102]++;

      if (changing) {
        cov_3xfj46l7x().b[32][0]++;
        cov_3xfj46l7x().s[103]++;
        throw new Error('cannot modify a playlist that is currently changing');
      } else {
        cov_3xfj46l7x().b[32][1]++;
      }

      cov_3xfj46l7x().s[104]++;

      if ((cov_3xfj46l7x().b[34][0]++, typeof index !== 'number') || (cov_3xfj46l7x().b[34][1]++, index < 0) || (cov_3xfj46l7x().b[34][2]++, index > list.length)) {
        cov_3xfj46l7x().b[33][0]++;
        cov_3xfj46l7x().s[105]++;
        return;
      } else {
        cov_3xfj46l7x().b[33][1]++;
      }

      cov_3xfj46l7x().s[106]++;
      list.splice(index, count); // playlistchange is triggered synchronously in this case because it does
      // not change the current media source

      cov_3xfj46l7x().s[107]++;
      player.trigger({
        type: 'playlistchange',
        action: 'remove'
      });
      cov_3xfj46l7x().s[108]++;
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


    cov_3xfj46l7x().s[109]++;

    playlist.contains = value => {
      cov_3xfj46l7x().f[14]++;
      cov_3xfj46l7x().s[110]++;
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


    cov_3xfj46l7x().s[111]++;

    playlist.indexOf = value => {
      cov_3xfj46l7x().f[15]++;
      cov_3xfj46l7x().s[112]++;

      if (typeof value === 'string') {
        cov_3xfj46l7x().b[35][0]++;
        cov_3xfj46l7x().s[113]++;
        return indexInSources(list, value);
      } else {
        cov_3xfj46l7x().b[35][1]++;
      }

      const sources = (cov_3xfj46l7x().s[114]++, Array.isArray(value) ? (cov_3xfj46l7x().b[36][0]++, value) : (cov_3xfj46l7x().b[36][1]++, value.sources));
      cov_3xfj46l7x().s[115]++;

      for (let i = (cov_3xfj46l7x().s[116]++, 0); i < sources.length; i++) {
        const source = (cov_3xfj46l7x().s[117]++, sources[i]);
        cov_3xfj46l7x().s[118]++;

        if (typeof source === 'string') {
          cov_3xfj46l7x().b[37][0]++;
          cov_3xfj46l7x().s[119]++;
          return indexInSources(list, source);
        } else {
          cov_3xfj46l7x().b[37][1]++;
          cov_3xfj46l7x().s[120]++;

          if (source.src) {
            cov_3xfj46l7x().b[38][0]++;
            cov_3xfj46l7x().s[121]++;
            return indexInSources(list, source.src);
          } else {
            cov_3xfj46l7x().b[38][1]++;
          }
        }
      }

      cov_3xfj46l7x().s[122]++;
      return -1;
    };
    /**
    * Get the index of the current item in the playlist. This is identical to
    * calling `currentItem()` with no arguments.
    *
    * @return {number}
    *         The current item index.
    */


    cov_3xfj46l7x().s[123]++;

    playlist.currentIndex = () => {
      cov_3xfj46l7x().f[16]++;
      cov_3xfj46l7x().s[124]++;
      return playlist.currentItem();
    };
    /**
    * Get the index of the last item in the playlist.
    *
    * @return {number}
    *         The index of the last item in the playlist or -1 if there are no
    *         items.
    */


    cov_3xfj46l7x().s[125]++;

    playlist.lastIndex = () => {
      cov_3xfj46l7x().f[17]++;
      cov_3xfj46l7x().s[126]++;
      return list.length - 1;
    };
    /**
    * Get the index of the next item in the playlist.
    *
    * @return {number}
    *         The index of the next item in the playlist or -1 if there is no
    *         current item.
    */


    cov_3xfj46l7x().s[127]++;

    playlist.nextIndex = () => {
      cov_3xfj46l7x().f[18]++;
      const current = (cov_3xfj46l7x().s[128]++, playlist.currentItem());
      cov_3xfj46l7x().s[129]++;

      if (current === -1) {
        cov_3xfj46l7x().b[39][0]++;
        cov_3xfj46l7x().s[130]++;
        return -1;
      } else {
        cov_3xfj46l7x().b[39][1]++;
      }

      const lastIndex = (cov_3xfj46l7x().s[131]++, playlist.lastIndex()); // When repeating, loop back to the beginning on the last item.

      cov_3xfj46l7x().s[132]++;

      if ((cov_3xfj46l7x().b[41][0]++, playlist.repeat_) && (cov_3xfj46l7x().b[41][1]++, current === lastIndex)) {
        cov_3xfj46l7x().b[40][0]++;
        cov_3xfj46l7x().s[133]++;
        return 0;
      } else {
        cov_3xfj46l7x().b[40][1]++;
      } // Don't go past the end of the playlist.


      cov_3xfj46l7x().s[134]++;
      return Math.min(current + 1, lastIndex);
    };
    /**
    * Get the index of the previous item in the playlist.
    *
    * @return {number}
    *         The index of the previous item in the playlist or -1 if there is
    *         no current item.
    */


    cov_3xfj46l7x().s[135]++;

    playlist.previousIndex = () => {
      cov_3xfj46l7x().f[19]++;
      const current = (cov_3xfj46l7x().s[136]++, playlist.currentItem());
      cov_3xfj46l7x().s[137]++;

      if (current === -1) {
        cov_3xfj46l7x().b[42][0]++;
        cov_3xfj46l7x().s[138]++;
        return -1;
      } else {
        cov_3xfj46l7x().b[42][1]++;
      } // When repeating, loop back to the end of the playlist.


      cov_3xfj46l7x().s[139]++;

      if ((cov_3xfj46l7x().b[44][0]++, playlist.repeat_) && (cov_3xfj46l7x().b[44][1]++, current === 0)) {
        cov_3xfj46l7x().b[43][0]++;
        cov_3xfj46l7x().s[140]++;
        return playlist.lastIndex();
      } else {
        cov_3xfj46l7x().b[43][1]++;
      } // Don't go past the beginning of the playlist.


      cov_3xfj46l7x().s[141]++;
      return Math.max(current - 1, 0);
    };
    /**
    * Plays the first item in the playlist.
    *
    * @return {Object|undefined}
    *         Returns undefined and has no side effects if the list is empty.
    */


    cov_3xfj46l7x().s[142]++;

    playlist.first = () => {
      cov_3xfj46l7x().f[20]++;
      cov_3xfj46l7x().s[143]++;

      if (changing) {
        cov_3xfj46l7x().b[45][0]++;
        cov_3xfj46l7x().s[144]++;
        return;
      } else {
        cov_3xfj46l7x().b[45][1]++;
      }

      const newItem = (cov_3xfj46l7x().s[145]++, playlist.currentItem(0));
      cov_3xfj46l7x().s[146]++;

      if (list.length) {
        cov_3xfj46l7x().b[46][0]++;
        cov_3xfj46l7x().s[147]++;
        return (cov_3xfj46l7x().b[47][0]++, list[newItem].originalValue) || (cov_3xfj46l7x().b[47][1]++, list[newItem]);
      } else {
        cov_3xfj46l7x().b[46][1]++;
      }

      cov_3xfj46l7x().s[148]++;
      playlist.currentIndex_ = -1;
    };
    /**
    * Plays the last item in the playlist.
    *
    * @return {Object|undefined}
    *         Returns undefined and has no side effects if the list is empty.
    */


    cov_3xfj46l7x().s[149]++;

    playlist.last = () => {
      cov_3xfj46l7x().f[21]++;
      cov_3xfj46l7x().s[150]++;

      if (changing) {
        cov_3xfj46l7x().b[48][0]++;
        cov_3xfj46l7x().s[151]++;
        return;
      } else {
        cov_3xfj46l7x().b[48][1]++;
      }

      const newItem = (cov_3xfj46l7x().s[152]++, playlist.currentItem(playlist.lastIndex()));
      cov_3xfj46l7x().s[153]++;

      if (list.length) {
        cov_3xfj46l7x().b[49][0]++;
        cov_3xfj46l7x().s[154]++;
        return (cov_3xfj46l7x().b[50][0]++, list[newItem].originalValue) || (cov_3xfj46l7x().b[50][1]++, list[newItem]);
      } else {
        cov_3xfj46l7x().b[49][1]++;
      }

      cov_3xfj46l7x().s[155]++;
      playlist.currentIndex_ = -1;
    };
    /**
    * Plays the next item in the playlist.
    *
    * @param {boolean} [suppressPoster]
    *         Should the native poster be suppressed? Defaults to false.
    * @return {Object|undefined}
    *         Returns undefined and has no side effects if on last item.
    */


    cov_3xfj46l7x().s[156]++;

    playlist.next = (suppressPoster = (cov_3xfj46l7x().b[51][0]++, false)) => {
      cov_3xfj46l7x().f[22]++;
      cov_3xfj46l7x().s[157]++;

      if (changing) {
        cov_3xfj46l7x().b[52][0]++;
        cov_3xfj46l7x().s[158]++;
        return;
      } else {
        cov_3xfj46l7x().b[52][1]++;
      }

      const index = (cov_3xfj46l7x().s[159]++, playlist.nextIndex());
      cov_3xfj46l7x().s[160]++;

      if (index !== playlist.currentIndex_) {
        cov_3xfj46l7x().b[53][0]++;
        const newItem = (cov_3xfj46l7x().s[161]++, playlist.currentItem(index, suppressPoster));
        cov_3xfj46l7x().s[162]++;
        return (cov_3xfj46l7x().b[54][0]++, list[newItem].originalValue) || (cov_3xfj46l7x().b[54][1]++, list[newItem]);
      } else {
        cov_3xfj46l7x().b[53][1]++;
      }
    };
    /**
    * Plays the previous item in the playlist.
    *
    * @return {Object|undefined}
    *         Returns undefined and has no side effects if on first item.
    */


    cov_3xfj46l7x().s[163]++;

    playlist.previous = () => {
      cov_3xfj46l7x().f[23]++;
      cov_3xfj46l7x().s[164]++;

      if (changing) {
        cov_3xfj46l7x().b[55][0]++;
        cov_3xfj46l7x().s[165]++;
        return;
      } else {
        cov_3xfj46l7x().b[55][1]++;
      }

      const index = (cov_3xfj46l7x().s[166]++, playlist.previousIndex());
      cov_3xfj46l7x().s[167]++;

      if (index !== playlist.currentIndex_) {
        cov_3xfj46l7x().b[56][0]++;
        const newItem = (cov_3xfj46l7x().s[168]++, playlist.currentItem(index));
        cov_3xfj46l7x().s[169]++;
        return (cov_3xfj46l7x().b[57][0]++, list[newItem].originalValue) || (cov_3xfj46l7x().b[57][1]++, list[newItem]);
      } else {
        cov_3xfj46l7x().b[56][1]++;
      }
    };
    /**
    * Set up auto-advance on the playlist.
    *
    * @param  {number} [delay]
    *         The number of seconds to wait before each auto-advance.
    */


    cov_3xfj46l7x().s[170]++;

    playlist.autoadvance = delay => {
      cov_3xfj46l7x().f[24]++;
      cov_3xfj46l7x().s[171]++;
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


    cov_3xfj46l7x().s[172]++;

    playlist.repeat = val => {
      cov_3xfj46l7x().f[25]++;
      cov_3xfj46l7x().s[173]++;

      if (val === undefined) {
        cov_3xfj46l7x().b[58][0]++;
        cov_3xfj46l7x().s[174]++;
        return playlist.repeat_;
      } else {
        cov_3xfj46l7x().b[58][1]++;
      }

      cov_3xfj46l7x().s[175]++;

      if (typeof val !== 'boolean') {
        cov_3xfj46l7x().b[59][0]++;
        cov_3xfj46l7x().s[176]++;
        videojs__default["default"].log.error('videojs-playlist: Invalid value for repeat', val);
        cov_3xfj46l7x().s[177]++;
        return;
      } else {
        cov_3xfj46l7x().b[59][1]++;
      }

      cov_3xfj46l7x().s[178]++;
      playlist.repeat_ = !!val;
      cov_3xfj46l7x().s[179]++;
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


    cov_3xfj46l7x().s[180]++;

    playlist.sort = compare => {
      cov_3xfj46l7x().f[26]++;
      cov_3xfj46l7x().s[181]++; // Bail if the array is empty.

      if (!list.length) {
        cov_3xfj46l7x().b[60][0]++;
        cov_3xfj46l7x().s[182]++;
        return;
      } else {
        cov_3xfj46l7x().b[60][1]++;
      }

      cov_3xfj46l7x().s[183]++;
      list.sort(compare); // If the playlist is changing, don't trigger events.

      cov_3xfj46l7x().s[184]++;

      if (changing) {
        cov_3xfj46l7x().b[61][0]++;
        cov_3xfj46l7x().s[185]++;
        return;
      } else {
        cov_3xfj46l7x().b[61][1]++;
      }
      /**
      * Triggered after the playlist is sorted internally.
      *
      * @event playlistsorted
      * @type {Object}
      */


      cov_3xfj46l7x().s[186]++;
      player.trigger('playlistsorted');
    };
    /**
    * Reverses the playlist array.
    *
    * @see {@link https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/reverse}
    * @fires playlistsorted
    */


    cov_3xfj46l7x().s[187]++;

    playlist.reverse = () => {
      cov_3xfj46l7x().f[27]++;
      cov_3xfj46l7x().s[188]++; // Bail if the array is empty.

      if (!list.length) {
        cov_3xfj46l7x().b[62][0]++;
        cov_3xfj46l7x().s[189]++;
        return;
      } else {
        cov_3xfj46l7x().b[62][1]++;
      }

      cov_3xfj46l7x().s[190]++;
      list.reverse(); // If the playlist is changing, don't trigger events.

      cov_3xfj46l7x().s[191]++;

      if (changing) {
        cov_3xfj46l7x().b[63][0]++;
        cov_3xfj46l7x().s[192]++;
        return;
      } else {
        cov_3xfj46l7x().b[63][1]++;
      }
      /**
      * Triggered after the playlist is sorted internally.
      *
      * @event playlistsorted
      * @type {Object}
      */


      cov_3xfj46l7x().s[193]++;
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


    cov_3xfj46l7x().s[194]++;

    playlist.shuffle = ({
      rest
    } = (cov_3xfj46l7x().b[64][0]++, {})) => {
      cov_3xfj46l7x().f[28]++;
      let index = (cov_3xfj46l7x().s[195]++, 0);
      let arr = (cov_3xfj46l7x().s[196]++, list); // When options.rest is true, start randomization at the item after the
      // current item.

      cov_3xfj46l7x().s[197]++;

      if (rest) {
        cov_3xfj46l7x().b[65][0]++;
        cov_3xfj46l7x().s[198]++;
        index = playlist.currentIndex_ + 1;
        cov_3xfj46l7x().s[199]++;
        arr = list.slice(index);
      } else {
        cov_3xfj46l7x().b[65][1]++;
      } // Bail if the array is empty or too short to shuffle.


      cov_3xfj46l7x().s[200]++;

      if (arr.length <= 1) {
        cov_3xfj46l7x().b[66][0]++;
        cov_3xfj46l7x().s[201]++;
        return;
      } else {
        cov_3xfj46l7x().b[66][1]++;
      }

      cov_3xfj46l7x().s[202]++;
      randomize(arr); // When options.rest is true, splice the randomized sub-array back into
      // the original array.

      cov_3xfj46l7x().s[203]++;

      if (rest) {
        cov_3xfj46l7x().b[67][0]++;
        cov_3xfj46l7x().s[204]++;
        list.splice(...[index, arr.length].concat(arr));
      } else {
        cov_3xfj46l7x().b[67][1]++;
      } // If the playlist is changing, don't trigger events.


      cov_3xfj46l7x().s[205]++;

      if (changing) {
        cov_3xfj46l7x().b[68][0]++;
        cov_3xfj46l7x().s[206]++;
        return;
      } else {
        cov_3xfj46l7x().b[68][1]++;
      }
      /**
      * Triggered after the playlist is sorted internally.
      *
      * @event playlistsorted
      * @type {Object}
      */


      cov_3xfj46l7x().s[207]++;
      player.trigger('playlistsorted');
    }; // If an initial list was given, populate the playlist with it.


    cov_3xfj46l7x().s[208]++;

    if (Array.isArray(initialList)) {
      cov_3xfj46l7x().b[69][0]++;
      cov_3xfj46l7x().s[209]++;
      playlist(initialList, initialIndex); // If there is no initial list given, silently set an empty array.
    } else {
      cov_3xfj46l7x().b[69][1]++;
      cov_3xfj46l7x().s[210]++;
      list = [];
    }

    cov_3xfj46l7x().s[211]++;
    return playlist;
  }

  var version = "5.2.0";

  function cov_1u4s9c1d43() {
    var path = "/Users/bzizmond/Documents/projects/bc/videojs-playlist/src/plugin.js";
    var hash = "bd736d29c0aa3449abc580a7676bb97081807986";
    var global = new Function("return this")();
    var gcv = "__coverage__";
    var coverageData = {
      path: "/Users/bzizmond/Documents/projects/bc/videojs-playlist/src/plugin.js",
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
      hash: "bd736d29c0aa3449abc580a7676bb97081807986"
    };
    var coverage = global[gcv] || (global[gcv] = {});

    if (!coverage[path] || coverage[path].hash !== hash) {
      coverage[path] = coverageData;
    }

    var actualCoverage = coverage[path];
    {
      // @ts-ignore
      cov_1u4s9c1d43 = function () {
        return actualCoverage;
      };
    }
    return actualCoverage;
  }

  cov_1u4s9c1d43();

  const registerPlugin = (cov_1u4s9c1d43().s[0]++, (cov_1u4s9c1d43().b[0][0]++, videojs__default["default"].registerPlugin) || (cov_1u4s9c1d43().b[0][1]++, videojs__default["default"].plugin));
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

  cov_1u4s9c1d43().s[1]++;

  const plugin = function (list, item) {
    cov_1u4s9c1d43().f[0]++;
    cov_1u4s9c1d43().s[2]++;
    factory(this, list, item);
  };

  cov_1u4s9c1d43().s[3]++;
  registerPlugin('playlist', plugin);
  cov_1u4s9c1d43().s[4]++;
  plugin.VERSION = version;

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
      if (Array.isArray(type)) {
        ones.push(...type);
      } else {
        ones.push(type);
      }
    };

    setup(player, 0);
    assert.equal(ones.length, 3, 'there should have been three event added');
    assert.deepEqual(ones, ['ended', 'abort', 'error'], 'the events we want to one is "ended", "abort" and "error"');
  });
  QUnit__default["default"].test('off previous listener if exists before adding a new one', function (assert) {
    const player = proxy();
    const ones = [];
    const offs = [];

    player.one = function (type) {
      if (Array.isArray(type)) {
        ones.push(...type);
      } else {
        ones.push(type);
      }
    };

    player.off = function (type) {
      if (Array.isArray(type)) {
        offs.push(...type);
      } else {
        offs.push(type);
      }
    };

    setup(player, 0);
    assert.equal(ones.length, 3, 'there should have been only three one events added');
    assert.deepEqual(ones, ['ended', 'abort', 'error'], 'the events we want to one is "ended", "abort" and "error"');
    assert.equal(offs.length, 0, 'we should not have off-ed anything yet');
    setup(player, 10);
    assert.equal(ones.length, 6, 'there should have been six one event added');
    assert.equal(ones[0], 'ended', 'first event to one is "ended"');
    assert.equal(ones[1], 'abort', 'second event to one is "abort"');
    assert.equal(ones[2], 'error', 'third event to one is "error"');
    assert.equal(ones[3], 'ended', 'fourth event to one is "ended"');
    assert.equal(ones[4], 'abort', 'fifth event to one is "abort"');
    assert.equal(ones[5], 'error', 'sixth event to one is "error"');
    assert.equal(offs.length, 3, 'there should have been three off event added');
    assert.deepEqual(offs.sort(), ['ended', 'abort', 'error'].sort(), 'the events we want to off is "ended", "abort" and "error"');
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
      if (Array.isArray(type)) {
        ones.push(...type);
      } else {
        ones.push(type);
      }
    };

    player.off = function (type) {
      if (Array.isArray(type)) {
        offs.push(...type);
      } else {
        offs.push(type);
      }
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
    assert.equal(offs.length, 24, 'we reset the advance 8 times, removing 3 events each time');
    assert.equal(ones.length, 24, 'we autoadvanced 8 times, adding 3 events each time');
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
  QUnit__default["default"].test('playlist.currentItem() shows a poster by default', function (assert) {
    const player = proxy();
    const playlist = factory(player, videoList);
    playlist.currentItem(0);
    assert.notEqual(player.poster(), '', 'poster is shown for playlist index 0');
  });
  QUnit__default["default"].test('playlist.currentItem() will hide the poster if suppressPoster param is true', function (assert) {
    const player = proxy();
    const playlist = factory(player, videoList);
    playlist.currentItem(1, true);
    assert.equal(player.poster(), '', 'poster is suppressed');
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

})(QUnit, sinon, videojs);
