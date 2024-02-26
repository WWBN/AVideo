# videojs-playlist-ui

[![NPM](https://nodei.co/npm/videojs-playlist-ui.png?downloads=true&downloadRank=true)](https://nodei.co/npm/videojs-playlist-ui/)

A playlist video picker for video.js and videojs-playlist

Maintenance Status: Stable

<!-- START doctoc generated TOC please keep comment here to allow auto update -->
<!-- DON'T EDIT THIS SECTION, INSTEAD RE-RUN doctoc TO UPDATE -->

- [Getting Started](#getting-started)
- [Root Element](#root-element)
  - [Using Automatic Discovery (default, example)](#using-automatic-discovery-default-example)
  - [Using a Custom Class (example)](#using-a-custom-class-example)
  - [Using a Custom Element (example)](#using-a-custom-element-example)
- [Other Options](#other-options)
    - [`className`](#classname)
    - [playOnSelect](#playonselect)
- [Playlists and Advertisements](#playlists-and-advertisements)

<!-- END doctoc generated TOC please keep comment here to allow auto update -->


## Getting Started
Include the plugin script in your page, and a placeholder list element with the class `vjs-playlist` to house the playlist menu:

```html
<!-- Include the playlist menu styles somewhere in your page -->
<link href="videojs-playlist-ui.css" rel="stylesheet">

<!-- Your player will be created here: -->
<video-js data-setup='{}' controls></video-js>

<!-- The playlist menu will be built automatically in here -->
<div class="vjs-playlist"></div>

<!-- Include video.js, the videojs-playlist plugin and this plugin -->
<script src="video.js"></script>
<script src="videojs-playlist.js"></script>
<script src="videojs-playlist-ui.js"></script>

<script>
  // Initialize the player
  const player = videojs(document.querySelector('video-js'));

  // Initialize the plugin and render the playlist
  player.playlistUi();
</script>
```

There's also a [working example](example.html) of the plugin you can check out if you're having trouble.

## Root Element
Before this plugin will work at all, it needs an element in the DOM to which to attach itself. There are three ways to find or provide this element.

> **NOTE:** In v2.x of this plugin, the root element was expected to be a list element (i.e., `<ol>` or `<ul>`). As of v3.x, the plugin creates a list; so, this root element _must_ be a non-list container element (e.g., `<div>`).

### Using Automatic Discovery (default, [example](example.html))
By default, the plugin will search for the first element in the DOM with the `vjs-playlist` class.

To defend against problems caused by multiple playlist players on a page, the plugin will only use an element with the `vjs-playlist` class if that element has not been used by another player's playlist.

### Using a Custom Class ([example](example-custom-class.html))
A custom `className` option can be passed to override the class the plugin will search for to find the root element. The same defense against multiple playlist players is reused in this case.

```js
player.playlistUi({
  className: 'hello-world'
});
```

### Using a Custom Element ([example](example-custom-element.html))
A custom element can be passed using the `el` option to explicitly define a specific root element.

```js
player.playlistUi({
  el: document.getElementById('hello-world')
});
```

## Other Options

The options passed to the plugin are passed to the internal `PlaylistMenu` [video.js Component][components]; so, you may pass in [any option][components-options] that is accepted by a component.

In addition, the options object may contain the following specialized properties:

#### `className`
Type: `string`
Default: `"vjs-playlist"`

As mentioned [above](#using-a-custom-class), the name of the class to search for to populate the playlist menu.

#### playOnSelect
Type: `boolean`
Default: `false`

The default behavior is that the play state is expected to stay the same between videos. If the player is playing when switching playlist items, continue playing. If paused, stay paused.

When this boolean is set to `true`, clicking on the playlist menu items will always play the video.

## Playlists and Advertisements

The `PlaylistMenu` automatically adapts to ad integrations based on [videojs-contrib-ads][contrib-ads]. When a linear ad is being played, the menu will darken and stop responding to click or touch events. If you'd prefer to allow your viewers to change videos during ad playback, you can override this behavior through CSS. You will also need to make sure that your ad integration is properly cancelled and cleaned up before switching -- consult the documentation for your ad library for details on how to do that.


[components]: https://videojs.com/guides/components/
[components-options]: https://videojs.com/guides/components/#using-options
[contrib-ads]: https://github.com/videojs/videojs-contrib-ads
