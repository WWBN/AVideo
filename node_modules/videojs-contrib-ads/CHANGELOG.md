<a name="7.3.2"></a>
## [7.3.2](https://github.com/videojs/videojs-contrib-ads/compare/v7.3.1...v7.3.2) (2023-07-19)

### Bug Fixes

* Suppress errors while looking for `__uspapiLocator`([#561](https://github.com/videojs/videojs-contrib-ads/issues/561)) ([953eff7](https://github.com/videojs/videojs-contrib-ads/commit/953eff7))

<a name="7.3.1"></a>
## [7.3.1](https://github.com/videojs/videojs-contrib-ads/compare/v7.3.0...v7.3.1) (2023-05-25)

### Bug Fixes

* updateUsPrivacyString() callback not invoked when USP API not in use in any window ([#553](https://github.com/videojs/videojs-contrib-ads/issues/553)) ([1f49440](https://github.com/videojs/videojs-contrib-ads/commit/1f49440))

<a name="7.3.0"></a>
# [7.3.0](https://github.com/videojs/videojs-contrib-ads/compare/v7.1.0...v7.3.0) (2023-05-15)

### Features

* Add additional static macros ([#549](https://github.com/videojs/videojs-contrib-ads/issues/549)) ([5ae0a47](https://github.com/videojs/videojs-contrib-ads/commit/5ae0a47))
* Add support for regex macros ([#552](https://github.com/videojs/videojs-contrib-ads/issues/552)) ([6bc0a3c](https://github.com/videojs/videojs-contrib-ads/commit/6bc0a3c))
* Add support for US Privacy macro for CCPA compliance ([#551](https://github.com/videojs/videojs-contrib-ads/issues/551)) ([03bc092](https://github.com/videojs/videojs-contrib-ads/commit/03bc092))
* refactor macro logic and add support for disableDefaultMacros and macroNameOverrides parameters ([#544](https://github.com/videojs/videojs-contrib-ads/issues/544)) ([dc64fcb](https://github.com/videojs/videojs-contrib-ads/commit/dc64fcb))

### Bug Fixes

* playback rate should be 1 while ad is playing ([#548](https://github.com/videojs/videojs-contrib-ads/issues/548)) ([9b0dfcf](https://github.com/videojs/videojs-contrib-ads/commit/9b0dfcf))

### Code Refactoring

* minor improvements in macro.js ([#546](https://github.com/videojs/videojs-contrib-ads/issues/546)) ([933e243](https://github.com/videojs/videojs-contrib-ads/commit/933e243))

<a name="7.2.0"></a>
# [7.2.0](https://github.com/videojs/videojs-contrib-ads/compare/v7.1.0...v7.2.0) (2023-04-18)

### Features

* Add additional static macros ([#549](https://github.com/videojs/videojs-contrib-ads/issues/549)) ([5ae0a47](https://github.com/videojs/videojs-contrib-ads/commit/5ae0a47))
* refactor macro logic and add support for disableDefaultMacros and macroNameOverrides parameters ([#544](https://github.com/videojs/videojs-contrib-ads/issues/544)) ([dc64fcb](https://github.com/videojs/videojs-contrib-ads/commit/dc64fcb))

### Bug Fixes

* playback rate should be 1 while ad is playing ([#548](https://github.com/videojs/videojs-contrib-ads/issues/548)) ([9b0dfcf](https://github.com/videojs/videojs-contrib-ads/commit/9b0dfcf))

### Code Refactoring

* minor improvements in macro.js ([#546](https://github.com/videojs/videojs-contrib-ads/issues/546)) ([933e243](https://github.com/videojs/videojs-contrib-ads/commit/933e243))

<a name="7.1.0"></a>
# [7.1.0](https://github.com/videojs/videojs-contrib-ads/compare/v7.0.0...v7.1.0) (2023-02-22)

### Features

* Standardize ads-ad-skipped event ([#542](https://github.com/videojs/videojs-contrib-ads/issues/542)) ([ad64309](https://github.com/videojs/videojs-contrib-ads/commit/ad64309))

<a name="7.0.0"></a>
# [7.0.0](https://github.com/videojs/videojs-contrib-ads/compare/v6.9.0...v7.0.0) (2023-02-03)

### Features

* Macro improvements ([#537](https://github.com/videojs/videojs-contrib-ads/issues/537)) ([dcf3aae](https://github.com/videojs/videojs-contrib-ads/commit/dcf3aae))

### Bug Fixes

* Don't break on nested macros with undefined parent ([#538](https://github.com/videojs/videojs-contrib-ads/issues/538)) ([18879a8](https://github.com/videojs/videojs-contrib-ads/commit/18879a8))

### Chores

* remove deprecated functionality ([#541](https://github.com/videojs/videojs-contrib-ads/issues/541)) ([169d6d2](https://github.com/videojs/videojs-contrib-ads/commit/169d6d2))

<a name="6.9.0"></a>
# [6.9.0](https://github.com/videojs/videojs-contrib-ads/compare/v6.8.0...v6.9.0) (2021-06-29)

### Features

* Add `allowVjsAutoplay` option to add support for custom video.js autoplay settings ([#532](https://github.com/videojs/videojs-contrib-ads/issues/532)) ([245b208](https://github.com/videojs/videojs-contrib-ads/commit/245b208))

### Chores

* **nvmrc:** use erbium ([#530](https://github.com/videojs/videojs-contrib-ads/issues/530)) ([cf691e7](https://github.com/videojs/videojs-contrib-ads/commit/cf691e7))

### Tests

* add test for multiple identical macros in string ([#531](https://github.com/videojs/videojs-contrib-ads/issues/531)) ([9f08d80](https://github.com/videojs/videojs-contrib-ads/commit/9f08d80))

<a name="6.8.0"></a>
# [6.8.0](https://github.com/videojs/videojs-contrib-ads/compare/v6.8.0-0...v6.8.0) (2021-04-16)

<a name="6.7.0"></a>
# [6.7.0](https://github.com/videojs/videojs-contrib-ads/compare/v6.6.5...v6.7.0) (2020-08-10)

### Features

* add pageUrl macro ([#476](https://github.com/videojs/videojs-contrib-ads/issues/476)) ([ba7fdfa](https://github.com/videojs/videojs-contrib-ads/commit/ba7fdfa))
* Add playlistinfo macros ([#491](https://github.com/videojs/videojs-contrib-ads/issues/491)) ([f4b5ff7](https://github.com/videojs/videojs-contrib-ads/commit/f4b5ff7))
* Add width and height macros ([#475](https://github.com/videojs/videojs-contrib-ads/issues/475)) ([48e13eb](https://github.com/videojs/videojs-contrib-ads/commit/48e13eb))

### Chores

* update travis-ci badge ([e2d2c5e](https://github.com/videojs/videojs-contrib-ads/commit/e2d2c5e))

<a name="6.6.5"></a>
## [6.6.5](https://github.com/videojs/videojs-contrib-ads/compare/v6.6.4...v6.6.5) (2019-09-16)

### Bug Fixes

* Fix an error being thrown when using changing sources with stitched ads scenarios ([#500](https://github.com/videojs/videojs-contrib-ads/issues/500)) ([7deadc4](https://github.com/videojs/videojs-contrib-ads/commit/7deadc4))

<a name="6.6.4"></a>
## [6.6.4](https://github.com/videojs/videojs-contrib-ads/compare/v6.6.3...v6.6.4) (2019-05-17)

### Bug Fixes

* when the video element is shared, do not request playback when ads are loaded for stitched ads or if playback has already started ([75ee707](https://github.com/videojs/videojs-contrib-ads/commit/75ee707))

### Chores

* **package:** Update dependencies ([#487](https://github.com/videojs/videojs-contrib-ads/issues/487)) ([734ebf7](https://github.com/videojs/videojs-contrib-ads/commit/734ebf7))

### Code Refactoring

* change log level for play middleware to debug ([#485](https://github.com/videojs/videojs-contrib-ads/issues/485)) ([cc00120](https://github.com/videojs/videojs-contrib-ads/commit/cc00120))

<a name="6.6.3"></a>
## [6.6.3](https://github.com/videojs/videojs-contrib-ads/compare/v6.6.2...v6.6.3) (2019-04-29)

### Bug Fixes

* playToggle state incorrect when autoplay is blocked in Firefox ([#474](https://github.com/videojs/videojs-contrib-ads/issues/474)) ([f95c5e6](https://github.com/videojs/videojs-contrib-ads/commit/f95c5e6))

<a name="6.6.2"></a>
## [6.6.2](https://github.com/videojs/videojs-contrib-ads/compare/v6.6.1...v6.6.2) (2019-03-20)

### Bug Fixes

* Clean up player when a preroll fails and prevent uncaught play promise exceptions. ([#470](https://github.com/videojs/videojs-contrib-ads/issues/470)) ([07946db](https://github.com/videojs/videojs-contrib-ads/commit/07946db))
* Remove duplicate playing listener from redispatch. ([#473](https://github.com/videojs/videojs-contrib-ads/issues/473)) ([944c363](https://github.com/videojs/videojs-contrib-ads/commit/944c363))

### Chores

* fix examples ([#429](https://github.com/videojs/videojs-contrib-ads/issues/429)) ([fc5bf22](https://github.com/videojs/videojs-contrib-ads/commit/fc5bf22))
* lint to code to vjs 7 standards ([#433](https://github.com/videojs/videojs-contrib-ads/issues/433)) ([df10d45](https://github.com/videojs/videojs-contrib-ads/commit/df10d45))
* Update development tooling ([#462](https://github.com/videojs/videojs-contrib-ads/issues/462)) ([d1171ec](https://github.com/videojs/videojs-contrib-ads/commit/d1171ec))

### Code Refactoring

* Add internal registration system for states to work around circular dependency issues. ([af9c527](https://github.com/videojs/videojs-contrib-ads/commit/af9c527))

<a name="6.6.1"></a>
## [6.6.1](https://github.com/videojs/videojs-contrib-ads/compare/v6.4.3...v6.6.1) (2018-08-31)

### Features

* More complete support for stitched ad scenarios. ([#415](https://github.com/videojs/videojs-contrib-ads/issues/415)) ([a533bbb](https://github.com/videojs/videojs-contrib-ads/commit/a533bbb))

### Bug Fixes

* Avoid multiple-registration warning messages by accepting only the first contrib-ads per context. ([#421](https://github.com/videojs/videojs-contrib-ads/issues/421)) ([c46ed1a](https://github.com/videojs/videojs-contrib-ads/commit/c46ed1a))
* middleware log message ([#423](https://github.com/videojs/videojs-contrib-ads/issues/423)) ([852e6c5](https://github.com/videojs/videojs-contrib-ads/commit/852e6c5))
* remove hack ([#424](https://github.com/videojs/videojs-contrib-ads/issues/424)) ([578ee12](https://github.com/videojs/videojs-contrib-ads/commit/578ee12))
* set contentresuming before calling adBreak.end ([#418](https://github.com/videojs/videojs-contrib-ads/issues/418)) ([627e94b](https://github.com/videojs/videojs-contrib-ads/commit/627e94b))

### Chores

* all lint warnings gone ([#428](https://github.com/videojs/videojs-contrib-ads/issues/428)) ([4a4a0f6](https://github.com/videojs/videojs-contrib-ads/commit/4a4a0f6))
* Remove unneeded onDispose handling ([#422](https://github.com/videojs/videojs-contrib-ads/issues/422)) ([b8e8dcc](https://github.com/videojs/videojs-contrib-ads/commit/b8e8dcc))

<a name="6.6.0"></a>
# [6.6.0](https://github.com/videojs/videojs-contrib-ads/compare/v6.4.3...v6.6.0) (2018-08-23)

### Features

* More complete support for stitched ad scenarios. ([#415](https://github.com/videojs/videojs-contrib-ads/issues/415)) ([a533bbb](https://github.com/videojs/videojs-contrib-ads/commit/a533bbb))

### Bug Fixes

* Avoid multiple-registration warning messages by accepting only the first contrib-ads per context. ([#421](https://github.com/videojs/videojs-contrib-ads/issues/421)) ([c46ed1a](https://github.com/videojs/videojs-contrib-ads/commit/c46ed1a))

<a name="6.5.0"></a>
# [6.5.0](https://github.com/videojs/videojs-contrib-ads/compare/v6.4.3...v6.5.0) (2018-08-13)

* Added liveCuePoints option

<a name="6.4.3"></a>
## [6.4.3](https://github.com/videojs/videojs-contrib-ads/compare/v6.4.2...v6.4.3) (2018-08-03)

* Restore all sources instead of single source on snapshot restore
* Send pause event when autoplay blocked on Chrome to be consistent with Safari

<a name="6.4.2"></a>
## [6.4.2](https://github.com/videojs/videojs-contrib-ads/compare/v6.4.1...v6.4.2) (2018-07-31)

### Chores

* Allow vjs7 dependency ([#413](https://github.com/videojs/videojs-contrib-ads/issues/413)) ([e4fe32e](https://github.com/videojs/videojs-contrib-ads/commit/e4fe32e))
* **package:** update conventional-changelog-cli to version 2.0.1 ([#414](https://github.com/videojs/videojs-contrib-ads/issues/414)) ([6d5ff0f](https://github.com/videojs/videojs-contrib-ads/commit/6d5ff0f)), closes [#393](https://github.com/videojs/videojs-contrib-ads/issues/393)
* Cleanup snapshot after ads done
* Handle dispose in certain cases. More to come.

<a name="6.4.1"></a>
## [6.4.1](https://github.com/videojs/videojs-contrib-ads/compare/v6.4.0...v6.4.1) (2018-06-07)

### Bug Fixes

* Safter play promise usage

<a name="6.4.0"></a>
# [6.4.0](https://github.com/videojs/videojs-contrib-ads/compare/v6.3.0...v6.4.0) (2018-06-06)

### Features

* Allow default values for macros ([#383](https://github.com/videojs/videojs-contrib-ads/issues/383)) ([09e7f59](https://github.com/videojs/videojs-contrib-ads/commit/09e7f59))

### Bug Fixes

* hide loading spinner after nopostroll ([#373](https://github.com/videojs/videojs-contrib-ads/issues/373)) ([79a72ff](https://github.com/videojs/videojs-contrib-ads/commit/79a72ff))

### Chores

* **package:** update rollup-plugin-json to version 3.0.0 ([#381](https://github.com/videojs/videojs-contrib-ads/issues/381)) ([c1f23c7](https://github.com/videojs/videojs-contrib-ads/commit/c1f23c7))

<a name="6.3.0"></a>
# [6.3.0](https://github.com/videojs/videojs-contrib-ads/compare/v6.2.1...v6.3.0) (2018-05-07)

### Features

* Add contentIsLive setting ([#374](https://github.com/videojs/videojs-contrib-ads/issues/374)) ([59d90ed](https://github.com/videojs/videojs-contrib-ads/commit/59d90ed))

### Bug Fixes

* Update link with initialization info ([#378](https://github.com/videojs/videojs-contrib-ads/issues/378)) ([3fda394](https://github.com/videojs/videojs-contrib-ads/commit/3fda394))

<a name="6.2.1"></a>
## [6.2.1](https://github.com/videojs/videojs-contrib-ads/compare/v6.2.0...v6.2.1) (2018-04-30)

## Bug Fixes

* Only trigger play event when our play middleware terminates

### Documentation

* New documentation site: http://videojs.github.io/videojs-contrib-ads/

<a name="6.2.0"></a>
# [6.2.0](https://github.com/videojs/videojs-contrib-ads/compare/v6.0.1...v6.2.0) (2018-04-25)

### Features

* Add `readyforpostroll` event. Replaces the current meaning of `contentended`. Use of `contentended` to trigger postrolls is now deprecated but will continue to work until a later update.

### Chores

* **package:** update karma to version 2.0.2 ([#366](https://github.com/videojs/videojs-contrib-ads/issues/366)) ([8a6b878](https://github.com/videojs/videojs-contrib-ads/commit/8a6b878))

<a name="6.1.0"></a>
# [6.1.0](https://github.com/videojs/videojs-contrib-ads/compare/v6.0.1...v6.1.0) (2018-04-19)

### Features

* add playMiddleware to avoid calling play on tech when possible ([#337](https://github.com/videojs/videojs-contrib-ads/issues/337)) ([1482511](https://github.com/videojs/videojs-contrib-ads/commit/1482511))

### Bug Fixes

* Address iOS playsinline flash of BPB + poster ([#360](https://github.com/videojs/videojs-contrib-ads/issues/360)) ([33de864](https://github.com/videojs/videojs-contrib-ads/commit/33de864))
* make the ads VERSION inline properly ([#332](https://github.com/videojs/videojs-contrib-ads/issues/332)) ([0b67022](https://github.com/videojs/videojs-contrib-ads/commit/0b67022))

<a name="6.0.1"></a>
## [6.0.1](https://github.com/videojs/videojs-contrib-ads/compare/v5.0.4-0...v6.0.1) (2018-03-27)

### Bug Fixes

* Fix bug that could cause double ended events ([81699b4](https://github.com/videojs/videojs-contrib-ads/commit/81699b4))
* Fix state logging when minified ([#339](https://github.com/videojs/videojs-contrib-ads/issues/339)) ([ae38894](https://github.com/videojs/videojs-contrib-ads/commit/ae38894))

### Documentation

* add autoplay attribute deprecation note to README ([#356](https://github.com/videojs/videojs-contrib-ads/issues/356)) ([bf82f40](https://github.com/videojs/videojs-contrib-ads/commit/bf82f40))

<a name="6.0.0"></a>
# [6.0.0](https://github.com/videojs/videojs-contrib-ads/compare/v5.0.4-0...v6.0.0) (2018-02-23)

This version features a major refactor for greatly improved stability and maintainability. Please refer to the [Migrating to 6.0](https://github.com/videojs/videojs-contrib-ads/blob/master/migration-guides/migrating-to-6.0.md) guide when updating to this version. The documentation in the [README](https://github.com/videojs/videojs-contrib-ads/blob/master/README.md) has also been revamped and updated.

<a name="5.1.6"></a>
## [5.1.6](https://github.com/videojs/videojs-contrib-ads/compare/v5.0.4-0...v5.1.6) (2018-01-22)

### Bug Fixes

* Fix caption persistence ([#308](https://github.com/videojs/videojs-contrib-ads/pull/308))
* Make sure spinner is animated while waiting for ads ([#309](https://github.com/videojs/videojs-contrib-ads/pull/309))

<a name="5.1.5"></a>
## [5.1.5](https://github.com/videojs/videojs-contrib-ads/compare/v5.0.4-0...v5.1.5) (2017-11-21)

### Bug Fixes

* Remove the placeholder div logic from cancelContentPlay() ([#296](https://github.com/videojs/videojs-contrib-ads/pull/296))

<a name="5.1.4"></a>
## [5.1.4](https://github.com/videojs/videojs-contrib-ads/compare/v5.0.4-0...v5.1.4) (2017-11-17)

### Bug Fixes

* content restarts from the beginning when snapshot restores source after midroll in iOS ([64f1587](https://github.com/videojs/videojs-contrib-ads/commit/64f1587))

### Chores

* Made example init correctly
* Fixed lint warnings

<a name="5.1.3"></a>
## [5.1.3](https://github.com/videojs/videojs-contrib-ads/compare/v5.0.4-0...v5.1.3) (2017-11-13)

### Bug Fixes

* remove cancelContentPlay on new content source hack ([#298](https://github.com/videojs/videojs-contrib-ads/pull/298))

<a name="5.1.2"></a>
## [5.1.2](https://github.com/videojs/videojs-contrib-ads/compare/v5.0.4-0...v5.1.2) (2017-11-03)

### Bug Fixes

* Added player.ads._cancelledPlay = false to reset on new content source ([#294](https://github.com/videojs/videojs-contrib-ads/pull/294))

<a name="5.1.1"></a>
## [5.1.1](https://github.com/videojs/videojs-contrib-ads/compare/v5.0.4-0...v5.1.1) (2017-11-02)

### Bug Fixes

* snapshot.trackChangeHandler is undefined ([#293](https://github.com/videojs/videojs-contrib-ads/issues/293)) ([8a66140](https://github.com/videojs/videojs-contrib-ads/commit/8a66140))

<a name="5.1.0"></a>
# [5.1.0](https://github.com/videojs/videojs-contrib-ads/compare/v3.3.13...v5.1.0) (2017-09-12)

### Bug Fixes

* Fix mis-named dists and potentially breaking change in package.json 'main' field. ([#280](https://github.com/videojs/videojs-contrib-ads/issues/280)) ([7633161](https://github.com/videojs/videojs-contrib-ads/commit/7633161))
* Remove old call to player.load() during snapshot restoration for players which share the video element with the ad plugin. This is causing problems in Chrome/Edge with Video.js 6 due to the asynchronous nature of calling player.src(). ([#257](https://github.com/videojs/videojs-contrib-ads/issues/257)) ([afb3ccf](https://github.com/videojs/videojs-contrib-ads/commit/afb3ccf))
* Fix issue where captions were showing during ads on iOS

### Chores

* Cross-compatibility between Video.js 5 and 6 ([#241](https://github.com/videojs/videojs-contrib-ads/issues/241)) ([eec856a](https://github.com/videojs/videojs-contrib-ads/commit/eec856a))

### Code Refactoring

* Better support for multiple module systems. ([#272](https://github.com/videojs/videojs-contrib-ads/issues/272)) ([0da0c1c](https://github.com/videojs/videojs-contrib-ads/commit/0da0c1c))

## 5.0.3

* [@ldayananda](http://github.com/ldayananda): Bugfixes for ad cancellation by cues
* [@ldayananda](http://github.com/ldayananda): cueTextTracks should always listen to addtrack event

## 5.0.2

* [@incompl](http://github.com/incompl): Fixed dispatching of `loadeddata` and `loadedmetadata` events
* [@incompl](http://github.com/incompl): Adserror ends linear ad mode

## 5.0.1

* [@incompl](http://github.com/incompl): Emit an error if plugin is initialized too late. [More info](https://github.com/videojs/videojs-contrib-ads#important-note-about-initialization)

## 5.0.0

Please refer to the [Migrating to 5.0](https://github.com/videojs/videojs-contrib-ads/blob/master/migration-guides/migrating-to-5.0.md) guide when updating to this version.

* [@incompl](http://github.com/incompl): Added integration tests for Redispatch		
* [@incompl](http://github.com/incompl): Added documentation for Redispatch		
* [@incompl](http://github.com/incompl): A more reliable and maintainable Redispatch implementation

## 4.2.8

* [@nochev](http://github.com/nochev): Clear registered timeouts when player is disposed

## 4.2.7

* [@nochev](http://github.com/nochev): Remove error throwing for live videos
* [@alex-barstow](https://github.com/alex-barstow): Placeholder div's CSS position and top values now match the player's

## 4.2.6

* [@brandonocasey](https://github.com/brandonocasey): Cross-compatibility between Video.js 5 and 6

## 4.2.5

* [@ldayananda](https://github.com/ldayananda): Adding a way to estimate adType
* [@ldayananda](https://github.com/ldayananda): Adding back support for es3
* [@ldayananda](https://github.com/ldayananda): Reverting "No longer take a postroll snapshot when we already know there will not be a postroll" to fix a bug with missing `ended` events.


## 4.2.4

This version introduces a bug with missing `ended` events. It is fixed in the next version.

* [@ldayananda](https://github.com/ldayananda): No longer take a postroll snapshot when we already know there will not be a postroll

## 4.2.3

* [@misteroneill](https://github.com/misteroneill): Video.js 5/6 cross-compatibility

## 4.2.2

* [@incompl](https://github.com/incompl): Re-fix iOS content flash
* [@ldayananda](https://github.com/ldayananda): Added cuepoints example
* [@incompl](https://github.com/incompl): Documented contentresumed event

## 4.2.1

* [@incompl](https://github.com/incompl): Revert progress bar clickthrough CSS
* [@ldayananda](https://github.com/ldayananda): Started using ES6 exports

## 4.2.0

* [@ldayananda](https://github.com/ldayananda): Adding a new module to process metadata tracks for ad cue point manipulation
* [@incompl](http://github.com/incompl): Update videojs-standard dependency

## 4.1.6

* [@marguinbc](http://github.com/marguinbc): Fix placeholder div on ios10 playsinline
* [@incompl](http://github.com/incompl): No longer send an undocumented `adcontentplaying` event, which was only sent to cancel an extra `adplaying` event. Code has been refactored to not need this extra event.

## 4.1.5

* [@incompl](http://github.com/incompl): Hide captions and audio track buttons during ads
* [@incompl](http://github.com/incompl): Prevent ad clickthrough when clicking progress bar during ad
* [@incompl](http://github.com/incompl): Trigger ended event for successive times the content ends after the first time

## 4.1.4

* [@marguinbc](https://github.com/marguinbc): Fix issue where blank div to prevent content flash covers ad on iPad
* [@ldayananda](https://github.com/ldayananda): Fix to snapshot test to avoid relying on track src

## 4.1.3

* [@Ambroos](https://github.com/Ambroos): Add missing import of videojs
* [@vdeshpande](https://github.com/vdeshpande): Fix for content playing behind ad on Android

## 4.1.2

* [@incompl](http://github.com/incompl): Fix bug with snapshot and text tracks

## 4.1.1

* [@incompl](http://github.com/incompl): Temporarily re-added `contentplayback` as a transitionary step. Do not use this event.

## 4.1.0

* [@incompl](http://github.com/incompl): New ad macros feature

## 4.0.0

Please refer to the [Migrating to 4.0](https://github.com/videojs/videojs-contrib-ads/blob/master/migration-guides/migrating-to-4.0.md) guide when updating to this version.

* [@incompl](http://github.com/incompl): `playing` event no longer sent before preroll
* [@incompl](http://github.com/incompl): `contentplayback` event removed
* [@incompl](http://github.com/incompl): Fixed a flash of content introduced in Chrome 53 where ads-loading class was being removed too soon
* [@ldayananda](http://github.com/ldayananda): Added `player.ads.VERSION`
* [@incompl](http://github.com/incompl): Updated to use conventions put forward by [generator-videojs-plugin](https://github.com/videojs/generator-videojs-plugin).
* [@incompl](http://github.com/incompl): Created separate files for feature modules

## 3.3.13

* [@marguinbc](https://github.com/marguinbc): Fix check to reset snapshot on contentupdate

## 3.3.12

* [@vdeshpande](https://github.com/vdeshpande): Fix for metrics on empty ad

## 3.3.11

* [@incompl](https://github.com/incompl): Fix for iOS in which a flash of video content is seen before a preroll
* [@ldayananda](https://github.com/ldayananda): Fix a bug in which the ended event does not trigger after video content source is changed

## 3.3.10

* [@incompl](https://github.com/incompl): Fix a bug in which content would replay after postrolls under certain circumstances

## 3.3.9

* [@incompl](https://github.com/incompl): Fix a bug in which contentupdate is missed in postroll? state

## 3.3.8

* [@incompl](https://github.com/incompl): Fix for issue resuming after ads on Android
* [@incompl](https://github.com/incompl): Fix for issue requesting ads for subsequent videos

## 3.3.7

* [@bcvio](https://github.com/bcvio): Fix a bug where content would replay after a postroll completed.

## 3.3.6

* Due to a build error, this version has no dist folder.

## 3.3.5

* Last version release was done in an abnormal way. No issues have been observed, but this release is guaranteed to be correct.

## 3.3.4

* [@incompl](https://github.com/incompl): Fix bug where content would not pause for preroll ad in cases where the "play" event fires before the "loadstart" event after a source change

## 3.3.3

* [@bcvio](https://github.com/bcvio): Fix a bug where two ad-end events would fire

## 3.3.2

* [@incompl](https://github.com/incompl): Fix bug related to snapshots during live streams on older devices
* [@incompl](https://github.com/incompl): Added `videoElementRecycled` method
* [@incompl](https://github.com/incompl): Added `stitchedAds` setting and method
* [@incompl](https://github.com/incompl): Fix prefixing of events when preload is set to `none`
* [@bcvio](https://github.com/bcvio): Document `disableNextSnapshotRestore` option

## 3.2.0

* [@incompl](https://github.com/incompl): Ad impl can now send 'nopreroll' and 'nopostroll' to inform contrib-ads it should not wait for an ad that isn't coming.
* [@incompl](https://github.com/incompl): In live streams, mute live stream and play it in the background during ads, except on platforms where ads reuse the content video element.
* [@bcvio](https://github.com/bcvio): Add ability to prevent snapshot restoration

## 3.1.3

* [@gkatsev](https://github.com/gkatsev): Updated path to videojs and media URLs in example page
* [@incompl](https://github.com/incompl): startLinearAdMode now only triggers adstart from appropriate states

## 3.1.2

* [@gkatsev](https://github.com/gkatsev): Addressed issues with some browsers (Firefox with MSE) where the `"canplay"` event fires at the wrong time. [#136](https://github.com/videojs/videojs-contrib-ads/pull/136)
* [@misteroneill](https://github.com/misteroneill): Ensure that editor files and other undesirable assets don't appear in npm packages. [#137](https://github.com/videojs/videojs-contrib-ads/pull/137)

## 3.1.1

* [@alex-phillips](https://github.com/alex-phillips): Fixed issues caused by overly-aggressive DOM node caching, which caused issues when ads and content used different techs. [#131](https://github.com/videojs/videojs-contrib-ads/pull/131)
* [@misteroneill](https://github.com/misteroneill): Fixed logic with determining if the source changed when trying to restore a player snapshot after an ad ends. [#133](https://github.com/videojs/videojs-contrib-ads/pull/133)
* [@misteroneill](https://github.com/misteroneill): Removed or simplified code with methods available in video.js 5.x. [#134](https://github.com/videojs/videojs-contrib-ads/pull/134)

## 3.1.0

* Adds a `"contentresumed"` event to support stitched-in ads.

## 3.0.0

* Mostly transparent to plugin users, this release is a VideoJS 5.0-compatible iteration of the plugin.
* Updated testing to be more modern and robust.
* Renamed `player.ads.timeout` to `player.ads.adTimeoutTimeout`.
* Exposed `player.ads.resumeEndedTimeout`.

## 2.0.0

* Prefix video events during ad playback to simplify the world for non-ad plugins

## 1.0.0

* Simplify ad timeout handling and remove the `ad-timeout-playback` state
* Introduce `aderror` event to get back to content when a problem occurs
* Fire `contentplayback` event any time the `content-playback` state is entered
* Expose the event that caused the transition to the current state

## 0.6.0

* Disable and re-enable text tracks automatically around ads
* Snapshot styles to fix damage caused by ad blockers

## 0.5.0

* Make the ad workflow cancelable through the `adscanceled` event

## 0.4.0

* Ad blocker snapshot restoration fixes
* Post-roll fixes
* Allow content source updates without restarting ad workflow

## 0.3.0

* Post-roll support

## 0.2.0

* Upgrade to video.js 4.4.3
* Added support for burned-in or out-of-band linear ad playback
* Debug mode

## 0.1.0

* Initial release.
