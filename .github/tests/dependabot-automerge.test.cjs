const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const { join } = require('node:path');
const { test } = require('node:test');

const workflow = readFileSync(join(__dirname, '../workflows/dependabotautomerge.yml'), 'utf8').replace(/\r\n/g, '\n');
const script = workflow.split('          script: |\n')[1].replace(/^ {12}/gm, '');
const AsyncFunction = Object.getPrototypeOf(async function () {}).constructor;
const run = new AsyncFunction('github', 'context', 'core', script);
const names = [
  'PHP 8.1 Tests', 'PHP 8.2 Tests', 'PHP 8.3 Tests',
  'PHP 8.1 on ubuntu-latest', 'PHP 8.2 on ubuntu-latest',
  'PHP 8.3 on ubuntu-latest', 'dependency-review', 'CodeQL-Build', 'CodeQL', 'build'
];

async function simulate(options = {}) {
  const pr = {
    number: 123, user: { login: 'dependabot[bot]' }, state: 'open', draft: false,
    base: { ref: 'master' }, head: { sha: 'tested-sha', repo: { full_name: 'WWBN/AVideo' } },
    mergeable: true, ...options.pr
  };
  const checks = options.checks || names.map(name => ({
    name, status: 'completed', conclusion: 'success',
    app: { slug: name === 'CodeQL' ? 'github-advanced-security' : 'github-actions' }
  }));
  const merged = [];
  const failures = [];
  const errors = [];
  const warnings = [];
  const summary = [];
  let summaryText = '';
  const github = {
    rest: {
      pulls: {
        list: 'pulls', get: async () => ({ data: { ...pr, ...options.fresh } }),
        merge: async args => {
          if (options.error) throw options.error;
          merged.push(args);
          return { data: { merged: true, sha: 'merge-sha' } };
        }
      },
      checks: { listForRef: 'checks' },
      repos: { getCombinedStatusForRef: async () => ({ data: options.status || { total_count: 0, state: 'pending' } }) }
    },
    paginate: async (method, args) => {
      if (method === 'pulls') return [pr];
      assert.equal(args.ref, 'tested-sha');
      assert.equal(args.filter, 'latest');
      return [...checks, ...(options.extraChecks || [])];
    }
  };
  await run(github, { repo: { owner: 'WWBN', repo: 'AVideo' } }, {
    info() {}, warning: message => warnings.push(message), error: message => errors.push(message), setFailed: message => failures.push(message),
    summary: {
      addHeading() { return this; },
      addRaw(value) { summaryText += value; return this; },
      addList(items) { summary.push(...items); return this; },
      async write() { options.onSummary?.(summary, summaryText); }
    }
  });
  options.onWarnings?.(warnings);
  options.onErrors?.(errors);
  return { merged, failures };
}

test('merges the tested SHA after all CI passes, ignoring its own old failure', async () => {
  const result = await simulate({ extraChecks: [{
    name: 'dependabot', app: { slug: 'github-actions' }, status: 'completed', conclusion: 'failure'
  }] });
  assert.equal(result.merged.length, 1);
  assert.equal(result.merged[0].sha, 'tested-sha');
  assert.equal(result.merged[0].merge_method, 'merge');
  assert.deepEqual(result.failures, []);
});

for (const [label, pr] of Object.entries({
  human: { user: { login: 'maintainer' } }, draft: { draft: true },
  closed: { state: 'closed' }, otherBranch: { base: { ref: 'develop' } },
  fork: { head: { repo: { full_name: 'other/AVideo' } } },
  conflict: { mergeable: false }, unknownMergeability: { mergeable: null }
})) {
  test(`does not merge ${label}`, async () => assert.equal((await simulate({ pr })).merged.length, 0));
}

test('rechecks eligibility before merging', async () => {
  assert.equal((await simulate({ fresh: { draft: true } })).merged.length, 0);
});
test('missing checks do not count as success', async () => {
  assert.equal((await simulate({ checks: [] })).merged.length, 0);
});
for (const conclusion of ['failure', 'cancelled', null]) {
  test(`waits for an additional check with conclusion ${conclusion}`, async () => {
    const result = await simulate({ extraChecks: [{
      name: 'external-test', app: { slug: 'external' },
      status: conclusion ? 'completed' : 'in_progress', conclusion
    }] });
    assert.equal(result.merged.length, 0);
  });
}
test('failed duplicate CI is not hidden by a successful check with the same name', async () => {
  assert.equal((await simulate({ extraChecks: [{
    name: 'PHP 8.1 Tests', app: { slug: 'github-actions' }, status: 'completed', conclusion: 'failure'
  }] })).merged.length, 0);
});
test('failed commit status blocks merge', async () => {
  assert.equal((await simulate({ status: { total_count: 1, state: 'failure' } })).merged.length, 0);
});
test('head changes or branch rules defer merge without overriding them', async () => {
  for (const status of [405, 409]) {
    assert.deepEqual(await simulate({ error: { status, message: 'Blocked' } }), { merged: [], failures: [] });
  }
});
test('permission errors fail the workflow instead of silently succeeding', async () => {
  const result = await simulate({ error: { status: 403, message: 'Forbidden' } });
  assert.equal(result.merged.length, 0);
  assert.equal(result.failures.length, 1);
});

test('workflow permission restriction fails automation with actionable setup instructions', async () => {
  let warnings;
  let errors;
  let summary;
  let summaryText;
  const result = await simulate({
    error: {
      status: 403,
      message: 'refusing to allow a GitHub App to create or update workflow `.github/workflows/release.yml` without `workflows` permission'
    },
    onWarnings: value => { warnings = value; },
    onErrors: value => { errors = value; },
    onSummary: (value, text) => { summary = value; summaryText = text; }
  });
  assert.equal(result.merged.length, 0);
  assert.equal(result.failures.length, 1);
  assert.equal(warnings.length, 0);
  assert.equal(errors.length, 1);
  assert.match(errors[0], /#123: configure the DEPENDABOT_AUTOMERGE_TOKEN Actions secret/);
  assert.match(summaryText, /Contents and Workflows write access/);
  assert.match(summaryText, /workflow_dispatch on master/);
  assert.deepEqual(summary, ['https://github.com/WWBN/AVideo/pull/123']);
});

test('other workflow-related permission errors still fail', async () => {
  const result = await simulate({ error: {
    status: 403, message: 'Resource not accessible by integration: workflows'
  } });
  assert.equal(result.failures.length, 1);
});
