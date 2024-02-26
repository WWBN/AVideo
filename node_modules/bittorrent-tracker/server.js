const bencode = require('bencode')
const debug = require('debug')('bittorrent-tracker:server')
const dgram = require('dgram')
const EventEmitter = require('events')
const http = require('http')
const peerid = require('bittorrent-peerid')
const series = require('run-series')
const string2compact = require('string2compact')
const WebSocketServer = require('ws').Server

const common = require('./lib/common')
const Swarm = require('./lib/server/swarm')
const parseHttpRequest = require('./lib/server/parse-http')
const parseUdpRequest = require('./lib/server/parse-udp')
const parseWebSocketRequest = require('./lib/server/parse-websocket')

const hasOwnProperty = Object.prototype.hasOwnProperty

/**
 * BitTorrent tracker server.
 *
 * HTTP service which responds to GET requests from torrent clients. Requests include
 * metrics from clients that help the tracker keep overall statistics about the torrent.
 * Responses include a peer list that helps the client participate in the torrent.
 *
 * @param {Object}  opts                options object
 * @param {Number}  opts.interval       tell clients to announce on this interval (ms)
 * @param {Number}  opts.trustProxy     trust 'x-forwarded-for' header from reverse proxy
 * @param {boolean|Object} opts.http    start an http server?, or options for http.createServer (default: true)
 * @param {boolean|Object} opts.udp     start a udp server?, or extra options for dgram.createSocket (default: true)
 * @param {boolean|Object} opts.ws      start a websocket server?, or extra options for new WebSocketServer (default: true)
 * @param {boolean} opts.stats          enable web-based statistics? (default: true)
 * @param {function} opts.filter        black/whitelist fn for disallowing/allowing torrents
 */
