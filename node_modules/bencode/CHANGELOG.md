## [2.0.3](https://github.com/themasch/node-bencode/compare/v2.0.2...v2.0.3) (2022-05-13)

## [2.0.2](https://github.com/themasch/node-bencode/compare/v2.0.1...v2.0.2) (2021-07-28)


### Bug Fixes

* Patch release to drop a dependecy to safe-buffer ([#99](https://github.com/themasch/node-bencode/issues/99)) ([a661715](https://github.com/themasch/node-bencode/commit/a6617150c53c3c00d0cd12c685c5f2ee47db30c0))

## 2.0.1

- fix deprecation warning on Buffer() constructor (@jhermsmeier)
- update dev depedencies (@jhermsmeier)

## 2.0.0

- Drop support for Node 0.10, 0.12., add support for Node 8 & 9  (@jhermsmeier)
- Support for typed arrays (@jhermsmeier, @nazar-pc)

## 1.0.0

- Support Node 0.10, 0.12, and early Node 4 (@feross)

## 0.12.0

- Add `btparse` to benchmarks (@themasch)
- Use `Buffer.from()` & `Buffer.allocUnsafe()` (@slang800)
- Use constants for character codes (@slang800)
- Fix Makefile (@zunsthy)

## 0.11.0

- Ignore null-values when encoding (@jhermsmeier)
- Add test/BEP-0023: Test correct handling of compacted peer lists (@jhermsmeier)
- Implement a faster way to parse intergers from buffers (@themasch)
- Fix string to be decoded in README (@ngotchac)

## 0.10.0

- Add `standard` code style (@slang800)
- Update benchmarks (@slang800)
- Remove `lib/dict.js` (@slang800)
- Move `main` entrypoint into ./lib (@slang800)
- Clean up `package.json` (@slang800)
- Remove extra files from being published to npm (@slang800)

## 0.9.0

- Implement the `abstract-encoding` API (@jhermsmeier)

## 0.8.0

- Add support for encoding `Boolean` values (@kaelar)

## 0.7.0

- Add binary key support (@deoxxa)
- Improve test output format (@jhermsmeier)
- Removed node v0.8 from CI tests

## 0.6.0

- Fixed invalid test data (@themasch)
- Added `Makefile` for browser tests (@themasch)
- Fixed Browserify compatibility (@themasch)

## 0.5.2

- Thorough fix for 64 bit and 53 bit numbers (@pwmckenna)

## 0.5.1

- Added warning on float conversion during encoding (@jhermsmeier)

## 0.5.0

- Added support for 64 bit number values (@pwmckenna)
- Switched benchmark lib to `matcha` (@themasch)
- Fixed npm scripts to work on Windows (@jhermsmeier)

## 0.4.3
 * improved performance a lot
 * dropped support for de- and encoding floats to respect the spec

   *note:* node-bencode will still decodes stuff like "i42.23e" but will cast the
   result to an interger

## 0.4.2
 * bugfix: sort dictionary keys to follow the spec

## 0.4.1
 * bugfix: number decoding was kinda broken

## 0.4.0
 * fixed problems with multibyte strings
 * some performance improvements
 * improved code quality

## 0.3.0
 * #decode() accepts a encoding as its second paramtere

## 0.2.0
 * complete rewrite, @jhermsmeier joins the team

## 0.1.0
 * added encoding

## 0.0.1
First version, decoding only
