# Cue Text Tracks

An optional feature that allows the manipulation of metadata tracks, specifically in the case of working with advertising cue points.

For example, an ad plugin may want to make an ad request when a cuepoint change has been observed. To do this, an ad plugin would need to do something like this:

`player.ads.cueTextTracks.processMetadataTracks(player, processMetadataTrack)`

where processMetadataTrack could be something like this:

```js
function processMetadataTrack(player, track) {
  track.addEventListener('cuechange', function() {
    var cues = this.cues;
    var processCue = function() {
      // Make an ad request
      ...
    };
    var cancelAds = function() {
      // Optional method to dynamically cancel ads
      // This will depend on the ad implementation
      ...
    };

    player.ads.cueTextTracks.processAdTrack(player, cues, processCue, cancelAds);
  });
}
```

For more information on the utility methods that are available, see [cueTextTracks.js](https://github.com/videojs/videojs-contrib-ads/blob/master/src/cueTextTracks.js).

## setMetadataTrackMode

A track is 'enabled' if the track.mode is set to `hidden` or `showing`. Otherwise, a track is `disabled` and is not updated. It is important to note that some tracks may be disabled as a workaround of not being able to remove them, and so should not be re-enabled. Ad plugins should be careful about setting the mode of tracks in these cases and shadow `setMetadataTrackMode` to determine which tracks are safe to change. For example, if all tracks should be hidden:

```js
player.ads.cueTextTracks.setMetadataTrackMode = function(track) {
  // Hide the tracks so they are enabled and get updated
  // but are not shown in the UI
  track.mode = 'hidden';
}
```
