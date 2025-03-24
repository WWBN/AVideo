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
 * An example of ack server.
 */
class AckServer extends ExampleServer {

    initialize() {
        this.namespace = 'ack';
    }

    handle() {
        this.nsp.on('connection', socket => {
            this.log('connected: %s', socket.id);
            socket
                .on('disconnect', () => {
                    this.log('disconnected: %s', socket.id);
                })
                .on('test-send-ack', (data, callback) => {
                    this.log('receive test send ack: %s', data);
                    if (typeof callback === 'function') {
                        callback('+ack');
                    }
                })
                .on('test-recv-ack', data => {
                    this.log('receive test recv ack: %s', data);
                    socket.emit('test-recv-ack', {success: true}, ack => {
                        this.log('client ack with: %s', ack);
                    });
                });
        });
        return true;
    }
}

module.exports = AckServer;