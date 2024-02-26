var assert = require('assert')
var vows = require('vows')
var LRU = require('../')

var suite = vows.describe('LRU')

suite.addBatch({
  'clear() sets the cache to its initial state': function () {
    var lru = new LRU(2)

    var json1 = JSON.stringify(lru)

    lru.set('foo', 'bar')
    lru.clear()
    var json2 = JSON.stringify(lru)

    assert.equal(json2, json1)
  }
})

suite.addBatch({
  'setting keys doesn\'t grow past max size': function () {
    var lru = new LRU(3)
    assert.equal(0, lru.length)
    lru.set('foo1', 'bar1')
    assert.equal(1, lru.length)
    lru.set('foo2', 'bar2')
    assert.equal(2, lru.length)
    lru.set('foo3', 'bar3')
    assert.equal(3, lru.length)

    lru.set('foo4', 'bar4')
    assert.equal(3, lru.length)
  }
})

suite.addBatch({
  'setting keys returns the value': function () {
    var lru = new LRU(2)
    assert.equal('bar1', lru.set('foo1', 'bar1'))
    assert.equal('bar2', lru.set('foo2', 'bar2'))
    assert.equal('bar3', lru.set('foo3', 'bar3'))
    assert.equal('bar2', lru.get('foo2'))
    assert.equal(undefined, lru.get('foo1'))
    assert.equal('bar1', lru.set('foo1', 'bar1'))
  }
})

suite.addBatch({
  'lru invariant is maintained for set()': function () {
    var lru = new LRU(2)

    lru.set('foo1', 'bar1')
    lru.set('foo2', 'bar2')
    lru.set('foo3', 'bar3')
    lru.set('foo4', 'bar4')

    assert.deepEqual(['foo3', 'foo4'], lru.keys)
  }
})

suite.addBatch({
  'ovrewriting a key updates the value': function () {
    var lru = new LRU(2)
    lru.set('foo1', 'bar1')
    assert.equal('bar1', lru.get('foo1'))
    lru.set('foo1', 'bar2')
    assert.equal('bar2', lru.get('foo1'))
  }
})

suite.addBatch({
  'lru invariant is maintained for get()': function () {
    var lru = new LRU(2)

    lru.set('foo1', 'bar1')
    lru.set('foo2', 'bar2')

    lru.get('foo1') // now foo2 should be deleted instead of foo1

    lru.set('foo3', 'bar3')

    assert.deepEqual(['foo1', 'foo3'], lru.keys)
  },
  'lru invariant is maintained after set(), get() and remove()': function () {
    var lru = new LRU(2)
    lru.set('a', 1)
    lru.set('b', 2)
    assert.deepEqual(lru.get('a'), 1)
    lru.remove('a')
    lru.set('c', 1)
    lru.set('d', 1)
    assert.deepEqual(['c', 'd'], lru.keys)
  }
})

suite.addBatch({
  'lru invariant is maintained in the corner case size == 1': function () {
    var lru = new LRU(1)

    lru.set('foo1', 'bar1')
    lru.set('foo2', 'bar2')

    lru.get('foo2') // now foo2 should be deleted instead of foo1

    lru.set('foo3', 'bar3')

    assert.deepEqual(['foo3'], lru.keys)
  }
})

suite.addBatch({
  'get() returns item value': function () {
    var lru = new LRU(2)

    assert.equal(lru.set('foo', 'bar'), 'bar')
  }
})

suite.addBatch({
  'peek() returns item value without changing the order': function () {
    var lru = new LRU(2)
    lru.set('foo', 'bar')
    lru.set('bar', 'baz')
    assert.equal(lru.peek('foo'), 'bar')
    lru.set('baz', 'foo')
    assert.equal(lru.get('foo'), null)
  }
})

suite.addBatch({
  'peek respects max age': {
    topic: function () {
      var lru = new LRU({maxAge: 5})
      lru.set('foo', 'bar')
      assert.equal(lru.get('foo'), 'bar')
      var callback = this.callback
      setTimeout(function () {
        callback(null, lru)
      }, 100)
    },
    'the entry is removed if age > max_age': function (lru) {
      assert.equal(lru.peek('foo'), null)
    }
  }
})

suite.addBatch({
  'evicting items by age': {
    topic: function () {
      var lru = new LRU({maxAge: 5})
      lru.set('foo', 'bar')
      assert.equal(lru.get('foo'), 'bar')
      var callback = this.callback
      setTimeout(function () {
        callback(null, lru)
      }, 100)
    },
    'the entry is removed if age > max_age': function (lru) {
      assert.equal(lru.get('foo'), null)
    }
  },
  'evicting items by age (2)': {
    topic: function () {
      var lru = new LRU({maxAge: 100000})
      lru.set('foo', 'bar')
      assert.equal(lru.get('foo'), 'bar')
      var callback = this.callback
      setTimeout(function () {
        callback(null, lru)
      }, 100)
    },
    'the entry is not removed if age < max_age': function (lru) {
      assert.equal(lru.get('foo'), 'bar')
    }
  }
})

suite.addBatch({
  'idempotent changes': {
    'set() and remove() on empty LRU is idempotent': function () {
      var lru = new LRU()
      var json1 = JSON.stringify(lru)

      lru.set('foo1', 'bar1')
      lru.remove('foo1')
      var json2 = JSON.stringify(lru)

      assert.deepEqual(json2, json1)
    },

    '2 set()s and 2 remove()s on empty LRU is idempotent': function () {
      var lru = new LRU()
      var json1 = JSON.stringify(lru)

      lru.set('foo1', 'bar1')
      lru.set('foo2', 'bar2')
      lru.remove('foo1')
      lru.remove('foo2')
      var json2 = JSON.stringify(lru)

      assert.deepEqual(json2, json1)
    },

    '2 set()s and 2 remove()s (in opposite order) on empty LRU is idempotent': function () {
      var lru = new LRU()
      var json1 = JSON.stringify(lru)

      lru.set('foo1', 'bar1')
      lru.set('foo2', 'bar2')
      lru.remove('foo2')
      lru.remove('foo1')
      var json2 = JSON.stringify(lru)

      assert.deepEqual(json2, json1)
    },

    'after setting one key, get() is idempotent': function () {
      var lru = new LRU(2)
      lru.set('a', 'a')
      var json1 = JSON.stringify(lru)

      lru.get('a')
      var json2 = JSON.stringify(lru)

      assert.equal(json2, json1)
    },

    'after setting two keys, get() on last-set key is idempotent': function () {
      var lru = new LRU(2)
      lru.set('a', 'a')
      lru.set('b', 'b')
      var json1 = JSON.stringify(lru)

      lru.get('b')
      var json2 = JSON.stringify(lru)

      assert.equal(json2, json1)
    }
  }
})

suite.addBatch({
  'evict event': {
    '\'evict\' event is fired when evicting old keys': function () {
      var lru = new LRU(2)
      var events = []
      lru.on('evict', function (element) { events.push(element) })

      lru.set('foo1', 'bar1')
      lru.set('foo2', 'bar2')
      lru.set('foo3', 'bar3')
      lru.set('foo4', 'bar4')

      var expect = [{key: 'foo1', value: 'bar1'}, {key: 'foo2', value: 'bar2'}]
      assert.deepEqual(events, expect)
    }
  }
})

suite.export(module)