class Server extends EventEmitter {
  constructor (opts = {}) {
    super()
    debug('new server %s', JSON.stringify(opts))

    this.intervalMs = opts.interval
      ? opts.interval
      : 10 * 60 * 1000 // 10 min

    this._trustProxy = !!opts.trustProxy
    if (typeof opts.filter === 'function') this._filter = opts.filter

    this.peersCacheLength = opts.peersCacheLength
    this.peersCacheTtl = opts.peersCacheTtl

    this._listenCalled = false
    this.listening = false
    this.destroyed = false
    this.torrents = {}

    this.http = null
    this.udp4 = null
    this.udp6 = null
    this.ws = null

    // start an http tracker unless the user explictly says no
    if (opts.http !== false) {
      this.http = http.createServer(isObject(opts.http) ? opts.http : undefined)
      this.http.on('error', err => { this._onError(err) })
      this.http.on('listening', onListening)

      // Add default http request handler on next tick to give user the chance to add
      // their own handler first. Handle requests untouched by user's handler.
      process.nextTick(() => {
        this.http.on('request', (req, res) => {
          if (res.headersSent) return
          this.onHttpRequest(req, res)
        })
      })
    }

    // start a udp tracker unless the user explicitly says no
    if (opts.udp !== false) {
      this.udp4 = this.udp = dgram.createSocket({
        type: 'udp4',
        reuseAddr: true,
        ...(isObject(opts.udp) ? opts.udp : undefined)
      })
      this.udp4.on('message', (msg, rinfo) => { this.onUdpRequest(msg, rinfo) })
      this.udp4.on('error', err => { this._onError(err) })
      this.udp4.on('listening', onListening)

      this.udp6 = dgram.createSocket({
        type: 'udp6',
        reuseAddr: true,
        ...(isObject(opts.udp) ? opts.udp : undefined)
      })
      this.udp6.on('message', (msg, rinfo) => { this.onUdpRequest(msg, rinfo) })
      this.udp6.on('error', err => { this._onError(err) })
      this.udp6.on('listening', onListening)
    }

    // start a websocket tracker (for WebTorrent) unless the user explicitly says no
    if (opts.ws !== false) {
      const noServer = isObject(opts.ws) && opts.ws.noServer
      if (!this.http && !noServer) {
        this.http = http.createServer()
        this.http.on('error', err => { this._onError(err) })
        this.http.on('listening', onListening)

        // Add default http request handler on next tick to give user the chance to add
        // their own handler first. Handle requests untouched by user's handler.
        process.nextTick(() => {
          this.http.on('request', (req, res) => {
            if (res.headersSent) return
            // For websocket trackers, we only need to handle the UPGRADE http method.
            // Return 404 for all other request types.
            res.statusCode = 404
            res.end('404 Not Found')
          })
        })
      }
      this.ws = new WebSocketServer({
        server: noServer ? undefined : this.http,
        perMessageDeflate: false,
        clientTracking: false,
        ...(isObject(opts.ws) ? opts.ws : undefined)
      })

      this.ws.address = () => {
        if (noServer) {
          throw new Error('address() unavailable with { noServer: true }')
        }
        return this.http.address()
      }

      this.ws.on('error', err => { this._onError(err) })
      this.ws.on('connection', (socket, req) => {
        // Note: socket.upgradeReq was removed in ws@3.0.0, so re-add it.
        // https://github.com/websockets/ws/pull/1099
        socket.upgradeReq = req
        this.onWebSocketConnection(socket)
      })
    }

    if (opts.stats !== false) {
      if (!this.http) {
        this.http = http.createServer()
        this.http.on('error', err => { this._onError(err) })
        this.http.on('listening', onListening)
      }

      // Http handler for '/stats' route
      this.http.on('request', (req, res) => {
        if (res.headersSent) return

        const infoHashes = Object.keys(this.torrents)
        let activeTorrents = 0
        const allPeers = {}

        function countPeers (filterFunction) {
          let count = 0
          let key

          for (key in allPeers) {
            if (hasOwnProperty.call(allPeers, key) && filterFunction(allPeers[key])) {
              count++
            }
          }

          return count
        }

        function groupByClient () {
          const clients = {}
          for (const key in allPeers) {
            if (hasOwnProperty.call(allPeers, key)) {
              const peer = allPeers[key]

              if (!clients[peer.client.client]) {
                clients[peer.client.client] = {}
              }
              const client = clients[peer.client.client]
              // If the client is not known show 8 chars from peerId as version
              const version = peer.client.version || Buffer.from(peer.peerId, 'hex').toString().substring(0, 8)
              if (!client[version]) {
                client[version] = 0
              }
              client[version]++
            }
          }
          return clients
        }

        function printClients (clients) {
          let html = '<ul>\n'
          for (const name in clients) {
            if (hasOwnProperty.call(clients, name)) {
              const client = clients[name]
              for (const version in client) {
                if (hasOwnProperty.call(client, version)) {
                  html += `<li><strong>${name}</strong> ${version} : ${client[version]}</li>\n`
                }
              }
            }
          }
          html += '</ul>'
          return html
        }

        if (req.method === 'GET' && (req.url === '/stats' || req.url === '/stats.json')) {
          infoHashes.forEach(infoHash => {
            const peers = this.torrents[infoHash].peers
            const keys = peers.keys
            if (keys.length > 0) activeTorrents++

            keys.forEach(peerId => {
              // Don't mark the peer as most recently used for stats
              const peer = peers.peek(peerId)
              if (peer == null) return // peers.peek() can evict the peer

              if (!hasOwnProperty.call(allPeers, peerId)) {
                allPeers[peerId] = {
                  ipv4: false,
                  ipv6: false,
                  seeder: false,
                  leecher: false
                }
              }

              if (peer.ip.includes(':')) {
                allPeers[peerId].ipv6 = true
              } else {
                allPeers[peerId].ipv4 = true
              }

              if (peer.complete) {
                allPeers[peerId].seeder = true
              } else {
                allPeers[peerId].leecher = true
              }

              allPeers[peerId].peerId = peer.peerId
              allPeers[peerId].client = peerid(peer.peerId)
            })
          })

          const isSeederOnly = peer => peer.seeder && peer.leecher === false
          const isLeecherOnly = peer => peer.leecher && peer.seeder === false
          const isSeederAndLeecher = peer => peer.seeder && peer.leecher
          const isIPv4 = peer => peer.ipv4
          const isIPv6 = peer => peer.ipv6

          const stats = {
            torrents: infoHashes.length,
            activeTorrents,
            peersAll: Object.keys(allPeers).length,
            peersSeederOnly: countPeers(isSeederOnly),
            peersLeecherOnly: countPeers(isLeecherOnly),
            peersSeederAndLeecher: countPeers(isSeederAndLeecher),
            peersIPv4: countPeers(isIPv4),
            peersIPv6: countPeers(isIPv6),
            clients: groupByClient()
          }

          if (req.url === '/stats.json' || req.headers.accept === 'application/json') {
            res.setHeader('Content-Type', 'application/json')
            res.end(JSON.stringify(stats))
          } else if (req.url === '/stats') {
            res.setHeader('Content-Type', 'text/html')
            res.end(`
              <h1>${stats.torrents} torrents (${stats.activeTorrents} active)</h1>
              <h2>Connected Peers: ${stats.peersAll}</h2>
              <h3>Peers Seeding Only: ${stats.peersSeederOnly}</h3>
              <h3>Peers Leeching Only: ${stats.peersLeecherOnly}</h3>
              <h3>Peers Seeding & Leeching: ${stats.peersSeederAndLeecher}</h3>
              <h3>IPv4 Peers: ${stats.peersIPv4}</h3>
              <h3>IPv6 Peers: ${stats.peersIPv6}</h3>
              <h3>Clients:</h3>
              ${printClients(stats.clients)}
            `.replace(/^\s+/gm, '')) // trim left
          }
        }
      })
    }

    let num = !!this.http + !!this.udp4 + !!this.udp6
    const self = this
    function onListening () {
      num -= 1
      if (num === 0) {
        self.listening = true
        debug('listening')
        self.emit('listening')
      }
    }
  }

