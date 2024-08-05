'use strict'

const test = require('tape')
const sinon = require('sinon')
const inherits = require('util').inherits
const AbstractLevelDOWN = require('../').AbstractLevelDOWN
const AbstractIterator = require('../').AbstractIterator
const AbstractChainedBatch = require('../').AbstractChainedBatch

const testCommon = require('./common')({
  test: test,
  clear: true,
  factory: function () {
    return new AbstractLevelDOWN()
  }
})

const rangeOptions = ['gt', 'gte', 'lt', 'lte']

// Test the suite itself as well as the default implementation,
// excluding noop operations that can't pass the test suite.

require('./leveldown-test')(test, testCommon)
require('./manifest-test')(test, testCommon)
require('./open-test').all(test, testCommon)

require('./open-create-if-missing-test').setUp(test, testCommon)
require('./open-create-if-missing-test').tearDown(test, testCommon)

require('./open-error-if-exists-test').setUp(test, testCommon)
require('./open-error-if-exists-test').tearDown(test, testCommon)

require('./del-test').setUp(test, testCommon)
require('./del-test').args(test, testCommon)

require('./get-test').setUp(test, testCommon)
require('./get-test').args(test, testCommon)

require('./get-many-test').setUp(test, testCommon)
require('./get-many-test').args(test, testCommon)

require('./put-test').setUp(test, testCommon)
require('./put-test').args(test, testCommon)

require('./put-get-del-test').setUp(test, testCommon)
require('./put-get-del-test').errorKeys(test, testCommon)
require('./put-get-del-test').tearDown(test, testCommon)

require('./batch-test').setUp(test, testCommon)
require('./batch-test').args(test, testCommon)

require('./chained-batch-test').setUp(test, testCommon)
require('./chained-batch-test').args(test, testCommon)
require('./chained-batch-test').tearDown(test, testCommon)

require('./close-test').all(test, testCommon)

require('./iterator-test').setUp(test, testCommon)
require('./iterator-test').args(test, testCommon)
require('./iterator-test').sequence(test, testCommon)
require('./iterator-test').tearDown(test, testCommon)

require('./iterator-range-test').setUp(test, testCommon)
require('./iterator-range-test').tearDown(test, testCommon)

require('./async-iterator-test').setup(test, testCommon)
require('./async-iterator-test').teardown(test, testCommon)

require('./iterator-snapshot-test').setUp(test, testCommon)
require('./iterator-snapshot-test').tearDown(test, testCommon)

require('./iterator-no-snapshot-test').setUp(test, testCommon)
require('./iterator-no-snapshot-test').tearDown(test, testCommon)

require('./iterator-seek-test').setUp(test, testCommon)
require('./iterator-seek-test').sequence(test, testCommon)
require('./iterator-seek-test').tearDown(test, testCommon)

require('./clear-test').setUp(test, testCommon)
require('./clear-test').args(test, testCommon)
require('./clear-test').tearDown(test, testCommon)

require('./clear-range-test').setUp(test, testCommon)
require('./clear-range-test').tearDown(test, testCommon)

function implement (ctor, methods) {
  function Test () {
    ctor.apply(this, arguments)
  }

  inherits(Test, ctor)

  for (const k in methods) {
    Test.prototype[k] = methods[k]
  }

  return Test
}

// Temporary test for browsers
// Not supported on Safari < 12 and Safari iOS < 12
test('async generator', async function (t) {
  let ended = false

  const end = async () => {
    await new Promise((resolve) => setTimeout(resolve, 100))
    ended = true
  }

  async function * generator () {
    try {
      yield 1
      yield 2
      yield 3
      yield 4
    } finally {
      // Test that we're always able to cleanup resources
      await end()
    }
  }

  const res = []

  for await (const x of generator()) {
    res.push(x)
    if (x === 2) break
  }

  t.same(res, [1, 2])
  t.is(ended, true)

  ended = false

  try {
    for await (const x of generator()) {
      res.push(x)
      if (x === 2) throw new Error('userland error')
    }
  } catch (err) {
    t.is(err.message, 'userland error')
  }

  t.same(res, [1, 2, 1, 2])
  t.is(ended, true)
})

/**
 * Extensibility
 */

test('test core extensibility', function (t) {
  const Test = implement(AbstractLevelDOWN)
  const test = new Test()
  t.equal(test.status, 'new', 'status is new')
  t.end()
})

test('test key/value serialization', function (t) {
  const Test = implement(AbstractLevelDOWN)
  const test = new Test()

  ;['', {}, null, undefined, Buffer.alloc(0)].forEach(function (v) {
    t.ok(test._serializeKey(v) === v, '_serializeKey is an identity function')
    t.ok(test._serializeValue(v) === v, '_serializeValue is an identity function')
  })

  t.end()
})

