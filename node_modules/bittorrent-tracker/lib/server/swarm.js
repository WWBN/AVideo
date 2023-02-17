const arrayRemove = require('unordered-array-remove')
const debug = require('debug')('bittorrent-tracker:swarm')
const LRU = require('lru')
const randomIterate = require('random-iterate')

// Regard this as the default implementation of an interface that you
// need to support when overriding Server.createSwarm() and Server.getSwarm()
class Swarm {
  constructor (infoHash, server) {
    const self = this
    self.infoHash = infoHash
    self.complete = 0
    self.incomplete = 0

    self.peers = new LRU({
      max: server.peersCacheLength || 1000,
      maxAge: server.peersCacheTtl || 20 * 60 * 1000 // 20 minutes
    })

    // When a peer is evicted from the LRU store, send a synthetic 'stopped' event
    // so the stats get updated correctly.
    self.peers.on('evict', data => {
      const peer = data.value
      const params = {
        type: peer.type,
        event: 'stopped',
        numwant: 0,
        peer_id: peer.peerId
      }
      self._onAnnounceStopped(params, peer, peer.peerId)
      peer.socket = null
    })
  }

  announce (params, cb) {
    const self = this
    const id = params.type === 'ws' ? params.peer_id : params.addr
    // Mark the source peer as recently used in cache
    const peer = self.peers.get(id)

    if (params.event === 'started') {
      self._onAnnounceStarted(params, peer, id)
    } else if (params.event === 'stopped') {
      self._onAnnounceStopped(params, peer, id)
      if (!cb) return // when websocket is closed
    } else if (params.event === 'completed') {
      self._onAnnounceCompleted(params, peer, id)
    } else if (params.event === 'update') {
      self._onAnnounceUpdate(params, peer, id)
    } else if (params.event === 'paused') {
      self._onAnnouncePaused(params, peer, id)
    } else {
      cb(new Error('invalid event'))
      return
    }
    cb(null, {
      complete: self.complete,
      incomplete: self.incomplete,
      peers: self._getPeers(params.numwant, params.peer_id, !!params.socket)
    })
  }

  scrape (params, cb) {
    cb(null, {
      complete: this.complete,
      incomplete: this.incomplete
    })
  }

  _onAnnounceStarted (params, peer, id) {
    if (peer) {
      debug('unexpected `started` event from peer that is already in swarm')
      return this._onAnnounceUpdate(params, peer, id) // treat as an update
    }

    if (params.left === 0) this.complete += 1
    else this.incomplete += 1
    this.peers.set(id, {
      type: params.type,
      complete: params.left === 0,
      peerId: params.peer_id, // as hex
      ip: params.ip,
      port: params.port,
      socket: params.socket // only websocket
    })
  }

  _onAnnounceStopped (params, peer, id) {
    if (!peer) {
      debug('unexpected `stopped` event from peer that is not in swarm')
      return // do nothing
    }

    if (peer.complete) this.complete -= 1
    else this.incomplete -= 1

    // If it's a websocket, remove this swarm's infohash from the list of active
    // swarms that this peer is participating in.
    if (peer.socket && !peer.socket.destroyed) {
      const index = peer.socket.infoHashes.indexOf(this.infoHash)
      arrayRemove(peer.socket.infoHashes, index)
    }

    this.peers.remove(id)
  }

  _onAnnounceCompleted (params, peer, id) {
    if (!peer) {
      debug('unexpected `completed` event from peer that is not in swarm')
      return this._onAnnounceStarted(params, peer, id) // treat as a start
    }
    if (peer.complete) {
      debug('unexpected `completed` event from peer that is already completed')
      return this._onAnnounceUpdate(params, peer, id) // treat as an update
    }

    this.complete += 1
    this.incomplete -= 1
    peer.complete = true
    this.peers.set(id, peer)
  }

  _onAnnounceUpdate (params, peer, id) {
    if (!peer) {
      debug('unexpected `update` event from peer that is not in swarm')
      return this._onAnnounceStarted(params, peer, id) // treat as a start
    }

    if (!peer.complete && params.left === 0) {
      this.complete += 1
      this.incomplete -= 1
      peer.complete = true
    }
    this.peers.set(id, peer)
  }

  _onAnnouncePaused (params, peer, id) {
    if (!peer) {
      debug('unexpected `paused` event from peer that is not in swarm')
      return this._onAnnounceStarted(params, peer, id) // treat as a start
    }

    this._onAnnounceUpdate(params, peer, id)
  }

  _getPeers (numwant, ownPeerId, isWebRTC) {
    const peers = []
    const ite = randomIterate(this.peers.keys)
    let peerId
    while ((peerId = ite()) && peers.length < numwant) {
      // Don't mark the peer as most recently used on announce
      const peer = this.peers.peek(peerId)
      if (!peer) continue
      if (isWebRTC && peer.peerId === ownPeerId) continue // don't send peer to itself
      if ((isWebRTC && peer.type !== 'ws') || (!isWebRTC && peer.type === 'ws')) continue // send proper peer type
      peers.push(peer)
    }
    return peers
  }
}

module.exports = Swarm
