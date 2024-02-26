var ipaddr = require('ipaddr.js');

var compact2string = function (buf) {
  switch(buf.length) {
  case 6:
    return buf[0] + "." + buf[1] + "." + buf[2] + "." + buf[3] + ":" + buf.readUInt16BE(4);
    break;
  case 18:
    var hexGroups = [];
    for(var i = 0; i < 8; i++) {
      hexGroups.push(buf.readUInt16BE(i * 2).toString(16));
    }
    var host = ipaddr.parse(hexGroups.join(":")).toString();
    return "[" + host + "]:" + buf.readUInt16BE(16);
  default:
    throw new Error("Invalid Compact IP/PORT, It should contain 6 or 18 bytes");
  }
};

compact2string.multi = function (buf) {
  if(buf.length % 6 !== 0)
    throw new Error("buf length isn't multiple of compact IP/PORTs (6 bytes)");

  var output = [];
  for (var i = 0; i <= buf.length - 1; i = i + 6) {
    output.push(compact2string(buf.slice(i, i + 6)));
  }

  return output;
};

compact2string.multi6 = function (buf) {
  if(buf.length % 18 !== 0)
    throw new Error("buf length isn't multiple of compact IP6/PORTs (18 bytes)");

  var output = [];
  for (var i = 0; i <= buf.length - 1; i = i + 18) {
    output.push(compact2string(buf.slice(i, i + 18)));
  }

  return output;
};

module.exports = compact2string;
