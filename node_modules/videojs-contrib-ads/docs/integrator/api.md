# API Reference

This page contains reference documentation for the interaction points between videojs-contrib-ads and ad plugins that use it. All methods are called on `player.ads`; for example, `player.ads.isInAdMode()`. All events are triggered or listened to on the `player` object; for example `player.trigger('nopreroll')` or `player.on('readyforpreroll', () => {...})`.

## Informational methods and events

* `isInAdMode()` (METHOD) -- Returns true if the player is in [ad mode](ad-mode.md).
* `isWaitingForAdBreak()` (METHOD) -- This method returns true during ad mode if an ad break hasn't started yet.
* `inAdBreak()` (METHOD) -- This method returns true after `startLinearAdMode` and before `endLinearAdMode`. This is the part of ad mode when an ad plugin may play ads.
* `isContentResuming()` (METHOD) -- This method returns true during ad mode after an ad break has ended but before content has resumed playing.
* `adstart` (EVENT) -- This event is fired directly as a consequence of calling `startLinearAdMode()`.
* `adend` (EVENT) -- This event is fired directly as a consequence of calling `endLinearAdMode()`.
* `adskip` (EVENT) -- This event is fired directly as a consequence of calling `skipLinearAdMode()`.

## How contrib-ads talks to your ad plugin

Your ad plugin will listen to these events to trigger its behaviors. See [Getting Started](getting-started.md) for more information.

* `readyforpreroll` (EVENT) -- Indicates that your ad plugin may start a preroll ad break by calling `startLinearAdMode`.
* `readyforpostroll` (EVENT) -- Indicates that your ad plugin may start a postroll ad break by calling `startLinearAdMode`.
* `adtimeout` (EVENT) -- A timeout managed by videojs-contrib-ads has expired and regular video content has begun to play. Ad plugins have a fixed amount of time to start an ad break when an opportunity arises. For example, if the ad plugin is blocked by network conditions or an error, this event will fire and regular playback will resume rather than the player stalling indefinitely.
* `contentchanged` (EVENT) -- Fires when a new content video has been loaded in the player (specifically, at the same time as the `loadstart` media event for the new source). This means the ad workflow has restarted from the beginning. Your ad plugin will need to trigger `adsready` again, for example. Note that when changing sources, the playback state of the player is retained: if the previous source was playing, the new source will also be playing and the ad workflow will not wait for a new `play` event.

## How your ad plugin talks to contrib-ads

Your ad plugin can invoke these methods and events to play (or skip) ads. See [Getting Started](getting-started.md) for more information.

* `adsready` (EVENT) -- Trigger this event to indicate that the ad plugin is ready to play prerolls. `readyforpreroll` will not be sent until after you trigger `adsready`, but it may not be sent right away (for example, if the user has not clicked play yet). A timeout can occur while waiting for `adsready`. You will need to trigger `adsready` again if you load a new content source.
* `startLinearAdMode()` (METHOD) -- Invoke this method to start an ad break.
  * For a preroll ad, you can invoke `startLinearAdMode` after the `readyforpreroll` event if `isWaitingForAdBreak()` is true.
  * For a midroll ad, you can invoke `startLinearAdMode` during content playback if `isInAdMode()` is false.
  * For a postroll ad, you can invoke `startLinearAdMode` after the `readyforpostroll` event if `isWaitingForAdBreak()` is true.
* `ads-ad-started` (event) -- Trigger this event during an ad break to indicate that an ad has actually started playing. This will hide the loading spinner. It is possible for an ad break to end without playing any ads.
* `endLinearAdMode()` (method) -- Invoke this method to end an ad break. This will cause content to resume. You can check if an ad break is active using `inAdBreak()`.
* `skipLinearAdMode()` (METHOD) -- At a time when `startLinearAdMode()` is expected, calling `skipLinearAdMode()` will immediately resume content playback instead.
* `nopreroll` (EVENT) -- You can trigger this event even before `readyforpreroll` to indicate that no preroll will play. The ad plugin will not check for prerolls and will instead begin content playback after the `play` event (or immediately, if playback was already requested).
* `nopostroll` (EVENT) -- Similar to `nopreroll`, you can trigger this event even before `readyforpostroll` to indicate that no postroll will play.  The ad plugin will not wait for a postroll to play and will instead immediately trigger the `ended` event.
* `contentresumed` (EVENT) - If your ad plugin does not result in a "playing" event when resuming content after an ad, send this event to signal that content has resumed. This was added to support stitched ads and is not normally necessary because content will result in a `playing` event when it resumes.

## Advanced Properties

Once the plugin is initialized, there are a couple properties you can
access modify its behavior.

### contentSrc

In order to detect changes to the content video, videojs-contrib-ads
monitors the src attribute of the player. If you need to make a change
to the src attribute during content playback that should *not* be
interpreted as loading a new video, you can update this property with
the new source you will be loading:

```js
// you might want to switch from a low bitrate version of a video to a
// higher quality one at the user's request without forcing them to
// re-watch all the ad breaks they've already viewed

// first, you'd update contentSrc on the ads plugin to the URL of the
// higher bitrate rendition:
player.ads.contentSrc = 'movie-high.mp4';

// then, modify the src attribute as usual
player.src('movie-high.mp4');
```

### disableNextSnapshotRestore

Advanced option. Prevents videojs-contrib-ads from restoring the previous video source.

If you need to change the video source during an ad break, you can use _disableNextSnapshotRestore_ to prevent videojs-contrib-ads from restoring the snapshot from the previous video source.
```js
if (player.ads.inAdBreak()) {
    player.ads.disableNextSnapshotRestore = true;
    player.src('another-video.mp4');
}
```

## Deprecated

The following are slated for removal from contrib-ads and will have no special behavior once removed. These should no longer be used in integrating ad plugins. Replacements are provided for matching functionality that will continue to be supported.

* `contentupdate` (EVENT) -- In the future, contrib-ads will no longer trigger this event. Listen to the new `contentchanged` event instead; it is is more reliable.
* `adscanceled` (EVENT) -- In the future, this event will no longer result in special behavior in contrib-ads. It was intended to cancel all ads, but it was never fully implemented. Instead, trigger `nopreroll` and `nopostroll`.
* `adserror` (EVENT) -- In the future, this event will no longer result in special behavior in contrib-ads. Today, this event skips prerolls when seen before a preroll ad break. It skips postrolls if seen after `readyforpostroll` and before a postroll ad break. It ends linear ad mode if seen during an ad break. These behaviors should be replaced using `skipLinearAdMode` and `endLinearAdMode` in the ad plugin.
* `adplaying` (EVENT) -- In the future, this event is no longer guaranteed to happen once per ad break. Your ad plugin should trigger a `ads-pod-started` event to indicate the beginning of an ad break. The `ads-ad-started` event can be used to indicate the start of an individual ad in an ad break. There should be multiple `ads-ad-started` events corresponding to each ad in the ad break.
* `isAdPlaying()` (METHOD) -- Does the same thing as `inAdBreak` but has a misleading name. Being in an ad break doesn't strictly mean that an ad is playing.
* `contentended` (EVENT) -- This used to be the event that was used to indicate that content had ended and that it was time to play postrolls. The name was confusing because the content prefix is usually used during content restoration after an ad. Integrations should use `readyforpostroll` instead. In the future, the meaning of `contentended` will be updated to match what is expected by the prefix.
