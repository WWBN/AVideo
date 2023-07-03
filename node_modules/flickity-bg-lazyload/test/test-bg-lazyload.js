QUnit.test( 'bgLazyLoad', function( assert ) {

  let done = assert.async();

  let carousel = document.querySelector('.carousel--bg-lazyload');
  let flkty = new Flickity( carousel, {
    bgLazyLoad: 1,
  } );

  let loadCount = 0;
  flkty.on( 'bgLazyLoad', function( event, elem ) {
    loadCount++;

    assert.equal( event.type, 'load', 'event.type == load' );
    assert.ok( elem, 'elem argument there' );

    // after first 2 have loaded, select 7th cell
    if ( loadCount === 2 ) {
      flkty.select( 6 );
    }
    if ( loadCount === 5 ) {
      let loadedImgs = carousel.querySelectorAll('.flickity-bg-lazyloaded');
      assert.equal( loadedImgs.length, '5', 'only 5 images loaded' );
      done();
    }
  } );

} );
