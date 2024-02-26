# Flickity background lazyload

Lazyload background images of selected cells.

## Install

Add `bg-lazyload.js` to your scripts.

### Download

[bg-lazyload.js](https://unpkg.com/flickity-bg-lazyload@1/bg-lazyload.js)

### CDN

``` html
<script src="https://unpkg.com/flickity-bg-lazyload@1/bg-lazyload.js"></script>
```

### Package managers

npm: `npm install flickity-bg-lazyload`

Bower: `bower install flickity-bg-lazyload`

## Usage

Set `data-flickity-bg-lazyload` attribute of the cell to the background image's url.

``` html
<div class="carousel">
  <div class="carousel-cell" data-flickity-bg-lazyload="oranges.jpg"></div>
  <div class="carousel-cell" data-flickity-bg-lazyload="submerge.jpg"></div>
  <div class="carousel-cell" data-flickity-bg-lazyload="cat-nose.jpg"></div>
</div>
```

Set `bgLazyLoad` option.

``` js
// lazyloads background image of selected cell
// jQuery
var $carousel = $('.carousel').flickity({
  bgLazyLoad: true
});
```

``` js
// vanilla JS
var flkty = new Flickity( '.carousel', {
  bgLazyLoad: true
});
```

Set `bgLazyLoad` to a number to load images in adjacent cells.

``` js
bgLazyLoad: 2
// load background images in selected cell
// and next 2 cells
// and previous 2 cells
```

### Webpack & Browserify

``` js
var Flickity = require('flickity-bg-lazyload');

var flkty = new Flickity( '.carousel', {
  bgLazyLoad: true
});
```


### RequireJS

``` js
requirejs( [ 'path/to/flickity-bg-lazyload' ], function( Flickity ) {
  var flkty = new Flickity( '.carousel', {
    bgLazyLoad: true
  });
});
```

---

By [Metafizzy 🌈🐻](https://metafizzy.co)