  _onError (err) {
    this.emit('error', err)
  }

  listen (...args) /* port, hostname, onlistening */{
    if (this._listenCalled || this.listening) throw new Error('server already listening')
    this._listenCalled = true

    const lastArg = args[args.length - 1]
    if (typeof lastArg === 'function') this.once('listening', lastArg)

    const port = toNumber(args[0]) || args[0] || 0
    const hostname = typeof args[1] !== 'function' ? args[1] : undefined

    debug('listen (port: %o hostname: %o)', port, hostname)

    const httpPort = isObject(port) ? (port.http || 0) : port
    const udpPort = isObject(port) ? (port.udp || 0) : port

    // binding to :: only receives IPv4 connections if the bindv6only sysctl is set 0,
    // which is the default on many operating systems
    const httpHostname = isObject(hostname) ? hostname.http : hostname
    const udp4Hostname = isObject(hostname) ? hostname.udp : hostname
    const udp6Hostname = isObject(hostname) ? hostname.udp6 : hostname

    if (this.http) this.http.listen(httpPort, httpHostname)
    if (this.udp4) this.udp4.bind(udpPort, udp4Hostname)
    if (this.udp6) this.udp6.bind(udpPort, udp6Hostname)
  }

  close (cb = noop) {
    debug('close')

    this.listening = false
    this.destroyed = true

    if (this.udp4) {
      try {
        this.udp4.close()
      } catch (err) {}
    }

    if (this.udp6) {
      try {
        this.udp6.close()
      } catch (err) {}
    }

    if (this.ws) {
      try {
        this.ws.close()
      } catch (err) {}
    }

    if (this.http) this.http.close(cb)
    else cb(null)
  }

  createSwarm (infoHash, cb) {
    if (Buffer.isBuffer(infoHash)) infoHash = infoHash.toString('hex')

    process.nextTick(() => {
      const swarm = this.torrents[infoHash] = new Server.Swarm(infoHash, this)
      cb(null, swarm)
    })
  }

  getSwarm (infoHash, cb) {
    if (Buffer.isBuffer(infoHash)) infoHash = infoHash.toString('hex')

    process.nextTick(() => {
      cb(null, this.torrents[infoHash])
    })
  }

