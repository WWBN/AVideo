module.exports = {
  isAzStyle (peerId) {
    if (peerId.charAt(0) !== '-') return false
    if (peerId.charAt(7) === '-') return true

    /**
     * Hack for FlashGet - it doesn't use the trailing dash.
     * Also, LH-ABC has strayed into "forgetting about the delimiter" territory.
     *
     * In fact, the code to generate a peer ID for LH-ABC is based on BitTornado's,
     * yet tries to give an Az style peer ID... oh dear.
     *
     * BT Next Evolution seems to be in the same boat as well.
     *
     * KTorrent 3 appears to use a dash rather than a final character.
     */
    if (['FG', 'LH', 'NE', 'KT', 'SP'].includes(peerId.substring(1, 3))) return true

    return false
  },

  /**
   * Checking whether a peer ID is Shadow style or not is a bit tricky.
   *
   * The BitTornado peer ID convention code is explained here:
   *   http://forums.degreez.net/viewtopic.php?t=7070
   *
   * The main thing we are interested in is the first six characters.
   * Although the other characters are base64 characters, there's no
   * guarantee that other clients which follow that style will follow
   * that convention (though the fact that some of these clients use
   * BitTornado in the core does blur the lines a bit between what is
   * "style" and what is just common across clients).
   *
   * So if we base it on the version number information, there's another
   * problem - there isn't the use of absolute delimiters (no fixed dash
   * character, for example).
   *
   * There are various things we can do to determine how likely the peer
   * ID is to be of that style, but for now, I'll keep it to a relatively
   * simple check.
   *
   * We'll assume that no client uses the fifth version digit, so we'll
   * expect a dash. We'll also assume that no client has reached version 10
   * yet, so we expect the first two characters to be "letter,digit".
   *
   * We've seen some clients which don't appear to contain any version
   * information, so we need to allow for that.
   */
  isShadowStyle (peerId) {
    if (peerId.charAt(5) !== '-') return false
    if (!isLetter(peerId.charAt(0))) return false
    if (!(isDigit(peerId.charAt(1)) || peerId.charAt(1) === '-')) return false

    // Find where the version number string ends.
    let lastVersionNumberIndex = 4
    for (; lastVersionNumberIndex > 0; lastVersionNumberIndex--) {
      if (peerId.charAt(lastVersionNumberIndex) !== '-') break
    }

    // For each digit in the version string, check if it is a valid version identifier.
    for (let i = 1; i <= lastVersionNumberIndex; i++) {
      const c = peerId.charAt(i)
      if (c === '-') return false
      if (isAlphaNumeric(c) === null) return false
    }

    return true
  },

  isMainlineStyle (peerId) {
    /**
     * One of the following styles will be used:
     *   Mx-y-z--
     *   Mx-yy-z-
     */
    return peerId.charAt(2) === '-' && peerId.charAt(7) === '-' &&
      (peerId.charAt(4) === '-' || peerId.charAt(5) === '-')
  },

  isPossibleSpoofClient (peerId) {
    return peerId.endsWith('UDP0') || peerId.endsWith('HTTPBT')
  },

  decodeNumericValueOfByte,

  getAzStyleVersionNumber (peerId, version) {
    if (typeof version === 'function') {
      return version(peerId)
    }
    return null
  },

  getShadowStyleVersionNumber (peerId) {
    // TODO
    return null
  },

  decodeBitSpiritClient (peerId, buffer) {
    if (peerId.substring(2, 4) !== 'BS') return null
    let version = `${buffer[1]}`
    if (version === '0') version = 1

    return {
      client: 'BitSpirit',
      version
    }
  },

  decodeBitCometClient (peerId, buffer) {
    let modName = ''
    if (peerId.startsWith('exbc')) modName = ''
    else if (peerId.startsWith('FUTB')) modName = '(Solidox Mod)'
    else if (peerId.startsWith('xUTB')) modName = '(Mod 2)'
    else return null

    const isBitlord = (peerId.substring(6, 10) === 'LORD')

    // Older versions of BitLord are of the form x.yy, whereas new versions (1 and onwards),
    // are of the form x.y. BitComet is of the form x.yy
    const clientName = (isBitlord) ? 'BitLord' : 'BitComet'
    const majVersion = decodeNumericValueOfByte(buffer[4])
    const minVersionLength = (isBitlord && majVersion !== '0' ? 1 : 2)

    return {
      client: clientName + (modName ? ` ${modName}` : ''),
      version: `${majVersion}.${decodeNumericValueOfByte(buffer[5], minVersionLength)}`
    }
  },

  identifyAwkwardClient (peerId, buffer) {
    let firstNonZeroIndex = 20
    let i

    for (i = 0; i < 20; ++i) {
      if (buffer[i] > 0) {
        firstNonZeroIndex = i
        break
      }
    }

    // Shareaza check
    if (firstNonZeroIndex === 0) {
      let isShareaza = true
      for (i = 0; i < 16; ++i) {
        if (buffer[i] === 0) {
          isShareaza = false
          break
        }
      }

      if (isShareaza) {
        for (i = 16; i < 20; ++i) {
          if (buffer[i] !== (buffer[i % 16] ^ buffer[15 - (i % 16)])) {
            isShareaza = false
            break
          }
        }

        if (isShareaza) return { client: 'Shareaza' }
      }
    }

    if (firstNonZeroIndex === 9 && buffer[9] === 3 && buffer[10] === 3 && buffer[11] === 3) { return { client: 'I2PSnark' } }

    if (firstNonZeroIndex === 12 && buffer[12] === 97 && buffer[13] === 97) { return { client: 'Experimental', version: '3.2.1b2' } }

    if (firstNonZeroIndex === 12 && buffer[12] === 0 && buffer[13] === 0) { return { client: 'Experimental', version: '3.1' } }

    if (firstNonZeroIndex === 12) { return { client: 'Mainline' } }

    return null
  }
}

//
// Private helper functions for the public utility functions
//

function isDigit (s) {
  const code = s.charCodeAt(0)
  return code >= '0'.charCodeAt(0) && code <= '9'.charCodeAt(0)
}

function isLetter (s) {
  const code = s.toLowerCase().charCodeAt(0)
  return code >= 'a'.charCodeAt(0) && code <= 'z'.charCodeAt(0)
}

function isAlphaNumeric (s) {
  return isDigit(s) || isLetter(s) || s === '.'
}

function decodeNumericValueOfByte (b, minDigits = 0) {
  let result = `${b & 0xff}`
  while (result.length < minDigits) { result = `0${result}` }
  return result
}
