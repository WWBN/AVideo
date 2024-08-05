'use strict'

exports.all = function (test, testCommon) {
  exports.setUp(test, testCommon)
  exports.sequence(test, testCommon)
  exports.seek(test, testCommon)
  exports.tearDown(test, testCommon)
}

exports.setUp = function (test, testCommon) {
  test('setUp common', testCommon.setUp)
}

exports.sequence = function (test, testCommon) {
  function make (name, testFn) {
    test(name, function (t) {
      const db = testCommon.factory()
      const done = function (err) {
        t.error(err, 'no error from done()')

        db.close(function (err) {
          t.error(err, 'no error from close()')
          t.end()
        })
      }

      db.open(function (err) {
        t.error(err, 'no error from open()')
        testFn(db, t, done)
      })
    })
  }

  make('iterator#seek() throws if next() has not completed', function (db, t, done) {
    const ite = db.iterator()
    let error
    let async = false

    ite.next(function (err, key, value) {
      t.error(err, 'no error from next()')
      t.ok(async, 'next is asynchronous')
      ite.end(done)
    })

    async = true

    try {
      ite.seek('two')
    } catch (err) {
      error = err.message
    }

    t.is(error, 'cannot call seek() before next() has completed', 'got error')
  })

  make('iterator#seek() throws after end()', function (db, t, done) {
    const ite = db.iterator()

    // TODO: why call next? Can't we end immediately?
    ite.next(function (err, key, value) {
      t.error(err, 'no error from next()')

      ite.end(function (err) {
        t.error(err, 'no error from end()')
        let error

        try {
          ite.seek('two')
        } catch (err) {
          error = err.message
        }

        t.is(error, 'cannot call seek() after end()', 'got error')
        done()
      })
    })
  })
}

exports.seek = function (test, testCommon) {
  function make (name, testFn) {
    test(name, function (t) {
      const db = testCommon.factory()
      const done = function (err) {
        t.error(err, 'no error from done()')

        db.close(function (err) {
          t.error(err, 'no error from close()')
          t.end()
        })
      }

      db.open(function (err) {
        t.error(err, 'no error from open()')

        db.batch([
          { type: 'put', key: 'one', value: '1' },
          { type: 'put', key: 'two', value: '2' },
          { type: 'put', key: 'three', value: '3' }
        ], function (err) {
          t.error(err, 'no error from batch()')
          testFn(db, t, done)
        })
      })
    })
  }

  make('iterator#seek() to string target', function (db, t, done) {
    const ite = db.iterator()
    ite.seek('two')
    ite.next(function (err, key, value) {
      t.error(err, 'no error')
      t.same(key.toString(), 'two', 'key matches')
      t.same(value.toString(), '2', 'value matches')
      ite.next(function (err, key, value) {
        t.error(err, 'no error')
        t.same(key, undefined, 'end of iterator')
        t.same(value, undefined, 'end of iterator')
        ite.end(done)
      })
    })
  })

  if (testCommon.bufferKeys) {
    make('iterator#seek() to buffer target', function (db, t, done) {
      const ite = db.iterator()
      ite.seek(Buffer.from('two'))
      ite.next(function (err, key, value) {
        t.error(err, 'no error from next()')
        t.equal(key.toString(), 'two', 'key matches')
        t.equal(value.toString(), '2', 'value matches')
        ite.next(function (err, key, value) {
          t.error(err, 'no error from next()')
          t.equal(key, undefined, 'end of iterator')
          t.equal(value, undefined, 'end of iterator')
          ite.end(done)
        })
      })
    })
  }

  make('iterator#seek() on reverse iterator', function (db, t, done) {
    const ite = db.iterator({ reverse: true, limit: 1 })
    ite.seek('three!')
    ite.next(function (err, key, value) {
      t.error(err, 'no error')
      t.same(key.toString(), 'three', 'key matches')
      t.same(value.toString(), '3', 'value matches')
      ite.end(done)
    })
  })

  make('iterator#seek() to out of range target', function (db, t, done) {
    const ite = db.iterator()
    ite.seek('zzz')
    ite.next(function (err, key, value) {
      t.error(err, 'no error')
      t.same(key, undefined, 'end of iterator')
      t.same(value, undefined, 'end of iterator')
      ite.end(done)
    })
  })

  make('iterator#seek() on reverse iterator to out of range target', function (db, t, done) {
    const ite = db.iterator({ reverse: true })
    ite.seek('zzz')
    ite.next(function (err, key, value) {
      t.error(err, 'no error')
      t.same(key.toString(), 'two')
      t.same(value.toString(), '2')
      ite.end(done)
    })
  })

  test('iterator#seek() respects range', function (t) {
    const db = testCommon.factory()

    db.open(function (err) {
      t.error(err, 'no error from open()')

      // Can't use Array.fill() because IE
      const ops = []

      for (let i = 0; i < 10; i++) {
        ops.push({ type: 'put', key: String(i), value: String(i) })
      }

      db.batch(ops, function (err) {
        t.error(err, 'no error from batch()')

        let pending = 0

        expect({ gt: '5' }, '4', undefined)
        expect({ gt: '5' }, '5', undefined)
        expect({ gt: '5' }, '6', '6')

        expect({ gte: '5' }, '4', undefined)
        expect({ gte: '5' }, '5', '5')
        expect({ gte: '5' }, '6', '6')

        expect({ lt: '5' }, '4', '4')
        expect({ lt: '5' }, '5', undefined)
        expect({ lt: '5' }, '6', undefined)

        expect({ lte: '5' }, '4', '4')
        expect({ lte: '5' }, '5', '5')
        expect({ lte: '5' }, '6', undefined)

        expect({ lt: '5', reverse: true }, '4', '4')
        expect({ lt: '5', reverse: true }, '5', undefined)
        expect({ lt: '5', reverse: true }, '6', undefined)

        expect({ lte: '5', reverse: true }, '4', '4')
        expect({ lte: '5', reverse: true }, '5', '5')
        expect({ lte: '5', reverse: true }, '6', undefined)

        expect({ gt: '5', reverse: true }, '4', undefined)
        expect({ gt: '5', reverse: true }, '5', undefined)
        expect({ gt: '5', reverse: true }, '6', '6')

        expect({ gte: '5', reverse: true }, '4', undefined)
        expect({ gte: '5', reverse: true }, '5', '5')
        expect({ gte: '5', reverse: true }, '6', '6')

        expect({ gt: '7', lt: '8' }, '7', undefined)
        expect({ gte: '7', lt: '8' }, '7', '7')
        expect({ gte: '7', lt: '8' }, '8', undefined)
        expect({ gt: '7', lte: '8' }, '8', '8')

        function expect (range, target, expected) {
          pending++
          const ite = db.iterator(range)

          ite.seek(target)
          ite.next(function (err, key, value) {
            t.error(err, 'no error from next()')

            const json = JSON.stringify(range)
            const msg = 'seek(' + target + ') on ' + json + ' yields ' + expected

            if (expected === undefined) {
              t.equal(value, undefined, msg)
            } else {
              t.equal(value.toString(), expected, msg)
            }

            ite.end(function (err) {
              t.error(err, 'no error from end()')
              if (!--pending) done()
            })
          })
        }

        function done () {
          db.close(function (err) {
            t.error(err, 'no error from close()')
            t.end()
          })
        }
      })
    })
  })
}

exports.tearDown = function (test, testCommon) {
  test('tearDown', testCommon.tearDown)
}