  onHttpRequest (req, res, opts = {}) {
    opts.trustProxy = opts.trustProxy || this._trustProxy

    let params
    try {
      params = parseHttpRequest(req, opts)
      params.httpReq = req
      params.httpRes = res
    } catch (err) {
      res.end(bencode.encode({
        'failure reason': err.message
      }))

      // even though it's an error for the client, it's just a warning for the server.
      // don't crash the server because a client sent bad data :)
      this.emit('warning', err)
      return
    }

    this._onRequest(params, (err, response) => {
      if (err) {
        this.emit('warning', err)
        response = {
          'failure reason': err.message
        }
      }
      if (this.destroyed) return res.end()

      delete response.action // only needed for UDP encoding
      res.end(bencode.encode(response))

      if (params.action === common.ACTIONS.ANNOUNCE) {
        this.emit(common.EVENT_NAMES[params.event], params.addr, params)
      }
    })
  }

  onUdpRequest (msg, rinfo) {
    let params
    try {
      params = parseUdpRequest(msg, rinfo)
    } catch (err) {
      this.emit('warning', err)
      // Do not reply for parsing errors
      return
    }

    this._onRequest(params, (err, response) => {
      if (err) {
        this.emit('warning', err)
        response = {
          action: common.ACTIONS.ERROR,
          'failure reason': err.message
        }
      }
      if (this.destroyed) return

      response.transactionId = params.transactionId
      response.connectionId = params.connectionId

      const buf = makeUdpPacket(response)

      try {
        const udp = (rinfo.family === 'IPv4') ? this.udp4 : this.udp6
        udp.send(buf, 0, buf.length, rinfo.port, rinfo.address)
      } catch (err) {
        this.emit('warning', err)
      }

      if (params.action === common.ACTIONS.ANNOUNCE) {
        this.emit(common.EVENT_NAMES[params.event], params.addr, params)
      }
    })
  }

  onWebSocketConnection (socket, opts = {}) {
    opts.trustProxy = opts.trustProxy || this._trustProxy

    socket.peerId = null // as hex
    socket.infoHashes = [] // swarms that this socket is participating in
    socket.onSend = err => {
      this._onWebSocketSend(socket, err)
    }

    socket.onMessageBound = params => {
      this._onWebSocketRequest(socket, opts, params)
    }
    socket.on('message', socket.onMessageBound)

    socket.onErrorBound = err => {
      this._onWebSocketError(socket, err)
    }
    socket.on('error', socket.onErrorBound)

    socket.onCloseBound = () => {
      this._onWebSocketClose(socket)
    }
    socket.on('close', socket.onCloseBound)
  }

  _onWebSocketRequest (socket, opts, params) {
    try {
      params = parseWebSocketRequest(socket, opts, params)
    } catch (err) {
      socket.send(JSON.stringify({
        'failure reason': err.message
      }), socket.onSend)

      // even though it's an error for the client, it's just a warning for the server.
      // don't crash the server because a client sent bad data :)
      this.emit('warning', err)
      return
    }

    if (!socket.peerId) socket.peerId = params.peer_id // as hex

    this._onRequest(params, (err, response) => {
      if (this.destroyed || socket.destroyed) return
      if (err) {
        socket.send(JSON.stringify({
          action: params.action === common.ACTIONS.ANNOUNCE ? 'announce' : 'scrape',
          'failure reason': err.message,
          info_hash: common.hexToBinary(params.info_hash)
        }), socket.onSend)

        this.emit('warning', err)
        return
      }

      response.action = params.action === common.ACTIONS.ANNOUNCE ? 'announce' : 'scrape'

      let peers
      if (response.action === 'announce') {
        peers = response.peers
        delete response.peers

        if (!socket.infoHashes.includes(params.info_hash)) {
          socket.infoHashes.push(params.info_hash)
        }

        response.info_hash = common.hexToBinary(params.info_hash)

        // WebSocket tracker should have a shorter interval – default: 2 minutes
        response.interval = Math.ceil(this.intervalMs / 1000 / 5)
      }

      // Skip sending update back for 'answer' announce messages – not needed
      if (!params.answer) {
        socket.send(JSON.stringify(response), socket.onSend)
        debug('sent response %s to %s', JSON.stringify(response), params.peer_id)
      }

      if (Array.isArray(params.offers)) {
        debug('got %s offers from %s', params.offers.length, params.peer_id)
        debug('got %s peers from swarm %s', peers.length, params.info_hash)
        peers.forEach((peer, i) => {
          peer.socket.send(JSON.stringify({
            action: 'announce',
            offer: params.offers[i].offer,
            offer_id: params.offers[i].offer_id,
            peer_id: common.hexToBinary(params.peer_id),
            info_hash: common.hexToBinary(params.info_hash)
          }), peer.socket.onSend)
          debug('sent offer to %s from %s', peer.peerId, params.peer_id)
        })
      }

      const done = () => {
        // emit event once the announce is fully "processed"
        if (params.action === common.ACTIONS.ANNOUNCE) {
          this.emit(common.EVENT_NAMES[params.event], params.peer_id, params)
        }
      }

      if (params.answer) {
        debug('got answer %s from %s', JSON.stringify(params.answer), params.peer_id)

        this.getSwarm(params.info_hash, (err, swarm) => {
          if (this.destroyed) return
          if (err) return this.emit('warning', err)
          if (!swarm) {
            return this.emit('warning', new Error('no swarm with that `info_hash`'))
          }
          // Mark the destination peer as recently used in cache
          const toPeer = swarm.peers.get(params.to_peer_id)
          if (!toPeer) {
            return this.emit('warning', new Error('no peer with that `to_peer_id`'))
          }

          toPeer.socket.send(JSON.stringify({
            action: 'announce',
            answer: params.answer,
            offer_id: params.offer_id,
            peer_id: common.hexToBinary(params.peer_id),
            info_hash: common.hexToBinary(params.info_hash)
          }), toPeer.socket.onSend)
          debug('sent answer to %s from %s', toPeer.peerId, params.peer_id)

          done()
        })
      } else {
        done()
      }
    })
  }

