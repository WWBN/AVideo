<a name="3.1.0"></a>
# [3.1.0](https://github.com/brightcove/videojs-overlay/compare/v3.0.0...v3.1.0) (2023-06-15)

### Features

* make overlays mutable (#223) ([a6c0353](https://github.com/brightcove/videojs-overlay/commit/a6c0353)), closes [#223](https://github.com/brightcove/videojs-overlay/issues/223)

<a name="3.0.0"></a>
# [3.0.0](https://github.com/brightcove/videojs-overlay/compare/v2.1.5...v3.0.0) (2022-12-16)

### Chores

* skip vjsverify es check ([e10a7b1](https://github.com/brightcove/videojs-overlay/commit/e10a7b1))
* update build tooling to drop older browser support (#220) ([85fe717](https://github.com/brightcove/videojs-overlay/commit/85fe717)), closes [#220](https://github.com/brightcove/videojs-overlay/issues/220)


### BREAKING CHANGES

* This removes support for some older browsers such as IE 11

<a name="2.1.5"></a>
## [2.1.5](https://github.com/brightcove/videojs-overlay/compare/v2.1.4...v2.1.5) (2021-09-13)

### Bug Fixes

* qualityMenu button to left of playToggle (#118) ([952188e](https://github.com/brightcove/videojs-overlay/commit/952188e)), closes [#118](https://github.com/brightcove/videojs-overlay/issues/118)

### Chores

* **package:** update lint-staged to version 8.1.0 (#94) ([b3cee2f](https://github.com/brightcove/videojs-overlay/commit/b3cee2f)), closes [#94](https://github.com/brightcove/videojs-overlay/issues/94)
* **package:** update npm-run-all/videojs-generator-verify for security ([9d2d40f](https://github.com/brightcove/videojs-overlay/commit/9d2d40f))
* **package:** update videojs-generate-karma-config to version 5.0.0 (#93) ([4e9d161](https://github.com/brightcove/videojs-overlay/commit/4e9d161)), closes [#93](https://github.com/brightcove/videojs-overlay/issues/93)
* **package:** update videojs-generate-rollup-config to version 2.3.1 (#95) ([dedba7c](https://github.com/brightcove/videojs-overlay/commit/dedba7c)), closes [#95](https://github.com/brightcove/videojs-overlay/issues/95)
* **package:** update videojs-standard to version 8.0.2 (#96) ([b548b3b](https://github.com/brightcove/videojs-overlay/commit/b548b3b)), closes [#96](https://github.com/brightcove/videojs-overlay/issues/96)

<a name="2.1.4"></a>
## [2.1.4](https://github.com/brightcove/videojs-overlay/compare/v2.1.3...v2.1.4) (2018-09-19)

### Bug Fixes

* Properly expose plugin version (#80) ([9c8822c](https://github.com/brightcove/videojs-overlay/commit/9c8822c)), closes [#80](https://github.com/brightcove/videojs-overlay/issues/80)
* Remove the postinstall script to prevent install issues (#77) ([5edecf7](https://github.com/brightcove/videojs-overlay/commit/5edecf7)), closes [#77](https://github.com/brightcove/videojs-overlay/issues/77)

### Chores

* update to generator-videojs-plugin[@7](https://github.com/7).2.0 ([7e0b357](https://github.com/brightcove/videojs-overlay/commit/7e0b357))

<a name="2.1.3"></a>
## [2.1.3](https://github.com/brightcove/videojs-overlay/compare/v2.1.2...v2.1.3) (2018-08-23)

### Chores

* generator v7 (#73) ([449679e](https://github.com/brightcove/videojs-overlay/commit/449679e)), closes [#73](https://github.com/brightcove/videojs-overlay/issues/73)

<a name="2.1.2"></a>
## [2.1.2](https://github.com/brightcove/videojs-overlay/compare/v2.1.1...v2.1.2) (2018-08-03)

### Bug Fixes

* babel the es dist, by updating the generator (#68) ([bd7f070](https://github.com/brightcove/videojs-overlay/commit/bd7f070)), closes [#68](https://github.com/brightcove/videojs-overlay/issues/68)

### Chores

* **package:** update dependencies, enable greenkeeper (#67) ([70afc00](https://github.com/brightcove/videojs-overlay/commit/70afc00)), closes [#67](https://github.com/brightcove/videojs-overlay/issues/67)

<a name="2.1.1"></a>
## [2.1.1](https://github.com/brightcove/videojs-overlay/compare/v2.1.0...v2.1.1) (2018-07-05)

### Chores

* update to generator v6 (#63) ([4ac2452](https://github.com/brightcove/videojs-overlay/commit/4ac2452)), closes [#63](https://github.com/brightcove/videojs-overlay/issues/63)

<a name="2.1.0"></a>
# [2.1.0](https://github.com/brightcove/videojs-overlay/compare/v2.0.0...v2.1.0) (2018-04-20)

### Features

* Allow choosing the placement of overlay elements in the control bar. ([b8b0607](https://github.com/brightcove/videojs-overlay/commit/b8b0607))

### Bug Fixes

* Upgrade rollup to v0.52.x to fix build failures ([#60](https://github.com/brightcove/videojs-overlay/issues/60)) ([b0b3a5d](https://github.com/brightcove/videojs-overlay/commit/b0b3a5d))

<a name="2.0.0"></a>
# [2.0.0](https://github.com/brightcove/videojs-overlay/compare/v1.1.3...v2.0.0) (2017-08-24)

### Features

* Fix vertical centre alignment and add align-center ([#38](https://github.com/brightcove/videojs-overlay/issues/38)) ([8649210](https://github.com/brightcove/videojs-overlay/commit/8649210))

### Bug Fixes

* Fix malformed README link ([#43](https://github.com/brightcove/videojs-overlay/issues/43)) ([c2b1315](https://github.com/brightcove/videojs-overlay/commit/c2b1315))
* remove global browserify transforms, so parent packages don't break ([#48](https://github.com/brightcove/videojs-overlay/issues/48)) ([aa74853](https://github.com/brightcove/videojs-overlay/commit/aa74853))

### Code Refactoring

* Update to use generator v5 tooling. ([#51](https://github.com/brightcove/videojs-overlay/issues/51)) ([bfeff8c](https://github.com/brightcove/videojs-overlay/commit/bfeff8c))

## 1.1.4 (2017-04-03)
* fix: remove global browserify transforms, so parent packages don't break

## 1.1.3 (2017-02-27)
* update travis to test vjs 5/6 (#46)

## 1.1.2 (2017-02-03)
* Added Video.js 5 and 6 cross-compatibility.

## 1.1.1 (2016-08-05)
* Fixed issue where max-width was being set on all overlays rather than only those showBackground=false.

## 1.1.0 (2016-07-27)
* Added showBackground option to show or hide the overlay background.
* Added attachToControlBar option to allow bottom align control bars to move when the control bar minimizes.

## 1.0.2 (2016-06-10)
_(none)_

## 1.0.1 (2016-03-08)
* Fixed #22, should not have been checking for integers only.

## 1.0.0 (2016-02-12)
* Major refactoring of plugin to align with generator-videojs-plugin standards.
* Fixed significant edge-case issues with creation/destruction of overlays.

## 0.1.0 (2014-04-29)
* Initial release
