QUnit.test( 'filterFindElements', function( assert ) {

  let gridB = document.querySelector('.grid-b');

  let itemElems = fizzyUIUtils.filterFindElements( gridB.children, '.item' );
  assert.equal( itemElems.length, 4, '4 items filter/found' );

} );
