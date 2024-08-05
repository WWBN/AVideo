# Upgrade Guide

This document describes breaking changes and how to upgrade. For a complete list of changes including minor and patch releases, please refer to the [changelog](CHANGELOG.md).

## Table of Contents

<details><summary>Click to expand</summary>

- [7.0.0](#700)
- [6.0.0](#600)
  - [Changes to public API](#changes-to-public-api)
    - [Nullish values are rejected](#nullish-values-are-rejected)
    - [Range options are serialized](#range-options-are-serialized)
    - [The rules for range options have been relaxed](#the-rules-for-range-options-have-been-relaxed)
    - [Zero-length array keys are rejected](#zero-length-array-keys-are-rejected)
    - [No longer assumes support of boolean and `NaN` keys](#no-longer-assumes-support-of-boolean-and-nan-keys)
    - [Browser support](#browser-support)
  - [Changes to private API](#changes-to-private-api)
    - [`location` was removed](#location-was-removed)
    - [Abstract test suite has moved to a single entry point](#abstract-test-suite-has-moved-to-a-single-entry-point)
    - [The `collectEntries` utility has moved](#the-collectentries-utility-has-moved)
    - [Setup and teardown became noops](#setup-and-teardown-became-noops)
    - [Optional tests have been separated](#optional-tests-have-been-separated)
    - [Iterator must have a `db` reference](#iterator-must-have-a-db-reference)
    - [Seeking became part of official API](#seeking-became-part-of-official-api)
    - [Chained batch has been refactored](#chained-batch-has-been-refactored)
    - [Default `_serializeKey` and `_serializeValue` became identity functions](#default-_serializekey-and-_serializevalue-became-identity-functions)
- [5.0.0](#500)
- [4.0.0](#400)
  - - [default `testCommon` parameter](#default-testcommon-parameter)
    - [`testBuffer` parameter removed](#testbuffer-parameter-removed)
    - [`.approximateSize` method removed](#approximatesize-method-removed)
    - [`._isBuffer` method removed](#_isbuffer-method-removed)
    - [`isLevelDOWN` function removed](#isleveldown-function-removed)
    - [`ranges-test.js` renamed](#ranges-testjs-renamed)
- [3.0.0](#300)

</details>

## 7.0.0

Legacy range options have been removed ([Level/community#86](https://github.com/Level/community/issues/86)). If you previously did:

```js
db.iterator({ start: 'a', end: 'z' })
```

An error would now be thrown and you must instead do:

```js
db.iterator({ gte: 'a', lte: 'z' })
```

This release also drops support of legacy runtime environments ([Level/community#98](https://github.com/Level/community/issues/98)):

- Node.js 6 and 8
- Internet Explorer 11
- Safari 9-11
- Stock Android browser (AOSP).

Lastly, and less likely to be a breaking change, the [`immediate`](https://github.com/calvinmetcalf/immediate) browser shim for `process.nextTick()` has been replaced with the smaller [`queue-microtask`](https://github.com/feross/queue-microtask). In the future we might use `queueMicrotask` in Node.js too.

## 6.0.0

This release brings a major refactoring of the test suite, decouples `abstract-leveldown` from disk-based implementations and solves long-standing issues around serialization and type support. Because the changes are substantial, this guide has two sections:

1. **Changes to public API** - for consumers of any implementation.
2. **Changes to private API** - intended for implementors.

### Changes to public API

#### Nullish values are rejected

In addition to rejecting `null` and `undefined` as _keys_, `abstract-leveldown` now also rejects these types as _values_, due to preexisting significance in streams and iterators.

Before this, the behavior of these types depended on a large number of factors: `_serializeValue` and type support of the underlying storage, whether `get()`, `iterator()` or a stream was used to retrieve values, the `keys` and `asBuffer` options of `iterator()` and finally, which encoding was selected.

#### Range options are serialized

Previously, range options like `lt` were passed through as-is, unlike keys.

#### The rules for range options have been relaxed

Because `null`, `undefined`, zero-length strings and zero-length buffers are significant types in encodings like `bytewise` and `charwise`, they became valid as range options. In fact, any type is now valid. This means `db.iterator({ gt: undefined })` is not the same as `db.iterator({})`.

Furthermore, `abstract-leveldown` makes no assumptions about the meaning of these types. Range tests that assumed `null` meant "not defined" have been removed.

#### Zero-length array keys are rejected

Though this was already the case because `_checkKey` stringified its input before checking the length, that behavior has been replaced with an explicit `Array.isArray()` check and a new error message.

#### No longer assumes support of boolean and `NaN` keys

A test that asserted boolean and `NaN` keys were valid has been removed.

#### Browser support

IE10 has been dropped.

### Changes to private API

#### `location` was removed

`AbstractLevelDOWN` is no longer associated with a `location`. It's up to the implementation to handle it if it's required.

If your implementation has a `location` and you previously did:

```js
function YourDOWN (location) {
  AbstractLevelDOWN.call(this, location)
}
```

You must now do:

```js
function YourDOWN (location) {
  this.location = location
  AbstractLevelDOWN.call(this)
}
```

Be sure to include appropriate type checks. If you relied on the default `AbstractLevelDOWN` behavior that would be:

```js
if (typeof location !== 'string') {
  throw new Error('constructor requires a location string argument')
}
```

#### Abstract test suite has moved to a single entry point

Instead of including test files individually, you can and should include the test suite with one `require()` statement. If you previously did:

```js
const test = require('tape')
const testCommon = require('abstract-leveldown/testCommon')
const YourDOWN = require('.')

require('abstract-leveldown/abstract/get-test').all(YourDOWN, test, testCommon)
require('abstract-leveldown/abstract/put-test').all(YourDOWN, test, testCommon)

// etc
```

You must now do:

```js
const test = require('tape')
const suite = require('abstract-leveldown/test')
const YourDOWN = require('.')

suite({
  test: test,
  factory: function () {
    return new YourDOWN()
  }
})
```

The input to the test suite is a new form of `testCommon`. Should you need to reuse `testCommon` for your own (additional) tests, use the included utility to create a `testCommon` with defaults:

```js
const test = require('tape')
const suite = require('abstract-leveldown/test')
const YourDOWN = require('.')

const testCommon = suite.common({
  test: test,
  factory: function () {
    return new YourDOWN()
  }
})

suite(testCommon)
```

As part of removing `location`, the abstract tests no longer use `testCommon.location()`. Instead an implementation _must_ implement `factory()` which _must_ return a unique and isolated database instance. This allows implementations to pass options to their constructor.

The `testCommon.cleanup` method has been removed. Because `factory()` returns a unique database instance, cleanup should no longer be necessary. The `testCommon.lastLocation` method has also been removed as there is no remaining use of it in abstract tests.

Previously, implementations using the default `testCommon` had to include `rimraf` in their `devDependencies` and browser-based implementations had to exclude `rimraf` from browserify builds. This is no longer the case.

If your implementation is disk-based we recommend using [`tempy`](https://github.com/sindresorhus/tempy) (or similar) to create unique temporary directories. Together with `factory()` your setup could now look something like:

```js
const test = require('tape')
const tempy = require('tempy')
const suite = require('abstract-leveldown/test')
const YourDOWN = require('.')

suite({
  test: test,
  factory: function () {
    return new YourDOWN(tempy.directory())
  }
})
```

#### The `collectEntries` utility has moved

The `testCommon.collectEntries` method has moved to the npm package  `level-concat-iterator`. If your (additional) tests depend on `collectEntries` and you previously did:

```js
testCommon.collectEntries(iterator, function (err, entries) {})
```

You must now do:

```js
const concat = require('level-concat-iterator')
concat(iterator, function (err, entries) {})
```

#### Setup and teardown became noops

Because cleanup is no longer necessary, the `testCommon.setUp` and `testCommon.tearDown` methods are now noops by default. If you do need to perform (a)synchronous work before or after each test, `setUp` and `tearDown` can be overridden:

```js
suite({
  // ..
  setUp: function (t) {
    t.end()
  },
  tearDown: function (t) {
    t.end()
  }
})
```

#### Optional tests have been separated

If your implementation does not support snapshots or other optional features, the relevant tests may be skipped. For example:

```js
suite({
  // ..
  snapshots: false
})
```

Please see the [README](README.md) for a list of options. Note that some of these have replaced `process.browser` checks.

#### Iterator must have a `db` reference

The `db` argument of the `AbstractIterator` constructor became mandatory, as well as a public `db` property on the instance. Its existence is not new; the test suite now asserts that your implementation also has it.

#### Seeking became part of official API

If your implementation previously defined the public `iterator.seek(target)`, it must now define the private `iterator._seek(target)`. The new public API is equal to the reference implementation of `leveldown` except for two differences:

- The `target` argument is not type checked, this is up to the implementation.
- The `target` argument is passed through `db._serializeKey`.

Please see the [README](README.md) for details.

#### Chained batch has been refactored

- The default `_clear` method is no longer a noop; instead it clears the operations queued by `_put` and/or `_del`
- The `_write` method now takes an `options` object as its first argument
- The `db` argument of the `AbstractChainedBatch` constructor became mandatory, as well as a public `db` property on the instance, which was previously named `_db`.

#### Default `_serializeKey` and `_serializeValue` became identity functions

They return whatever is given. Previously they were opinionated and mostly geared towards string- and Buffer-based storages. Implementations that didn't already define their own serialization should now do so, according to the types that they support. Please refer to the [README](README.md) for recommended behavior.

## 5.0.0

Dropped support for node 4. No other breaking changes.

## 4.0.0

#### default `testCommon` parameter

The `testCommon` parameter will now default to `abstract-leveldown/testCommon.js`. You can omit this parameter, unless your implementation needs a custom version.

If your code today looks something like:

```js
const test = require('tape')
const testCommon = require('abstract-leveldown/testCommon')
const leveldown = require('./your-leveldown')
const abstract = require('abstract-leveldown/abstract/get-test')

abstract.all(leveldown, test, testCommon)
```

You can simplify it to:

```js
const test = require('tape')
const leveldown = require('./your-leveldown')
const abstract = require('abstract-leveldown/abstract/get-test')

abstract.all(leveldown, test)
```

#### `testBuffer` parameter removed

The `abstract/put-get-del-test.js` previously took a custom `testBuffer` parameter. After an [analysis](https://github.com/Level/abstract-leveldown/pull/175#issuecomment-353867144) of various implementations we came to the conclusion that the parameter has no use.

If your implementation is using this abstract test, change from:

```js
const test = require('tape')
const testCommon = require('abstract-leveldown/testCommon')
const leveldown = require('./your-leveldown')
const fs = require('fs')
const path = require('path')
const testBuffer = fs.readFileSync(path.join(__dirname, 'data/testdata.bin'))
const abstract = require('abstract-leveldown/abstract/put-get-del-test')

abstract.all(leveldown, test, testBuffer, testCommon)
```

to:

```js
const test = require('tape')
const testCommon = require('abstract-leveldown/testCommon')
const leveldown = require('./your-leveldown')
const abstract = require('abstract-leveldown/abstract/put-get-del-test')

abstract.all(leveldown, test, testCommon)
```

or if `testCommon` is also redundant, to:

```js
const test = require('tape')
const leveldown = require('./your-leveldown')
const abstract = require('abstract-leveldown/abstract/put-get-del-test')

abstract.all(leveldown, test)
```

#### `.approximateSize` method removed

The `.approximateSize` method has been removed from the public API. It is heavily related to `LevelDB` and more often than not, other stores lack the native primitives to implement this. If you did implement the internal `_approximateSize` method, that is now dead code. To preserve the method in your public API, rename it to `approximateSize` and also take care of the initialization code. Look to `leveldown` for inspiration.

Also, the corresponding abstract tests have been removed, so your implementation can no longer require `abstract/approximate-size-test`.

#### `._isBuffer` method removed

Because `Buffer` is available in all environments nowadays, there is no need for alternatives like typed arrays. It is preferred to use `Buffer` and `Buffer.isBuffer()` directly.

#### `isLevelDOWN` function removed

This was a legacy function.

#### `ranges-test.js` renamed

We have refactored a lot of the tests. Specifically the iterator tests were split in two and in that process we renamed `ranges-test.js` to `iterator-range-test.js`.

If your implementation is using these tests then change from:

```js
const abstract = require('abstract-leveldown/abstract/ranges-test')
```

to:

```js
const abstract = require('abstract-leveldown/abstract/iterator-range-test')
```

## 3.0.0

No changes to the API. New major version because support for node 0.12 was dropped.
