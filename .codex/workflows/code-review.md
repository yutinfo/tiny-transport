# Workflow: Code Review

Use this workflow when reviewing local changes or a proposed patch.

## Steps

1. Read `AGENTS.md`, `CODEX.md`, `.codex/project-context.md`, and `.codex/guardrails.md`.
2. Run `git status --short`.
3. Inspect relevant diffs with `git diff`.
4. Focus on correctness, security, access control, data safety, backward compatibility, generated asset handling, and missing tests.
5. Report findings first, ordered by severity.
6. Reference exact files and line numbers when possible.
7. Include open questions or assumptions.
8. Add a brief summary only after findings.

## Review Checklist

- Does the change solve the requested problem?
- Did it change unrelated behavior?
- Are role and ownership checks preserved?
- Is validation complete at request boundaries?
- Are migrations reversible?
- Are generated assets avoided unless intentionally rebuilt?
- Are tests added or updated for changed behavior?
- Were relevant checks run?

## Output Shape

```md
Findings:
- [severity] file:line - issue and impact

Open questions:
- ...

Summary:
- ...

Checks:
- ...
```
