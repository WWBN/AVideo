const bencode = module.exports

bencode.encode = require('./encode.js')
bencode.decode = require('./decode.js')

/**
 * Determines the amount of bytes
 * needed to encode the given value
 * @param  {Object|Array|Buffer|String|Number|Boolean} value
 * @return {Number} byteCount
 */
bencode.byteLength = bencode.encodingLength = require('./encoding-length.js')
