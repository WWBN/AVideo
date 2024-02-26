var assert = require('assert');
var compact2string = require('./');

describe('compact2string', function() {

  it('should return expected IPv4 address', function() {
    assert.equal('10.10.10.5:65408', compact2string(new Buffer("0A0A0A05FF80", "hex")));
  });
  it('should return expected IPv6 address', function() {
    assert.equal('[2a03:2880:2110:9f07:face:b00c::1]:80', compact2string(new Buffer("2a03288021109f07faceb00c000000010050", "hex")));
  });

  it('should throw an error if the buffer length isn\'t 6 or 18', function() {
    assert.throws(function() {
      compact2string(new Buffer("0A0A0A05", "hex"));
    }, /should contain 6 or 18 bytes/);
  });
});

describe('compact2string.multi', function() {
  it('should return expected multi', function() {
    assert.deepEqual([ '10.10.10.5:128', '100.56.58.99:28525' ], compact2string.multi(new Buffer("0A0A0A05008064383a636f6d", "hex")));
  });

  it('should throw an error if the buffer isn\'t a multiple of 6', function() {
    assert.throws(function() {
      compact2string.multi(new Buffer("0A0A0A05050505", "hex"));
    }, /multiple of/);
  });

});

describe('compact2string.multi6', function() {
  it('should return expected multi6', function() {
    assert.deepEqual([ '[2a03:2880:2110:9f07:face:b00c::1]:80', '[2a00:1450:4008:801::1010]:443' ], compact2string.multi6(new Buffer("2a03288021109f07faceb00c0000000100502a00145040080801000000000000101001bb", "hex")));
  });

  it('should throw an error if the buffer isn\'t a multiple of 18', function() {
    assert.throws(function() {
      compact2string.multi6(new Buffer("0A0A0A050505050A0A0A050505050A0A0A05050505", "hex"));
    }, /multiple of/);
  });

});
