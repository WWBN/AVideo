## Plugin Options

videojs-contrib-ads can be configured with custom settings by providing a settings object at initialization:

```js
player.ads({
  timeout: 3000
});
```

The current set of options are described in detail below.

### timeout

Type: `number`
Default Value: 5000

The maximum amount of time to wait in ad mode before an ad break begins. If this time elapses, ad mode ends and content resumes.

Some ad plugins may want to play a preroll ad even after the timeout has expired and content has begun playing. To facilitate this, videojs-contrib-ads will respond to an `adsready` event during content playback with a `readyforpreroll` event. If you want to avoid this behavior, make sure your plugin does not send `adsready` after `adtimeout`.

### prerollTimeout

Type: `number`
No Default Value

Override the `timeout` setting just for preroll ads (the time between `play` and `startLinearAdMode`)

### postrollTimeout

Type: `number`
No Default Value

Override the `timeout` setting just for preroll ads (the time between `readyforpostroll` and `startLinearAdMode`)

### stitchedAds

Type: `boolean`
Default Value: `false`

Set this to true if you are using ads stitched into the content video. This is necessary for ad events to be sent correctly.

### playerMode

Type: `string`
No Default Value

Set this to `outstream` if you are creating a player that has no content video between ad breaks.

### liveCuePoints

Type: `boolean`
Default Value: `true`

If set to `true`, content will play muted behind ads on supported platforms when the content is detected to be a live stream. This is to support ads on live video metadata cuepoints. It also results in more precise resumes after ads in this scenario. If set to `false`, the [snapshot](snapshot.md) feature will be used to restore content to its previous state after an ad break.

Note: In a future major version update, we plan to change the default to `false` because we believe this reflects a more intuitive and common default behavior. The default is `true` for backwards compatibility. If you want to avoid having to migrate in the future, you might consider setting an explicit value for `liveCuePoints` instead of relying on the default.

### contentIsLive

Type: `boolean`
No Default Value

Use this to override detection of if the content video is a live stream. Live detection checks if the duration is `Infinity` but there are cases when this check is insufficient.

### allowVjsAutoplay

Type: `boolean`
Default Value: `videojs.options.normalizeAutoplay || false`

Set this to `true` if you intend to use video.js's custom autoplay settings ("play", "muted", or "any"). It defaults to `true` if the videojs `normalizeAutoplay` option is `true` since `normalizeAutoplay` signals an intent to use `autoplay: "play"` behavior.

### debug

Type: `boolean`
Default Value: `false`

If debug is set to true, the ads plugin will output additional debugging information.
This can be handy for diagnosing issues or unexpected behavior in an ad plugin.
