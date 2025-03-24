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
 * An example of polling server.
 */
class PollingServer extends ExampleServer {

    initialize() {
        this.namespace = 'polling';
    }

    handle() {
        this.nsp.on('connection', socket => {
            this.log('connected: %s', socket.id);
            socket
                .on('disconnect', () => {
                    this.log('disconnected: %s', socket.id);
                })
                .on('message', data => {
                    this.log('message < %s', data);
                    socket.emit('message', data);
                });
        });
        return true;
    }
}

module.exports = PollingServer;