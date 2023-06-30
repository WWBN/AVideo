'use strict';

var
  QUnit = require('qunit'),
  string = require('../lib/utils/string');

QUnit.module('String Utils');

QUnit.test('Converts a uint8 array into a C string from start of array until first null char', function(assert) {
  var uint8String = new Uint8Array([0x66, 0x6F, 0x6F, 0x2E, 0x62, 0x61, 0x72, 0x2E, 0x76, 0x61, 0x6C, 0x75, 0x65, 0x00,
    0x76, 0x61, 0x6C, 0x75, 0x65, 0x2E, 0x62, 0x61, 0x72, 0x00]); // foo.bar.value\0value.bar\0
  var firstString = string.uint8ToCString(uint8String);
  assert.equal(firstString, 'foo.bar.value\0', 'converts uint8 data to a c string');
  assert.equal(firstString.length, 14, 'string has the correct length');
  var secondString = string.uint8ToCString(uint8String.subarray(14));
  assert.equal(secondString, 'value.bar\0', 'converts uint8 data to a c string');
  assert.equal(secondString.length, 10, 'string has the correct length');
});

QUnit.test('Converts a uint8 array with no null char into a C string', function(assert) {
  var uint8String = new Uint8Array([0x66, 0x6F, 0x6F, 0x2E, 0x62, 0x61, 0x72]); // foo.bar
  var firstString = string.uint8ToCString(uint8String);
  assert.equal(firstString, 'foo.bar\0', 'converts uint8 data to a c string');
  assert.equal(firstString.length, 8, 'string has the correct length');
});

QUnit.test('Returns a null char from a uint8 array starting with a null char', function(assert) {
  var uint8String = new Uint8Array([0x00, 0x66, 0x6F, 0x6F, 0x2E, 0x62, 0x61, 0x72]); // \0foo.bar
  var firstString = string.uint8ToCString(uint8String);
  assert.equal(firstString, '\0', 'converts uint8 data to a c string');
  assert.equal(firstString.length, 1, 'string has the correct length');
});
