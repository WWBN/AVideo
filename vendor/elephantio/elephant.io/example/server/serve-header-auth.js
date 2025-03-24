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
 * An example of authorization using request header server.
 */
class HeaderAuthServer extends ExampleServer {

    tokens = {}
    users = {}

    initialize() {
        this.namespace = 'header-auth';
    }

    handle() {
        if (typeof this.nsp.use === 'function') {
            this.nsp.use((socket, next) => {
                const auth = socket.request.headers.authorization;
                const user = socket.request.headers.user;
                if (auth && user) {
                    const token = auth.replace('Bearer ', '');
                    this.log('auth token', token);
                    // do some security check with token
                    // ...
                    // store token and bind with specific socket id
                    if (!this.tokens[token] && !this.users[token]) {
                        this.tokens[token] = socket.id;
                        this.users[token] = user;
                    }
                    next();
                } else{
                    next(new Error('no authorization header'));
                }
            });
        }
        this.nsp.on('connection', socket => {
            let nb;
            this.log('connected: %s', socket.id);
            socket
                .on('disconnect', () => {
                    this.log('disconnected: %s', socket.id);
                })
                .on('message', message => {
                    ++nb;
                    let reply;
                    this.log('message < %s', message);
                    if (!message['token']) {
                        reply = 'Token is missed';
                    }
                    if (!this.tokens[message['token']]) {
                        reply = 'Token is invalid';
                    }
                    const user = this.users[message['token']];
                    if (!user) {
                        reply = 'Sorry. I don\'t remember you.';
                    } else if (message['message'].indexOf('remember') !== -1) {
                        reply = 'I remember you, ' + user;
                    } else {
                        reply = 'I am fine, ' + user;
                    }
                    this.log('message > %s', reply);
                    socket.emit('message', reply);
                });
        });
        return true;
    }
}

module.exports = HeaderAuthServer;