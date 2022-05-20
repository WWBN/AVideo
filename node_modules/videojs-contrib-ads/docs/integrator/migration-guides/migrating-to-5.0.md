# Migrating to videojs-contrib-ads 5.0.0

Version 5 of videojs-contrib-ads includes a rewrite of the Redispatch feature. The goal of
this rewrite was to provide reliable, maintainable, well-documented, and well-tested
functionality.

## Migration

* [The behavior of Redispatch is now documented](https://github.com/videojs/videojs-contrib-ads#redispatch). You should review this documentation and confirm that your ad plugin does not have expectations that conflict with the documented behavior.
* When there is no preroll, you may no longer see extra play events. If your ad plugin relies on play events at the start of a video, you should verify its behavior.
* The definition of [ad mode](https://github.com/videojs/videojs-contrib-ads#ad-mode-definition) is now documented and consistent. As a result, you may find events that were unprefixed* during ad mode in previous versions that are now prefixed. Any code that listens to unprefixed events during ad mode should be checked.

*An unprefixed event is a normal [media event](https://developer.mozilla.org/en-US/docs/Web/Guide/Events/Media_events) while a prefixed event is one that has been redispatched. For example, `play` is unprefixed and `adplay` is prefixed.