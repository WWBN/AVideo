'use strict'

const nfre = /NotFound/i
const spies = []

exports.verifyNotFoundError = function verifyNotFoundError (err) {
  return nfre.test(err.message) || nfre.test(err.name)
}

exports.isTypedArray = function isTypedArray (value) {
  return (typeof ArrayBuffer !== 'undefined' && value instanceof ArrayBuffer) ||
    (typeof Uint8Array !== 'undefined' && value instanceof Uint8Array)
}

/**
 * Wrap a callback to check that it's called asynchronously. Must be
 * combined with a `ctx()`, `with()` or `end()` call.
 *
 * @param {function} cb Callback to check.
 * @param {string} name Optional callback name to use in assertion messages.
 * @returns {function} Wrapped callback.
 */
exports.assertAsync = function (cb, name) {
  const spy = {
    called: false,
    name: name || cb.name || 'anonymous'
  }

  spies.push(spy)

  return function (...args) {
    spy.called = true
    return cb.apply(this, args)
  }
}

/**
 * Verify that callbacks wrapped with `assertAsync()` were not yet called.
 * @param {import('tape').Test} t Tape test object.
 */
exports.assertAsync.end = function (t) {
  for (const { called, name } of spies.splice(0, spies.length)) {
    t.is(called, false, `callback (${name}) is asynchronous`)
  }
}

/**
 * Wrap a test function to verify `assertAsync()` spies at the end.
 * @param {import('tape').TestCase} test Test function to be passed to `tape()`.
 * @returns {import('tape').TestCase} Wrapped test function.
 */
exports.assertAsync.ctx = function (test) {
  return function (...args) {
    const ret = test.call(this, ...args)
    exports.assertAsync.end(args[0])
    return ret
  }
}

/**
 * Wrap an arbitrary callback to verify `assertAsync()` spies at the end.
 * @param {import('tape').Test} t Tape test object.
 * @param {function} cb Callback to wrap.
 * @returns {function} Wrapped callback.
 */
exports.assertAsync.with = function (t, cb) {
  return function (...args) {
    const ret = cb.call(this, ...args)
    exports.assertAsync.end(t)
    return ret
  }
}
