# Getting started developing videojs-contrib-ads

## Building

videojs-contrib-ads is designed to be built with `npm`.

If you don't already have `npm`, you will need to install [Node.js](http://nodejs.org/) (which comes with npm). We recommend using [NVM](https://github.com/creationix/nvm) to manage your installed Node versions. Either way, the supported Node version is listed in the project's [.nvmrc file](https://github.com/videojs/videojs-contrib-ads/blob/master/.nvmrc).

With NPM ready, you can download the ads plugin's build-time dependencies and then build the ads plugin. Open a terminal to the directory where you've cloned this repository, then:

```sh
$ npm install
$ npm run build
```

We will run a suite of unit tests and code formatting checks, then create a `dist/` directory. Inside you'll find the minified ads plugin file `videojs.ads.min.js`, the unminified `videojs.ads.js`, and the CSS `videojs.ads.css`.

## Linting

Checks for errors or style issues.

```sh
npm run lint
```

## Testing

Runs QUnit tests.

### Using command line

```sh
npm run test
```

### In browser

Run `npm start` and a Chrome instance will launch with Karma's debug interface at `localhost:9876`, allowing you to debug tests. Also, a static server will run and allow you to look at examples at `localhost:9999`.

## What's Next

Check out the [architecture overview](overview.md) to learn your way around the code.
