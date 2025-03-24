/**
 * This file is part of the Elephant.io package
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 *
 * @copyright Wisembly
 * @license   http://www.opensource.org/licenses/MIT-License MIT License
 */

const { Server, Namespace } = require('socket.io');

/**
 * A base class for example server.
 */
class ExampleServer {

    /** @type {Server} */
    io = null

    /** @type {Namespace} */
    nsp = null

    /** @type {string} */
    namespace = null

    /**
     * Constructor.
     *
     * @param {Server} io
     */
    constructor(io) {
        this.io = io;
        this.initialize();
        this.nsp = io.of(this.namespace ? this.namespace : '');
    }

    /**
     * Initialize server.
     */
    initialize() {
    }

    /**
     * Do handle example such as listen for events or applying middleware.
     * 
     * @example
     * this.nsp.on('connection', socket => {
     *     this.log('connected: %s', socket.id);
     *     socket.on('disconnect', () => {
     *         this.log('disconnected: %s', socket.id);
     *     });
     * });
     */
    handle() {
    }

    /**
     * Log to console.
     *
     * @param  {...any} args
     */
    log(...args) {
        if (args.length) {
            const ns = this.name ? this.name : this.constructor.name;
            if (typeof args[0] === 'string') {
                args[0] = `${ns}: ${args[0]}`;
            } else {
                args.unshift(`${ns}: `);
            }
        }
        console.log.apply(this, args);
    }

    /**
     * Get namespace.
     *
     * @returns {string}
     */
    get ns() {
        return this.namespace ? this.namespace : '';
    }

    /**
     * Get socket.io version.
     *
     * @returns {object}
     */
    static get version() {
        if (ExampleServer.ver === undefined) {
            const fs = require('fs');
            const path = require('path');
            const info = JSON.parse(fs.readFileSync(path.join(__dirname, 'package.json')));
            const vers = info.dependencies['socket.io'].match(/(?<MAJOR>(\d+))\.(?<MINOR>(\d+))\.(?<PATCH>(\d+))/);
            if (vers) {
                ExampleServer.ver = {
                    MAJOR: parseInt(vers.MAJOR),
                    MINOR: parseInt(vers.MINOR),
                    PATCH: parseInt(vers.PATCH),
                }
            }
        }

        return ExampleServer.ver;
    }
}

module.exports = ExampleServer;