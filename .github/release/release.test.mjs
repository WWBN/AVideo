import assert from 'node:assert/strict';
import { execFileSync } from 'node:child_process';
import { mkdtempSync, rmSync, writeFileSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import { test } from 'node:test';
import { analyze, createPlan, latestStable, publishPlan, versionOf } from './release.mjs';

test('legacy tags normalize without resetting or including prereleases', () => {
  assert.equal(versionOf('29.0'), '29.0.0');
  assert.equal(versionOf('v8.2.3'), '8.2.3');
  assert.equal(versionOf('v8.2.3-rc.1'), null);
  assert.equal(latestStable([
    { tag_name: '29.0' }, { tag_name: '30.0', draft: true },
    { tag_name: '31.0', prerelease: true }, { tag_name: '26.0' }
  ]).tag_name, '29.0');
});

for (const [name, messages, expected] of [
  ['fix', ['fix: upload'], 'patch'],
  ['feature wins', ['fix: upload', 'feat: format'], 'minor'],
  ['breaking header', ['feat!: remove old endpoint'], 'major'],
  ['breaking body', ['fix: endpoint\n\nBREAKING CHANGE: removed old field'], 'major'],
  ['breaking maintenance', ['chore!: remove old runtime'], 'major'],
  ['legacy Encoder commits', ['Improve transfer handling'], 'patch'],
  ['unknown conventional type', ['update: transfer handling'], 'patch'],
  ['unknown breaking type', ['update!: remove old endpoint'], 'major'],
  ['dependencies', ['chore(deps): update library'], 'patch'],
  ['docs and tests', ['docs: guide', 'test: coverage', 'ci: workflow'], null],
  ['merge messages', ['Merge pull request #123 from example/feature'], null],
  ['empty range', [], null]
]) {
  test(name, async () => {
    assert.equal(await analyze(messages.map((message, i) => ({ message, hash: String(i) }))), expected);
  });
}

function fixture(t) {
  const cwd = mkdtempSync(join(tmpdir(), 'avideo-release-'));
  t.after(() => rmSync(cwd, { recursive: true, force: true }));
  const git = (...args) => execFileSync('git', args, { cwd, encoding: 'utf8' }).trim();
  git('init', '-q');
  git('config', 'user.name', 'Release Test');
  git('config', 'user.email', 'release-test@example.invalid');
  git('config', 'commit.gpgsign', 'false');
  const commit = (message, content = message) => {
    writeFileSync(join(cwd, 'example.txt'), content);
    git('add', 'example.txt');
    git('commit', '-qm', message);
    return git('rev-parse', 'HEAD');
  };
  const baseSha = commit('initial', 'base');
  git('tag', '29.0');
  const releases = [{ tag_name: '29.0', draft: false, prerelease: false }];
  const calls = [];
  const request = async (path, options = {}) => {
    calls.push({ path, ...options });
    if (path.includes('/releases?')) return releases;
    if (options.method === 'POST') return { html_url: 'https://github.com/WWBN/AVideo/releases/tag/v29.0.1' };
    return null;
  };
  return { cwd, baseSha, git, commit, releases, calls, request, repo: 'WWBN/AVideo' };
}

test('a feature reverted in the same range does not inflate a remaining fix', async () => {
  const hash = 'a'.repeat(40);
  assert.equal(await analyze([
    { hash: 'c'.repeat(40), message: 'fix: upload' },
    { hash: 'b'.repeat(40), message: `Revert "feat: temporary feature"\n\nThis reverts commit ${hash}.` },
    { hash, message: 'feat: temporary feature' }
  ]), 'patch');
});

test('plan uses the published legacy baseline and includes direct commits in notes', async t => {
  const f = fixture(t);
  const sha = f.commit('Improve upload handling');
  const plan = await createPlan({ ...f, sha });
  assert.equal(plan.tag, 'v29.0.1');
  assert.equal(plan.baseSha, f.baseSha);
  assert.match(plan.body, /Other changes/);
  assert.match(plan.body, /Improve upload handling/);
  assert.match(plan.body, /29\.0\.\.\.v29\.0\.1/);
  assert.match(plan.body, /avideo-release:/);
  assert.ok(f.calls.every(c => !c.method));
});

test('no modifications and a net revert produce no release', async t => {
  const f = fixture(t);
  assert.equal((await createPlan({ ...f, sha: f.baseSha })).publish, false);
  f.commit('fix: temporary');
  const sha = f.commit('fix: undo temporary', 'base');
  assert.equal((await createPlan({ ...f, sha })).publish, false);
});

test('documentation-only range produces no release', async t => {
  const f = fixture(t);
  assert.equal((await createPlan({ ...f, sha: f.commit('docs: guide') })).publish, false);
});

test('publication targets exactly the tested SHA and does not push master', async t => {
  const f = fixture(t);
  const plan = await createPlan({ ...f, sha: f.commit('fix: upload') });
  await publishPlan(plan, f);
  const posts = f.calls.filter(c => c.method === 'POST');
  assert.equal(posts.length, 1);
  assert.equal(posts[0].body.target_commitish, plan.sha);
  assert.equal(posts[0].body.tag_name, 'v29.0.1');
  assert.equal(posts[0].body.draft, false);
});

test('changed checkout or published baseline prevents publication', async t => {
  const f = fixture(t);
  const plan = await createPlan({ ...f, sha: f.commit('fix: upload') });
  f.commit('feat: later change');
  await assert.rejects(publishPlan(plan, f), /Checkout does not match/);
  f.git('checkout', '--detach', plan.sha);
  f.releases.push({ tag_name: 'v30.0.0' });
  f.git('tag', 'v30.0.0');
  await assert.rejects(publishPlan(plan, f), /Release plan changed/);
  assert.equal(f.calls.filter(c => c.method === 'POST').length, 0);
});

test('retry preserves an already published release, including editorial changes', async t => {
  const f = fixture(t);
  const plan = await createPlan({ ...f, sha: f.commit('fix: upload') });
  const edited = { html_url: 'published', body: 'Reviewed by Claude' };
  const request = async path => path.includes('/releases/tags/') ? edited : { object: { type: 'commit', sha: plan.sha } };
  assert.equal(await publishPlan(plan, { ...f, request }), edited);
});

test('partial tag publication is recovered only for the same tested commit', async t => {
  const f = fixture(t);
  const plan = await createPlan({ ...f, sha: f.commit('fix: upload') });
  const request = async (path, options) => path.includes('/git/ref/')
    ? { object: { type: 'commit', sha: plan.sha } } : f.request(path, options);
  await publishPlan(plan, { ...f, request });
  const conflict = async (path, options) => path.includes('/git/ref/')
    ? { object: { type: 'commit', sha: f.baseSha } } : f.request(path, options);
  await assert.rejects(publishPlan(plan, { ...f, request: conflict }), /Refusing to move/);
});

test('a failed GitHub request fails the run', async t => {
  const f = fixture(t);
  await assert.rejects(createPlan({ ...f, sha: f.baseSha, request: async () => { throw new Error('HTTP 403'); } }), /HTTP 403/);
});