test('test open() extensibility', function (t) {
  const spy = sinon.spy()
  const expectedCb = function () {}
  const expectedOptions = { createIfMissing: true, errorIfExists: false }
  const Test = implement(AbstractLevelDOWN, { _open: spy })
  const test = new Test('foobar')

  test.open(expectedCb)

  t.equal(spy.callCount, 1, 'got _open() call')
  t.equal(spy.getCall(0).thisValue, test, '`this` on _open() was correct')
  t.equal(spy.getCall(0).args.length, 2, 'got two arguments')
  t.deepEqual(spy.getCall(0).args[0], expectedOptions, 'got default options argument')

  test.open({ options: 1 }, expectedCb)

  expectedOptions.options = 1

  t.equal(spy.callCount, 2, 'got _open() call')
  t.equal(spy.getCall(1).thisValue, test, '`this` on _open() was correct')
  t.equal(spy.getCall(1).args.length, 2, 'got two arguments')
  t.deepEqual(spy.getCall(1).args[0], expectedOptions, 'got expected options argument')
  t.end()
})

test('test close() extensibility', function (t) {
  const spy = sinon.spy()
  const expectedCb = function () {}
  const Test = implement(AbstractLevelDOWN, { _close: spy })
  const test = new Test('foobar')

  test.close(expectedCb)

  t.equal(spy.callCount, 1, 'got _close() call')
  t.equal(spy.getCall(0).thisValue, test, '`this` on _close() was correct')
  t.equal(spy.getCall(0).args.length, 1, 'got one arguments')
  t.end()
})

test('test get() extensibility', function (t) {
  const spy = sinon.spy()
  const expectedCb = function () {}
  const expectedOptions = { asBuffer: true }
  const expectedKey = 'a key'
  const Test = implement(AbstractLevelDOWN, { _get: spy })
  const test = new Test('foobar')

  test.get(expectedKey, expectedCb)

  t.equal(spy.callCount, 1, 'got _get() call')
  t.equal(spy.getCall(0).thisValue, test, '`this` on _get() was correct')
  t.equal(spy.getCall(0).args.length, 3, 'got three arguments')
  t.equal(spy.getCall(0).args[0], expectedKey, 'got expected key argument')
  t.deepEqual(spy.getCall(0).args[1], expectedOptions, 'got default options argument')
  t.equal(spy.getCall(0).args[2], expectedCb, 'got expected cb argument')

  test.get(expectedKey, { options: 1 }, expectedCb)

  expectedOptions.options = 1

  t.equal(spy.callCount, 2, 'got _get() call')
  t.equal(spy.getCall(1).thisValue, test, '`this` on _get() was correct')
  t.equal(spy.getCall(1).args.length, 3, 'got three arguments')
  t.equal(spy.getCall(1).args[0], expectedKey, 'got expected key argument')
  t.deepEqual(spy.getCall(1).args[1], expectedOptions, 'got expected options argument')
  t.equal(spy.getCall(1).args[2], expectedCb, 'got expected cb argument')
  t.end()
})

test('test getMany() extensibility', function (t) {
  const spy = sinon.spy()
  const expectedCb = function () {}
  const expectedOptions = { asBuffer: true }
  const expectedKey = 'a key'
  const Test = implement(AbstractLevelDOWN, { _getMany: spy })
  const test = new Test('foobar')

  test.status = 'open'
  test.getMany([expectedKey], expectedCb)

  t.equal(spy.callCount, 1, 'got _getMany() call')
  t.equal(spy.getCall(0).thisValue, test, '`this` on _getMany() was correct')
  t.equal(spy.getCall(0).args.length, 3, 'got three arguments')
  t.deepEqual(spy.getCall(0).args[0], [expectedKey], 'got expected keys argument')
  t.deepEqual(spy.getCall(0).args[1], expectedOptions, 'got default options argument')
  t.equal(spy.getCall(0).args[2], expectedCb, 'got expected cb argument')

  test.getMany([expectedKey], { options: 1 }, expectedCb)

  expectedOptions.options = 1

  t.equal(spy.callCount, 2, 'got _getMany() call')
  t.equal(spy.getCall(1).thisValue, test, '`this` on _getMany() was correct')
  t.equal(spy.getCall(1).args.length, 3, 'got three arguments')
  t.deepEqual(spy.getCall(1).args[0], [expectedKey], 'got expected key argument')
  t.deepEqual(spy.getCall(1).args[1], expectedOptions, 'got expected options argument')
  t.equal(spy.getCall(1).args[2], expectedCb, 'got expected cb argument')
  t.end()
})

