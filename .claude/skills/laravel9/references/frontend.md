# Frontend — Blade, AdminLTE 3 / Bootstrap 4, Sass, assets

## Stack reality
- Blade templates under `resources/views/`. Admin screens: `resources/views/admin/`.
  Driver screens: `resources/views/driver/` (mobile-first, **not** the admin
  sidebar shell). Login/auth views are separate.
- UI kit: **AdminLTE 3.1 + Bootstrap 4.6 + jQuery 3.6 + Font Awesome 4**. This is
  Bootstrap **4**, not 5 — use `data-toggle`/`data-target` (not `data-bs-*`),
  `.form-group`, `.ml-2`/`.mr-2` spacing utilities, etc.

## Blade rules
- Reuse existing layouts and partials before adding a new wrapper. Look for the
  admin layout, the driver layout, and shared partials first.
- Match the existing AdminLTE markup for cards, forms, tables, nav, alerts,
  modals, and buttons — copy the shape that's already in the codebase.
- Extract a partial when the same fragment appears in multiple views.
- Keep output escaped: `{{ $value }}`. Only use `{!! !!}` for content you
  control and have reviewed.

## Sass / CSS
- Source lives in `resources/sass/`: `app.scss` imports `_variables.scss`,
  `_modern-ui.scss`, `_login.scss`, `_driver.scss`.
- Prefer a shared Sass improvement over page-specific CSS. Put driver-only rules
  in `_driver.scss`, login-only in `_login.scss`.

## jQuery
- Preserve existing jQuery behavior unless the task explicitly changes it. The
  pages wire up via `public/js/app.js` (built from `resources/js`).

## Asset pipeline (Laravel Mix 6 / Webpack 5) — runs on the HOST
```bash
npm install        # once
npm run dev        # build for local
npm run watch      # rebuild on change while developing
npm run prod        # production build (minified, versioned)
```
- **Never hand-edit** `public/css/app.css`, `public/js/app.js`, or
  `public/mix-manifest.json`. They are generated — edit source and rebuild.
- Blade references hashed assets via `mix('/css/app.css')`; the manifest maps
  the hash, so always rebuild after changing source rather than patching output.
