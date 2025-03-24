/**
 * This file is part of the Elephant.io package
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 *
 * @copyright Wisembly
 * @license   http://www.opensource.org/licenses/MIT-License MIT License
 */

const util = require('util');
const ExampleServer = require('./serve');

/**
 * An example of error handling server.
 */
class ErrorHandlingServer extends ExampleServer {

    initialize() {
        this.namespace = 'error-handling';
    }

    handle() {
        this.nsp.on('connection', socket => {
            this.log('connected: %s', socket.id);
            socket
                .on('disconnect', () => {
                    this.log('disconnected: %s', socket.id);
                });
            if (ExampleServer.version.MAJOR >= 3) {
                socket
                    .onAny((event, ...$args) => {
                        this.log('event %s: %s', event, util.inspect($args));
                    });
            }
        });
        return true;
    }
}

module.exports = ErrorHandlingServer;