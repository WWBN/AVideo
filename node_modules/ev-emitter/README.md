# EvEmitter

_Lil' event emitter_ — add a little pub/sub

EvEmitter adds publish/subscribe pattern to a browser class. It's a smaller version of [Olical/EventEmitter](https://github.com/Olical/EventEmitter). That EventEmitter is full featured, widely used, and great. This EvEmitter has just the base event functionality to power the event API in libraries like [Isotope](https://isotope.metafizzy.co), [Flickity](https://flickity.metafizzy.co), [Masonry](https://masonry.desandro.com), and [imagesLoaded](https://imagesloaded.desandro.com).

## API

``` js
// class inheritence
class MyClass extends EvEmitter {}

// mixin prototype
Object.assign( MyClass.prototype, EvEmitter.prototype );

// single instance
let emitter = new EvEmitter();
```

### on

Add an event listener.

``` js
emitter.on( eventName, listener )
```

+ `eventName` - _String_ - name of the event
+ `listener` - _Function_

### off

Remove an event listener.

``` js
emitter.off( eventName, listener )
```

### once

Add an event listener to be triggered only once.

``` js
emitter.once( eventName, listener )
```

### emitEvent

Trigger an event.

``` js
emitter.emitEvent( eventName, args )
```

+ `eventName` - _String_ - name of the event
+ `args` - _Array_ - arguments passed to listeners

### allOff

Removes all event listeners.

``` js
emitter.allOff()
```

## Code example

``` js
// create event emitter
var emitter = new EvEmitter();

// listeners
function hey( a, b, c ) {
  console.log( 'Hey', a, b, c )
}

function ho( a, b, c ) {
  console.log( 'Ho', a, b, c )
}

function letsGo( a, b, c ) {
  console.log( 'Lets go', a, b, c )
}

// bind listeners
emitter.on( 'rock', hey )
emitter.on( 'rock', ho )
// trigger letsGo once
emitter.once( 'rock', letsGo )

// emit event
emitter.emitEvent( 'rock', [ 1, 2, 3 ] )
// => 'Hey', 1, 2, 3
// => 'Ho', 1, 2, 3
// => 'Lets go', 1, 2, 3

// unbind
emitter.off( 'rock', ho )

emitter.emitEvent( 'rock', [ 4, 5, 6 ] )
// => 'Hey' 4, 5, 6
```

## Browser support

EvEmitter v2 uses ES6 features like [for...of loops](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Statements/for...of) and [class definition](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Classes) as such it supports Chrome 49+, Firefox 45+, Safari 9+, and Edge 13+.

For older browser support, use [EvEmitter v1](https://github.com/metafizzy/ev-emitter/releases/tag/v1.1.1).

## License

EvEmitter is released under the [MIT License](http://desandro.mit-license.org/). Have at it.
