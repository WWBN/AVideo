<a name="2.0.0"></a>
# [2.0.0](https://github.com/videojs/videojs-vr/compare/v1.10.1...v2.0.0) (2023-02-15)

### Chores

* update build tooling to drop older browser support ([#276](https://github.com/videojs/videojs-vr/issues/276)) ([0947a0f](https://github.com/videojs/videojs-vr/commit/0947a0f))


### BREAKING CHANGES

* This drops support for older browsers such as IE

<a name="1.10.1"></a>
## [1.10.1](https://github.com/videojs/videojs-vr/compare/v1.10.0...v1.10.1) (2022-08-16)

### Chores

* **pkg.json:** set license field back to MIT ([#265](https://github.com/videojs/videojs-vr/issues/265)) ([fd7abed](https://github.com/videojs/videojs-vr/commit/fd7abed)), closes [#264](https://github.com/videojs/videojs-vr/issues/264)

### Documentation

* add caveats section for safari ([eb80c8a](https://github.com/videojs/videojs-vr/commit/eb80c8a))

<a name="1.10.0"></a>
# [1.10.0](https://github.com/videojs/videojs-vr/compare/v1.9.0...v1.10.0) (2021-08-31)

### Chores

* skip syntax check with vjsverify due to three ([#249](https://github.com/videojs/videojs-vr/issues/249)) ([3d9df3c](https://github.com/videojs/videojs-vr/commit/3d9df3c))

<a name="1.9.0"></a>
# [1.9.0](https://github.com/videojs/videojs-vr/compare/v1.8.0...v1.9.0) (2021-08-31)

### Features

* vendor threejs files ([#247](https://github.com/videojs/videojs-vr/issues/247)) ([6a76537](https://github.com/videojs/videojs-vr/commit/6a76537)), closes [#244](https://github.com/videojs/videojs-vr/issues/244)

### Chores

* don't run tests on version ([4cfb996](https://github.com/videojs/videojs-vr/commit/4cfb996))
* udpate generator-helpers ([#248](https://github.com/videojs/videojs-vr/issues/248)) ([a3020df](https://github.com/videojs/videojs-vr/commit/a3020df))

<a name="1.8.0"></a>
# [1.8.0](https://github.com/videojs/videojs-vr/compare/v1.7.2...v1.8.0) (2021-06-15)

### Features

* Add an option to prevent clicks from toggling playback ([#239](https://github.com/videojs/videojs-vr/issues/239)) ([7dc2684](https://github.com/videojs/videojs-vr/commit/7dc2684))
* add option to change the detail level of the projection sphere ([#225](https://github.com/videojs/videojs-vr/issues/225)) ([9293eb4](https://github.com/videojs/videojs-vr/commit/9293eb4))

### Bug Fixes

* include edited three.js examples in module dist files ([#238](https://github.com/videojs/videojs-vr/issues/238)) ([93bec48](https://github.com/videojs/videojs-vr/commit/93bec48))
* separate 180_LR/180_TB, add 180_MONO, fix cropping in 180 views. ([91b7963](https://github.com/videojs/videojs-vr/commit/91b7963))

### Chores

* fix publish by skipping require verification ([360abb7](https://github.com/videojs/videojs-vr/commit/360abb7))
* update dependencies, readme, examples, and switch to github actions ci ([#242](https://github.com/videojs/videojs-vr/issues/242)) ([bf97c5b](https://github.com/videojs/videojs-vr/commit/bf97c5b))

### Documentation

* correct some typos ([#232](https://github.com/videojs/videojs-vr/issues/232)) ([643dead](https://github.com/videojs/videojs-vr/commit/643dead))
* fixed a typo in README ([#207](https://github.com/videojs/videojs-vr/issues/207)) ([153c690](https://github.com/videojs/videojs-vr/commit/153c690))

<a name="1.7.2"></a>
## [1.7.2](https://github.com/videojs/videojs-vr/compare/v1.7.1...v1.7.2) (2020-03-03)

### Bug Fixes

* null check fullscreen toggle to fix ios without it ([#197](https://github.com/videojs/videojs-vr/issues/197)) ([24f2483](https://github.com/videojs/videojs-vr/commit/24f2483))

<a name="1.7.1"></a>
## [1.7.1](https://github.com/videojs/videojs-vr/compare/v1.7.0...v1.7.1) (2019-11-18)

### Bug Fixes

* increase touchmove threshold for "taps" on mobile ([#190](https://github.com/videojs/videojs-vr/issues/190)) ([4e92c33](https://github.com/videojs/videojs-vr/commit/4e92c33))

### Chores

* add generator-helper deps back in ([0b8a8cd](https://github.com/videojs/videojs-vr/commit/0b8a8cd))
* add lint-staged as dev-dep ([4844494](https://github.com/videojs/videojs-vr/commit/4844494))
* fixup package-lock urls ([2ba689d](https://github.com/videojs/videojs-vr/commit/2ba689d))

<a name="1.7.0"></a>
# [1.7.0](https://github.com/videojs/videojs-vr/compare/v1.6.1...v1.7.0) (2019-09-09)

### Features

* Add support for Spatial audio rendering via Omnitone ([#181](https://github.com/videojs/videojs-vr/issues/181)) ([2af9aa3](https://github.com/videojs/videojs-vr/commit/2af9aa3))

<a name="1.6.1"></a>
## [1.6.1](https://github.com/videojs/videojs-vr/compare/v1.6.0...v1.6.1) (2019-09-05)

### Bug Fixes

* build css files before publish ([9a85fc9](https://github.com/videojs/videojs-vr/commit/9a85fc9))

<a name="1.6.0"></a>
# [1.6.0](https://github.com/videojs/videojs-vr/compare/v1.5.0...v1.6.0) (2019-08-28)

### Features

* Add 180 projection support ([#172](https://github.com/videojs/videojs-vr/issues/172)) ([67fe78b](https://github.com/videojs/videojs-vr/commit/67fe78b))
* Add an initialization event that triggers after init() is finished ([#158](https://github.com/videojs/videojs-vr/issues/158)) ([3c57c23](https://github.com/videojs/videojs-vr/commit/3c57c23))
* Add YouTube Equi-Angular Cubemap (EAC) projection ([#179](https://github.com/videojs/videojs-vr/issues/179)) ([804c713](https://github.com/videojs/videojs-vr/commit/804c713)), closes [ytdl-org/youtube-dl#15267](https://github.com/ytdl-org/youtube-dl/issues/15267)

### Bug Fixes

* Prevent switching to lowest resolution HLS resolution, due to non-displayed video ([#177](https://github.com/videojs/videojs-vr/issues/177)) ([7338726](https://github.com/videojs/videojs-vr/commit/7338726))

### Chores

* **package:** update npm-run-all to 4.1.5 ([#160](https://github.com/videojs/videojs-vr/issues/160)) ([b2ee794](https://github.com/videojs/videojs-vr/commit/b2ee794))

### Documentation

* Fix JavaScript highlight in README.md ([#143](https://github.com/videojs/videojs-vr/issues/143)) ([dd73e51](https://github.com/videojs/videojs-vr/commit/dd73e51))
* update examples and readme ([2440f77](https://github.com/videojs/videojs-vr/commit/2440f77))

### Tests

* Add a test, fix the build, update generator version ([#184](https://github.com/videojs/videojs-vr/issues/184)) ([17c7ee0](https://github.com/videojs/videojs-vr/commit/17c7ee0))

<a name="1.5.0"></a>
# [1.5.0](https://github.com/videojs/videojs-vr/compare/v1.4.7...v1.5.0) (2018-09-17)

### Features

* motion controls option ([#137](https://github.com/videojs/videojs-vr/issues/137)) ([8024a79](https://github.com/videojs/videojs-vr/commit/8024a79))

### Bug Fixes

* Remove the postinstall script to prevent install issues ([#134](https://github.com/videojs/videojs-vr/issues/134)) ([d6d9ac0](https://github.com/videojs/videojs-vr/commit/d6d9ac0))

### Chores

* update to generator-videojs-plugin[@7](https://github.com/7).2.0 ([634be2b](https://github.com/videojs/videojs-vr/commit/634be2b))
* **package:** update videojs-generate-rollup-config to version 2.2.0 🚀 ([#135](https://github.com/videojs/videojs-vr/issues/135)) ([d42d1f2](https://github.com/videojs/videojs-vr/commit/d42d1f2))

<a name="1.4.7"></a>
## [1.4.7](https://github.com/videojs/videojs-vr/compare/v1.4.6...v1.4.7) (2018-08-23)

### Chores

* generator v7 ([#125](https://github.com/videojs/videojs-vr/issues/125)) ([66d6544](https://github.com/videojs/videojs-vr/commit/66d6544))

<a name="1.4.6"></a>
## [1.4.6](https://github.com/videojs/videojs-vr/compare/v1.4.5...v1.4.6) (2018-08-08)

### Chores

* update deps ([#122](https://github.com/videojs/videojs-vr/issues/122)) ([d175793](https://github.com/videojs/videojs-vr/commit/d175793))

<a name="1.4.5"></a>
## [1.4.5](https://github.com/videojs/videojs-vr/compare/v1.4.4...v1.4.5) (2018-08-03)

### Bug Fixes

* babel the es dist, by updating the generator ([#117](https://github.com/videojs/videojs-vr/issues/117)) ([2d4468d](https://github.com/videojs/videojs-vr/commit/2d4468d))

<a name="1.4.4"></a>
## [1.4.4](https://github.com/videojs/videojs-vr/compare/v1.4.3...v1.4.4) (2018-08-01)

### Bug Fixes

* dispose event listeners on window correctly ([#119](https://github.com/videojs/videojs-vr/issues/119)) ([b6a8125](https://github.com/videojs/videojs-vr/commit/b6a8125))

<a name="1.4.3"></a>
## [1.4.3](https://github.com/videojs/videojs-vr/compare/v1.4.2...v1.4.3) (2018-07-20)

### Bug Fixes

* incorrect css naming ([b7068b7](https://github.com/videojs/videojs-vr/commit/b7068b7))

<a name="1.4.2"></a>
## [1.4.2](https://github.com/videojs/videojs-vr/compare/v1.4.1...v1.4.2) (2018-07-05)

### Chores

* update to generator v6 ([#102](https://github.com/videojs/videojs-vr/issues/102)) ([467a4e6](https://github.com/videojs/videojs-vr/commit/467a4e6))

<a name="1.4.1"></a>
## [1.4.1](https://github.com/videojs/videojs-vr/compare/v1.4.0...v1.4.1) (2018-06-18)

### Bug Fixes

* ios sizing issue when deactivating vr display ([#104](https://github.com/videojs/videojs-vr/issues/104)) ([3a83a05](https://github.com/videojs/videojs-vr/commit/3a83a05))

<a name="1.4.0"></a>
# [1.4.0](https://github.com/videojs/videojs-vr/compare/v1.3.0...v1.4.0) (2018-06-08)

### Bug Fixes

* hide control bar while moving, allow clicking to play/pause, allow right click ([#96](https://github.com/videojs/videojs-vr/issues/96)) ([21b66ca](https://github.com/videojs/videojs-vr/commit/21b66ca))

### Chores

* **package:** update rollup and rollup-plugin-json ([#93](https://github.com/videojs/videojs-vr/issues/93)) ([33db8a8](https://github.com/videojs/videojs-vr/commit/33db8a8))

<a name="1.3.0"></a>
# [1.3.0](https://github.com/videojs/videojs-vr/compare/v1.2.1...v1.3.0) (2018-05-23)

### Features

* handle 360_CUBE projection (thanks dillontiner!)  ([#86](https://github.com/videojs/videojs-vr/issues/86)) ([19ae76d](https://github.com/videojs/videojs-vr/commit/19ae76d))

### Bug Fixes

* FrontSide -> BackSide for 360 videos due to changes in three.js ([#88](https://github.com/videojs/videojs-vr/issues/88)) ([e58862d](https://github.com/videojs/videojs-vr/commit/e58862d))
* no rotate instructions and ios back arrow fix ([#75](https://github.com/videojs/videojs-vr/issues/75)) ([0c525cd](https://github.com/videojs/videojs-vr/commit/0c525cd))
* re-implement touch pan controls ([#89](https://github.com/videojs/videojs-vr/issues/89)) ([0cde016](https://github.com/videojs/videojs-vr/commit/0cde016))
* remove safari video image canvas work-around ([#74](https://github.com/videojs/videojs-vr/issues/74)) ([4a9d500](https://github.com/videojs/videojs-vr/commit/4a9d500))
* vjs fluid class usage, and remove hacky mutationobserver work-around ([#76](https://github.com/videojs/videojs-vr/issues/76)) ([db749dc](https://github.com/videojs/videojs-vr/commit/db749dc))

### Chores

* **package:** update rollup to version 0.58.2 ([#77](https://github.com/videojs/videojs-vr/issues/77)) ([a4d611f](https://github.com/videojs/videojs-vr/commit/a4d611f))
* **package:** update rollup-plugin-babel to version 3.0.4 ([#70](https://github.com/videojs/videojs-vr/issues/70)) ([7ae874a](https://github.com/videojs/videojs-vr/commit/7ae874a))
* **package:** update rollup-plugin-commonjs to version 9.1.3 ([#79](https://github.com/videojs/videojs-vr/issues/79)) ([90d5fb6](https://github.com/videojs/videojs-vr/commit/90d5fb6))
* **package:** update three to version 0.92.0 ([#78](https://github.com/videojs/videojs-vr/issues/78)) ([b9668cb](https://github.com/videojs/videojs-vr/commit/b9668cb))

### Documentation

* remove webvr-boilerplate from the readme ([a2015e1](https://github.com/videojs/videojs-vr/commit/a2015e1))

<a name="1.2.1"></a>
## [1.2.1](https://github.com/videojs/videojs-vr/compare/v1.2.0...v1.2.1) (2018-05-08)

### Bug Fixes

* Correctly show an error in IE/Safari when webvr is unsupported ([#67](https://github.com/videojs/videojs-vr/issues/67)) ([67988da](https://github.com/videojs/videojs-vr/commit/67988da))

<a name="1.2.0"></a>
# [1.2.0](https://github.com/videojs/videojs-vr/compare/v1.1.1...v1.2.0) (2018-03-27)

### Bug Fixes

* chrome m55 android gyro breakage by updating webvr-polyfill ([#64](https://github.com/videojs/videojs-vr/issues/64)) ([ff8e461](https://github.com/videojs/videojs-vr/commit/ff8e461))

### Chores

* **package:** update rollup to version 0.57.1 ([#62](https://github.com/videojs/videojs-vr/issues/62)) ([d81a5a4](https://github.com/videojs/videojs-vr/commit/d81a5a4)), closes [#57](https://github.com/videojs/videojs-vr/issues/57)

<a name="1.1.1"></a>
## [1.1.1](https://github.com/videojs/videojs-vr/compare/v1.1.0...v1.1.1) (2018-02-20)

### Bug Fixes

* expose version correctly ([#51](https://github.com/videojs/videojs-vr/issues/51)) ([dd7adc1](https://github.com/videojs/videojs-vr/commit/dd7adc1))
* **OrbitControls:** no pan, less speed, no zoom ([#52](https://github.com/videojs/videojs-vr/issues/52)) ([44b6d41](https://github.com/videojs/videojs-vr/commit/44b6d41))
* rework three example build, to fix webpack ([#53](https://github.com/videojs/videojs-vr/issues/53)) ([382156b](https://github.com/videojs/videojs-vr/commit/382156b))

<a name="1.1.0"></a>
# [1.1.0](https://github.com/videojs/videojs-vr/compare/v1.0.3...v1.1.0) (2018-01-31)

### Features

* remove threejs files from the repo ([#43](https://github.com/videojs/videojs-vr/issues/43)) ([a5cf671](https://github.com/videojs/videojs-vr/commit/a5cf671))

### Bug Fixes

* default projection typo ([#47](https://github.com/videojs/videojs-vr/issues/47)) ([4c0c7bb](https://github.com/videojs/videojs-vr/commit/4c0c7bb))
* native webvr ([#45](https://github.com/videojs/videojs-vr/issues/45)) ([4b3a89f](https://github.com/videojs/videojs-vr/commit/4b3a89f))
* safari hls ([#48](https://github.com/videojs/videojs-vr/issues/48)) ([7dc2a69](https://github.com/videojs/videojs-vr/commit/7dc2a69))

<a name="1.0.3"></a>
## [1.0.3](https://github.com/videojs/videojs-vr/compare/v1.0.2...v1.0.3) (2017-12-04)

### Bug Fixes

* workaround for firefox/polyfill display issue ([#41](https://github.com/videojs/videojs-vr/issues/41)) ([76e6e03](https://github.com/videojs/videojs-vr/commit/76e6e03))

<a name="1.0.2"></a>
## [1.0.2](https://github.com/videojs/videojs-vr/compare/v1.0.1...v1.0.2) (2017-10-19)

### Bug Fixes

* equirectangular in the readme ([#29](https://github.com/videojs/videojs-vr/issues/29)) ([7dad7a1](https://github.com/videojs/videojs-vr/commit/7dad7a1))
* make stereo modes so actually stereo [#32](https://github.com/videojs/videojs-vr/issues/32) ([#33](https://github.com/videojs/videojs-vr/issues/33)) ([1e06433](https://github.com/videojs/videojs-vr/commit/1e06433))

<a name="1.0.1"></a>
## [1.0.1](https://github.com/videojs/videojs-vr/compare/v1.0.0...v1.0.1) (2017-08-29)

### Bug Fixes

* equirectangular should be equivelent to 360 ([#28](https://github.com/videojs/videojs-vr/issues/28)) ([f0e5422](https://github.com/videojs/videojs-vr/commit/f0e5422))

### Chores

* update README ([#23](https://github.com/videojs/videojs-vr/issues/23)) ([9e54437](https://github.com/videojs/videojs-vr/commit/9e54437))

<a name="1.0.0"></a>
# 1.0.0 (2017-08-24)

### Features

* add an option to force cardboard button ([#20](https://github.com/videojs/videojs-vr/issues/20)) ([1dee5f7](https://github.com/videojs/videojs-vr/commit/1dee5f7))
* expose more of vrs methods ([#10](https://github.com/videojs/videojs-vr/issues/10)) ([3cc1092](https://github.com/videojs/videojs-vr/commit/3cc1092))

### Bug Fixes

* add a cardboard button for native webvr support ([#22](https://github.com/videojs/videojs-vr/issues/22)) ([e946219](https://github.com/videojs/videojs-vr/commit/e946219))
* auto projection should be set to the correct value and not auto ([#4](https://github.com/videojs/videojs-vr/issues/4)) ([377e8a6](https://github.com/videojs/videojs-vr/commit/377e8a6))
* cleanup window listeners ([#15](https://github.com/videojs/videojs-vr/issues/15)) ([d3e45ad](https://github.com/videojs/videojs-vr/commit/d3e45ad))
* correctly check for cardboard button on control bar so we don't add two ([#26](https://github.com/videojs/videojs-vr/issues/26)) ([9184472](https://github.com/videojs/videojs-vr/commit/9184472))
* encode svg in css, use exact button replacement size ([#3](https://github.com/videojs/videojs-vr/issues/3)) ([9a37374](https://github.com/videojs/videojs-vr/commit/9a37374))
* make cardboard button pseudo fullscreen on iOS ([#12](https://github.com/videojs/videojs-vr/issues/12)) ([17a41c0](https://github.com/videojs/videojs-vr/commit/17a41c0))
* pin webvr-polyfill to 0.9.23 ([#21](https://github.com/videojs/videojs-vr/issues/21)) ([a644d1e](https://github.com/videojs/videojs-vr/commit/a644d1e))
* pixelation issues on some devices ([#17](https://github.com/videojs/videojs-vr/issues/17)) ([6f09814](https://github.com/videojs/videojs-vr/commit/6f09814))
* prevent initialization from happening twice ([#9](https://github.com/videojs/videojs-vr/issues/9)) ([33deadc](https://github.com/videojs/videojs-vr/commit/33deadc))
* separate and reset CardboardButton and BigVrPlayButton ([#11](https://github.com/videojs/videojs-vr/issues/11)) ([3ae105e](https://github.com/videojs/videojs-vr/commit/3ae105e))
* use player `fullscreenchange` event so fullscreen toggle works on Safari ([#2](https://github.com/videojs/videojs-vr/issues/2)) ([05c0f23](https://github.com/videojs/videojs-vr/commit/05c0f23))

