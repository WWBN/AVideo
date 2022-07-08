### jQuery Lazy - Loader Plugins
[![GitHub version](https://badge.fury.io/gh/dkern%2Fjquery.lazy.svg)](http://github.com/dkern/jquery.lazy)
[![NPM version](https://badge.fury.io/js/jquery-lazy.svg)](http://www.npmjs.org/package/jquery-lazy)
[![Bower version](https://badge.fury.io/bo/jquery-lazy.svg)](http://bower.io/search/?q=jquery-lazy)
[![Dependencies Status](https://david-dm.org/dkern/jquery.lazy/status.svg)](https://david-dm.org/dkern/jquery.lazy)
[![devDependencies Status](https://david-dm.org/dkern/jquery.lazy/dev-status.svg)](https://david-dm.org/dkern/jquery.lazy?type=dev)

---

### Table of Contents
* [Document Note](#document-note)
* [About Loader Plugins](#about-loader-plugins)
* [Create an own Loader Plugin](#create-an-own-loader-plugin)
* [AJAX Loader](#ajax-loader)
* [Audio / Video Loader](#audio--video-loader)
* [iFrame Loader](#iframe-loader)
* [NOOP Loader](#noop-loader)
* [Picture Loader](#picture-loader)
* [JS / Script Loader](#js--script-loader)
* [Vimeo Video Loader](#vimeo-video-loader)
* [YouTube Video Loader](#youtube-video-loader)
* [Bugs / Feature request](#bugs--feature-request)
* [License](#license)
* [Donation](#donation)

---

## Document Note
This is not the main readme file of this project.
Please go to the [project root](https://github.com/dkern/jquery.lazy) and take a look in the [README.md](https://github.com/dkern/jquery.lazy/blob/master/README.md) to learn more about the basics of Lazy. 


## About Loader Plugins
The loader plugins for Lazy can be used whenever you want to extend the basic functionality by default or globally for many instances of Lazy.
Just add the plugins you want to use or a combined file, containing all plugins, to your page and all instances can use the plugins from now on.
```HTML
<!-- as single plugin files -->
<script type="text/javascript" src="jquery.lazy.min.js"></script>
<script type="text/javascript" src="plugins/jquery.lazy.ajax.min.js"></script>
<script type="text/javascript" src="plugins/jquery.lazy.av.min.js"></script>
<script type="text/javascript" src="plugins/jquery.lazy.iframe.min.js"></script>
<script type="text/javascript" src="plugins/jquery.lazy.noop.min.js"></script>
<script type="text/javascript" src="plugins/jquery.lazy.picture.min.js"></script>
<script type="text/javascript" src="plugins/jquery.lazy.script.min.js"></script>
<script type="text/javascript" src="plugins/jquery.lazy.vimeo.min.js"></script>
<script type="text/javascript" src="plugins/jquery.lazy.youtube.min.js"></script>

<!-- or combined in one file -->
<script type="text/javascript" src="jquery.lazy.min.js"></script>
<script type="text/javascript" src="jquery.lazy.plugins.min.js"></script>
```


## Create an own Loader Plugin
If you want to, you can easily create own loader plugins.
Just use jQuery or Zepto's public function `Lazy` to create and register them.
Best practice is to wrap everything by an [IIFE](https://en.wikipedia.org/wiki/Immediately-invoked_function_expression).
```JS
(function($) {
    $.Lazy("pluginName", function(element, response) {
        // add your logic here

        // 'this' is the current instance of Lazy
        // so it's possible to access all public functions, like:
        var imageBase = this.config("imageBase");
    });
})(window.jQuery || window.Zepto);
```

This loader can now be called on every element with the attribute `data-loader` (_by default_), like:
```HTML
<div data-loader="pluginName"></div>
```

It's possible to register a plugin with more than one name / alias.
```JS
(function($) {
    $.Lazy(["pluginName", "anotherPluginName"], function(element, response) {
        // the plugin is now available by 'data-loader="pluginLoaderName"'
        // and 'data-loader="anotherLoaderName"'
    });
})(window.jQuery || window.Zepto);
```

The optional second parameter gives you the ability to register a plugin by default to an element type.
When you do this, there is no need to set the `data-loader` attribute on each element you want to use this loader on.

But keep in mind, if you register an plugin on often used elements, like `<div>`, Lazy will try to handle each of them!
If you want to do so anyway, use a most specific selector for jQuery or Zepto.
```JS
(function($) {
    $.Lazy("av", ["audio", "video"], function(element, response) {
        // this plugin will automatically handle '<audio>' and '<video>' elements,
        // even when no 'data-loader' attribute was set on the elements
    });
})(window.jQuery || window.Zepto);
```

For more examples, take a look at the [existing plugins](https://github.com/dkern/jquery.lazy/tree/master/plugins).


## AJAX Loader
**Names:** `ajax`, `get`, `post`, `put`  
**Parameters:** `data-src`, `data-method`, `data-type`  
**Default for:** -

The AJAX loader can receive data from a given url and paste the response to the inner html of the element.
This is useful, when you want do load a bigger amount of content.
Use `ajax` as the loader name by default.
But there are even some shorthand names for specific request types `GET`, `POST` and `PUT` too.
```HTML
<!-- simple GET request -->
<div data-loader="ajax" data-src="ajax.html"></div>

<!-- simple post request with configurable response type -->
<div data-loader="ajax" data-src="ajax.html" data-method="post" data-type="html"></div>

<!-- GET request -->
<div data-loader="get" data-src="ajax.html"></div>

<!-- POST request-->
<div data-loader="post" data-src="ajax.html"></div>

<!-- PUT request-->
<div data-loader="put" data-src="ajax.html"></div>
```

On `POST` and `PUT` requests, the callback `ajaxCreateData` will be executed before every AJAX call.
If used, the callback function should return the value for the `data` parameter of jQuery's AJAX function.
```HTML
<div data-loader="post" data-src="ajax.html" data-value="post-data"></div>
```

```JS
$('div').Lazy({
   ajaxCreateData: function(element) {
       return {name: element.data('value')};
   } 
});
```


## Audio / Video Loader
**Names:** `av`, `audio`, `video`  
**Parameters:** `data-src`, `data-poster`  
**Default for:** `<audio>`, `<video>`

Loads `<audio>` and `<video>` elements and attach the sources and tracks in the right order.
There are two ways you can prepare your audio and/or video tags.
First way is to add all sources by `data-src` attribute, separated by comma and type by pipe on the element.
```HTML
<audio data-src="file.ogg|audio/ogg,file.mp3|audio/mp3,file.wav|audio/wav"></audio>
<video data-src="file.ogv|video/ogv,file.mp4|video/mp4,file.webm|video/webm" data-poster="poster.jpg"></video>
```

The other way is to add the sources and tracks like default, as child elements.
```HTML
<audio>
  <data-src src="file.ogg" type="audio/ogg"></data-src>
  <data-src src="file.mp3" type="audio/mp3"></data-src>
  <data-src src="file.wav" type="audio/wav"></data-src>
</audio>

<video data-poster="poster.jpg">
  <data-src src="file.ogv" type="video/ogv"></data-src>
  <data-src src="file.mp4" type="video/mp4"></data-src>
  <data-src src="file.webm" type="video/webm"></data-src>
  <data-track kind="captions" src="captions.vtt" srclang="en"></data-track>
  <data-track kind="descriptions" src="descriptions.vtt" srclang="en"></data-track>
  <data-track kind="subtitles" src="subtitles.vtt" srclang="de"></data-track>
</video>
```


## iFrame Loader
**Names:** `frame`, `iframe`  
**Parameters:** `data-src`, `data-error-detect`  
**Default for:** `<iframe>`

Loads `<iframe>` contents.
The default will return a successfull load, even if the iframe url is not reachable (_like on 404 or wrong url_), because there is no way to check the loaded content in javascript.
It might be the fastest and safest way to do that.
If you know the requested path is reachable every time or don't care about error checks, you should use this way!
```HTML
<iframe data-src="iframe.html"></iframe>
```

The second way is more professional and support error checks.
It will load the content by AJAX and checks the response.
Afterwards pass the HTML content to iframe inner and set the correct url.
This is a very secure check, but could be a bit more tricky on some use cases.
You should only use this on the same domain origin.

To enable this feature, set the attribute `data-error-detect` to `true` or `1` on the iframe element.
```HTML
<iframe data-loader="iframe" data-src="iframe.html" data-error-detect="true"></iframe>
```


## NOOP Loader
**Names:** `noop`, `noop-success`, `noop-error`  
**Parameters:** -  
**Default for:** -

The NOOP (_or no-operations_) loader will, like the name said, do nothing.
There will even be no callbacks triggered, like `beforeLoad` or `onError`, when using a NOOP` loader.
It could be useful for developers or to simple, secure and fast disable some other loaders.
It can be used with all elements.
```HTML
<div data-loader="noop"></div>
```

There are two other NOOP loaders, helping to debug your code.
The `noop-success` and `noop-error` loaders will return the current state to Lazy and trigger the right callbacks.
```HTML
<!-- triggers the 'afterLoad' and 'onFinishedAll' callback -->
<div data-loader="noop-success"></div>

<!-- triggers the 'onError' and 'onFinishedAll' callback -->
<div data-loader="noop-error"></div>
```


## Picture Loader
**Names:** `pic`, `picture`  
**Parameters:** `data-src`, `data-srcset`, `data-media`, `data-sizes`  
**Default for:** `<picture>`

Loads `<picture>` elements and attach the sources.
There are two ways you can prepare your picture tags.
First way is to create all child elements from a single line:
```HTML
<picture data-src="default.jpg" data-srcset="1x.jpg 1x, 2x.jpg 2x, 3x.jpg 3x" data-media="(min-width: 600px)" data-type="image/jpeg" />
```

The other way is to add the sources like default, as child elements.
```HTML
<picture>
  <data-src srcset="1x.jpg 1x, 2x.jpg 2x, 3x.jpg 3x" media="(min-width: 600px)" type="image/jpeg"></data-src>
  <data-img src="default.jpg"></data-img>
</picture>

<picture data-src="default.jpg">
  <data-src srcset="1x.jpg 1x, 2x.jpg 2x, 3x.jpg 3x" media="(min-width: 600px)" type="image/jpeg"></data-src>
</picture>
```


## JS / Script Loader
**Names:** `js`, `javascript`, `script`  
**Parameters:** `data-src`  
**Default for:** `<script>`

Loads javascript files on `<script>` element.
Change the element like the example below, and the files will be loaded automatically after page load.
```HTML
<script data-src="script.js" type="text/javascript"></script>
```

**Note:**
The viewport detection is not correct in some browsers.
So it could happen, that all script files get loaded right after page load, and not when the user scrolls to them.


## Vimeo Video Loader
**Names:** `vimeo`  
**Parameters:** `data-src`  
**Default for:** -

Loads vimeo videos in an `<iframe>`.
This is the suggested way by vimeo itself.
You can prepare the `<iframe>` element as you would do without Lazy.
Only add the vimeo video id to the attribute `data-src` and add the loader name.
That's all.
```HTML
<iframe data-loader="vimeo" data-src="176894130" width="640" height="360" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
```

**Please keep in mind:**
Because this is an iframe and there is no feedback javascript could check on, this loader can only return success to Lazy.
There is no way to check if the video was loaded correctly or your provided video id is existing.


## YouTube Video Loader
**Names:** `yt`, `youtube`  
**Parameters:** `data-src`, `data-nocookie`  
**Default for:** -

Loads youtube videos in an `<iframe>`.
This is the suggested way by youtube itself.
You can prepare the `<iframe>` element as you would do without Lazy.
Only add the youtube video id to the attribute `data-src` and add the loader name.
That's all.

```HTML
<iframe data-loader="youtube" data-src="1AYGnw6MwFM" width="560" height="315" frameborder="0"></iframe>
```

If you want to, you can control the cookie behavior of the embedded video with `data-nocookie="1"`.
This would change the url to `youtube-nocookie.com` instead of `youtube.com`.

```HTML
<iframe data-loader="youtube" data-src="1AYGnw6MwFM" data-nocookie="1" width="560" height="315" frameborder="0"></iframe>
```

**Please keep in mind:**
Because this is an iframe and there is no feedback javascript could check on, this loader can only return success to Lazy.
There is no way to check if the video was loaded correctly or your provided video id is existing.


## Bugs / Feature request
Please [report](http://github.com/dkern/jquery.lazy/issues) bugs and feel free to [ask](http://github.com/dkern/jquery.lazy/issues) for new features and loaders directly on GitHub.


## License
Lazy plugins are dual-licensed under [MIT](http://www.opensource.org/licenses/mit-license.php) and [GPL-2.0](http://www.gnu.org/licenses/gpl-2.0.html) license.


## Donation
_You like to support me?_  
_You appreciate my work?_  
_You use it in commercial projects?_  
  
Feel free to make a little [donation](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=FFL6VQJCUZMXC)! :wink:
