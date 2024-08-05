'use strict'

const isBuffer = require('is-buffer')
const isTypedArray = require('./util').isTypedArray
const assertAsync = require('./util').assertAsync

let db

/**
 * @param {import('tape')} test
 */
exports.setUp = function (test, testCommon) {
  test('setUp db', function (t) {
    db = testCommon.factory()
    db.open(t.end.bind(t))
  })
}

/**
 * @param {import('tape')} test
 */
exports.args = function (test, testCommon) {
  test('test getMany() requires an array argument (callback)', assertAsync.ctx(function (t) {
    // Add 1 assertion for every assertAsync()
    t.plan(4)

    db.getMany('foo', assertAsync(function (err) {
      t.is(err && err.message, 'getMany() requires an array argument')
    }))
    db.getMany('foo', {}, assertAsync(function (err) {
      t.is(err && err.message, 'getMany() requires an array argument')
    }))
  }))

  test('test getMany() requires an array argument (promise)', function (t) {
    t.plan(3)

    db.getMany().catch(function (err) {
      t.is(err && err.message, 'getMany() requires an array argument')
    })
    db.getMany('foo').catch(function (err) {
      t.is(err && err.message, 'getMany() requires an array argument')
    })
    db.getMany('foo', {}).catch(function (err) {
      t.is(err && err.message, 'getMany() requires an array argument')
    })
  })
}

/**
 * @param {import('tape')} test
 */
