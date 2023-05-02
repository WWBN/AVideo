/*! bittorrent-peerid. MIT License. WebTorrent LLC <https://webtorrent.io/opensource> */
const utils = require('./lib/utils')

/**
 * Parses and returns the client type and version of a bittorrent peer id.
 * Throws an exception if the peer id is invalid.
 *
 * @param {Buffer|string} peerId (as Buffer or hex/utf8 string)
 */
module.exports = peerId => {
  let buffer

  if (Buffer.isBuffer(peerId)) {
    buffer = peerId
  } else if (typeof peerId === 'string') {
    buffer = Buffer.from(peerId, 'utf8')

    // assume utf8 peerId, but if that's invalid, then try hex encoding
    if (buffer.length !== 20) { buffer = Buffer.from(peerId, 'hex') }
  } else {
    throw new Error(`Invalid peerId must be Buffer or hex string: ${peerId}`)
  }

  if (buffer.length !== 20) {
    throw new Error(`Invalid peerId length (hex buffer must be 20 bytes): ${peerId}`)
  }

  // overwrite original peerId string with guaranteed utf8 version
  peerId = buffer.toString('utf8')

  let client = null

  // If the client reuses parts of the peer ID of other peers, then try to determine this
  // first (before we misidentify the client).
  if (utils.isPossibleSpoofClient(peerId)) {
    if ((client = utils.decodeBitSpiritClient(peerId, buffer))) return client
    if ((client = utils.decodeBitCometClient(peerId, buffer))) return client
    return { client: 'BitSpirit?' }
  }

  // See if the client uses Az style identification
  if (utils.isAzStyle(peerId)) {
    if ((client = getAzStyleClientName(peerId))) {
      const version = getAzStyleClientVersion(client, peerId)

      // Hack for fake ZipTorrent clients - there seems to be some clients
      // which use the same identifier, but they aren't valid ZipTorrent clients
      if (client.startsWith('ZipTorrent') && peerId.startsWith('bLAde', 8)) {
        return {
          client: 'Unknown [Fake: ZipTorrent]',
          version
        }
      }

      // BitTorrent 6.0 Beta currently misidentifies itself
      if (client === '\u00B5Torrent' && version === '6.0 Beta') {
        return {
          client: 'Mainline',
          version: '6.0 Beta'
        }
      }

      // If it's the rakshasa libtorrent, then it's probably rTorrent
      if (client.startsWith('libTorrent (Rakshasa)')) {
        return {
          client: `${client} / rTorrent*`,
          version
        }
      }

      return {
        client,
        version
      }
    }
  }

  // See if the client uses Shadow style identification
  if (utils.isShadowStyle(peerId)) {
    if ((client = getShadowStyleClientName(peerId))) {
      // TODO: handle shadow style client version numbers
      return { client }
    }
  }

  // See if the client uses Mainline style identification
  if (utils.isMainlineStyle(peerId)) {
    if ((client = getMainlineStyleClientName(peerId))) {
      // TODO: handle mainline style client version numbers
      return { client }
    }
  }

  // Check for BitSpirit / BitComet disregarding spoof mode
  if ((client = utils.decodeBitSpiritClient(peerId, buffer))) return client
  if ((client = utils.decodeBitCometClient(peerId, buffer))) return client

  // See if the client identifies itself using a particular substring
  const data = getSimpleClient(peerId)
  if (data) {
    client = data.client

    // TODO: handle simple client version numbers
    return {
      client,
      version: data.version
    }
  }

  // See if client is known to be awkward / nonstandard
  if ((client = utils.identifyAwkwardClient(peerId, buffer))) {
    return client
  }

  // TODO: handle unknown az-formatted and shadow-formatted clients
  return { client: 'unknown' }
}

// Az style two byte code identifiers to real client name
const azStyleClients = {}
const azStyleClientVersions = {}

// Shadow's style one byte code identifiers to real client name
const shadowStyleClients = {}
const shadowStyleClientVersions = {}

// Mainline's new style uses one byte code identifiers too
const mainlineStyleClients = {}

// Clients with completely custom naming schemes
const customStyleClients = []

