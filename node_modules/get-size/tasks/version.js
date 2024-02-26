/* eslint-env node */

const fs = require('fs');
const path = require('path');
const { version } = require('../package.json');

function dir( file ) {
  return path.resolve( __dirname, file );
}

let content = fs.readFileSync( dir('../get-size.js'), 'utf8' );
content = content.replace( /getSize v[\w.-]+/,
    `getSize v${version}` );
fs.writeFileSync( dir('../get-size.js'), content, 'utf8' );
