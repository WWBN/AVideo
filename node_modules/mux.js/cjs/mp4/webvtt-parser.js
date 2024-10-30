"use strict";

var _require = require("../tools/mp4-inspector"),
    parseTfdt = _require.parseTfdt;

var findBox = require("./find-box");

var _require2 = require("./probe"),
    getTimescaleFromMediaHeader = _require2.getTimescaleFromMediaHeader;

var _require3 = require("./samples"),
    parseSamples = _require3.parseSamples,
    getMdatTrafPairs = _require3.getMdatTrafPairs;
/**
 * Module for parsing WebVTT text and styles from FMP4 segments.
 * Based on the ISO/IEC 14496-30.
 */


var WebVttParser = function WebVttParser() {
  // default timescale to 90k
  var timescale = 90e3;
  /**
   * Parses the timescale from the init segment.
   * @param {Array<Uint8Array>} segment The initialization segment to parse the timescale from.
   */

  this.init = function (segment) {
    // We just need the timescale from the init segment.
    var mdhd = findBox(segment, ['moov', 'trak', 'mdia', 'mdhd'])[0];

    if (mdhd) {
      timescale = getTimescaleFromMediaHeader(mdhd);
    }
  };
  /**
   * Parses a WebVTT FMP4 segment.
   * @param {Array<Uint8Array>} segment The content segment to parse the WebVTT cues from.
   * @returns The WebVTT cue text, styling, and timing info as an array of cue objects.
   */


  this.parseSegment = function (segment) {
    var vttCues = [];
    var mdatTrafPairs = getMdatTrafPairs(segment);
    var baseMediaDecodeTime = 0;
    mdatTrafPairs.forEach(function (pair) {
      var mdatBox = pair.mdat;
      var trafBox = pair.traf; // zero or one.

      var tfdtBox = findBox(trafBox, ['tfdt'])[0]; // zero or one.

      var tfhdBox = findBox(trafBox, ['tfhd'])[0]; // zero or more.

      var trunBoxes = findBox(trafBox, ['trun']);

      if (tfdtBox) {
        var tfdt = parseTfdt(tfdtBox);
        baseMediaDecodeTime = tfdt.baseMediaDecodeTime;
      }

      if (trunBoxes.length && tfhdBox) {
        var samples = parseSamples(trunBoxes, baseMediaDecodeTime, tfhdBox);
        var mdatOffset = 0;
        samples.forEach(function (sample) {
          // decode utf8 payload
          var UTF_8 = 'utf-8';
          var textDecoder = new TextDecoder(UTF_8); // extract sample data from the mdat box.
          // WebVTT Sample format:
          // Exactly one VTTEmptyCueBox box
          // OR one or more VTTCueBox boxes.

          var sampleData = mdatBox.slice(mdatOffset, mdatOffset + sample.size); // single vtte box.

          var vtteBox = findBox(sampleData, ['vtte'])[0]; // empty box

          if (vtteBox) {
            mdatOffset += sample.size;
            return;
          } // TODO: Support 'vtta' boxes.
          // VTTAdditionalTextBoxes can be interleaved between VTTCueBoxes.


          var vttcBoxes = findBox(sampleData, ['vttc']);
          vttcBoxes.forEach(function (vttcBox) {
            // mandatory payload box.
            var paylBox = findBox(vttcBox, ['payl'])[0]; // optional settings box

            var sttgBox = findBox(vttcBox, ['sttg'])[0];
            var start = sample.pts / timescale;
            var end = (sample.pts + sample.duration) / timescale;
            var cueText, settings; // contains cue text.

            if (paylBox) {
              try {
                cueText = textDecoder.decode(paylBox);
              } catch (e) {
                console.error(e);
              }
            } // settings box contains styling.


            if (sttgBox) {
              try {
                settings = textDecoder.decode(sttgBox);
              } catch (e) {
                console.error(e);
              }
            }

            if (sample.duration && cueText) {
              vttCues.push({
                cueText: cueText,
                start: start,
                end: end,
                settings: settings
              });
            }
          });
          mdatOffset += sample.size;
        });
      }
    });
    return vttCues;
  };
};

module.exports = WebVttParser;