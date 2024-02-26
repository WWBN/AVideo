# unordered-array-remove

Efficiently remove an element from an unordered array without doing a splice

```
npm install unordered-array-remove
```

[![build status](http://img.shields.io/travis/mafintosh/unordered-array-remove.svg?style=flat)](http://travis-ci.org/mafintosh/unordered-array-remove)

## Usage

``` js
var remove = require('unordered-array-remove')

var list = ['a', 'b', 'c', 'd', 'e']
remove(list, 2) // remove 'c'
console.log(list) // returns ['a', 'b', 'e', 'd'] (no 'c')
```

This works by popping the last element (which is fast because it doesn't need shift all array elements)
and overwriting the removed index with this element.

## License

MIT
