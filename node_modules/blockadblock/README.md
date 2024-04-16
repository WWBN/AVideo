BlockAdBlock (v3.2.1)
===========

([FuckAdBlock](https://github.com/sitexw/FuckAdBlock) same project but with a more convenient name)

You can detect nasty ad blockers.
Online example: http://sitexw.fr/blockadblock/


Valid on
---------------------
- Google Chrome
- Mozilla Firefox
- Internet Explorer (8+)
- Safari
- Opera

Install via
---------------------
Manual:
```
Download "blockadblock.js" and add it to your website.
```
Bower:
```
bower install blockadblock
```
Node.js/io.js:
```
npm install blockadblock
```


Code example
---------------------
```javascript
// Function called if AdBlock is not detected
function adBlockNotDetected() {
	alert('AdBlock is not enabled');
}
// Function called if AdBlock is detected
function adBlockDetected() {
	alert('AdBlock is enabled');
}

// Recommended audit because AdBlock lock the file 'blockadblock.js' 
// If the file is not called, the variable does not exist 'blockAdBlock'
// This means that AdBlock is present
if(typeof blockAdBlock === 'undefined') {
	adBlockDetected();
} else {
	blockAdBlock.onDetected(adBlockDetected);
	blockAdBlock.onNotDetected(adBlockNotDetected);
	// and|or
	blockAdBlock.on(true, adBlockDetected);
	blockAdBlock.on(false, adBlockNotDetected);
	// and|or
	blockAdBlock.on(true, adBlockDetected).onNotDetected(adBlockNotDetected);
}

// Change the options
blockAdBlock.setOption('checkOnLoad', false);
// and|or
blockAdBlock.setOption({
	debug: true,
	checkOnLoad: false,
	resetOnEnd: false
});
```

Default options
---------------------
```javascript
// At launch, check if AdBlock is enabled
// Uses the method blockAdBlock.check()
checkOnLoad: true

// At the end of the check, is that it removes all events added ?
resetOnEnd: true

// The number of milliseconds between each check
loopCheckTime: 50

// The number of negative checks after which there is considered that AdBlock is not enabled
// Time (ms) = 50*(5-1) = 200ms (per default)
loopMaxNumber: 5

// CSS class used by the bait caught AdBlock
baitClass: 'pub_300x250 pub_300x250m pub_728x90 text-ad textAd text_ad text_ads text-ads text-ad-links'

// CSS style used to hide the bait of the users
baitStyle: 'width: 1px !important; height: 1px !important; position: absolute !important; left: -10000px !important; top: -1000px !important;'

// Displays the debug in the console (available only from version 3.2 and more)
debug: false
```

Method available
---------------------
```javascript
// Allows to set options
// #options: string|object
// #value:   string
blockAdBlock.setOption(options, value);

// Allows to check if AdBlock is enabled
// The parameter 'loop' allows checking without loop several times according to the value of 'loopMaxNumber'
// Example: loop=true  => time~=200ms (time varies depending on the configuration)
//          loop=false => time~=1ms
// #loop: boolean (default: true)
blockAdBlock.check(loop);

// Allows to manually simulate the presence of AdBlock or not
// #detected: boolean (AdBlock is detected ?)
blockAdBlock.emitEvent(detected);

// Allows to clear all events added via methods 'on', 'onDetected' and 'onNotDetected'
blockAdBlock.clearEvent();

// Allows to add an event if AdBlock is detected
// #detected: boolean (true: detected, false: not detected)
// #fn:       function
blockAdBlock.on(detected, fn);

// Similar to blockAdBlock.on(true|false, fn)
blockAdBlock.onDetected(fn);
blockAdBlock.onNotDetected(fn);
```

Instance
---------------------
*(Available only from version 3.1 and more)*
By default, BlockAdBlock is instantiated automatically.
To block this automatic instantiation, simply create a variable "blockAdBlock" with a value (null, false, ...) before importing the script.
```html
<script>var blockAdBlock = false;</script>
<script src="./blockadblock.js"></script>
```
After that, you are free to create your own instances:
```javascript
blockAdBlock = new BlockAdBlock;
// and|or
myBlockAdBlock = new BlockAdBlock({
	checkOnLoad: true,
	resetOnEnd: true
});
```
