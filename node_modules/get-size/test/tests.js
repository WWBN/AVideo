/**
 * getSize tests
 * with QUnit
**/

/* globals getSize, QUnit */

( function() {

function getBoxSize( num ) {
  let box = document.querySelector( '#ex' + num + ' .box' );
  return getSize( box );
}

QUnit.test( 'arguments', function( assert ) {
  assert.ok( !getSize( 0 ), 'Number returns falsey' );
  assert.ok( !getSize( document.querySelector('#foobabbles') ),
      'bad querySelector returns falsey' );
  assert.ok( getSize('#ex1'), 'query selector string works' );
} );

QUnit.test( 'ex1: no styling', function( assert ) {
  let size = getBoxSize( 1 );
  assert.equal( size.width, 400, 'Inherit container width' );
  assert.equal( size.height, 0, 'No height' );
  assert.equal( size.isBorderBox, false, 'isBorderBox' );
} );

QUnit.test( 'ex2: height: 100%', function( assert ) {
  let size = getBoxSize( 2 );
  assert.equal( size.height, 200, 'Inherit height' );
} );

QUnit.test( 'ex3: width: 50%; height: 50%', function( assert ) {
  let size = getBoxSize( 3 );
  assert.equal( size.width, 200, 'half width' );
  assert.equal( size.height, 100, 'half height' );
} );

QUnit.test( 'ex4: border: 10px solid', function( assert ) {
  let size = getBoxSize( 4 );
  // console.log( size );
  assert.equal( size.width, 220, 'width = 220 width' );
  assert.equal( size.height, 120, 'height = 120 height' );
  assert.equal( size.innerWidth, 200, 'innerWidth = 200 width' );
  assert.equal( size.innerHeight, 100, 'innerHeight = 200 width' );
  assert.equal( size.outerWidth, 220, 'outerWidth = 200 width + 10 border + 10 border' );
  assert.equal( size.outerHeight, 120,
      'outerHeight = 100 height + 10 border + 10 border' );
} );

QUnit.test( 'ex5: border: 10px solid; margin: 15px', function( assert ) {
  // margin: 10px 20px 30px 40px;
  let size = getBoxSize( 5 );
  // console.log( size );
  assert.equal( size.width, 220, 'width = 220 width' );
  assert.equal( size.height, 120, 'height = 120 height' );
  assert.equal( size.marginTop, 10, 'marginTop' );
  assert.equal( size.marginRight, 20, 'marginRight' );
  assert.equal( size.marginBottom, 30, 'marginBottom' );
  assert.equal( size.marginLeft, 40, 'marginLeft ' );
  assert.equal( size.innerWidth, 200, 'innerWidth = 200 width' );
  assert.equal( size.innerHeight, 100, 'innerHeight = 200 width' );
  assert.equal( size.outerWidth, 280, 'outerWidth = 200 width + 20 border + 60 margin' );
  assert.equal( size.outerHeight, 160,
      'outerHeight = 100 height + 20 border + 40 margin' );
} );

QUnit.test( 'ex6: padding, set width/height', function( assert ) {
  let size = getBoxSize( 6 );
  // console.log( size );
  assert.equal( size.width, 260, 'width' );
  assert.equal( size.height, 140, 'height' );
  assert.equal( size.innerWidth, 200,
      'innerWidth = 200 width - 20 padding - 40 padding' );
  assert.equal( size.innerHeight, 100,
      'innerHeight = 200 height - 10 padding - 30 padding' );
  assert.equal( size.outerWidth, 260, 'outerWidth' );
  assert.equal( size.outerHeight, 140, 'outerHeight' );

} );

QUnit.test( 'ex7: padding, inherit width', function( assert ) {
  // padding: 10px 20px 30px 40px;
  let size = getBoxSize( 7 );
  // console.log( size );
  assert.equal( size.width, 400, 'width' );
  assert.equal( size.height, 140, 'height' );
  assert.equal( size.paddingTop, 10, 'paddingTop' );
  assert.equal( size.paddingRight, 20, 'paddingRight' );
  assert.equal( size.paddingBottom, 30, 'paddingBottom' );
  assert.equal( size.paddingLeft, 40, 'paddingLeft ' );
  assert.equal( size.innerWidth, 340,
      'innerWidth = 400 width - 20 padding - 40 padding' );
  assert.equal( size.innerHeight, 100,
      'innerHeight = 200 height - 10 padding - 30 padding' );
  assert.equal( size.outerWidth, 400, 'outerWidth' );
  assert.equal( size.outerHeight, 140, 'outerHeight' );

} );

QUnit.test( 'ex8: 66.666% values', function( assert ) {
  let size = getBoxSize( 8 );

  if ( size.width % 1 ) {
    assert.ok( size.width > 266.6 && size.width < 266.7,
        'width is between 266.6 and 266.7' );
  } else {
    // IE8 and Safari
    assert.equal( size.width, 267, 'width is 267' );
  }

  if ( size.height % 1 ) {
    assert.ok( size.height > 133.3 && size.height < 133.4,
        'height is between 133.3 and 133.4' );
  } else {
    // IE8
    assert.equal( size.height, 133, 'width is 133' );
  }
} );

QUnit.test( 'ex9: border-box', function( assert ) {
  let size = getBoxSize( 9 );
  assert.equal( size.isBorderBox, true, 'isBorderBox' );
  assert.equal( size.width, 400, 'width' );
  assert.equal( size.height, 200, 'height' );
  assert.equal( size.innerWidth, 280, 'innerWidth' );
  assert.equal( size.innerHeight, 120, 'innerHeight' );
  assert.equal( size.outerWidth, 400, 'outerWidth' );
  assert.equal( size.outerHeight, 200, 'outerHeight' );
} );

QUnit.test( 'display: none', function( assert ) {
  let size = getSize( document.querySelector('#hidden .box1') );
  assert.strictEqual( size.width, 0, 'width' );
  assert.strictEqual( size.height, 0, 'height' );
  assert.strictEqual( size.innerWidth, 0, 'innerWidth' );
  assert.strictEqual( size.innerHeight, 0, 'innerHeight' );
  assert.strictEqual( size.outerWidth, 0, 'outerWidth' );
  assert.strictEqual( size.outerHeight, 0, 'outerHeight' );

  size.width = 300;

  size = getSize( document.querySelector('#hidden .box2') );
  assert.strictEqual( size.width, 0, 'cannot over write zeroSize' );

} );

QUnit.test( 'percent values', function( assert ) {
  let size = getSize( document.querySelector('#percent .box') );
  assert.strictEqual( size.marginLeft, 40, 'marginLeft' );
  assert.strictEqual( size.marginTop, 80, 'marginTop' );
  assert.strictEqual( size.width, 200, 'width' );
  assert.strictEqual( size.height, 100, 'height' );
  assert.strictEqual( size.innerWidth, 200, 'innerWidth' );
  assert.strictEqual( size.innerHeight, 100, 'innerHeight' );
  assert.strictEqual( size.outerWidth, 240, 'outerWidth' );
  assert.strictEqual( size.outerHeight, 180, 'outerHeight' );
} );

} )( window );
