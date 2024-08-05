'use strict'

const isTypedArray = require('./util').isTypedArray

let db

exports.setUp = function (test, testCommon) {
  test('setUp common', testCommon.setUp)
  test('setUp db', function (t) {
    db = testCommon.factory()
    db.open(t.end.bind(t))
  })
}

exports.args = function (test, testCommon) {
  testCommon.promises || test('test argument-less put() throws', function (t) {
    t.throws(
      db.put.bind(db),
      /Error: put\(\) requires a callback argument/,
      'no-arg put() throws'
    )
    t.end()
  })

  testCommon.promises || test('test callback-less, 1-arg, put() throws', function (t) {
    t.throws(
      db.put.bind(db, 'foo'),
      /Error: put\(\) requires a callback argument/,
      'callback-less, 1-arg put() throws'
    )
    t.end()
  })

  testCommon.promises || test('test callback-less, 2-arg, put() throws', function (t) {
    t.throws(
      db.put.bind(db, 'foo', 'bar'),
      /Error: put\(\) requires a callback argument/,
      'callback-less, 2-arg put() throws'
    )
    t.end()
  })

  testCommon.promises || test('test callback-less, 3-arg, put() throws', function (t) {
    t.throws(
      db.put.bind(db, 'foo', 'bar', {}),
      /Error: put\(\) requires a callback argument/,
      'callback-less, 3-arg put() throws'
    )
    t.end()
  })
}

exports.put = function (test, testCommon) {
  test('test simple put()', function (t) {
    db.put('foo', 'bar', function (err) {
      t.error(err)
      db.get('foo', function (err, value) {
        t.error(err)
        let result = value.toString()
        if (isTypedArray(value)) {
          result = String.fromCharCode.apply(null, new Uint16Array(value))
        }
        t.equal(result, 'bar')
        t.end()
      })
    })
  })
}

exports.tearDown = function (test, testCommon) {
  test('tearDown', function (t) {
    db.close(testCommon.tearDown.bind(null, t))
  })
}

exports.all = function (test, testCommon) {
  exports.setUp(test, testCommon)
  exports.args(test, testCommon)
  exports.put(test, testCommon)
  exports.tearDown(test, testCommon)
}
