/**
 * This file is part of the Elephant.io package
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 *
 * @copyright Wisembly
 * @license   http://www.opensource.org/licenses/MIT-License MIT License
 */

const ExampleServer = require('./serve');

/**
 * An example of authorization using handshake server.
 */
class HandshakeAuthServer extends ExampleServer {

    initialize() {
        this.namespace = 'handshake-auth';
    }

    handle() {
        if (typeof this.nsp.use === 'function') {
            this.nsp.use((socket, next) => {
                if (socket.handshake && socket.handshake.auth) {
                    const user = socket.handshake.auth.user;
                    const token = socket.handshake.auth.token;
                    if (user && token) {
                        this.log('auth token', token);
                        // do some security check with token
                        // for example:
                        if (user === 'random@example.com' && token === 'my-secret-token') {
                            this.log('successfully authenticated');
                            next();
                        } else {
                            next(new Error('invalid credentials'));
                        }
                    } else {
                        next(new Error('missing auth from the handshake'));
                    }
                } else {
                    next();
                }
            });
        }
        this.nsp.on('connection', socket => {
            this.log('connected: %s', socket.id);
            socket
                .on('disconnect', () => {
                    this.log('disconnected: %s', socket.id);
                })
                .on('echo', message => {
                    socket.emit('echo', message);
                });
        });
        return true;
    }
}

module.exports = HandshakeAuthServer;