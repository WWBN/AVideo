const arrayRemove = require('unordered-array-remove')
const BN = require('bn.js')
const clone = require('clone')
const compact2string = require('compact2string')
const debug = require('debug')('bittorrent-tracker:udp-tracker')
const dgram = require('dgram')
const randombytes = require('randombytes')
const Socks = require('socks')

const common = require('../common')
const Tracker = require('./tracker')

/**
 * UDP torrent tracker client (for an individual tracker)
 *
 * @param {Client} client       parent bittorrent tracker client
 * @param {string} announceUrl  announce url of tracker
 * @param {Object} opts         options object
 */
class UDPTracker extends Tracker {
  constructor (client, announceUrl) {
    super(client, announceUrl)
    debug('new udp tracker %s', announceUrl)

    this.cleanupFns = []
    this.maybeDestroyCleanup = null
  }

  announce (opts) {
    if (this.destroyed) return
    this._request(opts)
  }

  scrape (opts) {
    if (this.destroyed) return
    opts._scrape = true
    this._request(opts) // udp scrape uses same announce url
  }

  destroy (cb) {
    const self = this
    if (this.destroyed) return cb(null)
    this.destroyed = true
    clearInterval(this.interval)

    let timeout

    // If there are no pending requests, destroy immediately.
    if (this.cleanupFns.length === 0) return destroyCleanup()

    // Otherwise, wait a short time for pending requests to complete, then force
    // destroy them.
    timeout = setTimeout(destroyCleanup, common.DESTROY_TIMEOUT)

    // But, if all pending requests complete before the timeout fires, do cleanup
    // right away.
    this.maybeDestroyCleanup = () => {
      if (this.cleanupFns.length === 0) destroyCleanup()
    }

    function destroyCleanup () {
      if (timeout) {
        clearTimeout(timeout)
        timeout = null
      }
      self.maybeDestroyCleanup = null
      self.cleanupFns.slice(0).forEach(cleanup => {
        cleanup()
      })
      self.cleanupFns = []
      cb(null)
    }
  }

