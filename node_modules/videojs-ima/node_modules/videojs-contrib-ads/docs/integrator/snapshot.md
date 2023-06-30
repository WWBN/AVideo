# Snapshot

The snapshot feature records the player state when an ad break begins and restores it when the ad break ends. This is useful when the ad plugin uses the content video element for ad playback. It also prevents metadata corresponding to the content from affecting ads; for example, you won't see your content's captions over ads. The following state is saved and restored:

* Content source
* Current time
* Style attribute
* Text tracks
