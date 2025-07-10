# chartjs-adapter-moment

[![release](https://img.shields.io/github/release/chartjs/chartjs-adapter-moment.svg?style=flat-square&maxAge=600)](https://github.com/chartjs/chartjs-adapter-moment/releases/latest) [![travis](https://img.shields.io/travis/chartjs/chartjs-adapter-moment.svg?style=flat-square&maxAge=60)](https://travis-ci.org/chartjs/chartjs-adapter-moment) [![awesome](https://awesome.re/badge-flat2.svg)](https://github.com/chartjs/awesome)

## Overview

This adapter allows the use of Moment.js with Chart.js. Moment.js is a very heavy library and thus not recommended for client-side development. However, it was previously the only library supported by Chart.js and so continues to be supported. You may prefer [chartjs-adapter-date-fns](https://github.com/chartjs/chartjs-adapter-date-fns) for a minimal bundle size or [chartjs-adapter-luxon](https://github.com/chartjs/chartjs-adapter-luxon) for larger bundle size with additional functionality included such as i18n and time zone support.

Requires [Chart.js](https://github.com/chartjs/Chart.js/releases) **3.0.0** or later and [Moment.js](https://momentjs.com/) **2.0.0** or later. To use Chart.js v2.x, utilize v0.1.2 of the adapter.

**Note:** once loaded, this adapter overrides the default date-adapter provided in Chart.js (as a side-effect).

## Installation

### npm

```
npm install moment chartjs-adapter-moment --save
```

```javascript
import { Chart } from 'chart.js';
import 'chartjs-adapter-moment';
```

### CDN

By default, `https://cdn.jsdelivr.net/npm/chartjs-adapter-moment` returns the latest (minified) version, however it's [highly recommended](https://www.jsdelivr.com/features) to always specify a version in order to avoid breaking changes. This can be achieved by appending `@{version}` to the URL:

```html
<script src="https://cdn.jsdelivr.net/npm/chart.js@^3"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@^2"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-moment@^1"></script>
```

Read more about jsDelivr versioning on their [website](http://www.jsdelivr.com/).

## Configuration

Read the [Chart.js documention](https://www.chartjs.org/docs/latest) for possible date/time related options. For example, the time scale [`time.*` options](https://www.chartjs.org/docs/latest/axes/cartesian/time.html#configuration-options) can be overridden using the [Moment formats](https://momentjs.com/docs/#/displaying/).

## Development

You first need to install node dependencies (requires [Node.js](https://nodejs.org/)):

```
> npm install
```

The following commands will then be available from the repository root:

```
> gulp build            // build dist files
> gulp build --watch    // build and watch for changes
> gulp lint             // perform code linting
```

## License

`chartjs-adapter-moment` is available under the [MIT license](LICENSE.md).