test('test del() extensibility', function (t) {
  const spy = sinon.spy()
  const expectedCb = function () {}
  const expectedOptions = { options: 1 }
  const expectedKey = 'a key'
  const Test = implement(AbstractLevelDOWN, { _del: spy })
  const test = new Test('foobar')

  test.del(expectedKey, expectedCb)

  t.equal(spy.callCount, 1, 'got _del() call')
  t.equal(spy.getCall(0).thisValue, test, '`this` on _del() was correct')
  t.equal(spy.getCall(0).args.length, 3, 'got three arguments')
  t.equal(spy.getCall(0).args[0], expectedKey, 'got expected key argument')
  t.deepEqual(spy.getCall(0).args[1], {}, 'got blank options argument')
  t.equal(spy.getCall(0).args[2], expectedCb, 'got expected cb argument')

  test.del(expectedKey, expectedOptions, expectedCb)

  t.equal(spy.callCount, 2, 'got _del() call')
  t.equal(spy.getCall(1).thisValue, test, '`this` on _del() was correct')
  t.equal(spy.getCall(1).args.length, 3, 'got three arguments')
  t.equal(spy.getCall(1).args[0], expectedKey, 'got expected key argument')
  t.deepEqual(spy.getCall(1).args[1], expectedOptions, 'got expected options argument')
  t.equal(spy.getCall(1).args[2], expectedCb, 'got expected cb argument')
  t.end()
})

test('test put() extensibility', function (t) {
  const spy = sinon.spy()
  const expectedCb = function () {}
  const expectedOptions = { options: 1 }
  const expectedKey = 'a key'
  const expectedValue = 'a value'
  const Test = implement(AbstractLevelDOWN, { _put: spy })
  const test = new Test('foobar')

  test.put(expectedKey, expectedValue, expectedCb)

  t.equal(spy.callCount, 1, 'got _put() call')
  t.equal(spy.getCall(0).thisValue, test, '`this` on _put() was correct')
  t.equal(spy.getCall(0).args.length, 4, 'got four arguments')
  t.equal(spy.getCall(0).args[0], expectedKey, 'got expected key argument')
  t.equal(spy.getCall(0).args[1], expectedValue, 'got expected value argument')
  t.deepEqual(spy.getCall(0).args[2], {}, 'got blank options argument')
  t.equal(spy.getCall(0).args[3], expectedCb, 'got expected cb argument')

  test.put(expectedKey, expectedValue, expectedOptions, expectedCb)

  t.equal(spy.callCount, 2, 'got _put() call')
  t.equal(spy.getCall(1).thisValue, test, '`this` on _put() was correct')
  t.equal(spy.getCall(1).args.length, 4, 'got four arguments')
  t.equal(spy.getCall(1).args[0], expectedKey, 'got expected key argument')
  t.equal(spy.getCall(1).args[1], expectedValue, 'got expected value argument')
  t.deepEqual(spy.getCall(1).args[2], expectedOptions, 'got blank options argument')
  t.equal(spy.getCall(1).args[3], expectedCb, 'got expected cb argument')
  t.end()
})

test('test batch([]) (array-form) extensibility', function (t) {
  const spy = sinon.spy()
  const expectedCb = function () {}
  const expectedOptions = { options: 1 }
  const expectedArray = [
    { type: 'put', key: '1', value: '1' },
    { type: 'del', key: '2' }
  ]
  const Test = implement(AbstractLevelDOWN, { _batch: spy })
  const test = new Test('foobar')

  test.batch(expectedArray, expectedCb)

  t.equal(spy.callCount, 1, 'got _batch() call')
  t.equal(spy.getCall(0).thisValue, test, '`this` on _batch() was correct')
  t.equal(spy.getCall(0).args.length, 3, 'got three arguments')
  t.deepEqual(spy.getCall(0).args[0], expectedArray, 'got expected array argument')
  t.deepEqual(spy.getCall(0).args[1], {}, 'got expected options argument')
  t.equal(spy.getCall(0).args[2], expectedCb, 'got expected callback argument')

  test.batch(expectedArray, expectedOptions, expectedCb)

  t.equal(spy.callCount, 2, 'got _batch() call')
  t.equal(spy.getCall(1).thisValue, test, '`this` on _batch() was correct')
  t.equal(spy.getCall(1).args.length, 3, 'got three arguments')
  t.deepEqual(spy.getCall(1).args[0], expectedArray, 'got expected array argument')
  t.deepEqual(spy.getCall(1).args[1], expectedOptions, 'got expected options argument')
  t.equal(spy.getCall(1).args[2], expectedCb, 'got expected callback argument')

  test.batch(expectedArray, null, expectedCb)

  t.equal(spy.callCount, 3, 'got _batch() call')
  t.equal(spy.getCall(2).thisValue, test, '`this` on _batch() was correct')
  t.equal(spy.getCall(2).args.length, 3, 'got three arguments')
  t.deepEqual(spy.getCall(2).args[0], expectedArray, 'got expected array argument')
  t.ok(spy.getCall(2).args[1], 'options should not be null')
  t.equal(spy.getCall(2).args[2], expectedCb, 'got expected callback argument')
  t.end()
})

