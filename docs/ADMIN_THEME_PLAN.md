# Admin Theme Plan — align /admin/* with the tech-blue public theme

> **Goal:** Make the AdminLTE admin (and by cascade the driver portal) visually
> consistent with the new public theme (landing `/`, tracking `/tracking`,
> login) — navy + blue→cyan gradient accents, Noto Sans Thai, rounder cards —
> **without rebuilding any screen or breaking any workflow**.
> **Status:** PLAN ONLY — no application code changed by this document.

---

## 0. Why this is smaller than it looks (verified)

The admin is NOT raw AdminLTE. `resources/sass/_modern-ui.scss` (34 KB) already
skins everything through **23 CSS custom properties** at `:root`
(`--ta-page-bg`, `--ta-primary`, `--ta-sidebar`, `--ta-radius`, …) plus `ta-*`
component classes used by every screen. The current primary is already
`#2563eb` — the same blue as the public theme.

**So the retheme is a token pass + targeted accent work, in one Sass file,**
plus 3 small satellites (sidebar brand, dashboard chart colors, driver
gradient). No Blade restructure, no per-screen CSS, tests stay green.

---

## 1. Decisions (locked)

- **Restyle via tokens & existing `ta-*` classes only.** No new layout, no new
  markup patterns, no Bootstrap 5, no Vue in admin.
- **Light admin stays light.** Content area remains white/`#F8FAFC` for data
  density; the "tech" identity comes from the **navy sidebar**, the **signature
  gradient** (`135deg #1D4ED8 → #22D3EE`) on primary actions/accents, and the
  shared fonts.
- **Print labels untouched** — `admin/parcel/labels.blade.php` is standalone
  with inline styles; it must not load or inherit any of this.
- **Brand contract extends to admin:** the sidebar brand text comes from
  `config('app.name')` (verify; fix if hardcoded).
- Driver portal inherits via shared `--ta-*` vars; its own gradient end-stop is
  aligned in a dedicated phase (visual-only).

---

## 2. Token map — `_modern-ui.scss:1-24` (the core change)

| Token | Now | New | Note |
|---|---|---|---|
| `--ta-page-bg` | `#f5f7fa` | `#F8FAFC` | match `--surface` of public pages |
| `--ta-surface` | `#ffffff` | keep | |
| `--ta-surface-soft` | `#f9fafb` | `#F1F5F9` | slightly cooler slate |
| `--ta-border` / `--ta-border-strong` | `#e2e8f0` / `#cbd5e1` | keep | already the public `--line` |
| `--ta-text` | `#1f2937` | `#0F172A` | public `--ink-900` |
| `--ta-muted` | `#64748b` | keep | already public `--ink-500` |
| `--ta-primary` | `#2563eb` | keep | already matches |
| `--ta-primary-hover` | `#1d4ed8` | keep | |
| `--ta-info` | `#0891b2` | `#0E7490` (text) + new `--ta-accent: #22D3EE` | cyan accent joins the family |
| `--ta-sidebar` | `#172033` | `#0A1628` | public `--navy-900` |
| `--ta-sidebar-hover` | `#22304a` | `#11233D` | derived from navy ramp |
| `--ta-sidebar-accent` | `rgba(37,99,235,.95)` | the signature gradient | active item indicator |
| `--ta-radius` | `8px` | `12px` | cards read closer to public 16–20px without breaking dense tables |
| `--ta-control-radius` | `6px` | `10px` | inputs/buttons match login page |
| `--ta-shadow-md` | rgba(15,23,42,.08) | `0 1px 3px rgba(2,6,23,.06), 0 12px 32px rgba(2,6,23,.07)` | landing card shadow |
| *(new)* `--ta-gradient` | — | `linear-gradient(135deg,#1D4ED8,#22D3EE)` | one source of truth for accents |

Everything reading these vars (cards, buttons, forms, tables, alerts, KPI
cards, toolbars, sticky savebar, timeline, empty states) updates for free.
Hardcoded hexes inside `_modern-ui.scss` that duplicate a token value get
swapped to `var(--ta-*)` opportunistically **only where they appear in the
sections we touch** — no blind find-and-replace.

---

## 3. Targeted accent work (beyond tokens)

