'use strict'

module.exports = function supports (...manifests) {
  const manifest = manifests.reduce((acc, m) => Object.assign(acc, m), {})

  return Object.assign(manifest, {
    // Features of abstract-leveldown
    bufferKeys: manifest.bufferKeys || false,
    snapshots: manifest.snapshots || false,
    permanence: manifest.permanence || false,
    seek: manifest.seek || false,
    clear: manifest.clear || false,
    getMany: manifest.getMany || false,
    keyIterator: manifest.keyIterator || false,
    valueIterator: manifest.valueIterator || false,
    iteratorNextv: manifest.iteratorNextv || false,
    iteratorAll: manifest.iteratorAll || false,

    // Features of abstract-leveldown that levelup doesn't have
    status: manifest.status || false,
    idempotentOpen: manifest.idempotentOpen || false,
    passiveOpen: manifest.passiveOpen || false,
    serialize: manifest.serialize || false,

    // Features of disk-based implementations
    createIfMissing: manifest.createIfMissing || false,
    errorIfExists: manifest.errorIfExists || false,

    // Features of level(up) that abstract-leveldown doesn't have yet
    deferredOpen: manifest.deferredOpen || false,
    openCallback: manifest.openCallback || false,
    promises: manifest.promises || false,
    streams: manifest.streams || false,
    encodings: maybeObject(manifest.encodings),
    events: maybeObject(manifest.events),

    // Methods that are not part of abstract-leveldown or levelup
    additionalMethods: Object.assign({}, manifest.additionalMethods)
  })
}

function maybeObject (value) {
  return !value ? false : Object.assign({}, value)
}
