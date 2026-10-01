const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const { join } = require('node:path');
const { test } = require('node:test');

// .github/release only uses @semantic-release/commit-analyzer and release-notes-generator as
// libraries. Installing their semantic-release peer pulls the whole npm CLI, whose bundled
// dependencies (undici, ip-address, brace-expansion) Dependabot cannot update, so those security
// alerts could never be fixed or auto-merged. This test makes CI fail, and auto-merge wait, if a
// regenerated lockfile brings them back.
const release = join(__dirname, '../release');

test('release tooling does not install semantic-release peers', () => {
  const npmrc = readFileSync(join(release, '.npmrc'), 'utf8');
  assert.match(npmrc, /^legacy-peer-deps=true$/m);

  const lock = JSON.parse(readFileSync(join(release, 'package-lock.json'), 'utf8'));
  const installed = Object.keys(lock.packages || {});
  for (const name of ['semantic-release', 'npm', '@semantic-release/npm', '@semantic-release/github']) {
    assert.ok(!installed.includes(`node_modules/${name}`), `${name} must not be in .github/release/package-lock.json`);
  }
});

test('release script keeps importing only the analyzer libraries', () => {
  const script = readFileSync(join(release, 'release.mjs'), 'utf8');
  assert.doesNotMatch(script, /from ['"]semantic-release['"]/);
  assert.match(script, /from '@semantic-release\/commit-analyzer'/);
  assert.match(script, /from '@semantic-release\/release-notes-generator'/);
});
