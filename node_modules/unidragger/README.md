# Unidragger

_Base draggable class_

Used in [Flickity](https://flickity.metafizzy.co) and [Draggabilly](https://draggabilly.desandro.com).

Unidragger handles all the event binding and handling to support a draggable library.

## Features

+ Touch device support: iOS, Android, Microsoft Surface
+ Handles click events in `input` elements 

## Install

npm: `npm install unidragger`

Yarn: `yarn add unidragger`

## Demo code

``` js
// your draggable class
function Dragger( elem ) {
  this.element = elem;
}

// use Unidragger as a mixin
extend( Dragger.prototype, Unidragger.prototype );

Dragger.prototype.create = function() {
  // set drag handles
  this.handles = [ this.element ];
  this.bindHandles();
};

Dragger.prototype.dragStart = function( event, pointer ) {
  console.log('drag start');
};

Dragger.prototype.dragMove = function( event, pointer, moveVector ) {
  var dragX = this.dragStartPoint.x + moveVector.x;
  var dragY = this.dragStartPoint.y + moveVector.y;
  this.element.style.left = dragX + 'px';
  this.element.style.top = dragY + 'px';
};

Dragger.prototype.dragEnd = function( event, pointer ) {
  console.log('drag end');
};
```

---

MIT license

By [Metafizzy 🌈🐻](https://metafizzy.co)