### 3.1 Sidebar (`layouts/sidebar.blade.php` + sidebar section of `_modern-ui.scss`)
- Background `--ta-sidebar` (#0A1628) + a very subtle top cyan glow (CSS only).
- **Brand area** restyled like `BrandLogo`: wordmark in Space Grotesk,
  letter-spacing wide, check-mark logo accent cyan; text from
  `config('app.name')` (grep the current hardcode "ADMIN CONSOLE" block).
- Active item: 3px left indicator using `--ta-gradient` + soft
  `--ta-primary-soft`-on-navy background; hover `--ta-sidebar-hover`.
- Section labels (`คนขับรถ`, menu groups) muted `#64748B`.

### 3.2 Top navbar
- White, `--ta-border` bottom border, slightly translucent +
  `backdrop-filter: blur` (glass nod to the public navbar); user chip rounded.

### 3.3 Buttons
- `.btn-primary`: `--ta-gradient` background, white text, hover lift
  `translateY(-1px)` + deeper shadow (same recipe as login submit).
- Secondary/outline/danger: token pass only.

### 3.4 Dashboard (`dashboard-script.blade.php:71-95` + metric cards)
- JS `themeColors`: `teal #0891b2 → #22D3EE`, keep blue/green/red/gray;
  `deliveryColors`/`tripColors` follow automatically.
- `.dashboard-metric--*` accents: primary card gets the gradient treatment;
  others keep semantic colors. KPI numbers switch to Space Grotesk via a
  `--ta-display-font` var (see fonts).

### 3.5 Fonts (`layouts/app.blade.php` head + `_modern-ui.scss:33`)
- Add Google Fonts link (preconnect + `display=swap`): **Noto Sans Thai**
  (400/500/600/700) + **Space Grotesk** (500/700) — same files the public pages
  already use, so they're likely cached.
- Body stack: `'Noto Sans Thai', -apple-system, …` (move from fallback to
  first). New `--ta-display-font: 'Space Grotesk', 'Noto Sans Thai', sans-serif`
  used by: KPI values, parcel/trip codes in tables (`.ta-code` if present, else
  introduce on the partials that print codes — smallest viable set).

### 3.6 Status badges (`_modern-ui.scss:841-881`)
- Align with the tracking page bucket colors: waiting=slate `#64748B`,
  in_transit=blue `#2563EB`, delivered/success=green `#16A34A`,
  failed/danger=`#DC2626`, returned/warning=`#B45309`. Pill radius 9999px,
  tinted backgrounds (50-stop) like the public result cards.

### 3.7 Plugin skins (cascade check, fix only if off)
- Select2 focus/selection color, DataTables pagination active pill, processing
  indicator, SweetAlert2 confirm button → all should inherit; add minimal
  scoped overrides in `_modern-ui.scss` where Bootstrap defaults leak through
  (known leak: DataTables `.page-item.active .page-link`).

### 3.8 Driver portal (`_driver.scss`) — visual-only alignment
- Gradient end-stop `#0891b2 → #22D3EE` (3 occurrences), token-derived status
  stripe colors. Nothing structural; the portal already shares `--ta-*`.

---

## 4. Explicitly NOT changing

- Any Blade structure/markup beyond the sidebar brand block and the fonts link.
- `admin/parcel/labels.blade.php` (print) — verify after the pass that it still
  renders pixel-identical (it loads no app.css).
- `ta-form-grid` mechanics (only its colors/radius via tokens) — the col-md-X
  span map stays as is.
- DataTables/Select2/SweetAlert *behavior*, all jQuery wiring, all routes/PHP.
- `_login.scss` (already rethemed).

---

## 5. Phases

1. **Token pass + fonts** — edit `:root` vars per §2, add fonts link, body
   stack, `--ta-gradient`/`--ta-display-font`/`--ta-accent`; rebuild; smoke
   every screen for contrast regressions. *Shippable alone.*
2. **Sidebar + navbar + buttons** — §3.1–3.3 incl. brand-from-config fix.
3. **Dashboard + badges** — §3.4, §3.6.
4. **Plugin skin sweep + driver alignment** — §3.7, §3.8.
5. **QA sweep + UI test** — checklist below, then `laravel-ui-tester` pass.

Each phase = one `npm run dev` rebuild; no PHP changes anywhere except the
sidebar brand Blade block.

---

## 6. QA checklist (phase 5)

Screens to eyeball after each phase (all behind admin login):
dashboard · orders list+form · order receive · contacts · trips list+form+show
(+assign incl. cross-page selection UI) · drivers list+form+show · users ·
parcel search · reports/exports page · a DataTables screen at 375px ·
**print labels (must be unchanged)** · driver portal home+trip (cascade check).

UI-test assertions: sidebar navy + gradient active state; primary buttons
gradient with hover; DataTables pagination/search styled; Select2 in trip form
styled; SweetAlert confirm themed; badges new palette; dashboard charts use
cyan not old teal; fonts render Noto Sans Thai; **zero console errors**; print
label page byte-identical styling.

Feature tests: full suite must stay green untouched (CSS-only + 1 Blade brand
block + fonts link). Add one assertion to an existing admin smoke test only if
the brand block text source changes markup.

---

## 7. Risks & gotchas

| Risk | Mitigation |
|---|---|
| Radius bump (8→12px) misaligns dense tables/inputs | Tokens are split (`--ta-radius` vs `--ta-control-radius`); verify tables at phase 1 before proceeding |
| Gradient buttons reduce contrast on small text | Keep white text, min 600 weight; check `:disabled` state explicitly |
| Google Fonts adds load to every admin page | Same families as public pages (browser-cached); `display=swap`; system stack fallback unchanged |
| Plugin CSS specificity beats tokens | Scoped overrides under `.ta-admin-shell` only; no `!important` unless a plugin uses it first |
| Driver portal visual regression from shared vars | Phase 4 explicitly eyeballs portal screens; `_driver.scss` gradients changed deliberately, not by accident |
| Print labels accidentally affected | They load no app.css — verify by diffing rendered HTML/screenshot in phase 5 |
| Dark-navy sidebar contrast (a11y) | Text on `#0A1628` uses `#E2E8F0`/white, muted `#94A3B8` — AA at 14px |

---

## 8. Out of scope (future)

- Dark mode for admin.
- Replacing jQuery/AdminLTE or any per-screen layout redesign.
- Theming the printable CSV/exports output.
- New admin components; this is strictly a reskin.

---

*Plan authored for the Tiny Transport admin retheme. Implementation pending
approval — no source changed by this document.*
