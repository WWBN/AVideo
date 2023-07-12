var uint8ToCString = require('../utils/string.js').uint8ToCString;
var getUint64 = require('../utils/numbers.js').getUint64;

/**
 * Based on: ISO/IEC 23009 Section: 5.10.3.3
 * References:
 * https://dashif-documents.azurewebsites.net/Events/master/event.html#emsg-format
 * https://aomediacodec.github.io/id3-emsg/
 * 
 * Takes emsg box data as a uint8 array and returns a emsg box object
 * @param {UInt8Array} boxData data from emsg box
 * @returns A parsed emsg box object
 */
var parseEmsgBox = function(boxData) {
  // version + flags
  var offset = 4;
  var version = boxData[0];
  var scheme_id_uri, 
    value, 
    timescale, 
    presentation_time, 
    presentation_time_delta, 
    event_duration, 
    id,
    message_data;
  if (version === 0) {
    scheme_id_uri = uint8ToCString(boxData.subarray(offset));
    offset += scheme_id_uri.length;
    value = uint8ToCString(boxData.subarray(offset));
    offset += value.length;
    var dv = new DataView(boxData.buffer);
    timescale = dv.getUint32(offset);
    offset += 4;
    presentation_time_delta = dv.getUint32(offset);
    offset += 4;
    event_duration = dv.getUint32(offset);
    offset += 4;
    id = dv.getUint32(offset);
    offset += 4;
  } else if (version === 1) {
    var dv = new DataView(boxData.buffer);
    timescale = dv.getUint32(offset);
    offset += 4;
    presentation_time = getUint64(boxData.subarray(offset));
    offset += 8;
    event_duration = dv.getUint32(offset);
    offset += 4;
    id = dv.getUint32(offset);
    offset += 4;
    scheme_id_uri = uint8ToCString(boxData.subarray(offset));
    offset += scheme_id_uri.length;
    value = uint8ToCString(boxData.subarray(offset));
    offset += value.length;        
  }

  message_data = new Uint8Array(boxData.subarray(offset, boxData.byteLength));
  var emsgBox = {
    scheme_id_uri, 
    value,
    // if timescale is undefined or 0 set to 1 
    timescale: timescale ? timescale : 1, 
    presentation_time, 
    presentation_time_delta, 
    event_duration, 
    id,
    message_data };

  return isValidEmsgBox(version, emsgBox) ? emsgBox : undefined;
};

/**
 * Scales a presentation time or time delta with an offset with a provided timescale
 * @param {number} presentationTime 
 * @param {number} timescale 
 * @param {number} timeDelta 
 * @param {number} offset 
 * @returns the scaled time as a number
 */
var scaleTime = function(presentationTime, timescale, timeDelta, offset) {
  return presentationTime || presentationTime === 0 ? presentationTime / timescale : offset + timeDelta / timescale;
};

/**
 * Checks the emsg box data for validity based on the version
 * @param {number} version of the emsg box to validate
 * @param {Object} emsg the emsg data to validate
 * @returns if the box is valid as a boolean
 */
var isValidEmsgBox = function(version, emsg) {
  var hasScheme = emsg.scheme_id_uri !== '\0'
  var isValidV0Box = version === 0 && isDefined(emsg.presentation_time_delta) && hasScheme;
  var isValidV1Box = version === 1 && isDefined(emsg.presentation_time) && hasScheme;
  // Only valid versions of emsg are 0 and 1
  return !(version > 1) && isValidV0Box || isValidV1Box;
};

// Utility function to check if an object is defined
var isDefined = function(data) {
  return data !== undefined || data !== null;
};

module.exports = { 
  parseEmsgBox: parseEmsgBox, 
  scaleTime: scaleTime
};
