# Prompt: Execute Task

```text
Read AGENTS.md, CODEX.md, .codex/project-context.md, and .codex/guardrails.md first.
Use .codex/workflows/<workflow>.md.
Execute the approved task in tasks/<task-file>.md.

Execution rules:
- Keep changes scoped to the task.
- Preserve existing stack and conventions.
- Use Docker for PHP commands.
- Run focused validation first.
- Run broader validation if shared behavior changed.
- Review your own diff before final response.

Final response:
- Summary
- Files changed
- Commands run and results
- Risks
- Next step
```
