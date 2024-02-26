# Circle Progress

> Lightweight (less than 5kB minified and gzipped), responsive, accessible, animated, stylable with CSS circular progress bar available as plain (vanilla) JavaScript and jQuery plugin.

![](https://i.imgur.com/gpxlBmm.png)

See [examples][examples] or go to the [project site][site]


## Getting Started

### Using npm

Navigate to your project directory and install the Circle Progress module
```shell
$ npm install --save js-circle-progress
```

Given you have this element in your html:
```html
<div class="progress"></div>
```

In your script:

```js
import CircleProgress from 'js-circle-progress'

const cp = new CircleProgress('.progress', {
    value: 50,
    max: 100,
})
```

Note: you can currently only use plain JavaScript version as an npm module (not jQuery). If you need the jQuery version, please [file an issue](https://github.com/tigrr/circle-progress/issues/new).

### Manually downloading the script

### As plain JavaScript

Download the minified [production version][vanilla-min]

In your web page:
```html
<div class="progress"></div>

<script src="dist/circle-progress.min.js"></script>

<script>
    new CircleProgress('.progress');
</script>
```

### As jQuery plugin

Download the minified [jQuery production version][jquery-min]

In your web page:
```html
<div class="progress"></div>

<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<script src="dist/jquery.circle-progress.min.js"></script>

<script>
  jQuery(function($) {
    $('.progress').circleProgress();
  });
</script>
```

#### A note about jQuery file
jQuery version of Circle Progress is built on top of plain JavaScript version.
It uses jQuery Widget Factory. Two files are available: one that contains the Widget Factory code, and one that doesn't.
1. You can use the smaller `jquery.circle-progress.bare.min.js`, if you have already included the jQuery Widget Factory or another native jQuery widget in your page.
1. Otherwise you must use `jquery.circle-progress.min.js`, which includes the jQuery Widget Factory code.


## Usage
### Initiate Circle Progress

#### Plain JavaScript
```js
const circleProgress = new CircleProgress(element, options, doc);
```
where

`element` is HTML element or selector to be converted into a progress circle (required),

`options` - object map of options (optional),

`doc` - the document we are acting upon (optional).


#### jQuery
```js
$('.progress').circleProgress(options);
```
where `options` is object map of options (optional).


### Options
You can customize Circle Progress with these options by either passing options object at initiation, or setting them later, e. g.:

#### In plain js

Set options as properties on a CircleProgress instance:
```js
circleProgress.max = 100;
circleProgress.value = 20;
```
or using the chainable `attr` method by passing it option key and value:
```js
circleProgress
	.attr('max', 100)
	.attr('value', 20);
```
or options object
```js
circleProgress.attr({
	max: 100,
	value: 20,
});
```

#### In jQuery
```js
$('.progress').circleProgress('value', 20);
```
or
```js
$('.progress').circleProgress({
	max: 100,
	value: 20,
});
```

#### All available options

| Option     | Type    | Default | Description |
| ------     | ----    | ------- | ----------- |
| value      | Number  | Indeterminate | Current value |
| min        | Number  | 0       | Minimum value |
| max        | Number  | 1       | Maximum value |
| startAngle | Number  | 0       | Starting angle in degrees. Angle of 0 points straight up. Direction depends on `clockwise`. |
| clockwise  | Boolean | true    | Whether to rotate clockwise (true) or anti-clockwise (false) |
| constrain  | Boolean | true    | Whether the value should be constrained between `min` and `max`. If true, values over `max` will be truncated to `max` and values under `min` will be set to `min`. |
| indeterminateText | String | '?' | Text to display as the value when it is indeterminate |
| textFormat | String or Function | 'horizontal' | Text layout for value, min, max. <br> You can pass either one of the possible keywords: <br> `horizontal` - <samp>value/max</samp> <br> `vertical` - value is shown over max <br> `percent` - <samp>value%</samp> <br> `value` - only value is shown <br> `valueOnCircle` - the value is painted on top of the filled region on the circle <br> `none` - no text is shown. <br>Alternatively you can provide your own function, which will be called each time progress is updated with value and max as arguments and is expected to return a string to insert in the center of the progress circle |
| animation  | String or Function | 'easeInOutCubic' | Animation easing function. Can be a string keyword (see the table below for available easings) or `'none'`.<br>Alternatively, you can pass your own function with the signature <br>`function(time, startAngle, angleDiff, duration)`.<br> The function will be called on each animation frame with the current time (milliseconds since animation start), starting angle, difference in angle (i.e. endAngle - startAngle) and animation duration as arguments, and must return the current angle. |
| animationDuration | Number | 600 | Animation duration in milliseconds |


The predefined animation easing functions:

| Easing name    | Easing |
| -----------    | ------ |
| linear         | Linear |
| easeInQuad     | Quadratic easing in |
| easeOutQuad    | Quadratic easing out |
| easeInOutQuad  | Quadratic easing in/out |
| easeInCubic    | Cubic easing in |
| easeOutCubic   | Cubic easing out |
| easeInOutCubic | Cubic easing in/out |
| easeInQuart    | Quartic (power of 4) easing in |
| easeOutQuart   | Quartic easing out |
| easeInOutQuart | Quartic easing in/out |
| easeInQuint    | Quintic (power of 5) easing in |
| easeOutQuint   | Quintic easing out |
| easeInOutQuint | Quintic easing in/out |
| easeInSine     | Sinusoidal easing in |
| easeOutSine    | Sinusoidal easing out |
| easeInOutSine  | Sinusoidal easing in/out |
| easeInExpo     | Exponential easing in |
| easeOutExpo    | Exponential easing out |
| easeInOutExpo  | Exponential easing in/out |
| easeInCirc     | Circular easing in |
| easeOutCirc    | Circular easing out |
| easeInOutCirc  | Circular easing in/out |

To customize widget's appearance, you can style its underlying SVG elements with CSS.
The elements are:

| Class                        | Description |
| ---------------------------  | ----------- |
| `circle-progress`            | The svg image. You can use this selector to scale the widget. E. g.: `.circle-progress {width: 200px; height: auto;}` |
| `circle-progress-circle`     | The entire circle (SVG circle element) |
| `circle-progress-value`      | The arc representing currently filled progress (SVG path element) |
| `circle-progress-text`       | Text controlled by `textFormat` option (SVG text element) |
| `circle-progress-text-value` | Current value text (SVG tspan element). Appears only for textFormat values of `horizontal`, `vertical`, `valueOnCircle` |
| `circle-progress-text-max`   | Maximum value text (SVG tspan element). Appears only for textFormat values of `horizontal`, `vertical`, `valueOnCircle` |

You can use any SVG presentation attributes on these elements. Particularly useful are:
`fill`, `stroke`, `stroke-width`, `stroke-linecap` properties. (See [examples][examples])

The default options are stored in CircleProgress.defaults or jQuery.fn.circleProgress.defaults. The two are references to the same object. You can override them, so that all instances will be created with the overridden options.


## Browser Support
Chrome, Firefox, Safari, Edge and IE 11 are supported.


## License
© 2018 Tigran Sargsyan

Licensed under ([the MIT License][license])


[vanilla-min]: https://github.com/tigrr/circle-progress/raw/master/dist/circle-progress.min.js
[jquery-min]: https://github.com/tigrr/circle-progress/raw/master/dist/jquery.circle-progress.min.js
[site]: https://tigrr.github.io/circle-progress/
[examples]: https://tigrr.github.io/circle-progress/examples.html
[license]: https://github.com/tigrr/circle-progress/blob/master/LICENSE