test('test batch([]) (array-form) with empty array is asynchronous', function (t) {
  const spy = sinon.spy()
  const Test = implement(AbstractLevelDOWN, { _batch: spy })
  const test = new Test()
  let async = false

  test.batch([], function (err) {
    t.ifError(err, 'no error')
    t.ok(async, 'callback is asynchronous')

    // Assert that asynchronicity is provided by batch() rather than _batch()
    t.is(spy.callCount, 0, '_batch() call was bypassed')
    t.end()
  })

  async = true
})

test('test chained batch() extensibility', function (t) {
  const spy = sinon.spy()
  const expectedCb = function () {}
  const expectedOptions = { options: 1 }
  const Test = implement(AbstractLevelDOWN, { _batch: spy })
  const test = new Test('foobar')

  test.batch().put('foo', 'bar').del('bang').write(expectedCb)

  t.equal(spy.callCount, 1, 'got _batch() call')
  t.equal(spy.getCall(0).thisValue, test, '`this` on _batch() was correct')
  t.equal(spy.getCall(0).args.length, 3, 'got three arguments')
  t.equal(spy.getCall(0).args[0].length, 2, 'got expected array argument')
  t.deepEqual(spy.getCall(0).args[0][0], { type: 'put', key: 'foo', value: 'bar' }, 'got expected array argument[0]')
  t.deepEqual(spy.getCall(0).args[0][1], { type: 'del', key: 'bang' }, 'got expected array argument[1]')
  t.deepEqual(spy.getCall(0).args[1], {}, 'got expected options argument')
  t.equal(spy.getCall(0).args[2], expectedCb, 'got expected callback argument')

  test.batch().put('foo', 'bar', expectedOptions).del('bang', expectedOptions).write(expectedOptions, expectedCb)

  t.equal(spy.callCount, 2, 'got _batch() call')
  t.equal(spy.getCall(1).thisValue, test, '`this` on _batch() was correct')
  t.equal(spy.getCall(1).args.length, 3, 'got three arguments')
  t.equal(spy.getCall(1).args[0].length, 2, 'got expected array argument')
  t.deepEqual(spy.getCall(1).args[0][0], { type: 'put', key: 'foo', value: 'bar', options: 1 }, 'got expected array argument[0]')
  t.deepEqual(spy.getCall(1).args[0][1], { type: 'del', key: 'bang', options: 1 }, 'got expected array argument[1]')
  t.deepEqual(spy.getCall(1).args[1], expectedOptions, 'got expected options argument')
  t.equal(spy.getCall(1).args[2], expectedCb, 'got expected callback argument')

  t.end()
})

test('test chained batch() with no operations is asynchronous', function (t) {
  const Test = implement(AbstractLevelDOWN, {})
  const test = new Test()
  let async = false

  test.batch().write(function (err) {
    t.ifError(err, 'no error')
    t.ok(async, 'callback is asynchronous')
    t.end()
  })

  async = true
})

test('test chained batch() (custom _chainedBatch) extensibility', function (t) {
  const spy = sinon.spy()
  const Test = implement(AbstractLevelDOWN, { _chainedBatch: spy })
  const test = new Test('foobar')

  test.batch()

  t.equal(spy.callCount, 1, 'got _chainedBatch() call')
  t.equal(spy.getCall(0).thisValue, test, '`this` on _chainedBatch() was correct')

  test.batch()

  t.equal(spy.callCount, 2, 'got _chainedBatch() call')
  t.equal(spy.getCall(1).thisValue, test, '`this` on _chainedBatch() was correct')

  t.end()
})

test('test AbstractChainedBatch extensibility', function (t) {
  const Test = implement(AbstractChainedBatch)
  const db = {}
  const test = new Test(db)
  t.ok(test.db === db, 'instance has db reference')
  t.end()
})

test('test AbstractChainedBatch expects a db', function (t) {
  t.plan(1)

  const Test = implement(AbstractChainedBatch)

  try {
    Test()
  } catch (err) {
    t.is(err.message, 'First argument must be an abstract-leveldown compliant store')
  }
})

test('test AbstractChainedBatch#write() extensibility', function (t) {
  const spy = sinon.spy()
  const spycb = sinon.spy()
  const Test = implement(AbstractChainedBatch, { _write: spy })
  const test = new Test({ test: true })

  test.write(spycb)

  t.equal(spy.callCount, 1, 'got _write() call')
  t.equal(spy.getCall(0).thisValue, test, '`this` on _write() was correct')
  t.equal(spy.getCall(0).args.length, 2, 'got two arguments')
  t.same(spy.getCall(0).args[0], {}, 'got options')
  // awkward here cause of nextTick & an internal wrapped cb
  t.equal(typeof spy.getCall(0).args[1], 'function', 'got a callback function')
  t.equal(spycb.callCount, 0, 'spycb not called')
  spy.getCall(0).args[1]()
  t.equal(spycb.callCount, 1, 'spycb called, i.e. was our cb argument')
  t.end()
})

