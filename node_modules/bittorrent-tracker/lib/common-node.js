/**
 * Functions/constants needed by both the client and server (but only in node).
 * These are separate from common.js so they can be skipped when bundling for the browser.
 */

const querystring = require('querystring')

exports.IPV4_RE = /^[\d.]+$/
exports.IPV6_RE = /^[\da-fA-F:]+$/
exports.REMOVE_IPV4_MAPPED_IPV6_RE = /^::ffff:/

exports.CONNECTION_ID = Buffer.concat([toUInt32(0x417), toUInt32(0x27101980)])
exports.ACTIONS = { CONNECT: 0, ANNOUNCE: 1, SCRAPE: 2, ERROR: 3 }
exports.EVENTS = { update: 0, completed: 1, started: 2, stopped: 3, paused: 4 }
exports.EVENT_IDS = {
  0: 'update',
  1: 'completed',
  2: 'started',
  3: 'stopped',
  4: 'paused'
}
exports.EVENT_NAMES = {
  update: 'update',
  completed: 'complete',
  started: 'start',
  stopped: 'stop',
  paused: 'pause'
}

/**
 * Client request timeout. How long to wait before considering a request to a
 * tracker server to have timed out.
 */
exports.REQUEST_TIMEOUT = 15000

/**
 * Client destroy timeout. How long to wait before forcibly cleaning up all
 * pending requests, open sockets, etc.
 */
exports.DESTROY_TIMEOUT = 1000

function toUInt32 (n) {
  const buf = Buffer.allocUnsafe(4)
  buf.writeUInt32BE(n, 0)
  return buf
}
exports.toUInt32 = toUInt32

/**
 * `querystring.parse` using `unescape` instead of decodeURIComponent, since bittorrent
 * clients send non-UTF8 querystrings
 * @param  {string} q
 * @return {Object}
 */
exports.querystringParse = q => querystring.parse(q, null, null, { decodeURIComponent: unescape })

/**
 * `querystring.stringify` using `escape` instead of encodeURIComponent, since bittorrent
 * clients send non-UTF8 querystrings
 * @param  {Object} obj
 * @return {string}
 */
exports.querystringStringify = obj => {
  let ret = querystring.stringify(obj, null, null, { encodeURIComponent: escape })
  ret = ret.replace(/[@*/+]/g, char => // `escape` doesn't encode the characters @*/+ so we do it manually
  `%${char.charCodeAt(0).toString(16).toUpperCase()}`)
  return ret
}
