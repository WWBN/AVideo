<a name="5.2.0"></a>
# [5.2.0](https://github.com/brightcove/videojs-playlist/compare/v5.1.2...v5.2.0) (2024-10-07)

### Features

* add isAutoadvancing flag to indicate auto-advancing state ([#267](https://github.com/brightcove/videojs-playlist/issues/267)) ([0d7a41a](https://github.com/brightcove/videojs-playlist/commit/0d7a41a))

### Bug Fixes

* properly remove event listeners in auto-advance.js and update tests ([#268](https://github.com/brightcove/videojs-playlist/issues/268)) ([2a455cd](https://github.com/brightcove/videojs-playlist/commit/2a455cd))

<a name="5.1.2"></a>
## [5.1.2](https://github.com/brightcove/videojs-playlist/compare/v5.1.1...v5.1.2) (2024-05-20)

<a name="5.1.1"></a>
## [5.1.1](https://github.com/brightcove/videojs-playlist/compare/v5.1.0...v5.1.1) (2024-04-23)

### Bug Fixes

* Poster hidden when index is greater than 0 ([#260](https://github.com/videojs/videojs-playlist/pull/260)) ([d117f2c](https://github.com/brightcove/videojs-playlist/commit/8d117f2c))

<a name="5.1.0"></a>
# [5.1.0](https://github.com/brightcove/videojs-playlist/compare/v5.0.1...v5.1.0) (2023-04-14)

### Features

* add `add` and `remove` methods to modify the playlist dynamically ([#240](https://github.com/brightcove/videojs-playlist/issues/240)) ([bd4aabb](https://github.com/brightcove/videojs-playlist/commit/bd4aabb))

### Chores

* add v8 to dependencies list ([#250](https://github.com/brightcove/videojs-playlist/issues/250)) ([5c2f9f3](https://github.com/brightcove/videojs-playlist/commit/5c2f9f3))

<a name="5.0.1"></a>
## [5.0.1](https://github.com/brightcove/videojs-playlist/compare/v5.0.0...v5.0.1) (2023-03-15)

### Bug Fixes

* posters flash between videos in playlist ([#243](https://github.com/brightcove/videojs-playlist/issues/243)) ([80dde66](https://github.com/brightcove/videojs-playlist/commit/80dde66))

<a name="5.0.0"></a>
# [5.0.0](https://github.com/brightcove/videojs-playlist/compare/v4.3.1...v5.0.0) (2021-12-17)

### Chores

* skip vjsverify es check ([#199](https://github.com/brightcove/videojs-playlist/issues/199)) ([78ee118](https://github.com/brightcove/videojs-playlist/commit/78ee118))
* Update generate-rollup-config to drop older browser support ([#198](https://github.com/brightcove/videojs-playlist/issues/198)) ([b85db66](https://github.com/brightcove/videojs-playlist/commit/b85db66))


### BREAKING CHANGES

* This removes support for some older browsers like IE 11

<a name="4.3.1"></a>
## [4.3.1](https://github.com/brightcove/videojs-playlist/compare/v4.3.0...v4.3.1) (2019-03-20)

### Bug Fixes

* Fix regression(s) introduced by changes to the currentItem function in 4.3.0 ([#145](https://github.com/brightcove/videojs-playlist/issues/145)) ([d49b929](https://github.com/brightcove/videojs-playlist/commit/d49b929))

### Chores

* **package:** update package-lock.json ([#149](https://github.com/brightcove/videojs-playlist/issues/149)) ([4915847](https://github.com/brightcove/videojs-playlist/commit/4915847))
* **package:** update rollup to version 1.7.0 ([#148](https://github.com/brightcove/videojs-playlist/issues/148)) ([4bf51d4](https://github.com/brightcove/videojs-playlist/commit/4bf51d4))
* **package:** update sinon to version 7.3.0 ([#147](https://github.com/brightcove/videojs-playlist/issues/147)) ([74c3f33](https://github.com/brightcove/videojs-playlist/commit/74c3f33))

<a name="4.3.0"></a>
# [4.3.0](https://github.com/brightcove/videojs-playlist/compare/v4.2.6...v4.3.0) (2019-01-11)

### Features

* Return correct index of a playlist item when there are multiple items with the same source ([#115](https://github.com/brightcove/videojs-playlist/issues/115)) ([0963d58](https://github.com/brightcove/videojs-playlist/commit/0963d58))

### Chores

* **package:** update lint-staged to version 8.1.0 ([#134](https://github.com/brightcove/videojs-playlist/issues/134)) ([7776c14](https://github.com/brightcove/videojs-playlist/commit/7776c14))
* **package:** update npm-run-all/videojs-generator-verify for security ([0491b47](https://github.com/brightcove/videojs-playlist/commit/0491b47))
* **package:** update rollup to version 0.66.0 ([#122](https://github.com/brightcove/videojs-playlist/issues/122)) ([9536367](https://github.com/brightcove/videojs-playlist/commit/9536367))
* **package:** update rollup to version 0.67.3 ([#132](https://github.com/brightcove/videojs-playlist/issues/132)) ([f3f333e](https://github.com/brightcove/videojs-playlist/commit/f3f333e))
* **package:** update videojs-generate-karma-config to version 5.0.0 ([#133](https://github.com/brightcove/videojs-playlist/issues/133)) ([d2953f4](https://github.com/brightcove/videojs-playlist/commit/d2953f4))
* **package:** update videojs-generate-rollup-config to version 2.3.1 ([#135](https://github.com/brightcove/videojs-playlist/issues/135)) ([ab78366](https://github.com/brightcove/videojs-playlist/commit/ab78366))

<a name="4.2.6"></a>
## [4.2.6](https://github.com/brightcove/videojs-playlist/compare/v4.2.5...v4.2.6) (2018-09-05)

### Bug Fixes

* Remove the postinstall script to prevent install issues ([#119](https://github.com/brightcove/videojs-playlist/issues/119)) ([159fafe](https://github.com/brightcove/videojs-playlist/commit/159fafe))

<a name="4.2.5"></a>
## [4.2.5](https://github.com/brightcove/videojs-playlist/compare/v4.2.4...v4.2.5) (2018-08-30)

### Chores

* update generator to v7.1.1 ([12c5d53](https://github.com/brightcove/videojs-playlist/commit/12c5d53))
* **package:** Update rollup to version 0.65.0 ([#116](https://github.com/brightcove/videojs-playlist/issues/116)) ([17d6a37](https://github.com/brightcove/videojs-playlist/commit/17d6a37))
* update to generator-videojs-plugin[@7](https://github.com/7).2.0 ([4b90483](https://github.com/brightcove/videojs-playlist/commit/4b90483))

<a name="4.2.4"></a>
## [4.2.4](https://github.com/brightcove/videojs-playlist/compare/v4.2.3...v4.2.4) (2018-08-23)

### Chores

* generator v7 ([#114](https://github.com/brightcove/videojs-playlist/issues/114)) ([e671236](https://github.com/brightcove/videojs-playlist/commit/e671236))

<a name="4.2.3"></a>
## [4.2.3](https://github.com/brightcove/videojs-playlist/compare/v4.2.2...v4.2.3) (2018-08-03)

### Bug Fixes

* babel the es dist, by updating the generator ([#107](https://github.com/brightcove/videojs-playlist/issues/107)) ([4f1fdb9](https://github.com/brightcove/videojs-playlist/commit/4f1fdb9))

### Chores

* **package:** update dependencies, enable greenkeeper ([#106](https://github.com/brightcove/videojs-playlist/issues/106)) ([5ed060e](https://github.com/brightcove/videojs-playlist/commit/5ed060e))

<a name="4.2.2"></a>
## [4.2.2](https://github.com/brightcove/videojs-playlist/compare/v4.2.1...v4.2.2) (2018-07-05)

### Chores

* generator v6 ([#102](https://github.com/brightcove/videojs-playlist/issues/102)) ([8c50798](https://github.com/brightcove/videojs-playlist/commit/8c50798))

<a name="4.2.1"></a>
## [4.2.1](https://github.com/brightcove/videojs-playlist/compare/v4.2.0...v4.2.1) (2018-06-13)

### Features

* Expose the version of the plugin at the `VERSION` property. ([#94](https://github.com/brightcove/videojs-playlist/issues/94)) ([d71dec1](https://github.com/brightcove/videojs-playlist/commit/d71dec1))

<a name="4.2.0"></a>
# [4.2.0](https://github.com/brightcove/videojs-playlist/compare/v4.1.1...v4.2.0) (2018-01-25)

### Features

* Add 'duringplaylistchange' event. ([#92](https://github.com/brightcove/videojs-playlist/issues/92)) ([eb80503](https://github.com/brightcove/videojs-playlist/commit/eb80503))
* Add 'rest' option to the shuffle method. ([#91](https://github.com/brightcove/videojs-playlist/issues/91)) ([57d5f0c](https://github.com/brightcove/videojs-playlist/commit/57d5f0c))

<a name="4.1.1"></a>
## [4.1.1](https://github.com/brightcove/videojs-playlist/compare/v4.1.0...v4.1.1) (2018-01-08)

### Bug Fixes

* Fix an issue where we could auto-advance even if the user restarted playback after an ended event. ([#88](https://github.com/brightcove/videojs-playlist/issues/88)) ([5d872d1](https://github.com/brightcove/videojs-playlist/commit/5d872d1))

<a name="4.1.0"></a>
# [4.1.0](https://github.com/brightcove/videojs-playlist/compare/v4.0.2...v4.1.0) (2017-11-28)

### Features

* Add new methods: `currentIndex`, `nextIndex`, `previousIndex`, `lastIndex`, `sort`, `reverse`, and `shuffle`. ([#87](https://github.com/brightcove/videojs-playlist/issues/87)) ([271a27b](https://github.com/brightcove/videojs-playlist/commit/271a27b))

### Documentation

* Fix missing call to playlist ([#86](https://github.com/brightcove/videojs-playlist/issues/86)) ([a7ffd57](https://github.com/brightcove/videojs-playlist/commit/a7ffd57))

<a name="4.0.2"></a>
## [4.0.2](https://github.com/brightcove/videojs-playlist/compare/v4.0.1...v4.0.2) (2017-11-13)

### Bug Fixes

* Fix item switching for asynchronous source setting in Video.js 6. ([#85](https://github.com/brightcove/videojs-playlist/issues/85)) ([8a77bf0](https://github.com/brightcove/videojs-playlist/commit/8a77bf0))

<a name="4.0.1"></a>
## [4.0.1](https://github.com/brightcove/videojs-playlist/compare/v4.0.0...v4.0.1) (2017-10-16)

### Chores

* depend on either vjs 5.x or 6.x ([#84](https://github.com/brightcove/videojs-playlist/issues/84)) ([3f3c946](https://github.com/brightcove/videojs-playlist/commit/3f3c946))

<a name="4.0.0"></a>
# [4.0.0](https://github.com/brightcove/videojs-playlist/compare/v2.0.0...v4.0.0) (2017-05-19)

### Chores

* Update tooling using generator v5 prerelease. ([#79](https://github.com/brightcove/videojs-playlist/issues/79)) ([0b53140](https://github.com/brightcove/videojs-playlist/commit/0b53140))


### BREAKING CHANGES

* Remove Bower support.

## 3.1.1 (2017-04-27)
_(none)_

## 3.1.0 (2017-04-03)
* @incompl Add repeat functionality to plugin [#71](https://github.com/brightcove/videojs-playlist/pull/71)

## 3.0.2 (2017-02-10)
* @misteroneill Suppress videojs.plugin deprecation warning in Video.js 6 [#68](https://github.com/brightcove/videojs-playlist/pull/68)

## 3.0.1 (2017-01-30)
* @misteroneill Update project to use latest version of plugin generator as well as ensure cross-version support between Video.js 5 and 6. [#63](https://github.com/brightcove/videojs-playlist/pull/63)

## 3.0.0 (2016-09-12)
* @misteroneill Remove Brightcove VideoCloud-specific Code [#51](https://github.com/brightcove/videojs-playlist/pull/51)

## 2.5.0 (2016-09-12)
* @mister-ben Load playlist with initial video at specified index or no starting video [#38](https://github.com/brightcove/videojs-playlist/pull/38)

## 2.4.1 (2016-04-21)
* @gkatsev fixed build scripts to only apply browserify-shim for dist files. Fixes [#36](https://github.com/brightcove/videojs-playlist/issues/36). [#44](https://github.com/brightcove/videojs-playlist/pull/44)

## 2.4.0 (2016-04-21)
* @vdeshpande Fixed an issue where incorrect end time value was used [#43](https://github.com/brightcove/videojs-playlist/pull/43)

## 2.3.0 (2016-04-19)
* @vdeshpande Support cue point intervals [#37](https://github.com/brightcove/videojs-playlist/pull/37)

## 2.2.0 (2016-01-29)
* @forbesjo Support turning a list of cue points into a TextTrack [#24](https://github.com/brightcove/videojs-playlist/pull/24)

## 2.1.0 (2015-12-30)
* @misteroneill Moved to the generator-videojs-plugin format and added `last()` method [#23](https://github.com/brightcove/videojs-playlist/pull/23)

## 2.0.0 (2015-11-25)
* @misteroneill Updates for video.js 5.x [#22](https://github.com/brightcove/videojs-playlist/pull/22)
* @saramartinez Fix typos in examples for `currentItem()` method [#18](https://github.com/brightcove/videojs-playlist/pull/18)

## 1.0.3 (2015-08-24)
* @forbesjo README update [#16](https://github.com/brightcove/videojs-playlist/pull/16)
* @forbesjo Fix for playlist items without a `src` [#14](https://github.com/brightcove/videojs-playlist/pull/14)

## 1.0.2 (2015-04-09)
* @gkatsev Explicitly define which files are included.

## 1.0.1 (2015-03-30)
* @gkatsev Added missing repository field to `package.json`.

## 1.0.0 (2015-03-30)
* @gkatsev Initial release.
