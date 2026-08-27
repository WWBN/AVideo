# AVideo Agent Instructions

## Mandatory Canonical Security Policy

For every task related to security in any way—including security advisories, vulnerability reports, audits, reviews, hardening, authentication, authorization, access control, sensitive-data exposure, secrets, PII, SQL injection, XSS, CSRF, SSRF, command execution, uploads, path traversal, or a proposed security fix—read [`.github/prompts/avideo-security-advisory-triage.prompt.md`](.github/prompts/avideo-security-advisory-triage.prompt.md) completely before analyzing the issue, reaching conclusions, suggesting changes, or modifying code.

Treat that prompt as the canonical and authoritative repository policy for security investigation, classification, regression analysis, fix decisions, testing, and reporting. Follow all applicable requirements from it. If another repository instruction conflicts with that prompt on a security matter, the canonical security prompt takes precedence; continue following all non-conflicting repository instructions.

Do not copy or restate the detailed security policy in this file. Update the canonical prompt only, so GitHub Copilot and Codex use the same source of truth.
