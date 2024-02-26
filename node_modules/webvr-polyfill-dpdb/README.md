# webvr-polyfill-dpdb

[![Build Status (Travis CI)](https://img.shields.io/travis/WebVRRocks/webvr-polyfill-dpdb.svg?style=flat-square)](https://travis-ci.org/WebVRRocks/webvr-polyfill-dpdb)
[![Package Info (npm)](https://img.shields.io/npm/v/webvr-polyfill-dpdb.svg?style=flat-square)](https://www.npmjs.org/package/webvr-polyfill-dpdb)

This is a fork of the [online cache](https://storage.googleapis.com/cardboard-dpdb/dpdb.json) of the **Device Parameter Database (DPDB)** for the [webvr-polyfill].

## Adding a Device

You'll need to update `dpdb-formatted.json` with your device's information in the following format:

```json
{
  "devices": [
    {
      "type": "android",
      "rules": [
        {
          "mdmh": "asus/*/ASUS_Z00AD/*"
        },
        {
          "ua": "ASUS_Z00AD"
        }
      ],
      "dpi": [
        403,
        404.6
      ],
      "bw": 3,
      "ac": 1000
    }
  ]
}
```

* `type`: Either `"android"` or `"ios"`.
* `rules`: An array of various rules that must be satisfied in order to use the configuration. See the [Rules](#rules) section below.
* `dpi`: The DPI of the device's screen, either as a scalar value or as an array of X and Y DPI. Get this information via the [Device Info App] and look for `Actual DPI X` and `Actual DPI Y` values.
* `bw`: The bezel width in millimeters, with many phones having between 3 or 4mm bezel widths. See the [Calculating Bezel Width](#calculating-bezel-width) section below.
* `ac`: The accuracy of this entry. This is not currently used in [webvr-polyfill], but may in the future, and used to settle discrepencies between device reported information versus DPDB data, although this is more for Android apps that have may potentially have access to both API reporting and DPDB results. Can be one of the following values:
    * `0`: measurements are speculative. Use as fallback only.
    * `500`: measurements are based on the device's self-reported values, which is often inaccurate. Unless you're physically measuring a device, this is probably the value to use.
    * `1000`: measurements are based on measuring a physical device.

### Rules

The `rules` entry is an array of objects, each with one key/value pair, and matches if *any* of the rules match the device.

#### User-Agent

The `ua` rule is matched with the device's User-Agent string. It is not a regular expression, but just a simple substring match. Go to [useragentstring.com] and find something unique that looks like the device's name. Use this if device type is `"android"`. Keep in mind of other models, where a string for the Nexus 5 could match the Nexus 5X, hence a string like `"Nexus 5 "` (note the extra space).

```js
  { "ua": "ASUS_Z00AD" }
```

#### MDMH

The `mdmh` rule matches a device based on manufacturer, device, model, and hardware, as reported by the device's Android API. This rule is only applicable to Android devices running native apps, not the web.

```js
  { "mdmh": "asus/*/ASUS_Z00AD/*" }
```

#### Resolution

The `res` rule matches a device based on its exact pixel resolution. This should normally only be used on iOS devices because there's a 1:1 mapping between exact resolution and phone model.

```js
  { "res": [640, 960] }
```

### Calculating Bezel Width

You can calculate the exact bezel width using this formula, where `deviceWidth` is in millimeters, `screen` is the screen's diagonal length in millimeters, and `ratio` is the screen resolution's `width / height`:

```js
(deviceWidth - Math.sqrt((screen * screen) / (1 + (1 / (ratio * ratio))))) / 2;
```

## Scripts

To generate the `dpdb.json` file from the `dpdb-formatted.json` source file, run this [npm](https://npmjs.org/) script from the command line:

```sh
npm run build
```

Or call this [Node](https://nodejs.org) script directly:

```sh
node scripts/build.js --write
```

## Change Log

The following devices were added (and/or corrections made):

### 2020-12-16
- [iPhone 12](https://github.com/immersive-web/webvr-polyfill-dpdb/pull/62)

### 2019-11-09
- [iPhone XR](https://github.com/immersive-web/webvr-polyfill-dpdb/pull/53)
- [OnePlus 3, OnePlus 3T, OnePlus 6, OnePlus 6T, international variants of OnePlus 2 and OnePlus X](https://github.com/immersive-web/webvr-polyfill-dpdb/pull/54)
- [Asus x008d, Asus x00pd](https://github.com/immersive-web/webvr-polyfill-dpdb/pull/57/files)
- [Samsung Galaxy S9+](https://github.com/immersive-web/webvr-polyfill-dpdb/pull/56)
- [Samsung Galaxy S10, variants](https://github.com/immersive-web/webvr-polyfill-dpdb/pull/55)

### 2019-04-25
- [Samsung J5 Prime](https://github.com/immersive-web/webvr-polyfill-dpdb/pull/52)

### 2019-02-19
- [iPhone XS Max](https://github.com/immersive-web/webvr-polyfill-dpdb/pull/51)

### 2018-12-10
- [Pixel 2](https://github.com/immersive-web/webvr-polyfill-dpdb/pull/46)
- [Pixel 3](https://github.com/immersive-web/webvr-polyfill-dpdb/pull/46)
- [Pixel 2 XL](https://github.com/immersive-web/webvr-polyfill-dpdb/pull/44)
- [Pixel 3 XL](https://github.com/immersive-web/webvr-polyfill-dpdb/pull/43)

### 2018-10-17
- [Samsung S8+ variant](https://github.com/immersive-web/webvr-polyfill-dpdb/issues/41)

### 2018-03-14
- [iPhone X](https://github.com/immersive-web/webvr-polyfill-dpdb/pull/35)

### 2018-02-07
- [OnePlus 5T](https://github.com/WebVRRocks/webvr-polyfill-dpdb/pull/32)

### 2018-01-19
- [OnePlus 5](https://github.com/WebVRRocks/webvr-polyfill-dpdb/pull/31)
- [BENEVE VR5](https://github.com/WebVRRocks/webvr-polyfill-dpdb/pull/30)

### 2017-10-12
- [Fly IQ4412](https://github.com/WebVRRocks/webvr-polyfill-dpdb/issues/28)

### 2017-09-12
- [Motorola G5 Plus](https://github.com/WebVRRocks/webvr-polyfill-dpdb/issues/27)
- [Samsung Note 5 (SM-N920P)](https://github.com/WebVRRocks/webvr-polyfill-dpdb/issues/26)

### 2017-08-27
- [Samsung S8](https://github.com/WebVRRocks/webvr-polyfill-dpdb/issues/25)
- [Lenovo Phab 2 Pro](https://github.com/WebVRRocks/webvr-polyfill-dpdb/issues/24)

### 2017-07-09
- [Samsung S8+](https://github.com/WebVRRocks/webvr-polyfill-dpdb/issues/23)

### 2017-06-01
- [Samsung S4](https://github.com/WebVRRocks/webvr-polyfill-dpdb/commit/8e89ba9bc3d2996dd845a005fa0f92b2f768d098)

### 2017-05-22
- [Google Pixel](https://github.com/googlevr/webvr-polyfill/commit/1da4b02f702bb0e2662ce713a52fb452290f36c1#diff-7c2d4996a1c9e98511cab90ef34c060d)

### 2017-01-19
- Added format key to avoid 'unexpected format version' error (thanks to [AdrienPoupa](https://github.com/AdrienPoupa) for spotting this oversight)

### 2017-01-12
- [Google Pixel XL](https://github.com/aframevr/aframe/issues/2117#issuecomment-263336591)
- [Motorola G4](https://github.com/aframevr/aframe/issues/2117#issuecomment-265275683)
- [Samsung Galaxy S7](https://github.com/googlevr/webvr-polyfill/issues/164#issuecomment-266108204)
- [Samsung Note 5 (UA variant)](https://github.com/googlevr/webvr-polyfill/pull/185)

### 2017-01-06
- [Samsung Galaxy S7 Edge](https://github.com/googlevr/webvr-polyfill/issues/164#issuecomment-266108204)
- [iPhone 6S+](https://github.com/borismus/webvr-boilerplate/issues/146#issuecomment-253711181)
- Removed double entries for several iOS settings

[webvr-polyfill]: https://github.com/googlevr/webvr-polyfill
[useragentstring.com]: http://useragentstring.com/
[Device Info App]: https://play.google.com/store/apps/details?id=com.jphilli85.deviceinfo
