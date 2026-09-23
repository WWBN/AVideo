// Release orchestration for the existing two-part tags; analysis and notes use
// semantic-release's maintained plugins. No application/database version writes.
import { execFileSync } from 'node:child_process';
import { appendFileSync, readFileSync, writeFileSync } from 'node:fs';
import { resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import semver from 'semver';
import { analyzeCommits } from '@semantic-release/commit-analyzer';
import { generateNotes } from '@semantic-release/release-notes-generator';

const logger = { log() {}, error() {} };
const types = [
  { type: 'feat', section: 'Features' },
  { type: 'fix', section: 'Fixes' },
  { type: 'perf', section: 'Performance' },
  { type: 'deps', section: 'Dependencies' },
  { type: 'revert', section: 'Reverts' },
  { type: 'other', section: 'Other changes' },
  ...['docs', 'test', 'ci', 'chore', 'style', 'refactor', 'build'].map(type => ({ type, hidden: true }))
];

export function versionOf(tag) {
  const match = /^v?(\d+)\.(\d+)(?:\.(\d+))?$/.exec(tag);
  return match ? semver.valid(`${match[1]}.${match[2]}.${match[3] || '0'}`) : null;
}

export function latestStable(releases) {
  return releases.filter(r => !r.draft && !r.prerelease && versionOf(r.tag_name))
    .sort((a, b) => semver.rcompare(versionOf(a.tag_name), versionOf(b.tag_name)))[0];
}

export function normalizeCommits(commits) {
  return commits.filter(c => !/^Merge\b/.test(c.message)).map(c => {
    const header = /^([a-z]+)(?:\([^\r\n)]+\))?(!)?: /.exec(c.message);
    const known = (header && types.some(type => type.type === header[1]))
      || /^Revert\b[\s\S]*This reverts commit [a-f0-9]+\./i.test(c.message);
    return {
      ...c,
      // Keep unclassified direct commits visible. Never guess a breaking change.
      message: known ? c.message : `other${header?.[2] || ''}: ${c.message}`
    };
  });
}

export async function analyze(commits) {
  return analyzeCommits({
    preset: 'conventionalcommits',
    releaseRules: [
      { breaking: true, release: 'major' },
      { type: 'deps', release: 'patch' },
      { type: 'other', release: 'patch' },
      { type: 'chore', scope: 'deps', release: 'patch' },
      { type: 'build', scope: 'deps', release: 'patch' }
    ]
  }, { commits: normalizeCommits(commits), logger });
}

function git(cwd, ...args) {
  return execFileSync('git', args, { cwd, encoding: 'utf8', maxBuffer: 32 * 1024 * 1024 }).trim();
}

export async function githubRequest(path, { method = 'GET', body } = {}) {
  const token = process.env.GH_TOKEN || process.env.GITHUB_TOKEN;
  const response = await fetch(`https://api.github.com${path}`, {
    method,
    headers: {
      Accept: 'application/vnd.github+json',
      'X-GitHub-Api-Version': '2022-11-28',
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
      ...(body ? { 'Content-Type': 'application/json' } : {})
    },
    ...(body ? { body: JSON.stringify(body) } : {}),
    signal: AbortSignal.timeout(60000)
  });
  if (response.status === 404) return null;
  if (!response.ok) throw new Error(`GitHub ${method} ${path}: HTTP ${response.status}`);
  return response.json();
}

async function releasesFor(repo, request) {
  const releases = [];
  for (let page = 1; ; page++) {
    const batch = await request(`/repos/${repo}/releases?per_page=100&page=${page}`);
    if (!Array.isArray(batch)) throw new Error('Cannot read releases.');
    releases.push(...batch);
    if (batch.length < 100) return releases;
  }
}

