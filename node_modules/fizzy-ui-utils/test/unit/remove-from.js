QUnit.test( 'removeFrom', function( assert ) {

  let removeFrom = fizzyUIUtils.removeFrom;

  let ary = [ 0, 1, 2, 3, 4, 5, 6 ];

  removeFrom( ary, 2 );
  let ary2 = [ 0, 1, 3, 4, 5, 6 ];
  assert.deepEqual( ary, ary2, '2 removed' );
  removeFrom( ary, 8 );
  assert.deepEqual( ary, ary2, '8 not removed' );

} );
