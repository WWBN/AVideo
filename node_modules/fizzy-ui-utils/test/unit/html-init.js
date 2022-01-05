QUnit.test( 'htmlInit', function( assert ) {

  fizzyUIUtils.htmlInit( NiceGreeter, 'niceGreeter' );

  let done = assert.async();
  fizzyUIUtils.docReady( function() {
    let greeterElems = document.querySelectorAll('[data-greeter-expected]');
    for ( let i = 0; i < greeterElems.length; i++ ) {
      let greeterElem = greeterElems[i];
      let attr = greeterElem.getAttribute('data-greeter-expected');
      assert.equal( greeterElem.textContent, attr, 'textContent matches options' );
    }
    done();
  } );

} );

function NiceGreeter( elem, options ) {
  this.element = elem;
  let greeting = options && options.greeting || 'hello';
  let recipient = options && options.recipient || 'world';
  this.element.textContent = greeting + ' ' + recipient;
}
