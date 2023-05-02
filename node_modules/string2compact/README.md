# string2compact [![ci][ci-image]][ci-url] [![npm][npm-image]][npm-url] [![downloads][downloads-image]][downloads-url] [![javascript style guide][standard-image]][standard-url]

[ci-image]: https://github.com/webtorrent/string2compact/actions/workflows/ci.yml/badge.svg
[ci-url]: https://github.com/webtorrent/string2compact/actions/workflows/ci.yml
[npm-image]: https://img.shields.io/npm/v/string2compact.svg
[npm-url]: https://npmjs.org/package/string2compact
[downloads-image]: https://img.shields.io/npm/dm/string2compact.svg
[downloads-url]: https://npmjs.org/package/string2compact
[standard-image]: https://img.shields.io/badge/code_style-standard-brightgreen.svg
[standard-url]: https://standardjs.com

#### Convert 'hostname:port' strings to BitTorrent's compact ip/host binary returned by Trackers

This module is the opposite of [compact2string](https://npmjs.org/package/compact2string). It works in the browser with [browserify](http://browserify.org/). It is used by [WebTorrent](http://webtorrent.io), and more specifically, the [bittorrent-tracker](https://github.com/webtorrent/bittorrent-tracker) and [bittorrent-dht](https://github.com/webtorrent/bittorrent-dht) modules.

### install

```
npm install string2compact
```

### usage

#### single string2compact

```js
var string2compact = require('string2compact')
var compact = string2compact('10.10.10.5:65408')
console.log(compact) // new Buffer('0A0A0A05FF80', 'hex')
```

#### tranform multiple into one buffer

```js
var compacts = string2compact([ '10.10.10.5:128', '100.56.58.99:28525' ])
console.log(compacts) // new Buffer('0A0A0A05008064383a636f6d', 'hex')
```

### license

MIT. Copyright (c) [Feross Aboukhadijeh](https://feross.org) and [WebTorrent, LLC](https://webtorrent.io).
