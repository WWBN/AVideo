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
 * An example of basic server.
 */
class BasicServer extends ExampleServer {

    handle() {
        this.nsp.on('connection', socket => {
            this.log('connected: %s', socket.id);
            socket
                .on('disconnect', () => {
                    this.log('disconnected: %s', socket.id);
                })
                .on('test', (arg1, arg2, arg3) => {
                    this.log('Test arguments', arg1, arg2, arg3);
                    socket.emit('test', 1, 2, 'Okay');
                });
            setTimeout(() => socket.emit('hello'), 500);
        });
        return true;
    }
}

module.exports = BasicServer;