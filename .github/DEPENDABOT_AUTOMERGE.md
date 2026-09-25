# Dependabot auto-merge

`workflows/dependabotautomerge.yml` merges same-repository Dependabot PRs targeting
`master` after every required CI check succeeds. Failed or pending checks, conflicts,
drafts and branch rules still block merging. The merge request includes the tested
head SHA so a newer, untested commit cannot be merged by that run.

## One-time credential setup

The built-in `GITHUB_TOKEN` cannot merge PRs that change `.github/workflows/`, even
with `contents: write` and `pull-requests: write`. This blocked PRs #10620, #10622
and #10623 despite passing CI. A missing workflow permission now fails the run and
lists the affected PRs in its summary instead of reporting success.

Create a fine-grained personal access token for an account with write access,
selecting only `WWBN/AVideo`, with these repository permissions:

- Contents: read and write (merge).
- Workflows: read and write (merge workflow changes).
- Pull requests: read (inspect PRs).
- Checks: read (inspect check runs).
- Commit statuses: read (inspect commit statuses).

Save it as the repository **Actions secret** `DEPENDABOT_AUTOMERGE_TOKEN` under
Settings > Secrets and variables > Actions. Complete organization approval if
required and renew the token before it expires. Do not put the token in source
files, logs or PR comments.

The workflow prefers this secret and falls back to `GITHUB_TOKEN` when it is
unavailable, so ordinary dependency updates continue to work without setup.
Dependabot-triggered events can have restricted secret access; the hourly schedule
and a maintainer's manual dispatch can use the Actions secret and reconsider all
open Dependabot PRs. There is no need to expose this token to PR test jobs.

After the workflow change reaches `master` and the secret is configured, run:

```sh
gh workflow run dependabotautomerge.yml --repo WWBN/AVideo --ref master
```

This can merge **all eligible open Dependabot PRs**, including existing ones. The
write-enabled job never checks out or executes PR code. Keep that restriction and
all CI gates when changing the workflow. Merges authenticated with the dedicated
token can trigger the normal push workflows.

## FullCalendar updates

PR #10618 upgraded only `@fullcalendar/core` to 7.1.0 while the plugins remained on
6.1.21. `npm ci` correctly rejected their incompatible peer dependencies with
`ERESOLVE`; the failed Tests checks correctly prevented auto-merge.

Keep `@fullcalendar/*` updates grouped and ignore major version updates until the
calendar consumers and packages are migrated together. Minor and patch updates
remain eligible. Do not use `--force` or `--legacy-peer-deps` to bypass this failure.
This policy does not repair or merge the already-open incompatible PR.

## Local verification

```sh
node --test .github/tests/*.test.cjs
node tests/Integration/frontendDependencies.js
```
