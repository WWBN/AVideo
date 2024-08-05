# leveldown

[![level badge][level-badge]](https://github.com/Level/awesome)
[![npm](https://img.shields.io/npm/v/leveldown.svg)](https://www.npmjs.com/package/leveldown)
[![Node version](https://img.shields.io/node/v/leveldown.svg)](https://www.npmjs.com/package/leveldown)
[![Test](https://img.shields.io/github/workflow/status/Level/leveldown/Test?label=test)](https://github.com/Level/leveldown/actions/workflows/test.yml)
[![Coverage](https://img.shields.io/codecov/c/github/Level/leveldown?label=&logo=codecov&logoColor=fff)](https://codecov.io/gh/Level/leveldown)
[![Standard](https://img.shields.io/badge/standard-informational?logo=javascript&logoColor=fff)](https://standardjs.com)
[![Common Changelog](https://common-changelog.org/badge.svg)](https://common-changelog.org)
[![Donate](https://img.shields.io/badge/donate-orange?logo=open-collective&logoColor=fff)](https://opencollective.com/level)

## Table of Contents

<details><summary>Click to expand</summary>

- [Introduction](#introduction)
- [Supported Platforms](#supported-platforms)
  - [Notes](#notes)
- [API](#api)
  - [`db = leveldown(location)`](#db--leveldownlocation)
  - [`db.open([options, ]callback)`](#dbopenoptions-callback)
    - [`options`](#options)
  - [`db.close(callback)`](#dbclosecallback)
  - [`db.put(key, value[, options], callback)`](#dbputkey-value-options-callback)
    - [`options`](#options-1)
  - [`db.get(key[, options], callback)`](#dbgetkey-options-callback)
    - [`options`](#options-2)
  - [`db.getMany(keys[, options][, callback])`](#dbgetmanykeys-options-callback)
  - [`db.del(key[, options], callback)`](#dbdelkey-options-callback)
    - [`options`](#options-3)
  - [`db.batch(operations[, options], callback)` _(array form)_](#dbbatchoperations-options-callback-array-form)
  - [`db.batch()` _(chained form)_](#dbbatch-chained-form)
  - [`db.approximateSize(start, end, callback)`](#dbapproximatesizestart-end-callback)
  - [`db.compactRange(start, end, callback)`](#dbcompactrangestart-end-callback)
  - [`db.getProperty(property)`](#dbgetpropertyproperty)
  - [`db.iterator([options])`](#dbiteratoroptions)
  - [`db.clear([options, ]callback)`](#dbclearoptions-callback)
  - [`chainedBatch`](#chainedbatch)
    - [`chainedBatch.put(key, value)`](#chainedbatchputkey-value)
    - [`chainedBatch.del(key)`](#chainedbatchdelkey)
    - [`chainedBatch.clear()`](#chainedbatchclear)
    - [`chainedBatch.write([options, ]callback)`](#chainedbatchwriteoptions-callback)
    - [`chainedBatch.db`](#chainedbatchdb)
  - [`iterator`](#iterator)
    - [`for await...of iterator`](#for-awaitof-iterator)
    - [`iterator.next([callback])`](#iteratornextcallback)
    - [`iterator.seek(key)`](#iteratorseekkey)
    - [`iterator.end([callback])`](#iteratorendcallback)
    - [`iterator.db`](#iteratordb)
  - [`leveldown.destroy(location, callback)`](#leveldowndestroylocation-callback)
  - [`leveldown.repair(location, callback)`](#leveldownrepairlocation-callback)
- [Safety](#safety)
  - [Database State](#database-state)
- [Snapshots](#snapshots)
- [Getting Support](#getting-support)
- [Contributing](#contributing)
  - [Git Submodules](#git-submodules)
  - [Publishing](#publishing)
- [Donate](#donate)
- [License](#license)

</details>

## Introduction

This module was originally part of [`levelup`](https://github.com/Level/levelup) but was later extracted and now serves as a stand-alone binding for LevelDB.

It is **strongly recommended** that you use `levelup` in preference to `leveldown` unless you have measurable performance reasons to do so. `levelup` is optimised for usability and safety. Although we are working to improve the safety of the `leveldown` interface it is still easy to crash your Node process if you don't do things in just the right way.

See the section on [safety](#safety) below for details of known unsafe operations with `leveldown`.

## Supported Platforms

We aim to support _at least_ Active LTS and Current Node.js releases, Electron 5.0.0, as well as any future Node.js and Electron releases thanks to [N-API](https://nodejs.org/api/n-api.html). The minimum node version for `leveldown` is `10.12.0`. Conversely, for node >= 12, the minimum `leveldown` version is `5.0.0`.

The `leveldown` npm package ships with prebuilt binaries for popular 64-bit platforms as well as ARM, M1, Android and Alpine (musl) and is known to work on:

- **Linux** (including ARM platforms such as Raspberry Pi and Kindle)
- **Mac OS** (10.7 and later)
- **Solaris** (SmartOS & Nodejitsu)
- **FreeBSD**
- **Windows**

When installing `leveldown`, [`node-gyp-build`](https://github.com/prebuild/node-gyp-build) will check if a compatible binary exists and fallback to a compile step if it doesn't. In that case you'll need a [valid `node-gyp` installation](https://github.com/nodejs/node-gyp#installation).

If you don't want to use the prebuilt binary for the platform you are installing on, specify the `--build-from-source` flag when you install. One of:

```
npm install --build-from-source
npm install leveldown --build-from-source
```

If you are working on `leveldown` itself and want to re-compile the C++ code, run `npm run rebuild`.

### Notes

- If you get compilation errors on Node.js 12, please ensure you have `leveldown` >= 5. This can be checked by running `npm ls leveldown`.
- On Linux flavors with an old glibc (Debian 8, Ubuntu 14.04, RHEL 7, CentOS 7) you must either update `leveldown` to >= 5.3.0 or use `--build-from-source`.
- On Alpine 3 it was previously necessary to use `--build-from-source`. This is no longer the case.
- The Android prebuilds are made for and built against Node.js core rather than the [`nodejs-mobile`](https://github.com/JaneaSystems/nodejs-mobile) fork.

## API

_If you are upgrading: please see [`UPGRADING.md`](UPGRADING.md)._

### `db = leveldown(location)`

Returns a new `leveldown` instance. `location` is a String pointing to the LevelDB location to be opened.

### `db.open([options, ]callback)`

Open the store. The `callback` function will be called with no arguments when the database has been successfully opened, or with a single `error` argument if the open operation failed for any reason.

#### `options`

The optional `options` argument may contain:

- `createIfMissing` (boolean, default: `true`): If `true`, will initialise an empty database at the specified location if one doesn't already exist. If `false` and a database doesn't exist you will receive an error in your `open()` callback and your database won't open.

- `errorIfExists` (boolean, default: `false`): If `true`, you will receive an error in your `open()` callback if the database exists at the specified location.

- `compression` (boolean, default: `true`): If `true`, all _compressible_ data will be run through the Snappy compression algorithm before being stored. Snappy is very fast and shouldn't gain much speed by disabling so leave this on unless you have good reason to turn it off.

- `cacheSize` (number, default: `8 * 1024 * 1024` = 8MB): The size (in bytes) of the in-memory [LRU](http://en.wikipedia.org/wiki/Cache_algorithms#Least_Recently_Used) cache with frequently used uncompressed block contents.

**Advanced options**

The following options are for advanced performance tuning. Modify them only if you can prove actual benefit for your particular application.

- `writeBufferSize` (number, default: `4 * 1024 * 1024` = 4MB): The maximum size (in bytes) of the log (in memory and stored in the .log file on disk). Beyond this size, LevelDB will convert the log data to the first level of sorted table files. From the LevelDB documentation:

> Larger values increase performance, especially during bulk loads. Up to two write buffers may be held in memory at the same time, so you may wish to adjust this parameter to control memory usage. Also, a larger write buffer will result in a longer recovery time the next time the database is opened.

- `blockSize` (number, default `4096` = 4K): The _approximate_ size of the blocks that make up the table files. The size related to uncompressed data (hence "approximate"). Blocks are indexed in the table file and entry-lookups involve reading an entire block and parsing to discover the required entry.

- `maxOpenFiles` (number, default: `1000`): The maximum number of files that LevelDB is allowed to have open at a time. If your data store is likely to have a large working set, you may increase this value to prevent file descriptor churn. To calculate the number of files required for your working set, divide your total data by `'maxFileSize'`.

- `blockRestartInterval` (number, default: `16`): The number of entries before restarting the "delta encoding" of keys within blocks. Each "restart" point stores the full key for the entry, between restarts, the common prefix of the keys for those entries is omitted. Restarts are similar to the concept of keyframes in video encoding and are used to minimise the amount of space required to store keys. This is particularly helpful when using deep namespacing / prefixing in your keys.

- `maxFileSize` (number, default: `2* 1024 * 1024` = 2MB): The maximum amount of bytes to write to a file before switching to a new one. From the LevelDB documentation:

> ... if your filesystem is more efficient with larger files, you could consider increasing the value. The downside will be longer compactions and hence longer latency/performance hiccups. Another reason to increase this parameter might be when you are initially populating a large database.

### `db.close(callback)`

`close()` is an instance method on an existing database object. The underlying LevelDB database will be closed and the `callback` function will be called with no arguments if the operation is successful or with a single `error` argument if the operation failed for any reason.

`leveldown` waits for any pending operations to finish before closing. For example:

```js
db.put('key', 'value', function (err) {
  // This happens first
})

db.close(function (err) {
  // This happens second
})
```

### `db.put(key, value[, options], callback)`

Store a new entry or overwrite an existing entry.

The `key` and `value` objects may either be strings or Buffers. Other object types are converted to strings with the `toString()` method. Keys may not be `null` or `undefined` and objects converted with `toString()` should not result in an empty-string. Values may not be `null` or `undefined`. Values of `''`, `[]` and `Buffer.alloc(0)` (and any object resulting in a `toString()` of one of these) will be stored as a zero-length character array and will therefore be retrieved as either `''` or `Buffer.alloc(0)` depending on the type requested.

A richer set of data-types is catered for in `levelup`.

#### `options`

The only property currently available on the `options` object is `sync` _(boolean, default: `false`)_. If you provide a `sync` value of `true` in your `options` object, LevelDB will perform a synchronous write of the data; although the operation will be asynchronous as far as Node is concerned. Normally, LevelDB passes the data to the operating system for writing and returns immediately, however a synchronous write will use `fsync()` or equivalent so your callback won't be triggered until the data is actually on disk. Synchronous filesystem writes are **significantly** slower than asynchronous writes but if you want to be absolutely sure that the data is flushed then you can use `{ sync: true }`.

The `callback` function will be called with no arguments if the operation is successful or with a single `error` argument if the operation failed for any reason.

### `db.get(key[, options], callback)`

Get a value from the LevelDB store by `key`.

The `key` object may either be a string or a Buffer and cannot be `undefined` or `null`. Other object types are converted to strings with the `toString()` method and the resulting string _may not_ be a zero-length. A richer set of data-types is catered for in `levelup`.

Values fetched via `get()` that are stored as zero-length character arrays (`null`, `undefined`, `''`, `[]`, `Buffer.alloc(0)`) will return as empty-`String` (`''`) or `Buffer.alloc(0)` when fetched with `asBuffer: true` (see below).

#### `options`

The optional `options` object may contain:

- `asBuffer` (boolean, default: `true`): Used to determine whether to return the `value` of the entry as a string or a Buffer. Note that converting from a Buffer to a string incurs a cost so if you need a string (and the `value` can legitimately become a UTF8 string) then you should fetch it as one with `{ asBuffer: false }` and you'll avoid this conversion cost.
- `fillCache` (boolean, default: `true`): LevelDB will by default fill the in-memory LRU Cache with data from a call to get. Disabling this is done by setting `fillCache` to `false`.

The `callback` function will be called with a single `error` if the operation failed for any reason, including if the key was not found. If successful the first argument will be `null` and the second argument will be the `value` as a string or Buffer depending on the `asBuffer` option.

### `db.getMany(keys[, options][, callback])`

Get multiple values from the store by an array of `keys`. The optional `options` object may contain `asBuffer` and `fillCache`, as described in [`get()`](#dbgetkey-options-callback).

The `callback` function will be called with an `Error` if the operation failed for any reason. If successful the first argument will be `null` and the second argument will be an array of values with the same order as `keys`. If a key was not found, the relevant value will be `undefined`.

If no callback is provided, a promise is returned.

### `db.del(key[, options], callback)`

Delete an entry. The `key` object may either be a string or a Buffer and cannot be `undefined` or `null`. Other object types are converted to strings with the `toString()` method and the resulting string _may not_ be a zero-length. A richer set of data-types is catered for in `levelup`.

#### `options`

The only property currently available on the `options` object is `sync` _(boolean, default: `false`)_. See [`db.put()`](#dbputkey-value-options-callback) for details about this option.

The `callback` function will be called with no arguments if the operation is successful or with a single `error` argument if the operation failed for any reason.

### `db.batch(operations[, options], callback)` _(array form)_

Perform multiple _put_ and/or _del_ operations in bulk. The `operations` argument must be an `Array` containing a list of operations to be executed sequentially, although as a whole they are performed as an atomic operation.

Each operation is contained in an object having the following properties: `type`, `key`, `value`, where the `type` is either `'put'` or `'del'`. In the case of `'del'` the `value` property is ignored.

Any entries where the `key` or `value` (in the case of `'put'`) is `null` or `undefined` will cause an error to be returned on the `callback`. Any entries where the `type` is `'put'` that have a `value` of `[]`, `''` or `Buffer.alloc(0)` will be stored as a zero-length character array and therefore be fetched during reads as either `''` or `Buffer.alloc(0)` depending on how they are requested. See [`levelup`](https://github.com/Level/levelup#batch) for full documentation on how this works in practice.

The optional `options` argument may contain:

- `sync` (boolean, default: `false`). See [`db.put()`](#dbputkey-value-options-callback) for details about this option.

The `callback` function will be called with no arguments if the batch is successful or with an `Error` if the batch failed for any reason.

### `db.batch()` _(chained form)_

Returns a new [`chainedBatch`](#chainedbatch) instance.

### `db.approximateSize(start, end, callback)`

`approximateSize()` is an instance method on an existing database object. Used to get the approximate number of bytes of file system space used by the range `[start..end)`. The result may not include recently written data.

The `start` and `end` parameters may be strings or Buffers representing keys in the LevelDB store.

The `callback` function will be called with a single `error` if the operation failed for any reason. If successful the first argument will be `null` and the second argument will be the approximate size as a Number.

### `db.compactRange(start, end, callback)`

`compactRange()` is an instance method on an existing database object. Used to manually trigger a database compaction in the range `[start..end)`.

The `start` and `end` parameters may be strings or Buffers representing keys in the LevelDB store.

The `callback` function will be called with no arguments if the operation is successful or with a single `error` argument if the operation failed for any reason.

### `db.getProperty(property)`

`getProperty` can be used to get internal details from LevelDB. When issued with a valid property string, a readable string will be returned (this method is synchronous).

Currently, the only valid properties are:

- `leveldb.num-files-at-levelN`: return the number of files at level _N_, where N is an integer representing a valid level (e.g. "0").

- `leveldb.stats`: returns a multi-line string describing statistics about LevelDB's internal operation.

- `leveldb.sstables`: returns a multi-line string describing all of the _sstables_ that make up contents of the current database.

### `db.iterator([options])`

Returns a new [`iterator`](#iterator) instance. Accepts the following range options:

- `gt` (greater than), `gte` (greater than or equal) define the lower bound of the range to be iterated. Only entries where the key is greater than (or equal to) this option will be included in the range. When `reverse=true` the order will be reversed, but the entries iterated will be the same.
- `lt` (less than), `lte` (less than or equal) define the higher bound of the range to be iterated. Only entries where the key is less than (or equal to) this option will be included in the range. When `reverse=true` the order will be reversed, but the entries iterated will be the same.
- `reverse` _(boolean, default: `false`)_: iterate entries in reverse order. Beware that a reverse seek can be slower than a forward seek.
- `limit` _(number, default: `-1`)_: limit the number of entries collected by this iterator. This number represents a _maximum_ number of entries and may not be reached if you get to the end of the range first. A value of `-1` means there is no limit. When `reverse=true` the entries with the highest keys will be returned instead of the lowest keys.

In addition to range options, `iterator()` takes the following options:

- `keys` _(boolean, default: `true`)_: whether to return the key of each entry. If set to `false`, calls to `iterator.next(callback)` will yield keys with a value of `undefined` <sup>1</sup>. There is a small efficiency gain if you ultimately don't care what the keys are as they don't need to be converted and copied into JavaScript.
- `values` _(boolean, default: `true`)_: whether to return the value of each entry. If set to `false`, calls to `iterator.next(callback)` will yield values with a value of `undefined`<sup>1</sup>.
- `keyAsBuffer` _(boolean, default: `true`)_: Whether to return the key of each entry as a Buffer or string. Converting from a Buffer to a string incurs a cost so if you need a string (and the `key` can legitimately become a UTF8 string) then you should fetch it as one.
- `valueAsBuffer` _(boolean, default: `true`)_: Whether to return the value of each entry as a Buffer or string.
- `fillCache` (boolean, default: `false`): whether LevelDB's LRU-cache should be filled with data read.

<sup>1</sup> `leveldown` returns an empty string rather than `undefined` at the moment.

### `db.clear([options, ]callback)`

Delete all entries or a range. Not guaranteed to be atomic. Accepts the following range options (with the same rules as on iterators):

- `gt` (greater than), `gte` (greater than or equal) define the lower bound of the range to be deleted. Only entries where the key is greater than (or equal to) this option will be included in the range. When `reverse=true` the order will be reversed, but the entries deleted will be the same.
- `lt` (less than), `lte` (less than or equal) define the higher bound of the range to be deleted. Only entries where the key is less than (or equal to) this option will be included in the range. When `reverse=true` the order will be reversed, but the entries deleted will be the same.
- `reverse` _(boolean, default: `false`)_: delete entries in reverse order. Only effective in combination with `limit`, to remove the last N entries.
- `limit` _(number, default: `-1`)_: limit the number of entries to be deleted. This number represents a _maximum_ number of entries and may not be reached if you get to the end of the range first. A value of `-1` means there is no limit. When `reverse=true` the entries with the highest keys will be deleted instead of the lowest keys.

If no options are provided, all entries will be deleted. The `callback` function will be called with no arguments if the operation was successful or with an `Error` if it failed for any reason.

### `chainedBatch`

#### `chainedBatch.put(key, value)`

Queue a `put` operation on this batch. This may throw if `key` or `value` is invalid, following the same rules as the [array form of `db.batch()`](#dbbatchoperations-options-callback-array-form).

#### `chainedBatch.del(key)`

Queue a `del` operation on this batch. This may throw if `key` is invalid.

#### `chainedBatch.clear()`

Clear all queued operations on this batch.

#### `chainedBatch.write([options, ]callback)`

Commit the queued operations for this batch. All operations will be written atomically, that is, they will either all succeed or fail with no partial commits.

The optional `options` argument may contain:

- `sync` (boolean, default: `false`). See [`db.put()`](#dbputkey-value-options-callback) for details about this option.

The `callback` function will be called with no arguments if the batch is successful or with an `Error` if the batch failed for any reason. After `write` has been called, no further operations are allowed.

#### `chainedBatch.db`

A reference to the `db` that created this chained batch.

### `iterator`

An iterator allows you to _iterate_ the entire store or a range. It operates on a snapshot of the store, created at the time `db.iterator()` was called. This means reads on the iterator are unaffected by simultaneous writes.

Iterators can be consumed with [`for await...of`](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Statements/for-await...of) or by manually calling `iterator.next()` in succession. In the latter mode, `iterator.end()` must always be called. In contrast, finishing, throwing or breaking from a `for await...of` loop automatically calls `iterator.end()`.

An iterator reaches its natural end in the following situations:

- The end of the store has been reached
- The end of the range has been reached
- The last `iterator.seek()` was out of range.

An iterator keeps track of when a `next()` is in progress and when an `end()` has been called so it doesn't allow concurrent `next()` calls, it does allow `end()` while a `next()` is in progress and it doesn't allow either `next()` or `end()` after `end()` has been called.

#### `for await...of iterator`

Yields arrays containing a `key` and `value`. The type of `key` and `value` depends on the options passed to `db.iterator()`.

```js
try {
  for await (const [key, value] of db.iterator()) {
    console.log(key)
  }
} catch (err) {
  console.error(err)
}
```

#### `iterator.next([callback])`

Advance the iterator and yield the entry at that key. If an error occurs, the `callback` function will be called with an `Error`. Otherwise, the `callback` receives `null`, a `key` and a `value`. The type of `key` and `value` depends on the options passed to `db.iterator()`. If the iterator has reached its natural end, both `key` and `value` will be `undefined`.

If no callback is provided, a promise is returned for either an array (containing a `key` and `value`) or `undefined` if the iterator reached its natural end.

**Note:** Always call `iterator.end()`, even if you received an error and even if the iterator reached its natural end.

#### `iterator.seek(key)`

Seek the iterator to a given key or the closest key. Subsequent calls to `iterator.next()` will yield entries with keys equal to or larger than `target`, or equal to or smaller than `target` if the `reverse` option passed to `db.iterator()` was true. The same applies to implicit `iterator.next()` calls in a `for await...of` loop.

If range options like `gt` were passed to `db.iterator()` and `target` does not fall within that range, the iterator will reach its natural end.

#### `iterator.end([callback])`

End iteration and free up underlying resources. The `callback` function will be called with no arguments on success or with an `Error` if ending failed for any reason.

If no callback is provided, a promise is returned.

#### `iterator.db`

A reference to the `db` that created this iterator.

### `leveldown.destroy(location, callback)`

Completely remove an existing LevelDB database directory. You can use this function in place of a full directory `rm` if you want to be sure to only remove LevelDB-related files. If the directory only contains LevelDB files, the directory itself will be removed as well. If there are additional, non-LevelDB files in the directory, those files, and the directory, will be left alone.

The callback will be called when the destroy operation is complete, with a possible `error` argument.

### `leveldown.repair(location, callback)`

Attempt a restoration of a damaged LevelDB store. From the LevelDB documentation:

> If a DB cannot be opened, you may attempt to call this method to resurrect as much of the contents of the database as possible. Some data may be lost, so be careful when calling this function on a database that contains important information.

You will find information on the _repair_ operation in the _LOG_ file inside the store directory.

A `repair()` can also be used to perform a compaction of the LevelDB log into table files.

The callback will be called when the repair operation is complete, with a possible `error` argument.

## Safety

### Database State

Currently `leveldown` does not track the state of the underlying LevelDB instance. This means that calling `open()` on an already open database may result in an error. Likewise, calling any other operation on a non-open database may result in an error.

`levelup` currently tracks and manages state and will prevent out-of-state operations from being send to `leveldown`. If you use `leveldown` directly then you must track and manage state for yourself.

## Snapshots

`leveldown` exposes a feature of LevelDB called [snapshots](https://github.com/google/leveldb/blob/master/doc/index.md#snapshots). This means that when you do e.g. `createReadStream` and `createWriteStream` at the same time, any data modified by the write stream will not affect data emitted from the read stream. In other words, a LevelDB Snapshot captures the latest state at the time the snapshot was created, enabling the snapshot to iterate or read the data without seeing any subsequent writes. Any read not performed on a snapshot will implicitly use the latest state.

## Getting Support

You're welcome to open an issue on the [GitHub repository](https://github.com/Level/leveldown) if you have a question.

Past and no longer active support channels include the `##leveldb` IRC channel on Freenode and the [Node.js LevelDB](https://groups.google.com/forum/#!forum/node-levelup) Google Group.

## Contributing

[`Level/leveldown`](https://github.com/Level/leveldown) is an **OPEN Open Source Project**. This means that:

> Individuals making significant and valuable contributions are given commit-access to the project to contribute as they see fit. This project is more like an open wiki than a standard guarded open source project.

See the [Contribution Guide](https://github.com/Level/community/blob/master/CONTRIBUTING.md) for more details.

### Git Submodules

This project uses Git Submodules. This means that you should clone it recursively if you're planning on working on it:

```bash
$ git clone --recurse-submodules https://github.com/Level/leveldown.git
```

Alternatively, you can initialize submodules inside the cloned folder:

```bash
$ git submodule update --init --recursive
```

### Publishing

1. Increment the version: `npm version ..`
2. Push to GitHub: `git push --follow-tags`
3. Wait for CI to complete
4. Download prebuilds into `./prebuilds`: `npm run download-prebuilds`
5. Optionally verify loading a prebuild: `npm run test-prebuild`
6. Optionally verify which files npm will include: `canadian-pub`
7. Finally: `npm publish`

## Donate

Support us with a monthly donation on [Open Collective](https://opencollective.com/level) and help us continue our work.

## License

[MIT](LICENSE)

_`leveldown` builds on the excellent work of the LevelDB and Snappy teams from Google and additional contributors. LevelDB and Snappy are both issued under the [New BSD License](http://opensource.org/licenses/BSD-3-Clause). A large portion of `leveldown` Windows support comes from the [Windows LevelDB port](http://code.google.com/r/kkowalczyk-leveldb/) (archived) by [Krzysztof Kowalczyk](http://blog.kowalczyk.info/) ([`@kjk`](https://twitter.com/kjk)). If you're using `leveldown` on Windows, you should give him your thanks!_

[level-badge]: https://leveljs.org/img/badge.svg
