# WOW.js [![Build Status](https://secure.travis-ci.org/graingert/WOW.svg?branch=master)](http://travis-ci.org/graingert/WOW)

Reveal CSS animation as you scroll down a page.
By default, you can use it to trigger [animate.css](https://github.com/daneden/animate.css) animations.
But you can easily change the settings to your favorite animation library.

Advantages:
- 100% MIT Licensed, not GPL keep your code yours.
- Naturally Caffeine free
- Smaller than other JavaScript parallax plugins, like Scrollorama (they do fantastic things, but can be too heavy for simple needs)
- Super simple to install, and works with animate.css, so if you already use it, that will be very fast to setup
- Fast execution and lightweight code: the browser will like it ;-)
- You can change the settings - [see below](#advanced-usage)

### [LIVE DEMO ➫](https://graingert.co.uk/WOW/)

## Live examples
- [MaterialUp](http://www.materialup.com)
- [Fliplingo](https://www.fliplingo.com)
- [Streamline Icons](http://www.streamlineicons.com)
- [Microsoft Stories](http://www.microsoft.com/en-us/news/stories/garage/)


## Documentation

It just take seconds to install and use WOW.js!
[Read the documentation ➫](https://graingert.co.uk/WOW/docs.html)

### Dependencies
- [animate.css](https://github.com/daneden/animate.css)

### Installation

- Bower

```bash
   bower install wow-mit
```

- NPM

```bash
   npm install wow.js
```

### Basic usage

- HTML

```html
  <section class="wow slideInLeft"></section>
  <section class="wow slideInRight"></section>
```

- JavaScript

```javascript
new WOW().init();
```

### Advanced usage

- HTML

```html
  <section class="wow slideInLeft" data-wow-duration="2s" data-wow-delay="5s"></section>
  <section class="wow slideInRight" data-wow-offset="10"  data-wow-iteration="10"></section>
```

- JavaScript

```javascript
var wow = new WOW(
  {
    boxClass:     'wow',      // animated element css class (default is wow)
    animateClass: 'animated', // animation css class (default is animated)
    offset:       0,          // distance to the element when triggering the animation (default is 0)
    mobile:       true,       // trigger animations on mobile devices (default is true)
    live:         true,       // act on asynchronously loaded content (default is true)
    callback:     function(box) {
      // the callback is fired every time an animation is started
      // the argument that is passed in is the DOM node being animated
    },
    scrollContainer: null // optional scroll container selector, otherwise use window
  }
);
wow.init();
```

### Asynchronous content support

In IE 10+, Chrome 18+ and Firefox 14+, animations will be automatically
triggered for any DOM nodes you add after calling `wow.init()`. If you do not
like that, you can disable this by setting `live` to `false`.

If you want to support older browsers (e.g. IE9+), as a fallback, you can call
the `wow.sync()` method after you have added new DOM elements to animate (but
`live` should still be set to `true`). Calling `wow.sync()` has no side
effects.


## Contribute

The library is transpiled using Babel, please update `wow.js` file.

We use grunt to compile and minify the library:

Install needed libraries

```
npm install
```

Get the compilation running in the background

```
grunt watch
```

Enjoy!

## Bug tracker

If you find a bug, please report it [here on Github](https://github.com/graingert/WOW/issues)!

## Developer

Originally Developed by Matthieu Aussaguel, [mynameismatthieu.com](http://mynameismatthieu.com)
Forked to remain under the MIT license by Thomas Grainger, https://graingert.co.uk

+ [Github Profile](//github.com/graingert)

## Contributors

Thanks to everyone who has contributed to the project so far:

- Attila Oláh - [@attilaolah](//twitter.com/attilaolah) - [Github Profile](//github.com/attilaolah)
- [and many others](//github.com/graingert/WOW/graphs/contributors)

Initiated and designed by [Vincent Le Moign](//www.webalys.com/), [@webalys](//twitter.com/webalys)