  _request (opts) {
    const self = this
    if (!opts) opts = {}

    let { hostname, port } = common.parseUrl(this.announceUrl)
    if (port === '') port = 80

    let timeout
    // Socket used to connect to the socks server to create a relay, null if socks is disabled
    let proxySocket
    // Socket used to connect to the tracker or to the socks relay if socks is enabled
    let socket
    // Contains the host/port of the socks relay
    let relay

    let transactionId = genTransactionId()

    const proxyOpts = this.client._proxyOpts && clone(this.client._proxyOpts.socksProxy)
    if (proxyOpts) {
      if (!proxyOpts.proxy) proxyOpts.proxy = {}
      // UDP requests uses the associate command
      proxyOpts.proxy.command = 'associate'
      if (!proxyOpts.target) {
        // This should contain client IP and port but can be set to 0 if we don't have this information
        proxyOpts.target = {
          host: '0.0.0.0',
          port: 0
        }
      }

      if (proxyOpts.proxy.type === 5) {
        Socks.createConnection(proxyOpts, onGotConnection)
      } else {
        debug('Ignoring Socks proxy for UDP request because type 5 is required')
        onGotConnection(null)
      }
    } else {
      onGotConnection(null)
    }

    this.cleanupFns.push(cleanup)

    function onGotConnection (err, s, info) {
      if (err) return onError(err)

      proxySocket = s
      socket = dgram.createSocket('udp4')
      relay = info

      timeout = setTimeout(() => {
        // does not matter if `stopped` event arrives, so supress errors
        if (opts.event === 'stopped') cleanup()
        else onError(new Error(`tracker request timed out (${opts.event})`))
        timeout = null
      }, common.REQUEST_TIMEOUT)
      if (timeout.unref) timeout.unref()

      send(Buffer.concat([
        common.CONNECTION_ID,
        common.toUInt32(common.ACTIONS.CONNECT),
        transactionId
      ]), relay)

      socket.once('error', onError)
      socket.on('message', onSocketMessage)
    }

    function cleanup () {
      if (timeout) {
        clearTimeout(timeout)
        timeout = null
      }
      if (socket) {
        arrayRemove(self.cleanupFns, self.cleanupFns.indexOf(cleanup))
        socket.removeListener('error', onError)
        socket.removeListener('message', onSocketMessage)
        socket.on('error', noop) // ignore all future errors
        try { socket.close() } catch (err) {}
        socket = null
        if (proxySocket) {
          try { proxySocket.close() } catch (err) {}
          proxySocket = null
        }
      }
      if (self.maybeDestroyCleanup) self.maybeDestroyCleanup()
    }

    function onError (err) {
      cleanup()
      if (self.destroyed) return

      try {
        // Error.message is readonly on some platforms.
        if (err.message) err.message += ` (${self.announceUrl})`
      } catch (ignoredErr) {}
      // errors will often happen if a tracker is offline, so don't treat it as fatal
      self.client.emit('warning', err)
    }

    function onSocketMessage (msg) {
      if (proxySocket) msg = msg.slice(10)
      if (msg.length < 8 || msg.readUInt32BE(4) !== transactionId.readUInt32BE(0)) {
        return onError(new Error('tracker sent invalid transaction id'))
      }

      const action = msg.readUInt32BE(0)
      debug('UDP response %s, action %s', self.announceUrl, action)
      switch (action) {
        case 0: { // handshake
          // Note: no check for `self.destroyed` so that pending messages to the
          // tracker can still be sent/received even after destroy() is called

          if (msg.length < 16) return onError(new Error('invalid udp handshake'))

          if (opts._scrape) scrape(msg.slice(8, 16))
          else announce(msg.slice(8, 16), opts)

          break
        }
        case 1: { // announce
          cleanup()
          if (self.destroyed) return

          if (msg.length < 20) return onError(new Error('invalid announce message'))

          const interval = msg.readUInt32BE(8)
          if (interval) self.setInterval(interval * 1000)

          self.client.emit('update', {
            announce: self.announceUrl,
            complete: msg.readUInt32BE(16),
            incomplete: msg.readUInt32BE(12)
          })

          let addrs
          try {
            addrs = compact2string.multi(msg.slice(20))
          } catch (err) {
            return self.client.emit('warning', err)
          }
          addrs.forEach(addr => {
            self.client.emit('peer', addr)
          })

          break
        }
        case 2: { // scrape
          cleanup()
          if (self.destroyed) return

          if (msg.length < 20 || (msg.length - 8) % 12 !== 0) {
            return onError(new Error('invalid scrape message'))
          }
          const infoHashes = (Array.isArray(opts.infoHash) && opts.infoHash.length > 0)
            ? opts.infoHash.map(infoHash => infoHash.toString('hex'))
            : [(opts.infoHash && opts.infoHash.toString('hex')) || self.client.infoHash]

          for (let i = 0, len = (msg.length - 8) / 12; i < len; i += 1) {
            self.client.emit('scrape', {
              announce: self.announceUrl,
              infoHash: infoHashes[i],
              complete: msg.readUInt32BE(8 + (i * 12)),
              downloaded: msg.readUInt32BE(12 + (i * 12)),
              incomplete: msg.readUInt32BE(16 + (i * 12))
            })
          }

          break
        }
        case 3: { // error
          cleanup()
          if (self.destroyed) return

          if (msg.length < 8) return onError(new Error('invalid error message'))
          self.client.emit('warning', new Error(msg.slice(8).toString()))

          break
        }
        default:
          onError(new Error('tracker sent invalid action'))
          break
      }
    }

    function send (message, proxyInfo) {
      if (proxyInfo) {
        const pack = Socks.createUDPFrame({ host: hostname, port }, message)
        socket.send(pack, 0, pack.length, proxyInfo.port, proxyInfo.host)
      } else {
        socket.send(message, 0, message.length, port, hostname)
      }
    }

    function announce (connectionId, opts) {
      transactionId = genTransactionId()

      send(Buffer.concat([
        connectionId,
        common.toUInt32(common.ACTIONS.ANNOUNCE),
        transactionId,
        self.client._infoHashBuffer,
        self.client._peerIdBuffer,
        toUInt64(opts.downloaded),
        opts.left != null ? toUInt64(opts.left) : Buffer.from('FFFFFFFFFFFFFFFF', 'hex'),
        toUInt64(opts.uploaded),
        common.toUInt32(common.EVENTS[opts.event] || 0),
        common.toUInt32(0), // ip address (optional)
        common.toUInt32(0), // key (optional)
        common.toUInt32(opts.numwant),
        toUInt16(self.client._port)
      ]), relay)
    }

    function scrape (connectionId) {
      transactionId = genTransactionId()

      const infoHash = (Array.isArray(opts.infoHash) && opts.infoHash.length > 0)
        ? Buffer.concat(opts.infoHash)
        : (opts.infoHash || self.client._infoHashBuffer)

      send(Buffer.concat([
        connectionId,
        common.toUInt32(common.ACTIONS.SCRAPE),
        transactionId,
        infoHash
      ]), relay)
    }
  }
}

UDPTracker.prototype.DEFAULT_ANNOUNCE_INTERVAL = 30 * 60 * 1000 // 30 minutes

function genTransactionId () {
  return randombytes(4)
}

function toUInt16 (n) {
  const buf = Buffer.allocUnsafe(2)
  buf.writeUInt16BE(n, 0)
  return buf
}

const MAX_UINT = 4294967295

function toUInt64 (n) {
  if (n > MAX_UINT || typeof n === 'string') {
    const bytes = new BN(n).toArray()
    while (bytes.length < 8) {
      bytes.unshift(0)
    }
    return Buffer.from(bytes)
  }
  return Buffer.concat([common.toUInt32(0), common.toUInt32(n)])
}

function noop () {}

module.exports = UDPTracker