  _onWebSocketSend (socket, err) {
    if (err) this._onWebSocketError(socket, err)
  }

  _onWebSocketClose (socket) {
    debug('websocket close %s', socket.peerId)
    socket.destroyed = true

    if (socket.peerId) {
      socket.infoHashes.slice(0).forEach(infoHash => {
        const swarm = this.torrents[infoHash]
        if (swarm) {
          swarm.announce({
            type: 'ws',
            event: 'stopped',
            numwant: 0,
            peer_id: socket.peerId
          })
        }
      })
    }

    // ignore all future errors
    socket.onSend = noop
    socket.on('error', noop)

    socket.peerId = null
    socket.infoHashes = null

    if (typeof socket.onMessageBound === 'function') {
      socket.removeListener('message', socket.onMessageBound)
    }
    socket.onMessageBound = null

    if (typeof socket.onErrorBound === 'function') {
      socket.removeListener('error', socket.onErrorBound)
    }
    socket.onErrorBound = null

    if (typeof socket.onCloseBound === 'function') {
      socket.removeListener('close', socket.onCloseBound)
    }
    socket.onCloseBound = null
  }

  _onWebSocketError (socket, err) {
    debug('websocket error %s', err.message || err)
    this.emit('warning', err)
    this._onWebSocketClose(socket)
  }

  _onRequest (params, cb) {
    if (params && params.action === common.ACTIONS.CONNECT) {
      cb(null, { action: common.ACTIONS.CONNECT })
    } else if (params && params.action === common.ACTIONS.ANNOUNCE) {
      this._onAnnounce(params, cb)
    } else if (params && params.action === common.ACTIONS.SCRAPE) {
      this._onScrape(params, cb)
    } else {
      cb(new Error('Invalid action'))
    }
  }

