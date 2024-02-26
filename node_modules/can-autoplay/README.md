# can-autoplay.js

The auto-play feature detection in HTMLMediaElement (`<audio>` or `<video>`).

![FileSize](http://img.badgesize.io/video-dev/can-autoplay/master/build/can-autoplay.min.js#1?compression=gzip)
![Version](https://img.shields.io/npm/v/can-autoplay.svg)

[Demo page](https://video-dev.github.io/can-autoplay/)

Table of contents:

<!-- START doctoc generated TOC please keep comment here to allow auto update -->
<!-- DON'T EDIT THIS SECTION, INSTEAD RE-RUN doctoc TO UPDATE -->


- [Installation](#installation)
- [Files](#files)
- [API](#api)
  - [`audio(options)`](#audiooptions)
  - [`video(options)`](#videooptions)
- [Example](#example)
- [Media](#media)
- [Implementation Details](#implementation-details)

<!-- END doctoc generated TOC please keep comment here to allow auto update -->

## Installation

```
npm install can-autoplay
```

## Files

Build files are available in the `build/` directory. Bundlers will choose get the correct file chosen for them but if you just want to include it on the page, grab the `build/can-autoplay.js` file.

## API

### `audio(options)`

Parameters:

- options.inline `<Boolean>`, check if auto-play is possible for an inline playback, default value is `false`
- options.muted `<Boolean>`, check if auto-play is possible for a muted content
- options.timeout `<Number>`, timeout for a check, default value is `250` ms

Returns:

- `<Promise>`, resolves to a `<Object>`:
  - `result <Boolean>`, `true` - if auto-play is possible
  - `error <Error>`, internal or timeout Error object


```js
canAutoplay.audio().then(({result}) => {
  if (result === true) {
    // Can auto-play
  } else {
    // Can not auto-play
  }
})
```

### `video(options)`

Parameters:

- options.inline `<Boolean>`, check if auto-play is possible for an inline playback, default value is `false`
- options.muted `<Boolean>`, check if auto-play is possible for a muted content
- options.timeout `<Number>`, timeout for a check, default value is `250` ms

Returns:

- `<Promise>`, resoles to a `<Object>`:
  - `result <Boolean>`, `true` - if auto-play is possible
  - `error <Error>`, internal or timeout Error object

```js
canAutoplay.video().then(({result}) => {
  if (result === true) {
    // Can autoplay
  } else {
    // Can not autoplay
  }
})
```

## Example

```js
import canAutoPlay from 'can-autoplay';

canAutoPlay
    .video({timeout: 100, muted: true})
    .then(({result, error}) => {
        if(result === false){
            console.warn('Error did occur: ', error)
        }
    })
```

## Media

- `audio.mp3`. Created by Weston Ruter (@westonruter). Smallest possible (<0.000001 seconds long) audio file.
- `video.mp4`. Source: https://github.com/mathiasbynens/small

## Implementation Details

If it's required to have a legacy browser support you could use latest `v2.x.x` version of the library.
