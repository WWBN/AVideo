# level-concat-iterator

**Concatenate entries from an iterator into an array.**

[![level badge][level-badge]](https://github.com/Level/awesome)
[![npm](https://img.shields.io/npm/v/level-concat-iterator.svg)](https://www.npmjs.com/package/level-concat-iterator)
[![Node version](https://img.shields.io/node/v/level-concat-iterator.svg)](https://www.npmjs.com/package/level-concat-iterator)
[![Test](https://img.shields.io/github/workflow/status/Level/concat-iterator/Test?label=test)](https://github.com/Level/concat-iterator/actions/workflows/test.yml)
[![Coverage](https://img.shields.io/codecov/c/github/Level/supports?label=&logo=codecov&logoColor=fff)](https://codecov.io/gh/Level/supports)
[![Standard](https://img.shields.io/badge/standard-informational?logo=javascript&logoColor=fff)](https://standardjs.com)
[![Common Changelog](https://common-changelog.org/badge.svg)](https://common-changelog.org)
[![Donate](https://img.shields.io/badge/donate-orange?logo=open-collective&logoColor=fff)](https://opencollective.com/level)

## Usage

```js
const concat = require('level-concat-iterator')
const level = require('level')

const db = level('./db')

db.put('foo', 'bar', function (err) {
  if (err) throw err

  concat(db.iterator(), function (err, entries) {
    if (err) throw err

    // [{ key: 'foo', value: 'bar' }]
    console.log(entries)
  })
})
```

With promises:

```js
await db.put('foo', 'bar')
const entries = await concat(db.iterator())
```

_If you are upgrading: please see [`UPGRADING.md`](UPGRADING.md)._

## API

### `concat(iterator[, callback])`

Takes an `abstract-leveldown` compatible `iterator` as first parameter and calls the `callback` with an array of entries, where each entry is an object in the form `{ key, value }`. Calls the `callback` with an error if `iterator.next()` or `iterator.end()` errors. If no callback is provided, a promise is returned.

## Contributing

[`Level/concat-iterator`](https://github.com/Level/concat-iterator) is an **OPEN Open Source Project**. This means that:

> Individuals making significant and valuable contributions are given commit-access to the project to contribute as they see fit. This project is more like an open wiki than a standard guarded open source project.

See the [Contribution Guide](https://github.com/Level/community/blob/master/CONTRIBUTING.md) for more details.

## Donate

Support us with a monthly donation on [Open Collective](https://opencollective.com/level) and help us continue our work.

## License

[MIT](LICENSE)

[level-badge]: https://leveljs.org/img/badge.svg
