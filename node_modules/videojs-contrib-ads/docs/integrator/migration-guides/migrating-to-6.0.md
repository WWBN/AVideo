# Migrating to videojs-contrib-ads 6.0.0

Version 6 of videojs-contrib-ads includes a major refactor and cleanup of the state management logic.

## Migration

* Timeouts have a more intuitive behavior. See the "Timeout behavior changes" section below for more information.
* Ended events are no longer delayed by 1 second.
* Ended events due to an ad ending will no longer be allowed to replace the ended event
that is triggered by linear ad mode ending. Ad plugins must not emit ended events
after the end of linear ad mode.
* There will no longer be a `contentended` event when content ends after the first time content ends.
* `ads.state` has been removed. Methods have been added to replace state checks, such as `ads.isInAdMode()`. See the documentation for a [list of available methods](http://videojs.github.io/videojs-contrib-ads/integrator/api.html). `ads._state` has been
added, but it is not compatible with the old `ads.state` and should not be inspected by ad plugins.
* The event parameter `triggerevent` has been removed. It is unlikely that ad plugins used it, but any usage must be migrated.
* We no longer trigger a `readyforpreroll` event after receiving a `nopreroll` event.
* adTimeoutTimeout has been removed. It was not part of the documented interface, but make note if your ad plugin inspected it.
* There is no longer a snapshot object while checking for postrolls. Now a snapshot is only taken when a postroll ad break actually begins.
* The `contentplayback` event (removed in [4.0.0](https://github.com/videojs/videojs-contrib-ads/blob/cc664517aa0d07398decc0aa5d41974330efc4e4/CHANGELOG.md#400), re-added as deprecated in [4.1.1](https://github.com/videojs/videojs-contrib-ads/blob/cc664517aa0d07398decc0aa5d41974330efc4e4/CHANGELOG.md#411)), has been removed. Use the `playing` event instead.
* The `adplaying` behavior is an implementation detail and has changed in this update. The `adplaying` event is no longer guaranteed to happen once per ad break. It is not intended to be used to detect the beginning of an ad break. The `ads-pod-started` event should be used instead. The `ads-ad-started` event can be used to detect the start of an individual ad in an ad break. There will be multiple `ads-ad-started` events corresponding to each ad in the ad break.

## Deprecation

Deprecated interfaces will be removed in a future major version update.

* `contentupdate` is now deprecated. It has been replaced by `contentchanged`. `contentupdate` was never intended to fire for the initial source, but over time its behavior eroded. To make migration easier for anyone who depends on the current behavior, we're providing a deprecation period and a new event with correct behavior.
* `adscanceled` is now deprecated. Instead, use `nopreroll` and `nopostroll`. `adscanceled` was initially intended to function similarly to calling both `nopreroll` and `nopostroll` but it was never fully implemented.
* `adserror` is now deprecated. Currently this event will skip prerolls when seen before a preroll ad break, skip postrolls if called after contentended and before a postroll ad break, and end linear ad mode if seen during an ad break. It is more declarative for the ad plugin to do these things explicitly with `skipLinearAdMode` and `endLinearAdMode`. In the future, this event will not have any special behavior in contrib-ads. Ad plugins may continue to use it for other purposes.

## Timeout behavior changes

Previous behavior:

* The `timeout` setting was the number of milliseconds that we waited for `adsready` after the `play` event if `adsready` was not before `play`.
* The `prerollTimeout` setting was the number of milliseconds we waited for `startLinearAdMode` after `readyforpreroll`. It was a separate timeout period after `timeout`.
* The `postrollTimeout` setting was the number of milliseconds we waited for `startLinearAdMode` after `contentended`.

Previous Defaults:

* timeout: 5000
* prerollTimeout: 100
* postrollTimeout: 100

New Behavior:

* The `timeout` setting is now the default setting for all timeouts. It can be overridden by `prerollTimeout` and/or `postrollTimeout`.
* `prerollTimeout` overrides `timeout` for the number of milliseconds we wait for a preroll ad (the time between `play` and `startLinearAdMode`).
* `postrollTimeout` overrides `timeout` for the number of milliseconds we wait for a postroll ad (the time between `contentended` and `startLinearAdMode`).

New Defaults:

* timeout: 5000
* prerollTimeout: no default
* postrollTimeout: no default