test('test AbstractChainedBatch#write() extensibility with null options', function (t) {
  const spy = sinon.spy()
  const Test = implement(AbstractChainedBatch, { _write: spy })
  const test = new Test({ test: true })

  test.write(null, function () {})

  t.equal(spy.callCount, 1, 'got _write() call')
  t.same(spy.getCall(0).args[0], {}, 'got options')
  t.end()
})

test('test AbstractChainedBatch#write() extensibility with options', function (t) {
  const spy = sinon.spy()
  const Test = implement(AbstractChainedBatch, { _write: spy })
  const test = new Test({ test: true })

  test.write({ test: true }, function () {})

  t.equal(spy.callCount, 1, 'got _write() call')
  t.same(spy.getCall(0).args[0], { test: true }, 'got options')
  t.end()
})

test('test AbstractChainedBatch#put() extensibility', function (t) {
  const spy = sinon.spy()
  const expectedKey = 'key'
  const expectedValue = 'value'
  const Test = implement(AbstractChainedBatch, { _put: spy })
  const test = new Test(testCommon.factory())
  const returnValue = test.put(expectedKey, expectedValue)

  t.equal(spy.callCount, 1, 'got _put call')
  t.equal(spy.getCall(0).thisValue, test, '`this` on _put() was correct')
  t.equal(spy.getCall(0).args.length, 3, 'got 3 arguments')
  t.equal(spy.getCall(0).args[0], expectedKey, 'got expected key argument')
  t.equal(spy.getCall(0).args[1], expectedValue, 'got expected value argument')
  t.same(spy.getCall(0).args[2], {}, 'got expected options argument')
  t.equal(returnValue, test, 'get expected return value')
  t.end()
})

test('test AbstractChainedBatch#del() extensibility', function (t) {
  const spy = sinon.spy()
  const expectedKey = 'key'
  const Test = implement(AbstractChainedBatch, { _del: spy })
  const test = new Test(testCommon.factory())
  const returnValue = test.del(expectedKey)

  t.equal(spy.callCount, 1, 'got _del call')
  t.equal(spy.getCall(0).thisValue, test, '`this` on _del() was correct')
  t.equal(spy.getCall(0).args.length, 2, 'got 2 arguments')
  t.equal(spy.getCall(0).args[0], expectedKey, 'got expected key argument')
  t.same(spy.getCall(0).args[1], {}, 'got expected options argument')
  t.equal(returnValue, test, 'get expected return value')
  t.end()
})

test('test AbstractChainedBatch#clear() extensibility', function (t) {
  const spy = sinon.spy()
  const Test = implement(AbstractChainedBatch, { _clear: spy })
  const test = new Test(testCommon.factory())
  const returnValue = test.clear()

  t.equal(spy.callCount, 1, 'got _clear call')
  t.equal(spy.getCall(0).thisValue, test, '`this` on _clear() was correct')
  t.equal(spy.getCall(0).args.length, 0, 'got zero arguments')
  t.equal(returnValue, test, 'get expected return value')
  t.end()
})

test('test iterator() extensibility', function (t) {
  const spy = sinon.spy()
  const expectedOptions = {
    options: 1,
    reverse: false,
    keys: true,
    values: true,
    limit: -1,
    keyAsBuffer: true,
    valueAsBuffer: true
  }
  const Test = implement(AbstractLevelDOWN, { _iterator: spy })
  const test = new Test('foobar')

  test.iterator({ options: 1 })

  t.equal(spy.callCount, 1, 'got _iterator() call')
  t.equal(spy.getCall(0).thisValue, test, '`this` on _iterator() was correct')
  t.equal(spy.getCall(0).args.length, 1, 'got one arguments')
  t.deepEqual(spy.getCall(0).args[0], expectedOptions, 'got expected options argument')
  t.end()
})

test('test AbstractIterator extensibility', function (t) {
  const Test = implement(AbstractIterator)
  const db = {}
  const test = new Test(db)
  t.ok(test.db === db, 'instance has db reference')
  t.end()
})

test('test AbstractIterator#next() extensibility', function (t) {
  const spy = sinon.spy()
  const spycb = sinon.spy()
  const Test = implement(AbstractIterator, { _next: spy })
  const test = new Test({})

  test.next(spycb)

  t.equal(spy.callCount, 1, 'got _next() call')
  t.equal(spy.getCall(0).thisValue, test, '`this` on _next() was correct')
  t.equal(spy.getCall(0).args.length, 1, 'got one arguments')
  // awkward here cause of nextTick & an internal wrapped cb
  t.equal(typeof spy.getCall(0).args[0], 'function', 'got a callback function')
  t.equal(spycb.callCount, 0, 'spycb not called')
  spy.getCall(0).args[0]()
  t.equal(spycb.callCount, 1, 'spycb called, i.e. was our cb argument')
  t.end()
})

