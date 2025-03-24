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
 * An example of keep alive server.
 */
class KeepAliveServer extends ExampleServer {

    initialize() {
        this.namespace = 'keep-alive';
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
                    socket.emit('message', {success: true});
                });
        });
        return true;
    }
}

module.exports = KeepAliveServer;