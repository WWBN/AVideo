# lru

**A simple LRU cache supporting O(1) set, get and eviction of old keys**

## Installation

```bash
$ npm install lru
```

### Example

```javascript
var LRU = require('lru');

var cache = new LRU(2),
    evicted

cache.on('evict',function(data) { evicted = data });

cache.set('foo', 'bar');
cache.get('foo'); //=> bar

cache.set('foo2', 'bar2');
cache.get('foo2'); //=> bar2

cache.set('foo3', 'bar3'); // => evicted = { key: 'foo', value: 'bar' }
cache.get('foo3');         // => 'bar3'
cache.remove('foo2')       // => 'bar2'
cache.remove('foo4')       // => undefined
cache.length               // => 1
cache.keys                 // => ['foo3']

cache.clear()              // => it will NOT emit the 'evict' event
cache.length               // => 0
cache.keys                 // => []
```

### API

#### `LRU( length )`
Create a new LRU cache that stores `length` elements before evicting the least recently used.
Optionally you can pass an options map with additional options:

```js
{
  max: maxElementsToStore,
  maxAge: maxAgeInMilliseconds
}
```

If you pass `maxAge` items will be evicted if they are older than `maxAge` when you access them.

**Returns**: the newly created LRU cache


#### Properties
##### `.length`
The number of keys currently in the cache.

##### `.keys`
Array of all the keys currently in the cache.

#### Methods

##### `.set( key, value )`
Set the value of the key and mark the key as most recently used.

**Returns**: `value`

##### `.get( key )`
Query the value of the key and mark the key as most recently used.

**Returns**: value of key if found; `undefined` otherwise.

##### `.peek( key )`
Query the value of the key without marking the key as most recently used.

**Returns**: value of key if found; `undefined` otherwise.

##### `.remove( key )`
Remove the value from the cache.


**Returns**: value of key if found; `undefined` otherwise.

##### `.clear()`
Clear the cache. This method does **NOT** emit the `evict` event.

##### `.on( event, callback )`
Respond to events. Currently only the `evict` event is implemented. When a key is evicted, the callback is executed with an associative array containing the evicted key: `{key: key, value: value}`.


### Credits

A big thanks to [Dusty Leary](https://github.com/dustyleary) who
finished the library.

### License

MIT
