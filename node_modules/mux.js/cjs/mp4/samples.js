"use strict";

var _require = require("../tools/mp4-inspector"),
    parseTrun = _require.parseTrun;

var _require2 = require("./probe"),
    findBox = _require2.findBox;

var window = require('global/window');
/**
 * Utility function for parsing data from mdat boxes.
 * @param {Array<Uint8Array>} segment the segment data to create mdat/traf pairs from.
 * @returns mdat and traf boxes paired up for easier parsing.
 */


var getMdatTrafPairs = function getMdatTrafPairs(segment) {
  var trafs = findBox(segment, ['moof', 'traf']);
  var mdats = findBox(segment, ['mdat']);
  var mdatTrafPairs = []; // Pair up each traf with a mdat as moofs and mdats are in pairs

  mdats.forEach(function (mdat, index) {
    var matchingTraf = trafs[index];
    mdatTrafPairs.push({
      mdat: mdat,
      traf: matchingTraf
    });
  });
  return mdatTrafPairs;
};
/**
  * Parses sample information out of Track Run Boxes and calculates
  * the absolute presentation and decode timestamps of each sample.
  *
  * @param {Array<Uint8Array>} truns - The Trun Run boxes to be parsed
  * @param {Number|BigInt} baseMediaDecodeTime - base media decode time from tfdt
      @see ISO-BMFF-12/2015, Section 8.8.12
  * @param {Object} tfhd - The parsed Track Fragment Header
  *   @see inspect.parseTfhd
  * @return {Object[]} the parsed samples
  *
  * @see ISO-BMFF-12/2015, Section 8.8.8
 **/


var parseSamples = function parseSamples(truns, baseMediaDecodeTime, tfhd) {
  var currentDts = baseMediaDecodeTime;
  var defaultSampleDuration = tfhd.defaultSampleDuration || 0;
  var defaultSampleSize = tfhd.defaultSampleSize || 0;
  var trackId = tfhd.trackId;
  var allSamples = [];
  truns.forEach(function (trun) {
    // Note: We currently do not parse the sample table as well
    // as the trun. It's possible some sources will require this.
    // moov > trak > mdia > minf > stbl
    var trackRun = parseTrun(trun);
    var samples = trackRun.samples;
    samples.forEach(function (sample) {
      if (sample.duration === undefined) {
        sample.duration = defaultSampleDuration;
      }

      if (sample.size === undefined) {
        sample.size = defaultSampleSize;
      }

      sample.trackId = trackId;
      sample.dts = currentDts;

      if (sample.compositionTimeOffset === undefined) {
        sample.compositionTimeOffset = 0;
      }

      if (typeof currentDts === 'bigint') {
        sample.pts = currentDts + window.BigInt(sample.compositionTimeOffset);
        currentDts += window.BigInt(sample.duration);
      } else {
        sample.pts = currentDts + sample.compositionTimeOffset;
        currentDts += sample.duration;
      }
    });
    allSamples = allSamples.concat(samples);
  });
  return allSamples;
};

module.exports = {
  getMdatTrafPairs: getMdatTrafPairs,
  parseSamples: parseSamples
};