/**
 * This file is part of the Elephant.io package
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 *
 * @copyright Wisembly
 * @license   http://www.opensource.org/licenses/MIT-License MIT License
 */

const fs = require('fs');
const path = require('path');
const server = require('http').createServer();
const socketio = require('socket.io');

const port = 14000;
const dir = __dirname;

const factory = options => {
    options = options || {};
    // is version 0x?
    if (typeof socketio.listen === 'function' && options.path) {
        options.resource = options.path;
        delete options.path;
    }
    return typeof socketio === 'function' ? socketio(server, options) :
        socketio.listen(server, options);
}
const serve = (prefix, io) => {
    fs
        .readdirSync(dir)
        .filter(file => file.startsWith(prefix))
        .map(file => {
            const Svr = require(path.join(dir, file));
            const s = new Svr(io);
            s.name = file.substr(prefix.length, file.length - prefix.length - 3);
            return s;
        })
        .sort((a, b) => a.ns.localeCompare(b.ns))
        .forEach(s => {
            if (s.nsp && s.handle()) {
                console.log('Serving %s on %s', s.name, '/' + s.ns);
            }
        });
}

console.log('Please wait, running servers...');
serve('serve-', factory());
// socket io 0x doesn't like multiple instances
serve('serve2-', factory({path: '/my/my.io'}));

server.listen(port, () => {
    console.log('Server listening at %d...', port);
});