# Driver Portal Redesign Plan — App-like Bottom Navigation

> **Goal:** Redesign the whole driver portal (`/driver/*`) to feel like a real
> mobile app — a persistent **bottom tab bar**, app-style headers, and modern,
> touch-friendly screens.
> **Constraint:** **No new libraries.** Stack = Laravel 9 + Blade + Bootstrap 4.6
> + AdminLTE 3 + FontAwesome 4 + jQuery. Only edit Blade views + SCSS
> (`resources/sass/_driver.scss`), then rebuild with `npm run dev/prod`
> (Laravel Mix). Reuse the existing blue design tokens already used across
> `_driver.scss` / `_modern-ui.scss`. A library is a **last resort** and may only
> be added if it does **not** change framework structure or correctness.
> **Status:** PLAN ONLY — no application code is changed by this document.

---

## 0. Decisions (locked)

- **Bottom nav = 3 tabs:** งาน (Jobs/Home) · ประวัติงาน (History) · โปรไฟล์ (Profile).
- **Logout** lives **inside the Profile tab** (not a direct tab), as a prominent
  button using the existing `POST login.logout` form.
- **Scope = the whole driver portal**, delivered in phases (shell → list →
  detail/parcel card → history → profile).

---

## 1. Current State (what exists today)

| Area | File | Notes |
|---|---|---|
| Shell layout | `resources/views/layouts/driver.blade.php` | `.driver-shell` → `.driver-app` (max 480px, centered). Loads FontAwesome + `mix('css/app.css')`. **No bottom nav.** |
| Jobs list | `resources/views/driver/trips/index.blade.php` | Hero header with inline top-right logout; active trips list. |
| Trip detail | `resources/views/driver/trips/show.blade.php` | Sticky topbar, summary grid (6 cards), pending/completed parcel sections. |
| Parcel card | `resources/views/driver/trips/_parcel-card.blade.php` | Call / map / delivery-status / COD forms. |
| Styles | `resources/sass/_driver.scss` | Hero, summary grid, trip card, parcel card, collapse header. Blue theme (`#2563eb` → `#0891b2`). |
| Controller | `app/Http/Controllers/DriverTripController.php` | `index()` = active trips (`whereNotIn` completed/cancelled). Ownership enforced via `ensureDriverOwnsTrip*`. |
| Routes | `routes/driver.php` (prefix `driver`, name `driver.`, middleware `web,auth,role:driver`) | `driver.dashboard`, `driver.trips.show`, delivery/payment status, `trips.start`, `trips.submit`. |

**Key facts to preserve**
- Every driver query is **ownership-scoped** to `trips.driver_user_id = Auth::id()`
  and item actions go through `ensureDriverOwnsTripItem`. This must not regress.
- Middleware `role:driver` guards the whole group — admin users must not land here.
- Assets are compiled; never hand-edit `public/css|js`.

---

## 2. Design Concept

A single-column **app shell** (≤ 480px, centered) with three fixed regions:

```
┌───────────────────────────────┐
│  App bar (per-screen header)  │  ← compact, sticky top
├───────────────────────────────┤
│                               │
│      Scrollable content       │  ← screen body (jobs / history / profile)
│                               │
│                               │
├───────────────────────────────┤
│  [ งาน ]  [ ประวัติ ]  [ โปรไฟล์ ] │  ← fixed bottom tab bar (safe-area aware)
└───────────────────────────────┘
```

Principles:
- **Thumb-first**: primary navigation at the bottom, 44px+ touch targets.
- **One job per screen**: list → detail → action, no nested chrome.
- **Native feel via CSS only**: fixed tab bar, active-tab highlight, subtle
  press states, `env(safe-area-inset-bottom)` for notched phones. No JS router,
  no SPA — each tab is a normal Laravel page (framework-safe).

---

## 3. Navigation Model

Bottom tab bar shown on **top-level** screens (Jobs, History, Profile). On the
**trip detail** screen it is replaced by a back-style app bar (drill-down), so
the detail feels like a pushed view — standard mobile pattern.

| Tab | Icon (FA4) | Route | New? |
|---|---|---|---|
| งาน | `fa-truck-moving` / `fa-route` | `driver.dashboard` (`GET /driver`) | existing |
| ประวัติงาน | `fa-clock-rotate-left` (FA4: `fa-history`) | `driver.trips.history` (`GET /driver/trips/history`) | **new** |
| โปรไฟล์ | `fa-user` | `driver.profile` (`GET /driver/profile`) | **new** |

Active state: resolved server-side with `request()->routeIs('driver.dashboard')`
etc. — no client JS needed. A small unread/active count badge on งาน is optional
(active trips count).

