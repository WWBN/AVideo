const test = require('ava');
const EvEmitter = require('../ev-emitter.js');

test( 'should emitEvent', function( t ) {
  let emitter = new EvEmitter();
  let didPop;
  emitter.on( 'pop', function() {
    didPop = true;
  } );
  emitter.emitEvent('pop');
  t.truthy( didPop, 'event emitted' );
} );

test( 'emitEvent should pass argument to listener', function( t ) {
  let emitter = new EvEmitter();
  let result;
  function onPop( arg ) {
    result = arg;
  }
  emitter.on( 'pop', onPop );
  emitter.emitEvent( 'pop', [ 1 ] );
  t.is( result, 1, 'event emitted, arg passed' );
} );

test( 'does not allow same listener to be added', function( t ) {
  let emitter = new EvEmitter();
  let ticks = 0;
  function onPop() {
    ticks++;
  }
  emitter.on( 'pop', onPop );
  emitter.on( 'pop', onPop );
  let _onPop = onPop;
  emitter.on( 'pop', _onPop );

  emitter.emitEvent('pop');
  t.is( ticks, 1, '1 tick for same listener' );
} );

test( 'should remove listener with .off()', function( t ) {
  let emitter = new EvEmitter();
  let ticks = 0;
  function onPop() {
    ticks++;
  }
  emitter.on( 'pop', onPop );
  emitter.emitEvent('pop');
  emitter.off( 'pop', onPop );
  emitter.emitEvent('pop');
  t.is( ticks, 1, '.off() removed listener' );

  // reset
  let ary = [];
  ticks = 0;
  emitter.allOff();

  function onPopA() {
    ticks++;
    ary.push('a');
    if ( ticks == 2 ) {
      emitter.off( 'pop', onPopA );
    }
  }
  function onPopB() {
    ary.push('b');
  }

  emitter.on( 'pop', onPopA );
  emitter.on( 'pop', onPopB );
  emitter.emitEvent('pop'); // a,b
  emitter.emitEvent('pop'); // a,b - remove onPopA
  emitter.emitEvent('pop'); // b

  t.is( ary.join(','), 'a,b,a,b,b', '.off in listener does not interfer' );

} );

test( 'should handle once()', function( t ) {
  let emitter = new EvEmitter();
  let ary = [];

  emitter.on( 'pop', function() {
    ary.push('a');
  } );
  emitter.once( 'pop', function() {
    ary.push('b');
  } );
  emitter.on( 'pop', function() {
    ary.push('c');
  } );
  emitter.emitEvent('pop');
  emitter.emitEvent('pop');

  t.is( ary.join(','), 'a,b,c,a,c', 'once listener triggered once' );

  // reset
  emitter.allOff();
  ary = [];

  // add two identical but not === listeners, only do one once
  emitter.on( 'pop', function() {
    ary.push('a');
  } );
  emitter.once( 'pop', function() {
    ary.push('a');
  } );
  emitter.emitEvent('pop');
  emitter.emitEvent('pop');

  t.is( ary.join(','), 'a,a,a',
      'identical listeners do not interfere with once' );

} );

test( 'does not infinite loop in once()', function( t ) {
  let emitter = new EvEmitter();
  let ticks = 0;
  function onPop() {
    ticks++;
    if ( ticks < 4 ) {
      emitter.emitEvent('pop');
    }
  }

  emitter.once( 'pop', onPop );
  emitter.emitEvent('pop');
  t.is( ticks, 1, '1 tick with emitEvent in once' );
} );

test( 'handles emitEvent with no listeners', function( t ) {
  let emitter = new EvEmitter();
  t.notThrows( function() {
    emitter.emitEvent( 'pop', [ 1, 2, 3 ] );
  } );

  function onPop() {}

  emitter.on( 'pop', onPop );
  emitter.off( 'pop', onPop );

  t.notThrows( function() {
    emitter.emitEvent( 'pop', [ 1, 2, 3 ] );
  } );

  emitter.on( 'pop', onPop );
  emitter.emitEvent( 'pop', [ 1, 2, 3 ] );
  emitter.off( 'pop', onPop );

  t.notThrows( function() {
    emitter.emitEvent( 'pop', [ 1, 2, 3 ] );
  } );
} );

test( 'removes all listeners after allOff', function( t ) {
  let emitter = new EvEmitter();
  let ary = [];
  emitter.on( 'pop', function() {
    ary.push('a');
  } );
  emitter.on( 'pop', function() {
    ary.push('b');
  } );
  emitter.once( 'pop', function() {
    ary.push('c');
  } );

  emitter.emitEvent('pop');
  emitter.allOff();
  emitter.emitEvent('pop');

  t.is( ary.join(','), 'a,b,c', 'allOff removed listeners' );
} );

test( 'class extends', function( t ) {
  class Widgey extends EvEmitter {}

  let wijjy = new Widgey();

  t.is( typeof wijjy.on, 'function' );
  t.is( typeof wijjy.off, 'function' );
  t.is( typeof wijjy.once, 'function' );
} );

test( 'Object.assign prototype', function( t ) {
  function Thingie() {}
  Object.assign( Thingie.prototype, EvEmitter.prototype );

  let thing = new Thingie();

  t.is( typeof thing.on, 'function' );
  t.is( typeof thing.off, 'function' );
  t.is( typeof thing.once, 'function' );
} );
