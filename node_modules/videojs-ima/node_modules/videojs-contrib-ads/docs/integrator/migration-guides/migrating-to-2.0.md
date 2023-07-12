# Guidance for Ad Plugin Maintainers
One of the best features of video.js is the community of plugins and customizations that has built up around it.
Ad support is an important part of that ecosystem but not all plugin authors write with advertisements in mind (and we probably won't be able to convince them to start).
Rather than throw our hands in the air, we're making some big changes in [videojs-contrib-ads](https://github.com/videojs/videojs-contrib-ads) to make advertisements more compatible with the rest of the video.js universe.
In version 2.0 of the plugin, we redispatch events with different prefixes depending on whether an ad is playing or not.
When an ad is playing, events are prefixed with `ad` and when content is resuming after an ad break, events are prefixed with `content`.
A `pause` event during an ad would become an `adpause` event, for instance.
This means from the perspective of a (non-ad) plugin author, video.js will behave just the same whether ads are playing or not.
And if someoene wants to write a plugin that is ad-aware, those original events are still available for them to hook into.

## Migration
If you've written your own ad plugin on top of videojs-contrib-ads, there's a couple things you should do to prepare for the upgrade:

- Apply the appropriate prefix to your event handlers.
If you were listening for `timeupdate` events during ad playback, you should now be listening for `adtimeupdate`.
Video events that occur during content playback are unaffected.
- Listen for `contentended` to trigger postrolls instead of `ended`.
When the content is playing, the `ended` event gets captured and redispatched as `contentended` so that other plugins don't see multiple `ended` events for the same video.
After the content and postrolls have finished, contrib-ads will fire an `ended` event.
- Advise your users to include and initialize your plugin before they fire up other plugins.
contrib-ads will take care of redispatching events but it can't hide them for plugins that are registered earlier in the listener chain.

The extended support for postrolls added a new `postrollTimeout` option, similar to prerolls.
If you do not wish to play a postroll for a video, you can fire `adtimeout` to proceed to the next video immediately.
