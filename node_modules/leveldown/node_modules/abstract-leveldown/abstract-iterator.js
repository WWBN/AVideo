'use strict'

function AbstractIterator (db) {
  if (typeof db !== 'object' || db === null) {
    throw new TypeError('First argument must be an abstract-leveldown compliant store')
  }

  this.db = db
  this._ended = false
  this._nexting = false
}

AbstractIterator.prototype.next = function (callback) {
  // In callback mode, we return `this`
  let ret = this

  if (callback === undefined) {
    ret = new Promise(function (resolve, reject) {
      callback = function (err, key, value) {
        if (err) reject(err)
        else if (key === undefined && value === undefined) resolve()
        else resolve([key, value])
      }
    })
  } else if (typeof callback !== 'function') {
    throw new Error('next() requires a callback argument')
  }

  if (this._ended) {
    this._nextTick(callback, new Error('cannot call next() after end()'))
    return ret
  }

  if (this._nexting) {
    this._nextTick(callback, new Error('cannot call next() before previous next() has completed'))
    return ret
  }

  this._nexting = true
  this._next((err, ...rest) => {
    this._nexting = false
    callback(err, ...rest)
  })

  return ret
}

AbstractIterator.prototype._next = function (callback) {
  this._nextTick(callback)
}

AbstractIterator.prototype.seek = function (target) {
  if (this._ended) {
    throw new Error('cannot call seek() after end()')
  }
  if (this._nexting) {
    throw new Error('cannot call seek() before next() has completed')
  }

  target = this.db._serializeKey(target)
  this._seek(target)
}

AbstractIterator.prototype._seek = function (target) {}

AbstractIterator.prototype.end = function (callback) {
  let promise

  if (callback === undefined) {
    promise = new Promise(function (resolve, reject) {
      callback = function (err) {
        if (err) reject(err)
        else resolve()
      }
    })
  } else if (typeof callback !== 'function') {
    throw new Error('end() requires a callback argument')
  }

  if (this._ended) {
    this._nextTick(callback, new Error('end() already called on iterator'))
    return promise
  }

  this._ended = true
  this._end(callback)

  return promise
}

AbstractIterator.prototype._end = function (callback) {
  this._nextTick(callback)
}

AbstractIterator.prototype[Symbol.asyncIterator] = async function * () {
  try {
    let kv

    while ((kv = (await this.next())) !== undefined) {
      yield kv
    }
  } finally {
    if (!this._ended) await this.end()
  }
}

// Expose browser-compatible nextTick for dependents
AbstractIterator.prototype._nextTick = require('./next-tick')

module.exports = AbstractIterator