test('test AbstractIterator#end() extensibility', function (t) {
  const spy = sinon.spy()
  const expectedCb = function () {}
  const Test = implement(AbstractIterator, { _end: spy })
  const test = new Test({})

  test.end(expectedCb)

  t.equal(spy.callCount, 1, 'got _end() call')
  t.equal(spy.getCall(0).thisValue, test, '`this` on _end() was correct')
  t.equal(spy.getCall(0).args.length, 1, 'got one arguments')
  t.equal(spy.getCall(0).args[0], expectedCb, 'got expected cb argument')
  t.end()
})

test('test clear() extensibility', function (t) {
  const spy = sinon.spy()
  const Test = implement(AbstractLevelDOWN, { _clear: spy })
  const db = new Test()
  const callback = function () {}

  call([callback], { reverse: false, limit: -1 })
  call([null, callback], { reverse: false, limit: -1 })
  call([undefined, callback], { reverse: false, limit: -1 })
  call([{ custom: 1 }, callback], { custom: 1, reverse: false, limit: -1 })
  call([{ reverse: true, limit: 0 }, callback], { reverse: true, limit: 0 })
  call([{ reverse: 1 }, callback], { reverse: true, limit: -1 })
  call([{ reverse: null }, callback], { reverse: false, limit: -1 })

  function call (args, expectedOptions) {
    db.clear.apply(db, args)

    t.is(spy.callCount, 1, 'got _clear() call')
    t.is(spy.getCall(0).thisValue, db, '`this` on _clear() was correct')
    t.is(spy.getCall(0).args.length, 2, 'got two arguments')
    t.same(spy.getCall(0).args[0], expectedOptions, 'got expected options argument')
    t.is(spy.getCall(0).args[1], callback, 'got expected callback argument')

    spy.resetHistory()
  }

  t.end()
})

test('test serialization extensibility (get)', function (t) {
  t.plan(2)

  const spy = sinon.spy()
  const Test = implement(AbstractLevelDOWN, {
    _get: spy,
    _serializeKey: function (key) {
      return key.toUpperCase()
    }
  })

  const test = new Test()
  test.get('foo', function () {})

  t.is(spy.callCount, 1, 'got _get() call')
  t.is(spy.getCall(0).args[0], 'FOO', 'got expected key argument')
})

test('test serialization extensibility (getMany)', function (t) {
  t.plan(2)

  const spy = sinon.spy()
  const Test = implement(AbstractLevelDOWN, {
    _getMany: spy,
    _serializeKey: function (key) {
      return key.toUpperCase()
    }
  })

  const test = new Test()

  test.status = 'open'
  test.getMany(['foo', 'bar'], function () {})

  t.is(spy.callCount, 1, 'got _getMany() call')
  t.same(spy.getCall(0).args[0], ['FOO', 'BAR'], 'got expected keys argument')
})

test('test serialization extensibility (put)', function (t) {
  t.plan(5)

  const spy = sinon.spy()
  const Test = implement(AbstractLevelDOWN, {
    _put: spy,
    _serializeKey: function (key) {
      t.equal(key, 'no')
      return 'foo'
    },

    _serializeValue: function (value) {
      t.equal(value, 'nope')
      return 'bar'
    }
  })

  const test = new Test('foobar')
  test.put('no', 'nope', function () {})

  t.equal(spy.callCount, 1, 'got _put() call')
  t.equal(spy.getCall(0).args[0], 'foo', 'got expected key argument')
  t.equal(spy.getCall(0).args[1], 'bar', 'got expected value argument')
})

test('test serialization extensibility (del)', function (t) {
  t.plan(3)

  const spy = sinon.spy()
  const Test = implement(AbstractLevelDOWN, {
    _del: spy,
    _serializeKey: function (key) {
      t.equal(key, 'no')
      return 'foo'
    },
    _serializeValue: function (value) {
      t.fail('should not be called')
    }
  })

  const test = new Test('foobar')
  test.del('no', function () {})

  t.equal(spy.callCount, 1, 'got _del() call')
  t.equal(spy.getCall(0).args[0], 'foo', 'got expected key argument')

  t.end()
})

test('test serialization extensibility (batch array put)', function (t) {
  t.plan(5)

  const spy = sinon.spy()
  const Test = implement(AbstractLevelDOWN, {
    _batch: spy,
    _serializeKey: function (key) {
      t.equal(key, 'no')
      return 'foo'
    },
    _serializeValue: function (value) {
      t.equal(value, 'nope')
      return 'bar'
    }
  })

  const test = new Test('foobar')
  test.batch([{ type: 'put', key: 'no', value: 'nope' }], function () {})

  t.equal(spy.callCount, 1, 'got _batch() call')
  t.equal(spy.getCall(0).args[0][0].key, 'foo', 'got expected key')
  t.equal(spy.getCall(0).args[0][0].value, 'bar', 'got expected value')
})

