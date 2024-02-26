### jQuery & Zepto Lazy - Delayed Content, Image and Background Loader
[![GitHub version](https://badge.fury.io/gh/dkern%2Fjquery.lazy.svg)](http://github.com/dkern/jquery.lazy)
[![NPM version](https://badge.fury.io/js/jquery-lazy.svg)](http://www.npmjs.org/package/jquery-lazy)
[![Bower version](https://badge.fury.io/bo/jquery-lazy.svg)](http://bower.io/search/?q=jquery-lazy)
[![Dependencies Status](https://david-dm.org/dkern/jquery.lazy/status.svg)](https://david-dm.org/dkern/jquery.lazy)
[![devDependencies Status](https://david-dm.org/dkern/jquery.lazy/dev-status.svg)](https://david-dm.org/dkern/jquery.lazy?type=dev)

---

[![JetBrains & PhpStorm](http://jquery.eisbehr.de/lazy/images/git_logo_jb_ps.png)](https://jetbrains.com/phpstorm)  
_This project is friendly supported by [JetBrains](https://jetbrains.com) & [PhpStorm](https://jetbrains.com/phpstorm)!_

---

### Table of Contents
* [About Lazy](#about-lazy)
* [Compatibility](#compatibility)
* [Documentation / Examples](#documentation--examples)
* [Installation](#installation)
  * [CDN](#cdn)
  * [Self-Hosted](#self-hosted)
  * [Package Managers](#package-managers)
* [Basic Usage](#basic-usage)
* [Callbacks / Events](#callbacks--events)
* [Instances and public Functions](#instances-and-public-functions)
* [Custom Content Loaders](#custom-content-loaders)
* [Loader Plugins](#loader-plugins)
* [Configuration Parameters](#configuration-parameters)
* [Build and Validation](#build-and-validation)
  * [Available gulp Tasks](#available-gulp-tasks)
* [Bugs / Feature request](#bugs--feature-request)
* [License](#license)
* [Donation](#donation)

---

## About Lazy
Lazy is a fast, feature-rich and lightweight delayed content loading plugin for jQuery and Zepto. 
It's designed to speed up page loading times and decrease traffic to your users by only loading the content in view. 
You can use Lazy in all vertical and horizontal scroll ways.
It supports images in `<img />` tags and backgrounds, supplied with css like `background-image`, by default.
On those elements Lazy can set an default image or a placeholder while loading and supports retina displays as well.
But Lazy is even able to load any other content you want by [plugins](#loader-plugins) and [custom loaders](#custom-content-loaders). 


## Compatibility
Lazy will work with a wide range of browsers and support jQuery versions for years backwards and Zepto as alternative. 
You can pick any version since jQuery 1.7.2 or Zepto 1.1.6 or greater.
There is no way to guarantee, that Lazy will work with all browsers, but all I've tested worked great so far.
If you find any problems in specific browsers, [please let me know](http://github.com/dkern/jquery.lazy/issues). 

**Tested in:** IE, Chrome (+ mobile), Firefox (+ mobile), Safari (+ mobile) and Android Browser.


## Documentation / Examples
For [documentation](http://jquery.eisbehr.de/lazy/#parameter), [examples](http://jquery.eisbehr.de/lazy/#examples) and other information take a look on the [project page](http://jquery.eisbehr.de/lazy/).


## Installation
First of all, you will need a copy of [jQuery](http://jquery.com) or [Zepto](http://zeptojs.com) to use Lazy successfully on your project.
If you get this you can install Lazy by different ways.
Some examples below:

#### CDN
Lazy and all plugins are available over [cdnjs](http://cdnjs.com) and [jsDelivr](http://jsdelivr.com) CDN and can directly included to every page.
```HTML
<!-- jsDeliver -->
<script type="text/javascript" src="//cdn.jsdelivr.net/gh/dkern/jquery.lazy@1.7.10/jquery.lazy.min.js"></script>
<script type="text/javascript" src="//cdn.jsdelivr.net/gh/dkern/jquery.lazy@1.7.10/jquery.lazy.plugins.min.js"></script>

<!-- cdnjs -->
<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery.lazy/1.7.10/jquery.lazy.min.js"></script>
<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery.lazy/1.7.10/jquery.lazy.plugins.min.js"></script>
```

#### Self-Hosted
[Download](https://github.com/dkern/jquery.lazy/archive/master.zip) and save one of two available files to include Lazy to your page, either the [development](http://raw.githubusercontent.com/dkern/jquery.lazy/master/jquery.lazy.js) or the [minified](http://raw.githubusercontent.com/dkern/jquery.lazy/master/jquery.lazy.min.js) version.
```HTML
<script type="text/javascript" src="jquery.lazy.min.js"></script>
```

#### Package Managers
Lazy is even available through [NPM](http://npmjs.org) and [Bower](http://bower.io).
Just use one of the following commands below:

[![NPM](https://nodei.co/npm/jquery-lazy.png?compact=true)](https://nodei.co/npm/jquery-lazy/)
```sh
$ npm install jquery-lazy
$ bower install jquery-lazy
```


## Basic Usage
1.) The basic usage of Lazy is pretty easy.
First of all you need to prepare all elements you want to lazy load. 
By default add a `data-src` attribute to images containing the loadable image and/or a `data-loader` attribute to elements witch shall use a [plugin](#loader-plugins) or [custom loaders](#custom-content-loaders).
```HTML
<img class="lazy" data-src="path/to/image_to_load.jpg" src="" />
```

2.) Start using Lazy by calling it after page load.
You don't have to specify your elements exactly, but for better performance, or different options, load your elements over unique classes or any other selector. 
```JS
$(function($) {
    $("img.lazy").Lazy();
});
```
Take a look at the [documentation](http://jquery.eisbehr.de/lazy/) to get an idea what Lazy is capable of.


## Callbacks / Events
Lazy comes with a bunch of [callbacks and events](http://jquery.eisbehr.de/lazy/index.php?c=callback) you can assign to.
Just add them by initialization settings:
* `beforeLoad` - before item is about to be loaded
* `afterLoad` - after the item was loaded successfully
* `onError` - whenever an item could not be loaded
* `onFinishedAll` - after all items in instance was loaded or returned an error


## Instances and public Functions
Lazy supports multiple parallel instances.
Just initialize them with different selectors.
To access an instances public functions you can initialize them in an object oriented manner or grab the instance bind to every element by default:
```JS
// object oriented way
var instance = $("img.lazy").Lazy({chainable: false});

// grab from elements (only works well if you use same selectors)
$("img.lazy").Lazy();
var instance = $("img.lazy").data("plugin_lazy");
```

Every instance has some public available functions to control it's behavior.
There are currently six available:
```JS
instance.config(entryName[, newValue]); // get or set an configuration entry
instance.addItems(items); // add new items to current instance
instance.getItems(); // get all unhandled items left of current instance
instance.update([useThrottle]); // loads all elements in current viewport
instance.force(items); // force loading specific items, ignoring the viewport
instance.loadAll(); // loads all remaining available elements from this instance
instance.destroy(); // unbinds all events and stop execution directly
```


## Custom Content Loaders
With the custom loaders option there is a powerful solution to load every contents the Lazy way.
Lazy will handle everything, you just create a loading method witch got triggered whenever the element hits the visibility threshold.
It is still possible to load images and custom loaders in the same Lazy instance.

To use this just define a loader function inside the Lazy initialisation and pass the loader name to the `data-loader` attribute of the elements witch should be lazy loaded.
```HTML
<div class="lazy" data-loader="customLoaderName"></div>
<img class="lazy" data-src="path/to/image_to_load.jpg" src="" />
<div class="lazy" data-loader="customLoaderName"></div>
<div class="lazy" data-loader="asyncLoader"></div>
```
```JS
$(".lazy").Lazy({
    // callback
    beforeLoad: function(element) {
        console.log("start loading " + element.prop("tagName"));
    },

    // custom loaders
    customLoaderName: function(element) {
        element.html("element handled by custom loader");
        element.load();
    },
    asyncLoader: function(element, response) {
        setTimeout(function() {
            element.html("element handled by async loader");
            response(true);
        }, 1000);
    }
});
```


## Loader Plugins
The loader plugins can extend the functionality of Lazy, like loading other elements and data.
It is basically the same as the [custom content loaders](#custom-content-loaders), with the difference, that plugins can extend all further instances globally at once permanently and let them handle specific elements like `<video>` by default, without `data-loader` attribute set. 
With custom content loaders you have to initialize each instance on setup with the loader.
With plugins you only load the plugin file and you're done for all instances from now on. 

For more information and examples, take a look at the [existing plugins](https://github.com/dkern/jquery.lazy/tree/master/plugins) or the [readme.md](https://github.com/dkern/jquery.lazy/tree/master/plugins/README.md) in there.


## Configuration Parameters
The following configurations is available by default:

Name               | Type       | Default            | Description
------------------ | ---------- | ------------------ | -----------
name               | *string*   | *'lazy'*           | Internal name, used for namespaces and bindings.
chainable          | *boolean*  | *true*             | By default Lazy is chainable and will return all elements. If set to `false` Lazy will return the created plugin instance itself for further use.
autoDestroy        | *boolean*  | *true*             | Will automatically destroy the instance when no further elements are available to handle.
bind               | *string*   | *'load'*           | If set to `load`' Lazy starts working directly after page load. If you want to use Lazy on own events set it to `event`'.
threshold          | *integer*  | *500*              | Amount of pixels below the viewport, in which all images gets loaded before the user sees them.
visibleOnly        | *boolean*  | *false*            | Determine if only visible elements should be load.
appendScroll       | *integer*  | *window*           | An element to listen on for scroll events, useful when images are stored in a container.
scrollDirection    | *string*   | *'both'*           | Determines the handles scroll direction. Possible values are `both`, `vertical` and `horizontal`.
imageBase          | *string*   | *null*             | If defined this will be used as base path for all images loaded by this instance.
defaultImage       | *string*   | *blank image*      | Base64 image string, set as default image source for every image without a predefined source attribute.
placeholder        | *string*   | *null*             | Base64 image string, set a background on every element as loading placeholder.
delay              | *integer*  | *-1*               | If you want to load all elements at once after page load, then you can specify a delay time in milliseconds.
combined           | *boolean*  | *false*            | With this parameter, Lazy will combine the event driven and delayed element loading.
**attributes**     |            |                    |
attribute          | *string*   | *'data-src'*       | Name of the image tag `src` attribute, where the image path is stored.
srcsetAttribute    | *string*   | *'data-srcset'*    | Name of the image tag `srcset` attribute, where the source set is stored.
sizesAttribute     | *string*   | *'data-sizes'*     | Name of the image tag `sizes` attribute, where the size definition for source set is stored.
retinaAttribute    | *string*   | *'data-retina'*    | Name of the image tag attribute, where the path for optional retina image is stored.
loaderAttribute    | *string*   | *'data-loader'*    | Name or the element attribute, where the identifier of the plugin or customer loader is sored.
imageBaseAttribute | *string*   | *'data-imagebase'* | Name ot the image tag element, where the specific image base is stored. This will overwrite the global `imageBase` config.
removeAttribute    | *boolean*  | *true*             | Determine if the attribute should be removed from the element after loading.
handledName        | *string*   | *'handled'*        | Name of the element tag data attribute, to determine if element is already handled.
loadedName         | *string*   | *'loaded'*         | Name of the element tag data attribute, to determine if element is already loaded.
**effect**         |            |                    |
effect             | *string*   | *'show'*           | Function name of the effect you want to use to show the loaded images, like `show` or `fadeIn`.
effectTime         | *integer*  | *0*                | Time in milliseconds the effect should use to view the image.
**throttle**       |            |                    |
enableThrottle     | *boolean*  | *true*             | Throttle down the loading calls on scrolling event.
throttle           | *integer*  | *250*              | Time in milliseconds the throttle will use to limit the loading calls.
**callbacks**      |            |                    |
beforeLoad         | *function* | *undefined*        | Callback function, which will be called before the element gets loaded. Has current element and response function as parameters. `this` is the current Lazy instance.
afterLoad          | *function* | *undefined*        | Callback function, which will be called after the element was loaded. Has current element and response function as parameters. `this` is the current Lazy instance.
onError            | *function* | *undefined*        | Callback function, which will be called if the element could not be loaded. Has current element and response function as parameters. `this` is the current Lazy instance.
onFinishedAll      | *function* | *undefined*        | Callback function, which will be called after all elements was loaded or returned an error. The callback has no parameters. `this` is the current Lazy instance.


## Build and Validation
This project includes an automated build script using `gulp`.
To build your own versions of Lazy you need to [install it](#package-managers) via [npm](https://www.npmjs.com/package/jquery-lazy) first.
Afterwards you can use the following command in your console to automatically generate all productive files.
While building these files everything will be checked with `jshint` for validity too.
```sh
$ gulp build
```

### Available `gulp` Tasks:

Name           | Description
-------------- | -----------
build          | check & build everything
build-main     | check & build main project file
build-plugins  | check & build single plugin files
concat-plugins | build concatenated plugins file
validate       | check all files with `jshint`
watch          | watches all files to check & build everything on change


## Bugs / Feature request
Please [report](http://github.com/dkern/jquery.lazy/issues) bugs and feel free to [ask](http://github.com/dkern/jquery.lazy/issues) for new features directly on GitHub.


## License
Lazy is dual-licensed under [MIT](http://www.opensource.org/licenses/mit-license.php) and [GPL-2.0](http://www.gnu.org/licenses/gpl-2.0.html) license.


## Donation
_You like to support me?_  
_You appreciate my work?_  
_You use it in commercial projects?_  
  
Feel free to make a little [donation](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=FFL6VQJCUZMXC)! :wink:
