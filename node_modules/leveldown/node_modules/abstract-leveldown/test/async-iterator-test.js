'use strict'

const input = [{ key: '1', value: '1' }, { key: '2', value: '2' }]

let db

exports.setup = function (test, testCommon) {
  test('setup', function (t) {
    t.plan(2)

    db = testCommon.factory()
    db.open(function (err) {
      t.ifError(err, 'no open() error')

      db.batch(input.map(entry => ({ ...entry, type: 'put' })), function (err) {
        t.ifError(err, 'no batch() error')
      })
    })
  })
}

exports.asyncIterator = function (test, testCommon) {
  test('for await...of db.iterator()', async function (t) {
    t.plan(2)

    const it = db.iterator({ keyAsBuffer: false, valueAsBuffer: false })
    const output = []

    for await (const [key, value] of it) {
      output.push({ key, value })
    }

    t.ok(it._ended, 'ended')
    t.same(output, input)
  })

  test('for await...of db.iterator() does not permit reuse', async function (t) {
    t.plan(3)

    const it = db.iterator()

    // eslint-disable-next-line no-unused-vars
    for await (const [key, value] of it) {
      t.pass('nexted')
    }

    try {
      // eslint-disable-next-line no-unused-vars
      for await (const [key, value] of it) {
        t.fail('should not be called')
      }
    } catch (err) {
      t.is(err.message, 'cannot call next() after end()')
    }
  })

  test('for await...of db.iterator() ends on user error', async function (t) {
    t.plan(2)

    const it = db.iterator()

    try {
      // eslint-disable-next-line no-unused-vars, no-unreachable-loop
      for await (const kv of it) {
        throw new Error('user error')
      }
    } catch (err) {
      t.is(err.message, 'user error')
      t.ok(it._ended, 'ended')
    }
  })

  test('for await...of db.iterator() with user error and end() error', async function (t) {
    t.plan(3)

    const it = db.iterator()
    const end = it._end

    it._end = function (callback) {
      end.call(this, function (err) {
        t.ifError(err, 'no real error from end()')
        callback(new Error('end error'))
      })
    }

    try {
      // eslint-disable-next-line no-unused-vars, no-unreachable-loop
      for await (const kv of it) {
        throw new Error('user error')
      }
    } catch (err) {
      // TODO: ideally, this would be a combined aka aggregate error
      t.is(err.message, 'user error')
      t.ok(it._ended, 'ended')
    }
  })

  test('for await...of db.iterator() ends on iterator error', async function (t) {
    t.plan(3)

    const it = db.iterator()

    it._next = function (callback) {
      t.pass('nexted')
      this._nextTick(callback, new Error('iterator error'))
    }

    try {
      // eslint-disable-next-line no-unused-vars
      for await (const kv of it) {
        t.fail('should not yield results')
      }
    } catch (err) {
      t.is(err.message, 'iterator error')
      t.ok(it._ended, 'ended')
    }
  })

  test('for await...of db.iterator() with iterator error and end() error', async function (t) {
    t.plan(4)

    const it = db.iterator()
    const end = it._end

    it._next = function (callback) {
      t.pass('nexted')
      this._nextTick(callback, new Error('iterator error'))
    }

    it._end = function (callback) {
      end.call(this, function (err) {
        t.ifError(err, 'no real error from end()')
        callback(new Error('end error'))
      })
    }

    try {
      // eslint-disable-next-line no-unused-vars
      for await (const kv of it) {
        t.fail('should not yield results')
      }
    } catch (err) {
      // TODO: ideally, this would be a combined aka aggregate error
      t.is(err.message, 'end error')
      t.ok(it._ended, 'ended')
    }
  })

  test('for await...of db.iterator() ends on user break', async function (t) {
    t.plan(2)

    const it = db.iterator()

    // eslint-disable-next-line no-unused-vars, no-unreachable-loop
    for await (const kv of it) {
      t.pass('got a chance to break')
      break
    }

    t.ok(it._ended, 'ended')
  })

  test('for await...of db.iterator() with user break and end() error', async function (t) {
    t.plan(4)

    const it = db.iterator()
    const end = it._end

    it._end = function (callback) {
      end.call(this, function (err) {
        t.ifError(err, 'no real error from end()')
        callback(new Error('end error'))
      })
    }

    try {
      // eslint-disable-next-line no-unused-vars, no-unreachable-loop
      for await (const kv of it) {
        t.pass('got a chance to break')
        break
      }
    } catch (err) {
      t.is(err.message, 'end error')
      t.ok(it._ended, 'ended')
    }
  })
}

exports.teardown = function (test, testCommon) {
  test('teardown', function (t) {
    t.plan(1)

    db.close(function (err) {
      t.ifError(err, 'no close() error')
    })
  })
}

exports.all = function (test, testCommon) {
  exports.setup(test, testCommon)
  exports.asyncIterator(test, testCommon)
  exports.teardown(test, testCommon)
}
