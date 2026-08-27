# AVideo Agent Instructions

## Mandatory Reuse of Copilot Instructions and Prompts

For every task in this repository, before analyzing the request, planning work, suggesting changes, reviewing code, or modifying files:

1. Read [`.github/copilot-instructions.md`](.github/copilot-instructions.md) completely and treat it as the canonical repository-wide guidance.
2. Enumerate every `.github/prompts/*.prompt.md` file and read the YAML front matter (`name`, `description`, and any usage hint) from each one.
3. Select every prompt whose `name` or `description` matches the current task, then read each selected prompt completely before continuing. A prompt explicitly named or linked by the user is always selected.
4. Read and follow the relevant `.github/instructions/*.instructions.md` files referenced by `copilot-instructions.md` when their scope matches the task or the files being inspected or changed.

Do not load the full body of prompts that are unrelated to the current task; the prompt front matter is the routing catalog. When multiple prompts apply, follow all non-conflicting requirements and let the most task-specific prompt govern its workflow and output. For any security-related task, `.github/prompts/avideo-security-advisory-triage.prompt.md` is mandatory and takes precedence for security investigation, classification, regression analysis, fix decisions, testing, and reporting.

The files under `.github/` are the source of truth for both GitHub Copilot and Codex. Do not copy or restate their detailed rules in `AGENTS.md`. Update the applicable Copilot instruction or prompt file so both agents receive the same future changes.

If a required instruction or selected prompt cannot be read completely, do not continue the affected work until the missing guidance is available; report the problem explicitly.