test('test serialization extensibility (batch chain put)', function (t) {
  t.plan(5)

  const spy = sinon.spy()
  const Test = implement(AbstractLevelDOWN, {
    _batch: spy,
    _serializeKey: function (key) {
      t.equal(key, 'no')
      return 'foo'
    },
    _serializeValue: function (value) {
      t.equal(value, 'nope')
      return 'bar'
    }
  })

  const test = new Test('foobar')
  test.batch().put('no', 'nope').write(function () {})

  t.equal(spy.callCount, 1, 'got _batch() call')
  t.equal(spy.getCall(0).args[0][0].key, 'foo', 'got expected key')
  t.equal(spy.getCall(0).args[0][0].value, 'bar', 'got expected value')
})

test('test serialization extensibility (batch array del)', function (t) {
  t.plan(3)

  const spy = sinon.spy()
  const Test = implement(AbstractLevelDOWN, {
    _batch: spy,
    _serializeKey: function (key) {
      t.equal(key, 'no')
      return 'foo'
    },
    _serializeValue: function (value) {
      t.fail('should not be called')
    }
  })

  const test = new Test('foobar')
  test.batch([{ type: 'del', key: 'no' }], function () {})

  t.equal(spy.callCount, 1, 'got _batch() call')
  t.equal(spy.getCall(0).args[0][0].key, 'foo', 'got expected key')
})

test('test serialization extensibility (batch chain del)', function (t) {
  t.plan(3)

  const spy = sinon.spy()
  const Test = implement(AbstractLevelDOWN, {
    _batch: spy,
    _serializeKey: function (key) {
      t.equal(key, 'no')
      return 'foo'
    },
    _serializeValue: function (value) {
      t.fail('should not be called')
    }
  })

  const test = new Test('foobar')
  test.batch().del('no').write(function () {})

  t.equal(spy.callCount, 1, 'got _batch() call')
  t.equal(spy.getCall(0).args[0][0].key, 'foo', 'got expected key')
})

test('test serialization extensibility (batch array is not mutated)', function (t) {
  t.plan(7)

  const spy = sinon.spy()
  const Test = implement(AbstractLevelDOWN, {
    _batch: spy,
    _serializeKey: function (key) {
      t.equal(key, 'no')
      return 'foo'
    },
    _serializeValue: function (value) {
      t.equal(value, 'nope')
      return 'bar'
    }
  })

  const test = new Test('foobar')
  const op = { type: 'put', key: 'no', value: 'nope' }

  test.batch([op], function () {})

  t.equal(spy.callCount, 1, 'got _batch() call')
  t.equal(spy.getCall(0).args[0][0].key, 'foo', 'got expected key')
  t.equal(spy.getCall(0).args[0][0].value, 'bar', 'got expected value')

  t.equal(op.key, 'no', 'did not mutate input key')
  t.equal(op.value, 'nope', 'did not mutate input value')
})

test('test serialization extensibility (iterator range options)', function (t) {
  t.plan(2)

  function Test () {
    AbstractLevelDOWN.call(this)
  }

  inherits(Test, AbstractLevelDOWN)

  Test.prototype._serializeKey = function (key) {
    t.is(key, 'input')
    return 'output'
  }

  Test.prototype._iterator = function (options) {
    return new Iterator(this, options)
  }

  function Iterator (db, options) {
    AbstractIterator.call(this, db)
    t.is(options.gt, 'output')
  }

  inherits(Iterator, AbstractIterator)

  const test = new Test()
  test.iterator({ gt: 'input' })
})

test('test serialization extensibility (iterator seek)', function (t) {
  t.plan(3)

  const spy = sinon.spy()
  const TestIterator = implement(AbstractIterator, { _seek: spy })

  const Test = implement(AbstractLevelDOWN, {
    _iterator: function () {
      return new TestIterator(this)
    },
    _serializeKey: function (key) {
      t.equal(key, 'target')
      return 'serialized'
    }
  })

  const test = new Test('foobar')
  const it = test.iterator()

  it.seek('target')

  t.equal(spy.callCount, 1, 'got _seek() call')
  t.equal(spy.getCall(0).args[0], 'serialized', 'got expected target argument')
})

test('test serialization extensibility (clear range options)', function (t) {
  t.plan(rangeOptions.length * 2)

  rangeOptions.forEach(function (key) {
    const Test = implement(AbstractLevelDOWN, {
      _serializeKey: function (key) {
        t.is(key, 'input')
        return 'output'
      },
      _clear: function (options, callback) {
        t.is(options[key], 'output')
      }
    })

    const db = new Test()
    const options = {}

    options[key] = 'input'
    db.clear(options, function () {})
  })
})

