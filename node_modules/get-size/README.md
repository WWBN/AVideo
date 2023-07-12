# getSize

Get the size of elements. Used in [Masonry](https://masonry.desandro.com), [Isotope](https://isotope.metafizzy.co), &  [Flickity](https://flickity.metafizzy.co). 

``` js
var size = getSize( elem );
// elem can be an element
var size = getSize( document.querySelector('.selector') )
// elem can be a selector string
var size = getSize('.selector')
```

Returns an object with: 

+ width, height
+ innerWidth, innerHeight
+ outerWidth, outerHeight
+ paddingLeft, paddingTop, paddingRight, paddingBottom
+ marginLeft, marginTop, marginRight, marginBottom
+ borderLeftWidth, borderTopWidth, borderRightWidth, borderBottomWidth
+ isBorderBox

Browser support: Chrome 51+, Firefox 50+, Edge 12+, Safari 10+,

## Install

npm: `npm install get-size`

Yarn: `yarn add get-size`

## MIT License

getSize is released under the MIT License. Have at it.
