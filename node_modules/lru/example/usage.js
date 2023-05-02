var LRU = require('../')

var cache = new LRU(2)

var evicted

cache.on('evict', function (data) {
  evicted = data
})

cache.set('foo', 'bar')           // => 'bar'
cache.get('foo')                  // => 'bar'

cache.set('foo2', 'bar2')         // => 'bar2'
cache.get('foo2')                 // => 'bar2'

cache.set('foo3', 'bar3')         // => 'bar3'
cache.get('foo3')                 // => 'bar3'

console.log(cache.remove('foo2')) // => { key: 'foo2', value: 'bar2' }
console.log(cache.remove('foo4')) // => undefined
console.log(cache.length)         // => 1
console.log(evicted)              // => evicted = { key: 'foo', value: 'bar' }

cache.clear()                     // it will NOT emit the 'evict' event
console.log(cache.length)         // => 0
