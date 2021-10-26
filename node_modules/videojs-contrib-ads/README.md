![Contrib Ads: A Tool for Building Video.js Ad Plugins](logo.png)

[![Build Status](https://travis-ci.org/videojs/videojs-contrib-ads.svg?branch=main)](https://travis-ci.org/videojs/videojs-contrib-ads) [![Greenkeeper badge](https://badges.greenkeeper.io/videojs/videojs-contrib-ads.svg)](https://greenkeeper.io/)

`videojs-contrib-ads` provides common functionality needed by video advertisement libraries working with [video.js.](http://www.videojs.com/)
It takes care of a number of concerns for you, reducing the code you have to write for your ad plugin.

`videojs-contrib-ads` is not a stand-alone ad plugin. It is a library that is used by other ad plugins in order to fully support video.js. If you want to build an ad plugin, you've come to the right place. If you want to play ads in video.js without writing code, this is not the right project for you.

Maintenance Status: Stable

## Benefits

* Ad timeouts are implemented by default. If ads take too long to load, content automatically plays.
* Player state is automatically restored after ad playback, even if the ad played back in the content's video element.
* Content is automatically paused and a loading spinner is shown while preroll ads load.
* [Media events](https://developer.mozilla.org/en-US/docs/Web/Guide/Events/Media_events) will fire as though ads don't exist. For more information, read the documentation on [Redispatch](http://videojs.github.io/videojs-contrib-ads/integrator/redispatch.html).
* Useful macros in ad server URLs are provided.
* Preroll checks automatically happen again when the video source changes.

## Documentation

[Documentation Index](http://videojs.github.io/videojs-contrib-ads/)

## Release History

A short list of features, fixes and changes for each release is available in [CHANGELOG.md](https://github.com/videojs/videojs-contrib-ads/blob/master/CHANGELOG.md).

## License

See [LICENSE](LICENSE).