exports.getMany = function (test, testCommon) {
  test('test getMany() support is reflected in manifest', function (t) {
    t.is(db.supports && db.supports.getMany, true)
    t.end()
  })

  test('test simple getMany()', function (t) {
    db.put('foo', 'bar', function (err) {
      t.error(err)

      function verify (err, values) {
        t.error(err)
        t.ok(Array.isArray(values), 'got an array')
        t.is(values.length, 1, 'array has 1 element')

        const value = values[0]
        let result

        if (!testCommon.encodings) {
          t.isNot(typeof value, 'string', 'should not be string by default')

          if (isTypedArray(value)) {
            result = String.fromCharCode.apply(null, new Uint16Array(value))
          } else {
            t.ok(isBuffer(value))
            try {
              result = value.toString()
            } catch (e) {
              t.error(e, 'should not throw when converting value to a string')
            }
          }
        } else {
          result = value
        }

        t.is(result, 'bar')
      }

      db.getMany(['foo'], function (err, values) {
        verify(err, values)

        db.getMany(['foo'], {}, function (err, values) {
          verify(err, values)

          db.getMany(['foo'], { asBuffer: false }, function (err, values) {
            t.error(err)
            t.is(values && typeof values[0], 'string', 'should be string if not buffer')
            t.same(values, ['bar'])
            t.end()
          })
        })
      })
    })
  })

  test('test getMany() with multiple keys', function (t) {
    t.plan(5)

    db.put('beep', 'boop', function (err) {
      t.ifError(err)

      db.getMany(['foo', 'beep'], { asBuffer: false }, function (err, values) {
        t.ifError(err)
        t.same(values, ['bar', 'boop'])
      })

      db.getMany(['beep', 'foo'], { asBuffer: false }, function (err, values) {
        t.ifError(err)
        t.same(values, ['boop', 'bar'], 'maintains order of input keys')
      })
    })
  })

  test('test empty getMany()', assertAsync.ctx(function (t) {
    t.plan(2 * 3)

    for (const asBuffer in [true, false]) {
      db.getMany([], { asBuffer }, assertAsync(function (err, values) {
        t.ifError(err)
        t.same(values, [])
      }))
    }
  }))

  test('test not-found getMany()', assertAsync.ctx(function (t) {
    t.plan(2 * 3)

    for (const asBuffer in [true, false]) {
      db.getMany(['nope', 'another'], { asBuffer }, assertAsync(function (err, values) {
        t.ifError(err)
        t.same(values, [undefined, undefined])
      }))
    }
  }))

  test('test getMany() with promise', async function (t) {
    t.same(await db.getMany(['foo'], { asBuffer: false }), ['bar'])
    t.same(await db.getMany(['beep'], { asBuffer: false }), ['boop'])
    t.same(await db.getMany(['foo', 'beep'], { asBuffer: false }), ['bar', 'boop'])
    t.same(await db.getMany(['beep', 'foo'], { asBuffer: false }), ['boop', 'bar'])
    t.same(await db.getMany(['beep', 'foo', 'nope'], { asBuffer: false }), ['boop', 'bar', undefined])
    t.same(await db.getMany([], { asBuffer: false }), [])
  })

  test('test simultaneous getMany()', function (t) {
    db.put('hello', 'world', function (err) {
      t.error(err)

      let completed = 0
      const done = function () {
        if (++completed === 20) t.end()
      }

      for (let i = 0; i < 10; ++i) {
        db.getMany(['hello'], function (err, values) {
          t.error(err)
          t.is(values.length, 1)
          t.is(values[0] && values[0].toString(), 'world')
          done()
        })
      }

      for (let i = 0; i < 10; ++i) {
        db.getMany(['not found'], function (err, values) {
          t.error(err)
          t.same(values, [undefined])
          done()
        })
      }
    })
  })

  test('test getMany() on new db', assertAsync.ctx(function (t) {
    t.plan(2 * 2 * 5)

    // Also test empty array because it has a fast-path
    for (const keys of [['foo'], []]) {
      // Opening should make no difference, because we call it after getMany()
      for (const open of [true, false]) {
        const db = testCommon.factory()

        if (testCommon.status) {
          t.is(db.status, testCommon.deferredOpen ? 'opening' : 'new')
        } else {
          t.pass('no status')
        }

        // Must be true if db supports deferredOpen
        const operational = testCommon.deferredOpen || db.isOperational()

        db.getMany(keys, assertAsync(function (err, values) {
          if (operational) {
            t.ifError(err, 'no error')
            t.same(values, keys.map(_ => undefined))
          } else {
            t.is(err && err.message, 'Database is not open')
            t.is(values, undefined)
          }
        }))

        if (open) {
          db.open(t.error.bind(t))
        } else {
          t.pass()
        }
      }
    }
  }))

  test('test getMany() on opening db', assertAsync.ctx(function (t) {
    t.plan(2 * 5)

    // Also test empty array because it has a fast-path
    for (const keys of [['foo'], []]) {
      const db = testCommon.factory()

      // Is a noop if db supports deferredOpen
      db.open(assertAsync(t.error.bind(t), 'open'))

      // Must be true if db supports deferredOpen
      const operational = testCommon.deferredOpen || db.isOperational()

      db.getMany(keys, assertAsync(function (err, values) {
        if (operational) {
          t.ifError(err, 'no error')
          t.same(values, keys.map(_ => undefined))
        } else {
          t.is(err && err.message, 'Database is not open')
          t.is(values, undefined)
        }
      }))
    }
  }))

  test('test getMany() on closed db', function (t) {
    t.plan(2 * 6)

    // Also test empty array because it has a fast-path
    for (const keys of [['foo'], []]) {
      const db = testCommon.factory()

      db.open(function (err) {
        t.ifError(err)
        t.is(db.isOperational(), true)

        db.close(assertAsync.with(t, function (err) {
          t.ifError(err)
          t.is(db.isOperational(), false)

          db.getMany(keys, assertAsync(function (err) {
            t.is(err && err.message, 'Database is not open')
          }))
        }))
      })
    }
  })

  test('test getMany() on closing db', function (t) {
    t.plan(2 * 4)

    // Also test empty array because it has a fast-path
    for (const keys of [['foo'], []]) {
      const db = testCommon.factory()

      db.open(assertAsync.with(t, function (err) {
        t.ifError(err)

        db.close(function (err) {
          t.ifError(err)
        })

        db.getMany(keys, assertAsync(function (err) {
          t.is(err && err.message, 'Database is not open')
        }))
      }))
    }
  })
}

/**
 * @param {import('tape')} test
 */
exports.tearDown = function (test, testCommon) {
  test('tearDown', function (t) {
    db.close(t.end.bind(t))
  })
}

/**
 * @param {import('tape')} test
 */
exports.all = function (test, testCommon) {
  exports.setUp(test, testCommon)
  exports.args(test, testCommon)
  exports.getMany(test, testCommon)
  exports.tearDown(test, testCommon)
}
