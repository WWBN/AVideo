# Migrating to videojs-contrib-ads 3.0.0

One of the best features of video.js is the community of plugins and customizations that has built up around it.

Version 3 of the videojs-contrib-ads plugin is primarily about compatibility with video.js 5 (and, therefore, non-compatibility with video.js 4). Refer to the "[5.0 Change Details](https://github.com/videojs/video.js/wiki/5.0-Change-Details)" document for more on that change.

## Migration

For the most part, ad plugin maintainers will only need to follow the video.js 5 migration changes.

However, there are a few minor changes to videojs-contrib-ads, which are unlikely to affect anyone using the plugin:

- The timer used to trigger an `'adtimeout'` event has been moved to a different property. It is no longer `player.ads.timeout`, but `player.ads.adTimeoutTimeout`. This naming change is less vague and more in line with other timers.

- A new timer is exposed for triggering a synthetic `'ended'` event, `player.ads.resumeEndedTimeout`.

- Internally, timers are now tracked universally using `setTimeout` rather than a mix of `setTimeout`, `requestAnimationFrame`, and the non-standard `setImmediate`. This is not only simpler, but it makes testing easier and more reliable/robust.
