# Vue 3 public pages — standalone SPA pattern

For **public-facing** pages only (`/web` tracking, the `/` landing). Admin and
driver screens stay Blade + AdminLTE — see `frontend.md`. Never mix the two:
no AdminLTE/Bootstrap/jQuery on a Vue public page, no Vue on an admin screen.

## The pattern (reference implementation: `/web`)

Each public page = **standalone Blade shell + separate Mix entry**:

| Piece | `/web` example | Rule |
|---|---|---|
| Blade shell | `resources/views/web/tracking.blade.php` | Own `<head>` (SEO meta, OG tags, Google Fonts preconnect), a single mount `<div>`, one `<script src="/js/<entry>.js">`. No app layout, no `@extends`. |
| Vue entry | `resources/js/web/app.js` | `createApp(X).mount('#...')` — nothing else. |
| Components | `resources/js/web/TrackingApp.vue` | Composition API + `<script setup>`. Styles live in `<style scoped>` inside the SFC — no global Sass for these pages. |
| Mix entry | `webpack.mix.js` → `mix.js('resources/js/<area>/app.js', 'public/js/<area>.js').vue({ version: 3 });` | One line per page area. |
| Route | `routes/web.php` | Public = no auth middleware. Keep the closure thin: `view(...)` only. |

- Vue 3 + `@vue/compiler-sfc` are **already installed** — do not re-add or bump.
- Thai font: Noto Sans Thai via Google Fonts in the Blade shell (preconnect +
  `display=swap`), never inside the SFC.
- No vue-router, no store, no axios — plain `fetch` and `window.location`.

## Brand rename contract (landing page)

The company name will be renamed later. It must exist in exactly three places:

1. `.env APP_NAME` → read via `config('app.name')` in the Blade shell
   (`<title>`, meta, OG).
2. `window.__BRAND = @json(['name' => config('app.name')])` injected by the
   shell; every component reads `window.__BRAND?.name`.
3. The logo/wordmark markup lives ONLY in `BrandLogo.vue`.

Hardcoding the brand name anywhere else is a review-blocking finding. A feature
test must prove `config(['app.name' => 'X'])` changes the response.

## Public API endpoints (paired with these pages)

- Live in `routes/api.php` **outside** the `auth:sanctum` group.
- Validate hard caps at the boundary (e.g. `/api/track` accepts max 10 codes,
  each `string|max:50`).
- Return **only whitelisted fields** — never `toArray()` a model into a public
  response; map explicitly. No internal ids/usernames/cost data in public JSON.

## Build & verify (host npm — same as all assets)

```bash
npm run dev          # rebuild after editing resources/js/<area>/
```
- `public/js/web.js`, `public/js/landing.js` are **generated** — never
  hand-edit, but they ARE committed (repo convention for `public/` builds).
- Verify: `curl -s -o /dev/null -w "%{http_code}" http://localhost:8000/<page>`
  → 200, and the paired API returns JSON via curl.

## Motion / quality bar for public pages

- Animations: GPU-only transforms, IntersectionObserver for scroll triggers,
  rAF-throttled scroll listeners. Always honor `prefers-reduced-motion: reduce`.
- Mobile-first responsive; no raster images unless unavoidable (prefer SVG/CSS).
- Zero console errors is part of done — verify with the `laravel-ui-tester`
  agent (it tests public pages as a **guest**, no login).

## Testing

- Feature test the shell: guest GET → 200 + mount div present; authed-redirect
  rules if the route has them (the `/` landing redirects logged-in users).
- Feature test the public API: validation caps (422), found/not-found shapes,
  no-auth-required (200 without `actingAs`).
- UI behavior (chips, search, animations) → `laravel-ui-tester` agent, not PHPUnit.