const VER_AZ_THREE_DIGITS = v => // "1.2.3"
  `${v[0]}.${v[1]}.${v[2]}`
const VER_AZ_DELUGE = v => {
  const alphabet = 'ABCDE'
  if (isNaN(v[2])) {
    return `${v[0]}.${v[1]}.1${alphabet.indexOf(v[2])}`
  }
  return `${v[0]}.${v[1]}.${v[2]}`
}
const VER_AZ_THREE_DIGITS_PLUS_MNEMONIC = v => {
  // "1.2.3 [4]"
  let mnemonic = v[3]
  if (mnemonic === 'B') {
    mnemonic = 'Beta'
  } else if (mnemonic === 'A') {
    mnemonic = 'Alpha'
  } else {
    mnemonic = ''
  }
  return `${v[0]}.${v[1]}.${v[2]} ${mnemonic}`
}
const VER_AZ_FOUR_DIGITS = v => // "1.2.3.4"
  `${v[0]}.${v[1]}.${v[2]}.${v[3]}`
const VER_AZ_TWO_MAJ_TWO_MIN = v => // "12.34"
  `${v[0] + v[1]}.${v[2]}${v[3]}`
const VER_AZ_SKIP_FIRST_ONE_MAJ_TWO_MIN = v => // "2.34"
  `${v[1]}.${v[2]}${v[3]}`
const VER_AZ_KTORRENT_STYLE = '1.2.3=[RD].4'
const VER_AZ_TRANSMISSION_STYLE = v => {
  // "transmission"
  if (v[0] === '0' && v[1] === '0' && v[2] === '0') {
    return `0.${v[3]}`
  } else if (v[0] === '0' && v[1] === '0') {
    return `0.${v[2]}${v[3]}`
  }
  return `${v[0]}.${v[1]}${v[2]}${v[3] === 'Z' || v[3] === 'X' ? '+' : ''}`
}
const VER_AZ_WEBTORRENT_STYLE = v => {
  // "webtorrent"
  let version = ''
  if (v[0] === '0') {
    version += `${v[1]}.`
  } else {
    version += `${v[0]}${v[1]}.`
  }
  if (v[2] === '0') {
    version += v[3]
  } else {
    version += `${v[2]}${v[3]}`
  }
  return version
}
const VER_AZ_THREE_ALPHANUMERIC_DIGITS = '2.33.4'
const VER_NONE = 'NO_VERSION'

function addAzStyle (id, client, version = VER_AZ_FOUR_DIGITS) {
  azStyleClients[id] = client
  azStyleClientVersions[client] = version
}

function addShadowStyle (id, client, version = VER_AZ_THREE_DIGITS) {
  shadowStyleClients[id] = client
  shadowStyleClientVersions[client] = version
}

function addMainlineStyle (id, client) {
  mainlineStyleClients[id] = client
}

function addSimpleClient (client, version, id, position) {
  if (typeof id === 'number' || typeof id === 'undefined') {
    position = id
    id = version
    version = undefined
  }

  customStyleClients.push({
    id,
    client,
    version,
    position: position || 0
  })
}

function getAzStyleClientName (peerId) {
  return azStyleClients[peerId.substring(1, 3)]
}

function getShadowStyleClientName (peerId) {
  return shadowStyleClients[peerId.substring(0, 1)]
}

function getMainlineStyleClientName (peerId) {
  return mainlineStyleClients[peerId.substring(0, 1)]
}

function getSimpleClient (peerId) {
  for (let i = 0; i < customStyleClients.length; ++i) {
    const client = customStyleClients[i]

    if (peerId.startsWith(client.id, client.position)) {
      return client
    }
  }

  return null
}

function getAzStyleClientVersion (client, peerId) {
  const version = azStyleClientVersions[client]
  if (!version) return null

  return utils.getAzStyleVersionNumber(peerId.substring(3, 7), version)
}

