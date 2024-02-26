# Video.js Icon Font

This project contains all of the tooling necessary to generate a new icon font for Video.js. The icons themselves are from
Google's [Material Design Icons](https://github.com/google/material-design-icons) (from the commonly available version 3 of the set, with version 4 symbols supplemented as custom SVGs) and [Font Awesome](https://fontawesome.com/).

You can see an overview of the icons used in the default Video.js font here: https://videojs.github.io/font/

## Usage

```sh
$ npm install grunt-cli # only if you don't already have grunt installed
$ npm install
$ grunt
```

### Custom icons

You can add custom icons by calling grunt with the `--custom-json` option. It takes a comma delimited list of paths to JSON files of the same format as below and merges it with the default icons file.

Example:
```sh
$ grunt --custom-json=./lib/custom.json,./lib/custom2.json
```

## Making changes to the font

To make changes to the default Video.js font, simply edit the `icons.json` file. You can add or remove icons, either by just selecting new
SVGs from the [Material Design set](https://www.google.com/design/icons/), or pulling in new SVGs altogether.

```json
{
  "font-name": "VideoJS",
  "root-dir": "./node_modules/material-design-icons/",
  "icons": [
    {
      "name": "play",
      "svg": "av/svg/production/ic_play_arrow_48px.svg"
    },
    {
      "name": "pause",
      "svg": "av/svg/production/ic_pause_48px.svg"
    },
    {
      "name": "cool-custom-icon",
      "svg": "neato-icon.svg",
      "root-dir": "./custom-icons/neato-icon.svg"
    }
  ]
}
```

Once you're done, simply run `grunt` again to regenerate the fonts and scss partial. To edit the `_icons.scss` partial,
update `templates/scss.hbs`.

## Creating your own font

If you are developing a Video.js plugin that uses custom icons, you can also create a new font instead of modifying the
default font. Simply specify a new `font-name` and define the icons you want to include:

```json
{
  "font-name": "MyPluginFont",
  "root-dir": "./node_modules/material-design-icons/",
  "icons": [
    {
      "name": "av-perm",
      "svg": "action/svg/production/ic_perm_camera_mic_48px.svg"
    },
    {
      "name": "video-perm",
      "svg": "av/svg/production/ic_videocam_48px.svg"
    },
    {
      "name": "audio-perm",
      "svg": "av/svg/production/ic_mic_48px.svg"
    }
  ]
}
```
Generate the `MyPluginFont` font files using the `--custom-json` option:

```sh
$ grunt --custom-json=MyPluginFont.json
```

### Exclude default icons

By default, the regular Video.js icons are also included in the font. If you want to exclude these icons, when you're creating a Video.js plugin font for example, use the `--exclude-default` option.

Example:
```sh
$ grunt --custom-json=MyPluginFont.json --exclude-default
```

## Icon unicode strings

Videojs-font generates unicode strings for default and custom icons which are used as css pseudo-element content values by the videojs-icons.css file.

### Version 4 default unicode values
| Icon Name  | Unicode |
| ---------- | ------- |
| play | 'f101' |
| play-circle | 'f102' |
| pause | 'f103' |
| volume-mute | 'f104' |
| volume-low | 'f105' |
| volume-mid | 'f106' |
| volume-high | 'f107' |
| fullscreen-enter | 'f108' |
| fullscreen-exit | 'f109' |
| spinner | 'f10a' |
| subtitles | 'f10b' |
| captions | 'f10c' |
| hd | 'f10d' |
| chapters | 'f10e' |
| downloading | 'f10f' |
| file-download | 'f110' |
| file-download-done | 'f111' |
| file-download-off | 'f112' |
| share | 'f113' |
| cog | 'f114' |
| square | 'f115' |
| circle | 'f116' |
| circle-outline | 'f117' |
| circle-inner-circle | 'f118' |
| cancel | 'f119' |
| repeat | 'f11a' |
| replay | 'f11b' |
| replay-5 | 'f11c' |
| replay-10 | 'f11d' |
| replay-30 | 'f11e' |
| forward-5 | 'f11f' |
| forward-10 | 'f120' |
| forward-30 | 'f121' |
| audio | 'f122' |
| next-item | 'f123' |
| previous-item | 'f124' |
| shuffle | 'f125' |
| cast | 'f126' |
| picture-in-picture-enter | 'f127' |
| picture-in-picture-exit | 'f128' |
| facebook | 'f129' |
| linkedin | 'f12a' |
| twitter | 'f12b' |
| tumblr | 'f12c' |
| pinterest | 'f12d' |
| audio-description | 'f12e' |
