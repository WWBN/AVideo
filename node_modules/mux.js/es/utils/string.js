/**
 * Returns the first string in the data array ending with a null char '\0'
 * @param {UInt8} data 
 * @returns the string with the null char
 */
var uint8ToCString = function uint8ToCString(data) {
  var index = 0;
  var curChar = String.fromCharCode(data[index]);
  var retString = '';

  while (curChar !== '\0') {
    retString += curChar;
    index++;
    curChar = String.fromCharCode(data[index]);
  } // Add nullChar


  retString += curChar;
  return retString;
};

module.exports = {
  uint8ToCString: uint8ToCString
};