> **Route ordering note:** declare `GET /trips/history` **before**
> `GET /trips/{trip}` in `routes/driver.php`, otherwise `history` is captured by
> the `{trip}` wildcard.

---

## 4. Screens (Information Architecture)

### 4.1 งาน — Jobs (Home) · `driver.dashboard`
- Keep the existing hero (welcome + driver name) but **remove the top-right
  logout** (moves to Profile).
- Active trips list (existing `driver-trip-card`), refined spacing/elevation.
- Empty state preserved.
- Bottom tab bar visible, งาน active.

### 4.2 รายละเอียดงาน — Trip Detail · `driver.trips.show`
- Drill-down view: compact app bar with **back to งาน**, trip code + status badge.
- Keep summary grid + start/submit actions + pending/completed parcel sections.
- Parcel card (`_parcel-card`) restyled to app cards: clearer COD line, bigger
  call/map buttons, bottom-sheet-style action area (CSS only).
- Bottom tab bar **hidden** here (it's a pushed screen) to maximize space.

### 4.3 ประวัติงาน — History · `driver.trips.history` *(new)*
- List of the driver's **completed/cancelled** trips
  (`whereIn(status, [COMPLETED, CANCELLED])`), newest first, paginated.
- Reuse `driver-trip-card` styling with a muted/closed treatment.
- Tapping a history trip opens the same detail view (already read-only when not
  `IN_TRANSIT`, per `isReadOnly()`), so **no new detail screen needed**.
- Empty state: "ยังไม่มีประวัติงาน".

### 4.4 โปรไฟล์ — Profile · `driver.profile` *(new)*
- Driver identity card: avatar, `auth()->user()->name`, role label.
- Lightweight stats (optional, cheap queries): active trips count, delivered
  today/this-period — keep minimal to avoid heavy queries.
- **Logout button** (prominent, full-width) → existing `POST login.logout` form
  with `@csrf`.
- App version / build label (optional, static).

---

## 5. Files & Components Plan

**New Blade**
- `resources/views/driver/partials/_tabbar.blade.php` — the bottom tab bar
  (3 items, active via `request()->routeIs(...)`). Included by top-level screens.
- `resources/views/driver/partials/_appbar.blade.php` — compact reusable app bar
  (title + optional back) for sub screens. *(optional — can inline first.)*
- `resources/views/driver/trips/history.blade.php` — History list.
- `resources/views/driver/profile.blade.php` — Profile + logout.

**Edited Blade**
- `resources/views/layouts/driver.blade.php` — add an optional `@yield('tabbar')`
  / `@stack` slot and bottom padding so content clears the fixed bar. Keep a flag
  (e.g. section `show_tabbar`) so detail screens can opt out.
- `driver/trips/index.blade.php` — remove inline logout, include tab bar.
- `driver/trips/show.blade.php` — app-bar back, no tab bar, restyled cards.
- `driver/trips/_parcel-card.blade.php` — app-card restyle (no logic change).

**SCSS** (`resources/sass/_driver.scss`, extend — don't fork)
- `.driver-tabbar` — `position: fixed; bottom: 0;` centered to the 480px column,
  `padding-bottom: env(safe-area-inset-bottom)`, top border + soft shadow.
- `.driver-tab` — flex column (icon + label), inactive vs `.is-active` color,
  press state, optional count `.driver-tab__badge`.
- `.driver-content` — increase bottom padding to
  `calc(72px + env(safe-area-inset-bottom))` when the tab bar is present.
- `.driver-appbar` — compact sticky header for sub screens.
- Profile + history card variants reusing existing card tokens/colors.

**No new libs:** all of the above is static CSS + Blade conditionals. jQuery
already present handles the existing collapse; nothing new required.

---

## 6. Backend Changes (minimal, safety-preserving)

All additions stay inside the existing `role:driver` group and remain
ownership-scoped.

`routes/driver.php` (add, mind ordering vs `{trip}`):
```php
Route::get('/trips/history', [DriverTripController::class, 'history'])->name('trips.history');
Route::get('/profile', [DriverTripController::class, 'profile'])->name('profile');
```

`DriverTripController` (new methods):
```php
public function history()
{
    $trips = Trip::query()
        ->where('driver_user_id', Auth::id())               // ownership scope
        ->whereIn('status', [Trip::STATUS_COMPLETED, Trip::STATUS_CANCELLED])
        ->orderByDesc('trip_date')->orderByDesc('id')
        ->withCount('tripItems')
        ->paginate(10);

    return view('driver.trips.history', ['trips' => $trips]);
}

public function profile()
{
    return view('driver.profile', ['user' => Auth::user()]);
}
```

- **No schema changes**, no new model, no API change.
- Reuses existing `Trip::STATUS_*` constants and `tripItems` relation.
- History view links to `driver.trips.show`, which already 403s on non-owned
  trips and renders read-only when not in transit.

---

## 7. Design Tokens

Stay on the established blue system already in `_driver.scss`:

| Token | Value | Use |
|---|---|---|
| primary | `#2563eb` | active tab, primary buttons, accents |
| secondary | `#0891b2` | hero gradient end |
| surface | `#ffffff` | cards, tab bar |
| bg | `#f1f5f9` | app background |
| ink | `#0f172a` | text |
| muted | `#64748b` | inactive tab, captions |
| border | `#e2e8f0` | dividers |

If `_modern-ui.scss` exposes CSS variables for these, prefer referencing them so
the driver portal tracks global theme changes; otherwise keep the existing hex to
avoid scope creep.

---

## 8. Implementation Phases

1. **Shell + tab bar** — add `_tabbar`, update `layouts/driver.blade.php` +
   `_driver.scss`; wire active states. (Visual only; no backend.)
2. **Jobs screen** — move logout out, adopt tab bar, polish list cards.
3. **Detail + parcel card** — app-bar back, hide tab bar, restyle parcel actions.
4. **History** — route + `history()` + `history.blade.php`.
5. **Profile** — route + `profile()` + `profile.blade.php` with logout.
6. **Polish & rebuild** — spacing, safe-area, press states; `npm run dev`/`prod`.

Each phase is independently shippable and reviewable.

---

## 9. Guardrails & Safety

- **Ownership** — every new query filters `driver_user_id = Auth::id()`; reuse
  `ensureDriverOwnsTrip*`. (See `.claude` skill `laravel9/references/access-control.md`.)
- **Middleware** — new routes stay in the `role:driver` group; admins blocked.
- **No framework drift** — no SPA, no client router; each tab is a server-rendered
  Laravel page. No Bootstrap 5 syntax (this is BS4: `data-toggle`, not `data-bs-*`).
- **Assets** — edit `resources/sass/_driver.scss` only; rebuild via Mix; never
  touch `public/css|js` by hand.
- **Smallest diff** — restyle by adding classes; do not rename existing routes,
  columns, or model properties.

---

## 10. Risks & Library Fallback

| Risk | Mitigation |
|---|---|
| `history` route shadowed by `{trip}` | Declare `history` first; verify with `route:list`. |
| Fixed tab bar overlaps content / notch | Bottom padding + `env(safe-area-inset-bottom)`. |
| Logout moved → users can't find it | Clear Profile tab + prominent button; keep one tap away. |
| Detail screen losing nav feels stuck | App-bar back + tab bar returns on list screens. |

**Library policy:** the whole design is achievable with CSS + Blade. A library
is considered **only** if a must-have interaction proves infeasible in pure CSS
(e.g. native-quality swipe-between-tabs gestures). Even then it must be a small,
self-contained JS/CSS asset that does **not** alter routing, the Blade/Bootstrap
structure, or backend correctness — and it would be raised for approval before
adding. Default plan adds **none**.

---

## 11. Out of Scope (future)

- PWA / installable app (manifest, service worker, offline).
- Push notifications.
- In-tab AJAX navigation / skeleton loaders.
- Driver profile editing (currently read-only display + logout).

---

## 12. Acceptance Criteria

- `/driver`, `/driver/trips/history`, `/driver/profile` all render with a fixed
  3-tab bottom bar; the correct tab shows active per screen.
- Trip detail shows a back app bar (no bottom bar) and all existing actions
  (start, submit, delivery status, COD) still work.
- History lists only the logged-in driver's completed/cancelled trips; a driver
  cannot reach another driver's trip (403 preserved).
- Logout works from Profile.
- Layout is correct on a notched phone viewport (safe-area respected) and on the
  ≤480px column; no horizontal scroll.
- `npm run dev`/`prod` builds cleanly; no new dependency in `package.json`.

### Manual verification
```bash
docker compose up -d
npm run dev
# login as a driver-role account, then:
#   /driver               → งาน tab active, list + bottom bar
#   /driver/trips/history → ประวัติ tab active, closed trips only
#   /driver/profile       → โปรไฟล์ tab active, logout button
# open a trip → detail with back bar; start/submit/COD still function
```
Add/extend feature tests under `tests/Feature` for the two new routes: each
requires `role:driver`, and `history` returns only `Auth::id()`-owned trips.
```

---

*Plan authored for the Tiny Transport driver portal. Implementation pending
approval — no source changed by this document.*