  _onAnnounce (params, cb) {
    const self = this

    if (this._filter) {
      this._filter(params.info_hash, params, err => {
        // Presence of `err` means that this announce request is disallowed
        if (err) return cb(err)

        getOrCreateSwarm((err, swarm) => {
          if (err) return cb(err)
          announce(swarm)
        })
      })
    } else {
      getOrCreateSwarm((err, swarm) => {
        if (err) return cb(err)
        announce(swarm)
      })
    }

    // Get existing swarm, or create one if one does not exist
    function getOrCreateSwarm (cb) {
      self.getSwarm(params.info_hash, (err, swarm) => {
        if (err) return cb(err)
        if (swarm) return cb(null, swarm)
        self.createSwarm(params.info_hash, (err, swarm) => {
          if (err) return cb(err)
          cb(null, swarm)
        })
      })
    }

    function announce (swarm) {
      if (!params.event || params.event === 'empty') params.event = 'update'
      swarm.announce(params, (err, response) => {
        if (err) return cb(err)

        if (!response.action) response.action = common.ACTIONS.ANNOUNCE
        if (!response.interval) response.interval = Math.ceil(self.intervalMs / 1000)

        if (params.compact === 1) {
          const peers = response.peers

          // Find IPv4 peers
          response.peers = string2compact(peers.filter(peer => common.IPV4_RE.test(peer.ip)).map(peer => `${peer.ip}:${peer.port}`))
          // Find IPv6 peers
          response.peers6 = string2compact(peers.filter(peer => common.IPV6_RE.test(peer.ip)).map(peer => `[${peer.ip}]:${peer.port}`))
        } else if (params.compact === 0) {
          // IPv6 peers are not separate for non-compact responses
          response.peers = response.peers.map(peer => ({
            'peer id': common.hexToBinary(peer.peerId),
            ip: peer.ip,
            port: peer.port
          }))
        } // else, return full peer objects (used for websocket responses)

        cb(null, response)
      })
    }
  }

  _onScrape (params, cb) {
    if (params.info_hash == null) {
      // if info_hash param is omitted, stats for all torrents are returned
      // TODO: make this configurable!
      params.info_hash = Object.keys(this.torrents)
    }

    series(params.info_hash.map(infoHash => cb => {
      this.getSwarm(infoHash, (err, swarm) => {
        if (err) return cb(err)
        if (swarm) {
          swarm.scrape(params, (err, scrapeInfo) => {
            if (err) return cb(err)
            cb(null, {
              infoHash,
              complete: (scrapeInfo && scrapeInfo.complete) || 0,
              incomplete: (scrapeInfo && scrapeInfo.incomplete) || 0
            })
          })
        } else {
          cb(null, { infoHash, complete: 0, incomplete: 0 })
        }
      })
    }), (err, results) => {
      if (err) return cb(err)

      const response = {
        action: common.ACTIONS.SCRAPE,
        files: {},
        flags: { min_request_interval: Math.ceil(this.intervalMs / 1000) }
      }

      results.forEach(result => {
        response.files[common.hexToBinary(result.infoHash)] = {
          complete: result.complete || 0,
          incomplete: result.incomplete || 0,
          downloaded: result.complete || 0 // TODO: this only provides a lower-bound
        }
      })

      cb(null, response)
    })
  }
}

Server.Swarm = Swarm

function makeUdpPacket (params) {
  let packet
  switch (params.action) {
    case common.ACTIONS.CONNECT: {
      packet = Buffer.concat([
        common.toUInt32(common.ACTIONS.CONNECT),
        common.toUInt32(params.transactionId),
        params.connectionId
      ])
      break
    }
    case common.ACTIONS.ANNOUNCE: {
      packet = Buffer.concat([
        common.toUInt32(common.ACTIONS.ANNOUNCE),
        common.toUInt32(params.transactionId),
        common.toUInt32(params.interval),
        common.toUInt32(params.incomplete),
        common.toUInt32(params.complete),
        params.peers
      ])
      break
    }
    case common.ACTIONS.SCRAPE: {
      const scrapeResponse = [
        common.toUInt32(common.ACTIONS.SCRAPE),
        common.toUInt32(params.transactionId)
      ]
      for (const infoHash in params.files) {
        const file = params.files[infoHash]
        scrapeResponse.push(
          common.toUInt32(file.complete),
          common.toUInt32(file.downloaded), // TODO: this only provides a lower-bound
          common.toUInt32(file.incomplete)
        )
      }
      packet = Buffer.concat(scrapeResponse)
      break
    }
    case common.ACTIONS.ERROR: {
      packet = Buffer.concat([
        common.toUInt32(common.ACTIONS.ERROR),
        common.toUInt32(params.transactionId || 0),
        Buffer.from(String(params['failure reason']))
      ])
      break
    }
    default:
      throw new Error(`Action not implemented: ${params.action}`)
  }
  return packet
}

function isObject (obj) {
  return typeof obj === 'object' && obj !== null
}

function toNumber (x) {
  x = Number(x)
  return x >= 0 ? x : false
}

function noop () {}

module.exports = Server
