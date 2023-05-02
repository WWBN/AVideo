var tape = require('tape')
var remove = require('./')

tape('remove', function (t) {
  var list = [0, 1, 2]
  remove(list, 1)
  t.same(list.sort(), [0, 2])
  remove(list, 0)
  t.same(list.sort(), [2])
  remove(list, 0)
  t.same(list, [])
  t.end()
})

tape('out of bounds', function (t) {
  var list = [0, 1, 2]
  remove(list, 42)
  t.same(list, [0, 1, 2])
  remove(list, -1)
  t.same(list, [0, 1, 2])
  t.end()
})
