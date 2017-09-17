# Changelog

## 0.4.3 (2017-04-27)

* Bug fix: Bugfix in the usage sample code #57 (@dandelionred) 
* Improvement: Remove branch-alias definition #53 (@WyriHaximus)
* Improvement: StreamSelectLoop: Use fresh time so Timers added during stream events are accurate #51 (@andrewminerd)
* Improvement: Avoid deprecation warnings in test suite due to deprecation of getMock() in PHPUnit #68 (@martinschroeder)
* Improvement: Add PHPUnit 4.8 to require-dev #69 (@shaunbramley)
* Improvement: Increase test timeouts for HHVM and unify timeout handling #70 (@clue)
* Improvement: Travis improvements (backported from #74) #75 (@clue)
* Improvement: Test suite now uses socket pairs instead of memory streams #66 (@martinschroeder)
* Improvement: StreamSelectLoop: Test suite uses signal constant names in data provider #67 (@martinschroeder)
* Improvement: ExtEventLoop: No longer suppress all errors #65 (@mamciek)
* Improvement: Readme cleanup #89 (@jsor)
* Improvement: Restructure and improve README #90 (@jsor)
* Bug fix: StreamSelectLoop: Fix erroneous zero-time sleep (backport to 0.4) #94 (@jsor)

## 0.4.2 (2016-03-07)

* Bug fix: No longer error when signals sent to StreamSelectLoop
* Support HHVM and PHP7 (@ondrejmirtes, @cebe)
* Feature: Added support for EventConfig for ExtEventLoop (@steverhoades)
* Bug fix: Fixed an issue loading loop extension libs via autoloader (@czarpino)

## 0.4.1 (2014-04-13)

* Bug fix: null timeout in StreamSelectLoop causing 100% CPU usage (@clue)
* Bug fix: v0.3.4 changes merged for v0.4.1

## 0.3.4 (2014-03-30)

* Changed StreamSelectLoop to use non-blocking behavior on tick() (@astephens25)

## 0.4.0 (2014-02-02)

* Feature: Added `EventLoopInterface::nextTick()`, implemented in all event loops (@jmalloc)
* Feature: Added `EventLoopInterface::futureTick()`, implemented in all event loops (@jmalloc)
* Feature: Added `ExtEventLoop` implementation using pecl/event (@jmalloc)
* BC break: Bump minimum PHP version to PHP 5.4, remove 5.3 specific hacks
* BC break: New method: `EventLoopInterface::nextTick()`
* BC break: New method: `EventLoopInterface::futureTick()`
* Dependency: Autoloading and filesystem structure now PSR-4 instead of PSR-0

## 0.3.3 (2013-07-08)

* Bug fix: No error on removing non-existent streams (@clue)
* Bug fix: Do not silently remove feof listeners in `LibEvLoop`

## 0.3.0 (2013-04-14)

* BC break: New timers API (@nrk)
* BC break: Remove check on return value from stream callbacks (@nrk)

## 0.2.7 (2013-01-05)

* Bug fix: Fix libevent timers with PHP 5.3
* Bug fix: Fix libevent timer cancellation (@nrk)

## 0.2.6 (2012-12-26)

* Bug fix: Plug memory issue in libevent timers (@cameronjacobson)
* Bug fix: Correctly pause LibEvLoop on stop()

## 0.2.3 (2012-11-14)

* Feature: LibEvLoop, integration of `php-libev`

## 0.2.0 (2012-09-10)

* Version bump

## 0.1.1 (2012-07-12)

* Version bump

## 0.1.0 (2012-07-11)

* First tagged release
