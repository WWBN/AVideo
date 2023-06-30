
'use strict';

var
  QUnit = require('qunit'),
  typedArrayIndexOf = require('../lib/utils/typed-array').typedArrayIndexOf;

QUnit.module('typedArrayIndexOf');

QUnit.test('returns -1 when no typed array', function(assert) {
  assert.equal(typedArrayIndexOf(null, 5, 0), -1, 'returned -1');
});

QUnit.test('returns -1 when element not found', function(assert) {
  assert.equal(typedArrayIndexOf(new Uint8Array([2, 3]), 5, 0), -1, 'returned -1');
});

QUnit.test('returns -1 when element not found starting from index', function(assert) {
  assert.equal(typedArrayIndexOf(new Uint8Array([3, 5, 6, 7]), 5, 2), -1, 'returned -1');
});

QUnit.test('returns index when element found', function(assert) {
  assert.equal(typedArrayIndexOf(new Uint8Array([2, 3, 5]), 5, 0), 2, 'returned 2');
});

QUnit.test('returns index when element found starting from index', function(assert) {
  assert.equal(typedArrayIndexOf(new Uint8Array([2, 3, 5]), 5, 2), 2, 'returned 2');
});
