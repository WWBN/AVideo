var tape = require('tape')
var iterate = require('./')

tape('iterates all', function (t) {
  var ite = iterate([1, 2, 3, 4, 5, 6, 7, 8, 9])
  var found = {}

  for (var i = 0; i < 9; i++) {
    var j = ite()
    t.ok(!!j, 'not null')
    if (found[j]) t.ok(false, 'duplicate')
    found[j] = true
  }

  t.ok(!ite(), 'no more')
  t.end()
})
