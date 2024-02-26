module.exports = parseUdpRequest

const ipLib = require('ip')
const common = require('../common')

function parseUdpRequest (msg, rinfo) {
  if (msg.length < 16) throw new Error('received packet is too short')

  const params = {
    connectionId: msg.slice(0, 8), // 64-bit
    action: msg.readUInt32BE(8),
    transactionId: msg.readUInt32BE(12),
    type: 'udp'
  }

  if (!common.CONNECTION_ID.equals(params.connectionId)) {
    throw new Error('received packet with invalid connection id')
  }

  if (params.action === common.ACTIONS.CONNECT) {
    // No further params
  } else if (params.action === common.ACTIONS.ANNOUNCE) {
    params.info_hash = msg.slice(16, 36).toString('hex') // 20 bytes
    params.peer_id = msg.slice(36, 56).toString('hex') // 20 bytes
    params.downloaded = fromUInt64(msg.slice(56, 64)) // TODO: track this?
    params.left = fromUInt64(msg.slice(64, 72))
    params.uploaded = fromUInt64(msg.slice(72, 80)) // TODO: track this?

    params.event = common.EVENT_IDS[msg.readUInt32BE(80)]
    if (!params.event) throw new Error('invalid event') // early return

    const ip = msg.readUInt32BE(84) // optional
    params.ip = ip
      ? ipLib.toString(ip)
      : rinfo.address

    params.key = msg.readUInt32BE(88) // Optional: unique random key from client

    // never send more than MAX_ANNOUNCE_PEERS or else the UDP packet will get bigger than
    // 512 bytes which is not safe
    params.numwant = Math.min(
      msg.readUInt32BE(92) || common.DEFAULT_ANNOUNCE_PEERS, // optional
      common.MAX_ANNOUNCE_PEERS
    )

    params.port = msg.readUInt16BE(96) || rinfo.port // optional
    params.addr = `${params.ip}:${params.port}` // TODO: ipv6 brackets
    params.compact = 1 // udp is always compact
  } else if (params.action === common.ACTIONS.SCRAPE) { // scrape message
    if ((msg.length - 16) % 20 !== 0) throw new Error('invalid scrape message')
    params.info_hash = []
    for (let i = 0, len = (msg.length - 16) / 20; i < len; i += 1) {
      const infoHash = msg.slice(16 + (i * 20), 36 + (i * 20)).toString('hex') // 20 bytes
      params.info_hash.push(infoHash)
    }
  } else {
    throw new Error(`Invalid action in UDP packet: ${params.action}`)
  }

  return params
}

const TWO_PWR_32 = (1 << 16) * 2

/**
 * Return the closest floating-point representation to the buffer value. Precision will be
 * lost for big numbers.
 */
function fromUInt64 (buf) {
  const high = buf.readUInt32BE(0) | 0 // force
  const low = buf.readUInt32BE(4) | 0
  const lowUnsigned = (low >= 0) ? low : TWO_PWR_32 + low

  return (high * TWO_PWR_32) + lowUnsigned
}
