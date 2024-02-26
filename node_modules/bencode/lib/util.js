const util = module.exports

util.digitCount = function digitCount (value) {
  // Add a digit for negative numbers, as the sign will be prefixed
  const sign = value < 0 ? 1 : 0
  // Guard against negative numbers & zero going into log10(),
  // as that would return -Infinity
  value = Math.abs(Number(value || 1))
  return Math.floor(Math.log10(value)) + 1 + sign
}

util.getType = function getType (value) {
  if (Buffer.isBuffer(value)) return 'buffer'
  if (ArrayBuffer.isView(value)) return 'arraybufferview'
  if (Array.isArray(value)) return 'array'
  if (value instanceof Number) return 'number'
  if (value instanceof Boolean) return 'boolean'
  if (value instanceof Set) return 'set'
  if (value instanceof Map) return 'map'
  if (value instanceof String) return 'string'
  if (value instanceof ArrayBuffer) return 'arraybuffer'
  return typeof value
}
