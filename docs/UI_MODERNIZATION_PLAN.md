# UI Modernization Plan — 2026 Blue Theme

> Goal: Refresh the whole UI to a modern 2026 look, blue-led palette, easy to use.
> Constraint: **No library changes.** Stack = Laravel 9 + AdminLTE 3 (Bootstrap 4).
> Only edit Blade views + SCSS (`resources/sass/*`), then `npm run dev/prod` (Laravel Mix).
> A blue design-token system already exists in `_modern-ui.scss`, `_login.scss`, `_driver.scss`.
> Login + driver portal are already refreshed → this plan focuses on **admin screens**.

---

## 1. Design System (source of truth)

Use the existing CSS variables in `resources/sass/_modern-ui.scss`. Extend, don't fork.

### Color (blue-led)
| Token | Value | Use |
|---|---|---|
| `--ta-primary` | `#2563eb` | primary actions, links, active nav |
| `--ta-primary-hover` | `#1d4ed8` | hover/pressed |
| `--ta-sidebar` | `#172033` | sidebar bg (deep navy) |
| `--ta-page-bg` | `#f5f7fa` | app background |
| `--ta-surface` | `#ffffff` | cards/panels |
| `--ta-border` | `#e2e8f0` | borders |
| `--ta-text` / `--ta-muted` | `#1f2937` / `#64748b` | text |
| info/success/warning/danger | `#0891b2` / `#15803d` / `#b45309` / `#dc2626` | status only |

Add (new): `--ta-primary-soft: #eff6ff` (selected rows, badge bg), `--ta-focus-ring: rgba(37,99,235,.25)`.

### Typography
- Base 14px, line-height 1.5. Headings 600 weight, tight (-0.01em).
- Keep system + Noto Sans Thai stack (already set).
- Table header: 12px, uppercase, letter-spacing .04em, muted.

### Spacing / radius / shadow
- Spacing scale: 4 / 8 / 12 / 16 / 24 / 32px. Card padding 24px, control padding 8–12px.
- Radius: cards `--ta-radius:8px`, controls `--ta-control-radius:6px`, pills/badges 999px.
- Shadow: rest `--ta-shadow-sm`, hover/elevated `--ta-shadow-md`. No heavy AdminLTE shadows.

### Interaction
- Transitions 150ms ease on bg/border/shadow/transform.
- Focus: 2px `--ta-focus-ring` ring on all inputs/buttons (a11y).
- Buttons: subtle lift on hover (`translateY(-1px)` + shadow). Tables: row hover = `--ta-surface-soft`, selected = `--ta-primary-soft`.
- Loading: use existing SweetAlert2 for confirms; add `.is-loading` spinner state on submit buttons.

---

## 2. Task Breakdown

Each task is independent unless noted. Format: scope → files → done-when.

### T0 — Token audit (do first, blocks others)
- Add new tokens (`--ta-primary-soft`, `--ta-focus-ring`) to `:root` in `_modern-ui.scss`.
- Verify no hardcoded blues remain in SCSS; replace with tokens.
- **Files:** `resources/sass/_modern-ui.scss`, `_variables.scss`
- **Done:** tokens defined; `npm run dev` builds clean.

### T1 — App shell (navbar + sidebar)
- Navbar: white, 1px bottom border, slim (56px). User dropdown → rounded avatar, clean menu.
- Sidebar: navy `--ta-sidebar`, active item = primary bg + left accent bar, icon alignment, section spacing.
- Add brand logo lockup in `brand-link`.
- **Files:** `layouts/app.blade.php`, `layouts/sidebar.blade.php`, `layouts/menu.blade.php`, `_modern-ui.scss`
- **Done:** shell consistent on every admin page; active route highlighted.

### T2 — Buttons & forms (global)
- Buttons: primary/secondary/ghost/danger variants, consistent height (38px), icon+label spacing, focus ring, hover lift.
- Forms: 6px radius inputs, clear labels, focus ring, inline validation styling, select2/daterangepicker match input look.
- **Files:** `_modern-ui.scss` (buttons/forms already partly there — refine), affected partials
- **Done:** one button + input style used everywhere; validation states styled.

### T3 — Tables & list pages
- Card-wrapped tables, sticky header, zebra + hover, status badges (pill), aligned action buttons (icon-only ghost), empty state, pagination styling.
- DataTables controls (search/length/buttons) restyled to match.
- **Files:** `admin/*/list.blade.php` (order, trip, user, contact), `_modern-ui.scss`
- **Done:** all list pages share table component look.

### T4 — Forms / create-edit pages
- Two-column responsive layout, grouped sections in cards with headers, sticky save bar on long forms.
- Apply to: order create/edit, trip create/edit, user create/edit, contact create/edit.
- **Files:** `admin/order/*`, `admin/trip/*`, `admin/user/*`, `admin/contact/*` + `form-component/*`
- **Done:** consistent form scaffolding; mobile-friendly.

### T5 — Dashboard
- Modern KPI cards (info-box) with icon chip, value, trend; chart cards with clean headers; responsive grid.
- **Files:** `admin/dashboard.blade.php`, `admin/dashboard/dashboard-script.blade.php`, `_modern-ui.scss`
- **Done:** dashboard reads as a 2026 analytics page.

### T6 — Parcel tracking / search / labels
- Tracking: timeline/stepper component for status history. Search: prominent search field + result cards. Labels: keep print layout clean (print CSS untouched by theme).
- **Files:** `admin/parcel/*`, `_modern-ui.scss`
- **Done:** tracking has visual timeline; print unaffected.

### T7 — Reports
- Filter bar (date range + selects) in a card, results table reusing T3 styles, export buttons grouped.
- **Files:** `admin/report.blade.php`
- **Done:** report page matches list-page system.

### T8 — Feedback & states
- Standardize alerts (`layouts/alert-message.blade.php`), toasts, empty states, loading/skeleton, 404/403 pages.
- **Files:** `layouts/alert-message.blade.php`, error views, `_modern-ui.scss`
- **Done:** consistent feedback components.

### T9 — Responsive & a11y pass
- Mobile: collapsible sidebar, scrollable tables, tap targets ≥44px.
- A11y: focus rings, color contrast ≥4.5:1, aria labels on icon buttons.
- **Files:** cross-cutting, `_modern-ui.scss`
- **Done:** usable on mobile; keyboard navigable.

---

## 3. Execution Order
T0 → T1 → T2 → (T3, T4, T5 parallel) → (T6, T7, T8) → T9.

## 4. Working Rules for Implementers
- Touch **only** Blade + SCSS. Never edit `node_modules`/`vendor`/compiled `public/css/app.css` directly.
- Build: `npm run dev` (watch: `npm run watch`).
- Reuse AdminLTE/BS4 classes; override via tokens in SCSS. No new CSS framework, no CDN libs.
- Keep Thai labels intact. Test each page logged in as admin.
- One task = one focused commit.

## 5. References (modern dashboard patterns)
- Tailwind UI / shadcn dashboard layouts (for spacing & card rhythm — visual ref only).
- Stripe / Linear / Vercel dashboards (blue-led, calm neutrals, clear hierarchy).
- AdminLTE 3 docs for component class names already available.
