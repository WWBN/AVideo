# Common Interface

videojs-contrib-ads does not implement these. This page establishes a convention used by some ad plugins that you may want to consider sending for consistency as they may be useful.

## Events

* `ads-request`: Fired when ad data is requested.
* `ads-load`: Fired when ad data is available following an ad request.
* `ads-pod-started`: Fired when a LINEAR ad pod has started.
* `ads-pod-ended`: Fired when a LINEAR ad pod has completed.
* `ads-allpods-completed`: Fired when all LINEAR ads are completed.
* `ads-ad-started`: Fired when the ad starts playing. Should include the event parameter `indexInBreak`.
* `ads-ad-skipped`: Fired when the ad unit is skipped. 
* `ads-ad-ended`: Fired when the ad completes playing.
* `ads-first-quartile`: Fired when the ad playhead crosses first quartile.
* `ads-midpoint`: Fired when the ad playhead crosses midpoint.
* `ads-third-quartile`: Fired when the ad playhead crosses third quartile.
* `ads-pause`: Fired when the ad is paused.
* `ads-play`: Fired when the ad is resumed.
* `ads-mute`: Fired when the ad volume has been muted.
* `ads-click`: Fired when the ad is clicked.

## Properties

```js
player.ads.provider = {
  "type": `String`,
  "event": `Object`
}

player.ads.ad = {
  "type": `String`,
  "index": `Number`,
  "id": `String`,
  "duration": `Number`,
  "currentTime": `Function`
}

player.ads.pod = {
  "id": `String`,
  "size": `Number`
}
```