(() => {
  // add known clients alphabetically
  addAzStyle('A~', 'Ares', VER_AZ_THREE_DIGITS)
  addAzStyle('AG', 'Ares', VER_AZ_THREE_DIGITS)
  addAzStyle('AN', 'Ares', VER_AZ_FOUR_DIGITS)
  addAzStyle('AR', 'Ares')// Ares is more likely than ArcticTorrent
  addAzStyle('AV', 'Avicora')
  addAzStyle('AX', 'BitPump', VER_AZ_TWO_MAJ_TWO_MIN)
  addAzStyle('AT', 'Artemis')
  addAzStyle('AZ', 'Vuze', VER_AZ_FOUR_DIGITS)
  addAzStyle('BB', 'BitBuddy', '1.234')
  addAzStyle('BC', 'BitComet', VER_AZ_SKIP_FIRST_ONE_MAJ_TWO_MIN)
  addAzStyle('BE', 'BitTorrent SDK')
  addAzStyle('BF', 'BitFlu', VER_NONE)
  addAzStyle('BG', 'BTG', VER_AZ_FOUR_DIGITS)
  addAzStyle('bk', 'BitKitten (libtorrent)')
  addAzStyle('BR', 'BitRocket', '1.2(34)')
  addAzStyle('BS', 'BTSlave')
  addAzStyle('BT', 'BitTorrent', VER_AZ_THREE_DIGITS_PLUS_MNEMONIC)
  addAzStyle('BW', 'BitWombat')
  addAzStyle('BX', 'BittorrentX')
  addAzStyle('CB', 'Shareaza Plus')
  addAzStyle('CD', 'Enhanced CTorrent', VER_AZ_TWO_MAJ_TWO_MIN)
  addAzStyle('CT', 'CTorrent', '1.2.34')
  addAzStyle('DP', 'Propogate Data Client')
  addAzStyle('DE', 'Deluge', VER_AZ_DELUGE)
  addAzStyle('EB', 'EBit')
  addAzStyle('ES', 'Electric Sheep', VER_AZ_THREE_DIGITS)
  addAzStyle('FC', 'FileCroc')
  addAzStyle('FG', 'FlashGet', VER_AZ_SKIP_FIRST_ONE_MAJ_TWO_MIN)
  addAzStyle('FX', 'Freebox BitTorrent')
  addAzStyle('FT', 'FoxTorrent/RedSwoosh')
  addAzStyle('GR', 'GetRight', '1.2')
  addAzStyle('GS', 'GSTorrent')// TODO: Format is v"abcd"
  addAzStyle('HL', 'Halite', VER_AZ_THREE_DIGITS)
  addAzStyle('HN', 'Hydranode')
  addAzStyle('KG', 'KGet')
  addAzStyle('KT', 'KTorrent', VER_AZ_KTORRENT_STYLE)
  addAzStyle('LC', 'LeechCraft')
  addAzStyle('LH', 'LH-ABC')
  addAzStyle('LK', 'linkage', VER_AZ_THREE_DIGITS)
  addAzStyle('LP', 'Lphant', VER_AZ_TWO_MAJ_TWO_MIN)
  addAzStyle('LT', 'libtorrent (Rasterbar)', VER_AZ_THREE_ALPHANUMERIC_DIGITS)
  addAzStyle('lt', 'libTorrent (Rakshasa)', VER_AZ_THREE_ALPHANUMERIC_DIGITS)
  addAzStyle('LW', 'LimeWire', VER_NONE)// The "0001" bytes found after the LW commonly refers to the version of the BT protocol implemented. Documented here: http://www.limewire.org/wiki/index.php?title=BitTorrentRevision
  addAzStyle('MO', 'MonoTorrent')
  addAzStyle('MP', 'MooPolice', VER_AZ_THREE_DIGITS)
  addAzStyle('MR', 'Miro')
  addAzStyle('MT', 'MoonlightTorrent')
  addAzStyle('NE', 'BT Next Evolution', VER_AZ_THREE_DIGITS)
  addAzStyle('NX', 'Net Transport')
  addAzStyle('OS', 'OneSwarm', VER_AZ_FOUR_DIGITS)
  addAzStyle('OT', 'OmegaTorrent')
  addAzStyle('PC', 'CacheLogic', '12.3-4')
  addAzStyle('PT', 'Popcorn Time')
  addAzStyle('PD', 'Pando')
  addAzStyle('PE', 'PeerProject')
  addAzStyle('pX', 'pHoeniX')
  addAzStyle('qB', 'qBittorrent', VER_AZ_DELUGE)
  addAzStyle('QD', 'qqdownload')
  addAzStyle('RT', 'Retriever')
  addAzStyle('RZ', 'RezTorrent')
  addAzStyle('S~', 'Shareaza alpha/beta')
  addAzStyle('SB', 'SwiftBit')
  addAzStyle('SD', '\u8FC5\u96F7\u5728\u7EBF (Xunlei)')// Apparently, the English name of the client is "Thunderbolt".
  addAzStyle('SG', 'GS Torrent', VER_AZ_FOUR_DIGITS)
  addAzStyle('SN', 'ShareNET')
  addAzStyle('SP', 'BitSpirit', VER_AZ_THREE_DIGITS)// >= 3.6
  addAzStyle('SS', 'SwarmScope')
  addAzStyle('ST', 'SymTorrent', '2.34')
  addAzStyle('st', 'SharkTorrent')
  addAzStyle('SZ', 'Shareaza')
  addAzStyle('TG', 'Torrent GO')
  addAzStyle('TN', 'Torrent.NET')
  addAzStyle('TR', 'Transmission', VER_AZ_TRANSMISSION_STYLE)
  addAzStyle('TS', 'TorrentStorm')
  addAzStyle('TT', 'TuoTu', VER_AZ_THREE_DIGITS)
  addAzStyle('UL', 'uLeecher!')
  addAzStyle('UE', '\u00B5Torrent Embedded', VER_AZ_THREE_DIGITS_PLUS_MNEMONIC)
  addAzStyle('UT', '\u00B5Torrent', VER_AZ_THREE_DIGITS_PLUS_MNEMONIC)
  addAzStyle('UM', '\u00B5Torrent Mac', VER_AZ_THREE_DIGITS_PLUS_MNEMONIC)
  addAzStyle('UW', '\u00B5Torrent Web', VER_AZ_THREE_DIGITS_PLUS_MNEMONIC)
  addAzStyle('WD', 'WebTorrent Desktop', VER_AZ_WEBTORRENT_STYLE)// Go Webtorrent!! :)
  addAzStyle('WT', 'Bitlet')
  addAzStyle('WW', 'WebTorrent', VER_AZ_WEBTORRENT_STYLE)// Go Webtorrent!! :)
  addAzStyle('WY', 'FireTorrent')// formerly Wyzo.
  addAzStyle('VG', '\u54c7\u560E (Vagaa)', VER_AZ_FOUR_DIGITS)
  addAzStyle('XL', '\u8FC5\u96F7\u5728\u7EBF (Xunlei)')// Apparently, the English name of the client is "Thunderbolt".
  addAzStyle('XT', 'XanTorrent')
  addAzStyle('XF', 'Xfplay', VER_AZ_TRANSMISSION_STYLE)
  addAzStyle('XX', 'XTorrent', '1.2.34')
  addAzStyle('XC', 'XTorrent', '1.2.34')
  addAzStyle('ZT', 'ZipTorrent')
  addAzStyle('7T', 'aTorrent')
  addAzStyle('ZO', 'Zona', VER_AZ_FOUR_DIGITS)
  addAzStyle('#@', 'Invalid PeerID')

  addShadowStyle('A', 'ABC')
  addShadowStyle('O', 'Osprey Permaseed')
  addShadowStyle('Q', 'BTQueue')
  addShadowStyle('R', 'Tribler')
  addShadowStyle('S', 'Shad0w')
  addShadowStyle('T', 'BitTornado')
  addShadowStyle('U', 'UPnP NAT')

  addMainlineStyle('M', 'Mainline')
  addMainlineStyle('Q', 'Queen Bee')

  // Simple clients with no version number.
  addSimpleClient('\u00B5Torrent', '1.7.0 RC', '-UT170-')// http://forum.utorrent.com/viewtopic.php?pid=260927#p260927
  addSimpleClient('Azureus', '1', 'Azureus')
  addSimpleClient('Azureus', '2.0.3.2', 'Azureus', 5)
  addSimpleClient('Aria', '2', '-aria2-')
  addSimpleClient('BitTorrent Plus!', 'II', 'PRC.P---')
  addSimpleClient('BitTorrent Plus!', 'P87.P---')
  addSimpleClient('BitTorrent Plus!', 'S587Plus')
  addSimpleClient('BitTyrant (Azureus Mod)', 'AZ2500BT')
  addSimpleClient('Blizzard Downloader', 'BLZ')
  addSimpleClient('BTGetit', 'BG', 10)
  addSimpleClient('BTugaXP', 'btuga')
  addSimpleClient('BTugaXP', 'BTuga', 5)
  addSimpleClient('BTugaXP', 'oernu')
  addSimpleClient('Deadman Walking', 'BTDWV-')
  addSimpleClient('Deadman', 'Deadman Walking-')
  addSimpleClient('External Webseed', 'Ext')
  addSimpleClient('G3 Torrent', '-G3')
  addSimpleClient('GreedBT', '2.7.1', '271-')
  addSimpleClient('Hurricane Electric', 'arclight')
  addSimpleClient('HTTP Seed', '-WS')
  addSimpleClient('JVtorrent', '10-------')
  addSimpleClient('Limewire', 'LIME')
  addSimpleClient('Martini Man', 'martini')
  addSimpleClient('Pando', 'Pando')
  addSimpleClient('PeerApp', 'PEERAPP')
  addSimpleClient('SimpleBT', 'btfans', 4)
  addSimpleClient('Swarmy', 'a00---0')
  addSimpleClient('Swarmy', 'a02---0')
  addSimpleClient('Teeweety', 'T00---0')
  addSimpleClient('TorrentTopia', '346-')
  addSimpleClient('XanTorrent', 'DansClient')
  addSimpleClient('MediaGet', '-MG1')
  addSimpleClient('MediaGet', '2.1', '-MG21')

  /**
   * This is interesting - it uses Mainline style, except uses two characters instead of one.
   * And then - the particular numbering style it uses would actually break the way we decode
   * version numbers (our code is too hardcoded to "-x-y-z--" style version numbers).
   *
   * This should really be declared as a Mainline style peer ID, but I would have to
   * make my code more generic. Not a bad thing - just something I'm not doing right
   * now.
   */
  addSimpleClient('Amazon AWS S3', 'S3-')

  // Simple clients with custom version schemes
  // TODO: support custom version schemes
  addSimpleClient('BitTorrent DNA', 'DNA')
  addSimpleClient('Opera', 'OP')// Pre build 10000 versions
  addSimpleClient('Opera', 'O')// Post build 10000 versions
  addSimpleClient('Burst!', 'Mbrst')
  addSimpleClient('TurboBT', 'turbobt')
  addSimpleClient('BT Protocol Daemon', 'btpd')
  addSimpleClient('Plus!', 'Plus')
  addSimpleClient('XBT', 'XBT')
  addSimpleClient('BitsOnWheels', '-BOW')
  addSimpleClient('eXeem', 'eX')
  addSimpleClient('MLdonkey', '-ML')
  addSimpleClient('Bitlet', 'BitLet')
  addSimpleClient('AllPeers', 'AP')
  addSimpleClient('BTuga Revolution', 'BTM')
  addSimpleClient('Rufus', 'RS', 2)
  addSimpleClient('BitMagnet', 'BM', 2)// BitMagnet - predecessor to Rufus
  addSimpleClient('QVOD', 'QVOD')
  // Top-BT is based on BitTornado, but doesn't quite stick to Shadow's naming conventions,
  // so we'll use substring matching instead.
  addSimpleClient('Top-BT', 'TB')
  addSimpleClient('Tixati', 'TIX')
  // seems to have a sub-version encoded in following 3 bytes, not worked out how: "folx/1.0.456.591" : 2D 464C 3130 FF862D 486263574A43585F66314D5A
  addSimpleClient('folx', '-FL')
  addSimpleClient('\u00B5Torrent Mac', '-UM')
  addSimpleClient('\u00B5Torrent', '-UT') // UT 3.4+
})()