test('clear() does not delete empty or nullish range options', function (t) {
  const rangeValues = [Buffer.alloc(0), '', null, undefined]

  t.plan(rangeOptions.length * rangeValues.length)

  rangeValues.forEach(function (value) {
    const Test = implement(AbstractLevelDOWN, {
      _clear: function (options, callback) {
        rangeOptions.forEach(function (key) {
          t.ok(key in options, key + ' option should not be deleted')
        })
      }
    })

    const db = new Test()
    const options = {}

    rangeOptions.forEach(function (key) {
      options[key] = value
    })

    db.clear(options, function () {})
  })
})

test('.status', function (t) {
  t.plan(5)

  t.test('empty prototype', function (t) {
    const Test = implement(AbstractLevelDOWN)
    const test = new Test('foobar')

    t.equal(test.status, 'new')

    test.open(function (err) {
      t.error(err)
      t.equal(test.status, 'open')

      test.close(function (err) {
        t.error(err)
        t.equal(test.status, 'closed')
        t.end()
      })
    })

    t.equal(test.status, 'opening')
  })

  t.test('open error', function (t) {
    const Test = implement(AbstractLevelDOWN, {
      _open: function (options, cb) {
        cb(new Error())
      }
    })

    const test = new Test('foobar')

    test.open(function (err) {
      t.ok(err)
      t.equal(test.status, 'new')
      t.end()
    })
  })

  t.test('close error', function (t) {
    const Test = implement(AbstractLevelDOWN, {
      _close: function (cb) {
        cb(new Error())
      }
    })

    const test = new Test('foobar')
    test.open(function () {
      test.close(function (err) {
        t.ok(err)
        t.equal(test.status, 'open')
        t.end()
      })
    })
  })

  t.test('open', function (t) {
    const Test = implement(AbstractLevelDOWN, {
      _open: function (options, cb) {
        this._nextTick(cb)
      }
    })

    const test = new Test('foobar')
    test.open(function (err) {
      t.error(err)
      t.equal(test.status, 'open')
      t.end()
    })
    t.equal(test.status, 'opening')
  })

  t.test('close', function (t) {
    const Test = implement(AbstractLevelDOWN, {
      _close: function (cb) {
        this._nextTick(cb)
      }
    })

    const test = new Test('foobar')
    test.open(function (err) {
      t.error(err)
      test.close(function (err) {
        t.error(err)
        t.equal(test.status, 'closed')
        t.end()
      })
      t.equal(test.status, 'closing')
    })
  })
})

test('_setupIteratorOptions', function (t) {
  const keys = rangeOptions.slice()
  const db = new AbstractLevelDOWN()

  function setupOptions (constrFn) {
    const options = {}
    keys.forEach(function (key) {
      options[key] = constrFn()
    })
    return options
  }

  function verifyOptions (t, options) {
    keys.forEach(function (key) {
      t.ok(key in options, key + ' option should not be deleted')
    })
    t.end()
  }

  t.plan(7)

  t.test('default options', function (t) {
    t.same(db._setupIteratorOptions(), {
      reverse: false,
      keys: true,
      values: true,
      limit: -1,
      keyAsBuffer: true,
      valueAsBuffer: true
    }, 'correct defaults')
    t.end()
  })

  t.test('set options', function (t) {
    t.same(db._setupIteratorOptions({
      reverse: false,
      keys: false,
      values: false,
      limit: 20,
      keyAsBuffer: false,
      valueAsBuffer: false
    }), {
      reverse: false,
      keys: false,
      values: false,
      limit: 20,
      keyAsBuffer: false,
      valueAsBuffer: false
    }, 'options set correctly')
    t.end()
  })

  t.test('does not delete empty buffers', function (t) {
    const options = setupOptions(function () { return Buffer.from('') })
    keys.forEach(function (key) {
      t.is(Buffer.isBuffer(options[key]), true, 'should be buffer')
      t.is(options[key].length, 0, 'should be empty')
    })
    verifyOptions(t, db._setupIteratorOptions(options))
  })

  t.test('does not delete empty strings', function (t) {
    const options = setupOptions(function () { return '' })
    keys.forEach(function (key) {
      t.is(typeof options[key], 'string', 'should be string')
      t.is(options[key].length, 0, 'should be empty')
    })
    verifyOptions(t, db._setupIteratorOptions(options))
  })

  t.test('does not delete null', function (t) {
    const options = setupOptions(function () { return null })
    keys.forEach(function (key) {
      t.is(options[key], null, 'should be null')
    })
    verifyOptions(t, db._setupIteratorOptions(options))
  })

  t.test('does not delete undefined', function (t) {
    const options = setupOptions(function () { return undefined })
    keys.forEach(function (key) {
      t.is(options[key], undefined, 'should be undefined')
    })
    verifyOptions(t, db._setupIteratorOptions(options))
  })

  t.test('rejects legacy range options', function (t) {
    t.plan(2)

    for (const key of ['start', 'end']) {
      const options = {}
      options[key] = 'x'

      try {
        db._setupIteratorOptions(options)
      } catch (err) {
        t.is(err.message, 'Legacy range options ("start" and "end") have been removed')
      }
    }
  })
})
