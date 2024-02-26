# Autoplay

Using the `autoplay` attribute on the video element is not supported. Due to Autoplay Restrictions introduced by browsers, such as [Safari](https://webkit.org/blog/7734/auto-play-policy-changes-for-macos/) and [Chrome](https://developers.google.com/web/updates/2017/09/autoplay-policy-changes), using the `autoplay` attribute on the player will often not behave as expected. The recommended [best practice](https://developers.google.com/web/updates/2017/09/autoplay-policy-changes#best-practices) is to use the `player.play()` method to autoplay.

Contrib Ads blocks play requests so that prerolls can be handled. To get access to the [play promise](https://developer.mozilla.org/en-US/docs/Web/API/HTMLMediaElement/play), you must wait until the `play` event. For example:

```js
player.on('play', () => {
  var playPromise = player.play();

  playPromise.then(() => {
    // play succeeded
  })
  .catch(() => {
    // play failed
  });
});
```
