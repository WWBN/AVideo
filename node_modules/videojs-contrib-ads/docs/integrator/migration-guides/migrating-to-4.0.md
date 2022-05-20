# Migrating to videojs-contrib-ads 4.0.0

Version 4 of videojs-contrib-ads is mainly about improving code quality. Functional
changes are in support of that effort.

## Migration

* A `playing` event is no longer sent before a preroll ad. This is because a `playing`
event should only be sent when content actually plays. If your ad plugin relies on
this event being fired at this time, consider another solution.
* Contrib-ads expects your plugin to send a "playing" event when an ad starts playing,
which will be redispatched as "adplaying". This may happen automatically, for example,
if you play your ad in the content's `video` element.
* The `contentplayback` event has been removed. Use `playing` instead.
* `videojs.ads.global.js` and `videojs.ads.global.min.js` have been removed. Use
`videojs.ads.js` or `videojs.ads.min.js` instead.
* `videojs.ads.css` is now available in the `dist` folder rather than the `src` folder.
Your ad plugin should be updated to include it from there.
