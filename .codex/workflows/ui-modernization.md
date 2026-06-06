# Workflow: UI Modernization

Use this workflow when improving Blade, Bootstrap, AdminLTE, Sass, or responsive behavior without changing backend logic.

## Steps

1. Read `AGENTS.md`, `CODEX.md`, `.codex/project-context.md`, and `.codex/guardrails.md`.
2. Inspect the active Blade layout and relevant partials.
3. Inspect `resources/sass/app.scss` and imported Sass partials.
4. Preserve Bootstrap 4 and AdminLTE 3 conventions.
5. Prefer shared Sass improvements before page-level style duplication.
6. Keep form names, route targets, CSRF fields, and existing jQuery hooks intact.
7. Make incremental visual improvements.
8. Run `npm run dev` when Sass or frontend source changed.
9. Manually inspect responsive behavior when possible.
10. Summarize changed screens, asset checks, and residual UI risk.

## Design Rules

- Keep cards, forms, tables, nav, alerts, and modals compatible with Bootstrap/AdminLTE.
- Do not introduce Tailwind or a new UI framework.
- Do not hand edit generated public assets.
- For driver screens, use a mobile-first layout and avoid the admin sidebar shell.
- Keep text readable and touch targets practical on mobile.

## Useful Checks

```bash
npm run dev
git diff -- resources/sass resources/views
```