export async function createPlan({ repo, sha, cwd = process.cwd(), request = githubRequest }) {
  if (!/^WWBN\/(AVideo|AVideo-Encoder)$/.test(repo)) throw new Error('Unexpected repository.');
  if (!/^[a-f0-9]{40}$/.test(sha)) throw new Error('An exact commit SHA is required.');
  if (git(cwd, 'rev-parse', 'HEAD') !== sha) throw new Error('Checkout does not match the candidate SHA.');
  const baseline = latestStable(await releasesFor(repo, request));
  if (!baseline) throw new Error('No stable numeric release found; establish an explicit baseline first.');
  const baseSha = git(cwd, 'rev-parse', `refs/tags/${baseline.tag_name}^{commit}`);
  git(cwd, 'merge-base', '--is-ancestor', baseSha, sha);
  const plan = { schema: 1, repo, sha, baseTag: baseline.tag_name, baseSha, publish: false };
  // A reverted set of changes must not produce an empty release.
  if (!git(cwd, 'diff', '--name-only', baseSha, sha)) return plan;
  const entries = git(cwd, 'log', '--no-merges', '--format=%H%x00%B%x00', `${baseSha}..${sha}`).split('\0');
  const commits = [];
  for (let i = 0; i + 1 < entries.length; i += 2) {
    commits.push({ hash: entries[i].trim(), message: entries[i + 1].trim() });
  }
  const releaseType = await analyze(commits);
  if (!releaseType) return plan;
  const version = semver.inc(versionOf(baseline.tag_name), releaseType);
  const tag = `v${version}`;
  const normalized = normalizeCommits(commits).map(c => ({
    ...c,
    message: c.message.replace(/^(?:chore|build)\(deps\): /, 'deps: ')
  }));
  const notes = await generateNotes({ preset: 'conventionalcommits', presetConfig: { types } }, {
    cwd, logger, commits: normalized,
    options: { repositoryUrl: `https://github.com/${repo}` },
    lastRelease: { version: versionOf(baseline.tag_name), gitTag: baseline.tag_name, gitHead: baseSha },
    nextRelease: { version, gitTag: tag, gitHead: sha }
  });
  const marker = `<!-- avideo-release:${JSON.stringify({ schema: 1, sha, baseTag: baseline.tag_name, baseSha })} -->`;
  const body = `${notes.trim()}\n\nRelease commit: [\`${sha.slice(0, 12)}\`](https://github.com/${repo}/commit/${sha})\n\n${marker}\n`;
  if (body.length > 120000) throw new Error('Release notes are too large; refusing to truncate them silently.');
  return { ...plan, publish: true, releaseType, version, tag, body };
}

export async function publishPlan(plan, { cwd = process.cwd(), request = githubRequest } = {}) {
  if (!plan.publish) return null;
  // Recalculate from the tested checkout, never trust an altered artifact or a
  // newly published baseline. A manual concurrent release makes this run stop.
  const existing = await request(`/repos/${plan.repo}/releases/tags/${encodeURIComponent(plan.tag)}`);
  if (existing) {
    const ref = await request(`/repos/${plan.repo}/git/ref/tags/${encodeURIComponent(plan.tag)}`);
    if (!existing.draft && !existing.prerelease && ref?.object?.type === 'commit' && ref.object.sha === plan.sha) {
      return existing; // Idempotent retry; preserve subsequent editorial edits.
    }
    throw new Error('Release/tag already exists with different content or state.');
  }
  const fresh = await createPlan({ repo: plan.repo, sha: plan.sha, cwd, request });
  for (const key of ['schema', 'repo', 'sha', 'baseTag', 'baseSha', 'publish', 'version', 'tag', 'releaseType']) {
    if (fresh[key] !== plan[key]) throw new Error(`Release plan changed (${key}); run the workflow again.`);
  }
  const ref = await request(`/repos/${plan.repo}/git/ref/tags/${encodeURIComponent(plan.tag)}`);
  if (ref && (ref.object.type !== 'commit' || ref.object.sha !== plan.sha)) {
    throw new Error('Refusing to move an existing tag.');
  }
  // GitHub creates the missing tag at the exact tested SHA with the release.
  // A tag left by a partial attempt is accepted only when it matches that SHA.
  const release = await request(`/repos/${plan.repo}/releases`, {
    method: 'POST', body: {
      tag_name: plan.tag, target_commitish: plan.sha, name: plan.tag,
      body: fresh.body, draft: false, prerelease: false, make_latest: 'true'
    }
  });
  if (!release?.html_url) throw new Error('GitHub did not return a published release.');
  return release;
}

async function main() {
  const cwd = resolve(fileURLToPath(new URL('../..', import.meta.url)));
  const file = resolve(cwd, '.github/release/release-plan.json');
  if (process.argv[2] === 'plan') {
    const plan = await createPlan({
      cwd, repo: process.env.GITHUB_REPOSITORY,
      sha: process.env.GITHUB_SHA || git(cwd, 'rev-parse', 'HEAD')
    });
    writeFileSync(file, JSON.stringify(plan, null, 2) + '\n');
    if (process.env.GITHUB_OUTPUT) appendFileSync(process.env.GITHUB_OUTPUT, `publish=${plan.publish}\n`);
    const summary = plan.publish ? `Proposed release: ${plan.tag}\n\n${plan.body}` : 'No publishable changes since the last stable release.\n';
    if (process.env.GITHUB_STEP_SUMMARY) appendFileSync(process.env.GITHUB_STEP_SUMMARY, summary);
    console.log(summary);
  } else if (process.argv[2] === 'publish') {
    const plan = JSON.parse(readFileSync(file, 'utf8'));
    if (plan.repo !== process.env.GITHUB_REPOSITORY || plan.sha !== process.env.GITHUB_SHA) {
      throw new Error('Plan does not belong to this workflow candidate.');
    }
    console.log((await publishPlan(plan, { cwd }))?.html_url || 'Nothing to publish.');
  } else {
    throw new Error('Use plan (read-only) or publish (after all tests pass).');
  }
}

if (process.argv[1] && resolve(process.argv[1]) === fileURLToPath(import.meta.url)) {
  main().catch(error => { console.error(error.message); process.exitCode = 1; });
}
