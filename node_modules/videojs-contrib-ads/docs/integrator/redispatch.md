# Redispatch

This project includes a feature called `redispatch` which will monitor all [media
events](https://developer.mozilla.org/en-US/docs/Web/Guide/Events/Media_events) and modify them with the goal of making the usage of ads transparent. For example, in ad mode, a `playing` event would be sent as an `adplaying` event. Code that listens to the `playing` event will not see `playing` events that result from an advertisement playing.

In order for redispatch to work correctly, any ad plugin built using contrib-ads must be initialized as soon as possible, before any other plugins that attach event listeners.

Different platforms, browsers, devices, etc. send different media events at different times. Redispatch does not guarantee a specific sequence of events, but instead ensures that certain expectations are met. The next section describes those expectations.

## The Law of the Land: Redispatch Event Behavior

### `play` events

* Play events represent intention to play, such as clicking the play button.
* Play events do not occur during ad breaks.
* Play events can happen during [ad mode when not currently in an ad break](ad-mode.md), but content will not play as a result.

### `playing` events

* Playing events may occur when content plays.
* If there is a preroll, there is no playing event before the preroll.
* If there is a preroll, there is at least one playing event after the preroll.

### `ended` events

* If there is no postroll, there is a single ended event when content ends.
* If there is a postroll, there is no ended event before the postroll.
* If there is a postroll, there is a single ended event after the postroll.

### `loadstart` events

* There is always a loadstart event after content starts loading.
* There is always a loadstart when the source changes.
* There is never a loadstart due to an ad loading.

### Ad events

* Events are given the `ad` prefix in [ad mode](ad-mode.md) unless content is resuming. For example, if the video element emits a `playing` event during an ad break, that event would be redispatched as `adplaying`. These events can be useful when building ad plugins that use the content video element for ad playback, particularly `adplaying` and `adended`. See [getting started](getting-started.md) for an example that does this.

Exceptions:

* `loadstart`, `loadeddata`, and `loadedmetadata` can occur much later than the source set that triggered them. Contrib Ads will not prefix them during ad mode if they originated from a source change before ad mode began.

### Content resuming events

* Events are given the `content` prefix while content is resuming (while `isContentResuming()` is true). These events are not particularly useful to listen to; they mainly exist to prevent extra unprefixed events that would be confusing otherwise.
