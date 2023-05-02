const { getType } = require('./util.js')

/**
 * Encodes data in bencode.
 *
 * @param  {Buffer|Array|String|Object|Number|Boolean} data
 * @return {Buffer}
 */
function encode (data, buffer, offset) {
  const buffers = []
  let result = null

  encode._encode(buffers, data)
  result = Buffer.concat(buffers)
  encode.bytes = result.length

  if (Buffer.isBuffer(buffer)) {
    result.copy(buffer, offset)
    return buffer
  }

  return result
}

encode.bytes = -1
encode._floatConversionDetected = false

encode._encode = function (buffers, data) {
  if (data == null) { return }

  switch (getType(data)) {
    case 'buffer': encode.buffer(buffers, data); break
    case 'object': encode.dict(buffers, data); break
    case 'map': encode.dictMap(buffers, data); break
    case 'array': encode.list(buffers, data); break
    case 'set': encode.listSet(buffers, data); break
    case 'string': encode.string(buffers, data); break
    case 'number': encode.number(buffers, data); break
    case 'boolean': encode.number(buffers, data); break
    case 'arraybufferview': encode.buffer(buffers, Buffer.from(data.buffer, data.byteOffset, data.byteLength)); break
    case 'arraybuffer': encode.buffer(buffers, Buffer.from(data)); break
  }
}

const buffE = Buffer.from('e')
const buffD = Buffer.from('d')
const buffL = Buffer.from('l')

encode.buffer = function (buffers, data) {
  buffers.push(Buffer.from(data.length + ':'), data)
}

encode.string = function (buffers, data) {
  buffers.push(Buffer.from(Buffer.byteLength(data) + ':' + data))
}

encode.number = function (buffers, data) {
  const maxLo = 0x80000000
  const hi = (data / maxLo) << 0
  const lo = (data % maxLo) << 0
  const val = hi * maxLo + lo

  buffers.push(Buffer.from('i' + val + 'e'))

  if (val !== data && !encode._floatConversionDetected) {
    encode._floatConversionDetected = true
    console.warn(
      'WARNING: Possible data corruption detected with value "' + data + '":',
      'Bencoding only defines support for integers, value was converted to "' + val + '"'
    )
    console.trace()
  }
}

encode.dict = function (buffers, data) {
  buffers.push(buffD)

  let j = 0
  let k
  // fix for issue #13 - sorted dicts
  const keys = Object.keys(data).sort()
  const kl = keys.length

  for (; j < kl; j++) {
    k = keys[j]
    if (data[k] == null) continue
    encode.string(buffers, k)
    encode._encode(buffers, data[k])
  }

  buffers.push(buffE)
}

encode.dictMap = function (buffers, data) {
  buffers.push(buffD)

  const keys = Array.from(data.keys()).sort()

  for (const key of keys) {
    if (data.get(key) == null) continue
    Buffer.isBuffer(key)
      ? encode._encode(buffers, key)
      : encode.string(buffers, String(key))
    encode._encode(buffers, data.get(key))
  }

  buffers.push(buffE)
}

encode.list = function (buffers, data) {
  let i = 0
  const c = data.length
  buffers.push(buffL)

  for (; i < c; i++) {
    if (data[i] == null) continue
    encode._encode(buffers, data[i])
  }

  buffers.push(buffE)
}

encode.listSet = function (buffers, data) {
  buffers.push(buffL)

  for (const item of data) {
    if (item == null) continue
    encode._encode(buffers, item)
  }

  buffers.push(buffE)
}

module.exports = encode
