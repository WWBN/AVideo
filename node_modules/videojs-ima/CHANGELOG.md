<a name="2.2.0"></a>
# [2.2.0](https://github.com/googleads/videojs-ima/compare/v2.1.0...v2.2.0) (2023-07-18)

<a name="2.1.0"></a>
# [2.1.0](https://github.com/googleads/videojs-ima/compare/v2.0.1...v2.1.0) (2022-08-03)

<a name="2.0.1"></a>
## [2.0.1](https://github.com/googleads/videojs-ima/compare/v2.0.0...v2.0.1) (2022-06-06)

<a name="1.11.0"></a>
# [1.11.0](https://github.com/googleads/videojs-ima/compare/v1.10.1...v1.11.0) (2021-05-07)

<a name="1.10.1"></a>
## [1.10.1](https://github.com/googleads/videojs-ima/compare/v1.10.0...v1.10.1) (2021-03-31)

<a name="1.10.0"></a>
# [1.10.0](https://github.com/googleads/videojs-ima/compare/v1.9.1...v1.10.0) (2021-03-23)

<a name="1.9.1"></a>
## [1.9.1](https://github.com/googleads/videojs-ima/compare/v1.9.0...v1.9.1) (2021-01-13)

<a name="1.9.0"></a>
# [1.9.0](https://github.com/googleads/videojs-ima/compare/v1.8.3...v1.9.0) (2020-11-18)

<a name="1.8.3"></a>
## [1.8.3](https://github.com/googleads/videojs-ima/compare/v1.8.2...v1.8.3) (2020-10-14)

<a name="1.8.2"></a>
## [1.8.2](https://github.com/googleads/videojs-ima/compare/v1.8.1...v1.8.2) (2020-10-14)

<a name="1.8.1"></a>
## [1.8.1](https://github.com/googleads/videojs-ima/compare/v1.8.0...v1.8.1) (2020-08-05)

### Bug Fixes

