When the target is Encoder code, use the source at
`D:\git\htdocs\AVideo\.compose\encoder\`; do not substitute similarly named Streamer files from
the repository root.
---
name: "AVideo SonarQube Review"
description: "Review AVideo code for SonarQube quality and security standards. Checks duplication, complexity, unused code, unsafe input handling, empty catch blocks, weak typing, and security hotspots."
argument-hint: "File path or plugin name to review for SonarQube issues"
agent: agent
---

# AVideo SonarQube Code Review

Review the following file or feature for SonarQube readiness: **{{TARGET_FILE_OR_FEATURE}}**

Read the target file(s) fully before beginning the review.

---

## SonarQube Quality Categories

### 1. Code Duplication

Check for:
- Functions or SQL queries that are identical or near-identical to code in other files
- Copy-pasted AJAX handler logic that could be extracted (check `objects/functions*.php`)
- Repeated DB connection or transaction boilerplate outside `sqlDAL`
- Repeated HTML/Bootstrap markup that should use a shared template or helper

For each duplication found:
- Cite the original file and the duplicate location
- Suggest the existing helper or function that should be reused instead
- Do not suggest extracting new abstractions unless the duplication appears 3+ times

---

### 2. Cyclomatic Complexity

Flag functions that:
- Have more than 10 conditional branches (`if`, `switch`, `?:`, `&&`, `||`)
- Have nesting deeper than 4 levels
- Are longer than ~50 lines of logic (excluding comments)
- Mix multiple responsibilities (fetching data AND rendering HTML AND validating input)

For each flagged function:
- Describe the complexity issue
- Suggest how to simplify (split into smaller functions, early returns, etc.)
- Keep suggestions consistent with AVideo's existing patterns

---

### 3. Unused Code

Check for:
- Variables that are assigned but never read
- Parameters that are never used in the function body
- Functions or methods that are never called
- Commented-out code blocks that have been dead for a long time
- `require_once` / `include` statements for files that contribute nothing

Flag each with location and suggested action (remove or document why it is kept).

---

### 4. Unsafe Input Handling (Security Hotspots)

Check every occurrence of:
- `$_GET`, `$_POST`, `$_REQUEST`, `$_COOKIE`, `$_FILES` — are they sanitized before use?
- `eval()`, `exec()`, `system()`, `shell_exec()`, `passthru()` — are inputs validated?
- `include`, `require`, `include_once`, `require_once` with variable paths — path traversal risk?
- `file_get_contents()`, `fopen()`, `readfile()` with user-supplied paths
- `header('Location: ' . $userInput)` — open redirect risk?
- `preg_replace()` with `e` modifier (deprecated, code execution)

For each hotspot:
- State the risk (SQL injection, XSS, path traversal, command injection, etc.)
- Cite the line and the unsafe pattern
- Show the corrected version using AVideo's existing sanitization helpers

---

### 5. Empty or Swallowed Catch Blocks

Flag all patterns like:
```php
} catch (Exception $e) {
    // nothing here
}

} catch (\Throwable $th) {
    // silent
}
```

Each should at minimum:
```php
} catch (\Throwable $th) {
    _error_log($th->getMessage());
}
```

Also flag overly broad catches that hide logic errors — these should be specific where appropriate.

---

### 6. Weak Typing & Unclear Variables

Check for:
- Loose comparisons (`==`, `!=`) where strict (`===`, `!==`) should be used
- Variables named `$a`, `$b`, `$x`, `$temp`, `$data` without context
- Functions returning mixed types (sometimes `false`, sometimes an array, sometimes `null`) without documentation
- Type-juggling issues (e.g., `0 == "string"` evaluating to `true`)

For AVideo PHP:
- Integer IDs must always be cast: `(int)$_POST['id']` or `intval()`
- Boolean checks on DB query results should use `!== false` not `!= false`

---

### 7. Security Hotspots (OWASP Top 10 Relevant)

Check specifically for:

**A01 - Broken Access Control**
- Endpoints that do not call `User::isLogged()` or `User::isAdmin()`
- Missing ownership validation (user accessing another user's resource)
- Permission denials using raw `die(json_encode(['error'=>true,...]))` instead of `forbiddenPage()` (bypasses HTTP 403 and logging)

**A03 - Injection**
- SQL built with string concatenation instead of `sqlDAL` prepared statements
- Shell commands (`exec()`, `system()`) with unsanitized input

**A05 - Security Misconfiguration**
- PHP error display enabled (`display_errors = On`) in production code
- Hardcoded development URLs or credentials

**A07 - Identification and Authentication Failures**
- Session management bypasses or custom session handling outside the existing framework

**A10 - Server-Side Request Forgery (SSRF)**
- `file_get_contents()`, `curl` calls using user-supplied URLs without validation

---

## Output Format

For each issue found, report:

```
[CATEGORY] Severity: Critical / Major / Minor / Info
File: path/to/file.php  Line: NN
Issue: Description of the problem
Current code:
  <relevant snippet>
Suggested fix:
  <corrected snippet>
```

End with:
- **Issues by Category**: count of Critical / Major / Minor / Info per category
- **Top 3 Priorities**: the three most impactful fixes to address first
- **SonarQube Readiness Score**: Not Ready / Needs Work / Mostly Ready / Ready
