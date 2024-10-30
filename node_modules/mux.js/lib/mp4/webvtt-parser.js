const { parseTfdt } = require("../tools/mp4-inspector");
const findBox = require("./find-box");
const { getTimescaleFromMediaHeader } = require("./probe");
const { parseSamples, getMdatTrafPairs } = require("./samples");

/**
 * Module for parsing WebVTT text and styles from FMP4 segments.
 * Based on the ISO/IEC 14496-30.
 */
const WebVttParser = function() {
  // default timescale to 90k
  let timescale = 90e3;

  /**
   * Parses the timescale from the init segment.
   * @param {Array<Uint8Array>} segment The initialization segment to parse the timescale from.
   */
  this.init = function(segment) {
    // We just need the timescale from the init segment.
    const mdhd = findBox(segment, ['moov', 'trak', 'mdia', 'mdhd'])[0];

    if (mdhd) {
      timescale = getTimescaleFromMediaHeader(mdhd);
    }
  };

  /**
   * Parses a WebVTT FMP4 segment.
   * @param {Array<Uint8Array>} segment The content segment to parse the WebVTT cues from.
   * @returns The WebVTT cue text, styling, and timing info as an array of cue objects.
   */
  this.parseSegment = function(segment) {
    const vttCues = [];
    const mdatTrafPairs = getMdatTrafPairs(segment);
    let baseMediaDecodeTime = 0;

    mdatTrafPairs.forEach(function(pair) {
      const mdatBox = pair.mdat;
      const trafBox = pair.traf;
      // zero or one.
      const tfdtBox = findBox(trafBox, ['tfdt'])[0];
      // zero or one.
      const tfhdBox = findBox(trafBox, ['tfhd'])[0];
      // zero or more.
      const trunBoxes = findBox(trafBox, ['trun']);
  
      if (tfdtBox) {
        const tfdt = parseTfdt(tfdtBox);
  
        baseMediaDecodeTime = tfdt.baseMediaDecodeTime;
      }

      if (trunBoxes.length && tfhdBox) {
        const samples = parseSamples(trunBoxes, baseMediaDecodeTime, tfhdBox);
        let mdatOffset = 0;

        samples.forEach(function(sample) {
          // decode utf8 payload
          const UTF_8 = 'utf-8';
          const textDecoder = new TextDecoder(UTF_8);
          // extract sample data from the mdat box.
          // WebVTT Sample format:
          // Exactly one VTTEmptyCueBox box
          // OR one or more VTTCueBox boxes.
          const sampleData = mdatBox.slice(mdatOffset, mdatOffset + sample.size);
          // single vtte box.
          const vtteBox = findBox(sampleData, ['vtte'])[0];

          // empty box
          if (vtteBox) {
            mdatOffset += sample.size;
            return;
          }

          // TODO: Support 'vtta' boxes.
          // VTTAdditionalTextBoxes can be interleaved between VTTCueBoxes.

          const vttcBoxes = findBox(sampleData, ['vttc']);

          vttcBoxes.forEach(function(vttcBox) {
            // mandatory payload box.
            const paylBox = findBox(vttcBox, ['payl'])[0];
            // optional settings box
            const sttgBox = findBox(vttcBox, ['sttg'])[0];
            const start = sample.pts / timescale;
            const end = (sample.pts + sample.duration) / timescale;
            let cueText, settings;

            // contains cue text.
            if (paylBox) {
              try {
                cueText = textDecoder.decode(paylBox);
              } catch(e) {
                console.error(e);
              }
            }

            // settings box contains styling.
            if (sttgBox) {
              try {
                settings = textDecoder.decode(sttgBox);
              } catch(e) {
                console.error(e);
              }
            }

            if (sample.duration && cueText) {
              vttCues.push({
                cueText,
                start,
                end,
                settings
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