* updates urls in README ([63043a0](https://github.com/googleads/videojs-ima/commit/63043a0))

<a name="1.8.0"></a>
# [1.8.0](https://github.com/googleads/videojs-ima/compare/v1.7.5...v1.8.0) (2020-03-03)

<a name="1.7.5"></a>
## [1.7.5](https://github.com/googleads/videojs-ima/compare/v1.7.4...v1.7.5) (2020-02-24)

<a name="1.7.4"></a>
## [1.7.4](https://github.com/googleads/videojs-ima/compare/v1.7.3...v1.7.4) (2019-12-12)

<a name="1.7.3"></a>
## [1.7.3](https://github.com/googleads/videojs-ima/compare/v1.7.0...v1.7.3) (2019-12-12)

<a name="1.7.0"></a>
# [1.7.0](https://github.com/googleads/videojs-ima/compare/v1.6.3...v1.7.0) (2019-10-04)

### Features

* adds IMA events ([229b771](https://github.com/googleads/videojs-ima/commit/229b771))

### Bug Fixes

* fixes bug with multiple contentEndedListeners being registered ([2208d86](https://github.com/googleads/videojs-ima/commit/2208d86))

<a name="1.6.3"></a>
## [1.6.3](https://github.com/googleads/videojs-ima/compare/v1.6.2...v1.6.3) (2019-09-30)

### Features

* adds a request mode for ad requests ([278556b](https://github.com/googleads/videojs-ima/commit/278556b))

<a name="1.6.2"></a>
## [1.6.2](https://github.com/googleads/videojs-ima/compare/v1.6.1...v1.6.2) (2019-09-11)

### Bug Fixes

* fixed error in videojs.ima.min.js ([3d4e995](https://github.com/googleads/videojs-ima/commit/3d4e995))

<a name="1.6.1"></a>
## [1.6.1](https://github.com/googleads/videojs-ima/compare/v1.6.0...v1.6.1) (2019-09-10)

### Bug Fixes

* changed to parseFloat ([2b854a4](https://github.com/googleads/videojs-ima/commit/2b854a4))
* fixed small errors in Readme update ([18186f9](https://github.com/googleads/videojs-ima/commit/18186f9))
* removed second param from parseFloat ([4b5eef9](https://github.com/googleads/videojs-ima/commit/4b5eef9))

<a name="1.6.0"></a>
# [1.6.0](https://github.com/googleads/videojs-ima/compare/v1.5.2...v1.6.0) (2019-06-26)

### Features

* Allow for an adsRequest object to be passed to the plugin. This allows us to support additional adRequest properties. Fixes [#653](https://github.com/googleads/videojs-ima/issues/653). ([9e0463a](https://github.com/googleads/videojs-ima/commit/9e0463a))

### Bug Fixes

* fix via npm audit, take two ([#771](https://github.com/googleads/videojs-ima/issues/771)) ([e0d59f5](https://github.com/googleads/videojs-ima/commit/e0d59f5))
* fixes security issue with packages as per npm audit fix ([6daa9d5](https://github.com/googleads/videojs-ima/commit/6daa9d5))
* issue with initialization of adDisplayContainer ([d711072](https://github.com/googleads/videojs-ima/commit/d711072))
* update package.json for supporting video.js 7.x ([dbc87e6](https://github.com/googleads/videojs-ima/commit/dbc87e6))
* update packages to latest versions ([#749](https://github.com/googleads/videojs-ima/issues/749)) ([6f94112](https://github.com/googleads/videojs-ima/commit/6f94112))

### Code Refactoring

* Remove playOnLoad from setContentWith(AdTag|AdsResponse|adsRequest). This stopped working with the refactor, and due to how the player and contrib-ads handle autoplay, is no longer supported. Fixes [#524](https://github.com/googleads/videojs-ima/issues/524). ([c36dedf](https://github.com/googleads/videojs-ima/commit/c36dedf))
* Remove playOnLoad from setContentWith(AdTag|AdsResponse|adsRequest). This stopped working with the refactor, and due to how the player and contrib-ads handle autoplay, is no longer supported. Fixes [#524](https://github.com/googleads/videojs-ima/issues/524)." ([722930a](https://github.com/googleads/videojs-ima/commit/722930a))

### Tests

* change test content video URL ([#766](https://github.com/googleads/videojs-ima/issues/766)) ([e64564b](https://github.com/googleads/videojs-ima/commit/e64564b))

<a name="1.5.2"></a>
## [1.5.2](https://github.com/googleads/videojs-ima/compare/v1.5.1...v1.5.2) (2018-07-25)

### Features

* adds ad log, fixes [#698](https://github.com/googleads/videojs-ima/issues/698) ([#704](https://github.com/googleads/videojs-ima/issues/704)) ([3b16758](https://github.com/googleads/videojs-ima/commit/3b16758))
* Allow for an adsRequest object to be passed to the plugin. This… ([#656](https://github.com/googleads/videojs-ima/issues/656)) ([80aba35](https://github.com/googleads/videojs-ima/commit/80aba35)), closes [#653](https://github.com/googleads/videojs-ima/issues/653)

### Bug Fixes

* Added type to content source ([#680](https://github.com/googleads/videojs-ima/issues/680)) ([d8cb13d](https://github.com/googleads/videojs-ima/commit/d8cb13d))

### Tests

* Re-disable flaky browserstack tests. ([#669](https://github.com/googleads/videojs-ima/issues/669)) ([69659b8](https://github.com/googleads/videojs-ima/commit/69659b8))
* Re-enable browserstack tests, 2nd attempt. ([#657](https://github.com/googleads/videojs-ima/issues/657)) ([02b9852](https://github.com/googleads/videojs-ima/commit/02b9852))
* timeouts are now 60000 ms ([#672](https://github.com/googleads/videojs-ima/issues/672)). Re-enable bs tests. ([fc81aae](https://github.com/googleads/videojs-ima/commit/fc81aae))

<a name="1.5.1"></a>
## [1.5.1](https://github.com/googleads/videojs-ima/compare/v1.5.0...v1.5.1) (2018-06-11)

### Bug Fixes

* Quick fix issues in 1.5.0. Re-adds missing css file and adds a null check around adDisplayContainer.initialize(). ([#648](https://github.com/googleads/videojs-ima/issues/648)) ([0c763c3](https://github.com/googleads/videojs-ima/commit/0c763c3))

<a name="1.5.0"></a>
# [1.5.0](https://github.com/googleads/videojs-ima/compare/v1.4.0...v1.5.0) (2018-06-11)

### Features

* Added scss file. Fixes [#636](https://github.com/googleads/videojs-ima/issues/636). ([#637](https://github.com/googleads/videojs-ima/issues/637)) ([5e622a7](https://github.com/googleads/videojs-ima/commit/5e622a7))
* expose vastLoadTimeout ([#644](https://github.com/googleads/videojs-ima/issues/644)) ([8222570](https://github.com/googleads/videojs-ima/commit/8222570))

### Bug Fixes

* Call startLinearAdMode on post-rolls. Fixes an issue where contrib-ads thought we were timing out on all post-rolls.\Fixes [#620](https://github.com/googleads/videojs-ima/issues/620). ([#631](https://github.com/googleads/videojs-ima/issues/631)) ([6088f86](https://github.com/googleads/videojs-ima/commit/6088f86))
* removed incorrect comment ([#624](https://github.com/googleads/videojs-ima/issues/624)) ([30734c6](https://github.com/googleads/videojs-ima/commit/30734c6))
* Use getComputedStyle but fall back to boundingClientRect ([#623](https://github.com/googleads/videojs-ima/issues/623)) ([a017044](https://github.com/googleads/videojs-ima/commit/a017044))
* Wait until PlayerWrapper ready before invoking SdkImpl.initAdObjects ([#638](https://github.com/googleads/videojs-ima/issues/638)) ([fd409d6](https://github.com/googleads/videojs-ima/commit/fd409d6))
* **wrapper:** Resets contentComplete correctly ([#641](https://github.com/googleads/videojs-ima/issues/641)) ([2255a11](https://github.com/googleads/videojs-ima/commit/2255a11)), closes [#639](https://github.com/googleads/videojs-ima/issues/639)

### Code Refactoring

* Deprecated id setting, instead get the id from Vjs player. ([#625](https://github.com/googleads/videojs-ima/issues/625)) ([f08408a](https://github.com/googleads/videojs-ima/commit/f08408a))
* Remove unused CSS classes and IDs in the example stylesheet. Fixes [#565](https://github.com/googleads/videojs-ima/issues/565). ([#632](https://github.com/googleads/videojs-ima/issues/632)) ([b6dbc62](https://github.com/googleads/videojs-ima/commit/b6dbc62))

<a name="1.4.0"></a>
# [1.4.0](https://github.com/googleads/videojs-ima/compare/v1.3.0...v1.4.0) (2018-05-16)

### Features

* Multilingual UI support for the adLabel "N of N" string ([#592](https://github.com/googleads/videojs-ima/issues/592)) ([4cba6d4](https://github.com/googleads/videojs-ima/commit/4cba6d4))

### Bug Fixes

* Updated samples to work on iOS. ([#613](https://github.com/googleads/videojs-ima/issues/613)) ([a21b544](https://github.com/googleads/videojs-ima/commit/a21b544))

### Tests

* Disable browserstack tests. To be re-enabled once we fix their flakiness. ([#615](https://github.com/googleads/videojs-ima/issues/615)) ([7d4b5a6](https://github.com/googleads/videojs-ima/commit/7d4b5a6))
* Enable browserstack network logs. ([#597](https://github.com/googleads/videojs-ima/issues/597)) ([9cfbd08](https://github.com/googleads/videojs-ima/commit/9cfbd08))
* Enable verbose browserstack logs. ([#614](https://github.com/googleads/videojs-ima/issues/614)) ([b87735f](https://github.com/googleads/videojs-ima/commit/b87735f))

<a name="1.3.0"></a>
# [1.3.0](https://github.com/googleads/videojs-ima/compare/v1.2.1...v1.3.0) (2018-03-29)

### Bug Fixes

* Add nopostroll trigger. ([#585](https://github.com/googleads/videojs-ima/issues/585)) ([e790e6d](https://github.com/googleads/videojs-ima/commit/e790e6d))
* Change source for examples to something that supports https. ([#566](https://github.com/googleads/videojs-ima/issues/566)) ([6810fb3](https://github.com/googleads/videojs-ima/commit/6810fb3))
* Fix locale and numRedirects settings ([#584](https://github.com/googleads/videojs-ima/issues/584)) ([e4de93d](https://github.com/googleads/videojs-ima/commit/e4de93d))
* Fix typo'd boundOnMouseMove property. ([#569](https://github.com/googleads/videojs-ima/issues/569)) ([4cc710b](https://github.com/googleads/videojs-ima/commit/4cc710b))
* Resize handler now resizes ([#555](https://github.com/googleads/videojs-ima/issues/555)) ([a10e82f](https://github.com/googleads/videojs-ima/commit/a10e82f)), closes [#554](https://github.com/googleads/videojs-ima/issues/554)
* Resolve dangling endLinearAdMode call in onAdBreakEnd. ([#574](https://github.com/googleads/videojs-ima/issues/574)) ([2158ba0](https://github.com/googleads/videojs-ima/commit/2158ba0))
* Use contentended instead of ended as trigger for post-rolls. ([#559](https://github.com/googleads/videojs-ima/issues/559)) ([5046440](https://github.com/googleads/videojs-ima/commit/5046440)), closes [#539](https://github.com/googleads/videojs-ima/issues/539)

### Code Refactoring

* Update autoplay sample and how we report adsWillAutoplay an… ([#562](https://github.com/googleads/videojs-ima/issues/562)) ([b580e21](https://github.com/googleads/videojs-ima/commit/b580e21)), closes [#341](https://github.com/googleads/videojs-ima/issues/341)

### Tests

* Remove unnecessary sleep in test. ([#580](https://github.com/googleads/videojs-ima/issues/580)) ([a82c421](https://github.com/googleads/videojs-ima/commit/a82c421))

<a name="1.2.1"></a>
## [1.2.1](https://github.com/googleads/videojs-ima/compare/v1.2.0...v1.2.1) (2018-03-06)

### Bug Fixes

* Fix setAdBreakReadyListener. ([#551](https://github.com/googleads/videojs-ima/issues/551)) ([a835fd8](https://github.com/googleads/videojs-ima/commit/a835fd8)), closes [#550](https://github.com/googleads/videojs-ima/issues/550)

### Tests

* Test against both video.js 5 and 6. ([#548](https://github.com/googleads/videojs-ima/issues/548)) ([60dabe5](https://github.com/googleads/videojs-ima/commit/60dabe5))

<a name="1.2.0"></a>
# [1.2.0](https://github.com/googleads/videojs-ima/compare/v1.1.1...v1.2.0) (2018-03-01)

### Features

* Add support for contrib-ads 6 and by extension VJS 6. ([#538](https://github.com/googleads/videojs-ima/issues/538)) ([d8edd05](https://github.com/googleads/videojs-ima/commit/d8edd05))

### Bug Fixes

* Fix undefined isMobile in sdk-impl. Fixes [#541](https://github.com/googleads/videojs-ima/issues/541) ([#542](https://github.com/googleads/videojs-ima/issues/542)) ([e7dd9c8](https://github.com/googleads/videojs-ima/commit/e7dd9c8))

### Documentation

* Move README badges to the top. It's what everyone else does. ([#540](https://github.com/googleads/videojs-ima/issues/540)) ([23d01fb](https://github.com/googleads/videojs-ima/commit/23d01fb))

<a name="1.1.1"></a>
## [1.1.1](https://github.com/googleads/videojs-ima/compare/v1.1.0...v1.1.1) (2018-02-27)

### Bug Fixes

* Fix redundant calculation of remainingTime for ad UI. ([#527](https://github.com/googleads/videojs-ima/issues/527)) ([d8d70a4](https://github.com/googleads/videojs-ima/commit/d8d70a4)), closes [#526](https://github.com/googleads/videojs-ima/issues/526)

### Tests

* removed pull request check ([#522](https://github.com/googleads/videojs-ima/issues/522)) ([e9b5490](https://github.com/googleads/videojs-ima/commit/e9b5490))

<a name="1.1.0"></a>
# [1.1.0](https://github.com/googleads/videojs-ima/compare/v1.0.4...v1.1.0) (2018-02-14)

### Features

* Add support for full slot ads by changing the default non-linear ad slot height from 1/3 player height to 100% player height. ([#501](https://github.com/googleads/videojs-ima/issues/501)) ([9532a7f](https://github.com/googleads/videojs-ima/commit/9532a7f))
* Auto-populate setAdWillPlayMuted if not provided in settings. ([b313873](https://github.com/googleads/videojs-ima/commit/b313873))
* Use font relative units in CSS instead of pixels. ([#503](https://github.com/googleads/videojs-ima/issues/503)) ([aff9e5e](https://github.com/googleads/videojs-ima/commit/aff9e5e)), closes [#492](https://github.com/googleads/videojs-ima/issues/492)

### Bug Fixes

* Actually use adWillPlayMuted variable I created. ([#520](https://github.com/googleads/videojs-ima/issues/520)) ([f2837c4](https://github.com/googleads/videojs-ima/commit/f2837c4))
* Fix preversion script. ([#516](https://github.com/googleads/videojs-ima/issues/516)) ([c370e72](https://github.com/googleads/videojs-ima/commit/c370e72))

### Tests

* Added Travis CI credentials for browserstack. ([#511](https://github.com/googleads/videojs-ima/issues/511)) ([6b6f124](https://github.com/googleads/videojs-ima/commit/6b6f124))
* Fix error with BrowserStack tests. ([#519](https://github.com/googleads/videojs-ima/issues/519)) ([e4722d0](https://github.com/googleads/videojs-ima/commit/e4722d0))
* **webdriver:** Adds browserstack config (local only). ([#510](https://github.com/googleads/videojs-ima/issues/510)) ([d7d7939](https://github.com/googleads/videojs-ima/commit/d7d7939))

<a name="1.0.4"></a>
## [1.0.4](https://github.com/googleads/videojs-ima/compare/v1.0.3...v1.0.4) (2018-01-17)

### Documentation

* Add keywords to package.json. This should list us on the videoj… ([#486](https://github.com/googleads/videojs-ima/issues/486)) ([7af46cf](https://github.com/googleads/videojs-ima/commit/7af46cf))
* Update README with new snippet and codepen link. ([#483](https://github.com/googleads/videojs-ima/issues/483)) ([2d40f74](https://github.com/googleads/videojs-ima/commit/2d40f74))

<a name="1.0.3"></a>
## [1.0.3](https://github.com/googleads/videojs-ima/compare/v1.0.2...v1.0.3) (2018-01-03)

<a name="1.0.2"></a>
## [1.0.2](https://github.com/googleads/videojs-ima/compare/v1.0.1...v1.0.2) (2018-01-03)

### Bug Fixes

* Added babel to build for ES2015, so older browsers are supported ([#478](https://github.com/googleads/videojs-ima/issues/478)) ([9b25179](https://github.com/googleads/videojs-ima/commit/9b25179))
* Fix advanced sample for mobile. ([#469](https://github.com/googleads/videojs-ima/issues/469)) ([c0c4bee](https://github.com/googleads/videojs-ima/commit/c0c4bee))

### Documentation

* Add commit message guidelines to CONTRIBUTING.md. ([#480](https://github.com/googleads/videojs-ima/issues/480)) ([f6a982a](https://github.com/googleads/videojs-ima/commit/f6a982a))

### Tests

* Added basic webdriver tests ([#464](https://github.com/googleads/videojs-ima/issues/464)) ([8786de9](https://github.com/googleads/videojs-ima/commit/8786de9)), closes [#445](https://github.com/googleads/videojs-ima/issues/445)

<a name="1.0.1"></a>
## [1.0.1](https://github.com/googleads/videojs-ima/compare/v1.0.0...v1.0.1) (2017-12-13)

### Bug Fixes

* Add src to package.json ([#461](https://github.com/googleads/videojs-ima/issues/461)) ([8a94908](https://github.com/googleads/videojs-ima/commit/8a94908))
* Fixed player version reporting. ([#459](https://github.com/googleads/videojs-ima/issues/459)) ([c176781](https://github.com/googleads/videojs-ima/commit/c176781))

<a name="1.0.0"></a>
# [1.0.0](https://github.com/googleads/videojs-ima/compare/0.8.0...v1.0.0) (2017-12-12)

### Code Refactoring

* Massive refactor of the plugin. ([a5cd819](https://github.com/googleads/videojs-ima/commit/a5cd819))

<a name="0.8.0"></a>
# [0.8.0](https://github.com/googleads/videojs-ima/compare/0.6.0...0.8.0) (2017-11-16)

<a name="0.5.0"></a>
# [0.5.0](https://github.com/googleads/videojs-ima/compare/0.4.0...0.5.0) (2016-09-20)

