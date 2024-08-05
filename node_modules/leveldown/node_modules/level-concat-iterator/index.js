'use strict'

const { fromCallback } = require('catering')
const kPromise = Symbol('promise')

module.exports = function (iterator, callback) {
  callback = fromCallback(callback, kPromise)

  // Use close() method of abstract-level or end() of abstract-leveldown
  const close = typeof iterator.close === 'function' ? 'close' : 'end'
  const entries = []

  const onnext = function (err, key, value) {
    if (err || (key === undefined && value === undefined)) {
      return iterator[close](function (err2) {
        callback(err || err2, entries)
      })
    }
    entries.push({ key, value })
    iterator.next(onnext)
  }

  iterator.next(onnext)
  return callback[kPromise]
}
