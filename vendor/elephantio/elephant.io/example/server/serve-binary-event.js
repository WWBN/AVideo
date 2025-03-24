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
 * An example of binary event message server.
 */
class BinaryEventServer extends ExampleServer {

    initialize() {
        this.namespace = 'binary-event';
    }

    handle() {
        this.nsp.on('connection', socket => {
            this.log('connected: %s', socket.id);
            socket
                .on('disconnect', () => {
                    this.log('disconnected: %s', socket.id);
                })
                .on('test-binary', data => {
                    this.log('receive data: %s', data);
                    const payload = [];
                    const f = function(p) {
                        if (typeof p === 'object') {
                            Object.keys(p).forEach(k => {
                                if (p[k] instanceof Buffer) {
                                    payload.push(p[k]);
                                } else if (typeof p[k] === 'object' && p[k].constructor.name === 'Object') {
                                    f(p[k]);
                                }
                            });
                        }
                    }
                    f(data);
                    socket.emit('test-binary', {success: true, time: Buffer.from(new Date().toString()), payload: payload});
                });
        });
        return true;
    }
}

module.exports = BinaryEventServer;