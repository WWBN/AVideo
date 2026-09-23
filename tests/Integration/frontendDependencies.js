// Run with Node after npm ci. Checks the dependency overrides used by CI.
const assert = require('node:assert/strict');
const {createRequire} = require('node:module');

// Resolve from PouchDB, so a nested, unpatched copy cannot pass this check.
const pouchRequire = createRequire(require.resolve('pouchdb'));
const uuid = pouchRequire('uuid');
assert.equal(uuid.version(uuid.v4()), 4, 'PouchDB needs the CommonJS v4 API');
assert.throws(() => uuid.v5('test', uuid.v5.DNS, new Uint8Array(8)), RangeError);

const coreVersion = require('@fullcalendar/core/package.json').version;
for (const plugin of ['daygrid', 'interaction', 'list', 'timegrid', 'timeline']) {
    const manifest = require(`@fullcalendar/${plugin}/package.json`);
    assert.equal(manifest.version, coreVersion,
        `${plugin} and FullCalendar core must be upgraded together`);
    assert.ok(require(`@fullcalendar/${plugin}`).default,
        `${plugin} must load against the installed core`);
}
console.log('PASS: PouchDB UUID compatibility, buffer bounds, and FullCalendar peer versions.');
