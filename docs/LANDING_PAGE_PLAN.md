# Landing Page Plan — public company site at `/` (www.domain.com)

> **Goal:** Replace the root redirect with a stunning public landing page for the
> transport company — **tech-blue theme, Vue 3, "wow" factor** — with an employee
> login button (→ `/login`) and a parcel-tracking entry point (→ `/web`).
> **Constraint:** Brand name **will be renamed later** — the name must live in ONE
> place so the future rename is a one-line change.
> **Status:** PLAN ONLY — no application code changed by this document.

---

## 0. Decisions (locked)

- **Vue 3 SPA** on a standalone Blade shell — same proven pattern as `/web`
  (separate Mix entry, no AdminLTE / Bootstrap / jQuery on this page).
- **Route `/` becomes the public landing.** Current behavior (redirect to
  `/admin` or `/driver`) moves behind the login flow — see §2 for the exact rule.
- **Brand name from `config('app.name')`** (i.e. `.env APP_NAME`) everywhere —
  Blade `<title>`, meta tags, and injected into Vue via `window.__BRAND`.
  Future rename = edit `.env` + rebuild nothing.
- **No new dependency.** Vue 3 + `@vue/compiler-sfc` are already installed
  (added by the `/web` feature). Fonts via Google Fonts CDN. Icons/illustrations
  are inline SVG (no icon library, no image assets to optimize).
- **Dark-hero + light-body** composition: deep-navy hero with animated gradient,
  then alternating light sections — the strongest "tech" look per effort.

---

## 1. Current state (verified)

- `routes/web.php:16` — `GET /` is a closure: driver → `driver.dashboard`,
  everyone else → `redirect('admin')` (guests then bounce to `/login`).
- `webpack.mix.js:21` — `/web` Vue entry already compiles via
  `mix.js(...).vue({ version: 3 })`; copy that line for the landing entry.
- `/web` tracking page accepts `?q=CODE1,CODE2` and auto-searches on mount —
  the landing hero search box can simply redirect there. No API work needed.

---

## 2. Routing & auth behavior

```php
// routes/web.php — replace the current '/' closure
Route::get('/', function () {
    if (auth()->check()) {
        return auth()->user()->isDriver()
            ? redirect()->route('driver.dashboard')
            : redirect('admin');
    }
    return view('landing.index');
})->name('landing');
```

- **Guests** see the landing page.
- **Logged-in staff/drivers** keep today's behavior (bounce to their dashboard) —
  zero disruption for employees who bookmarked `/`.
- `/login`, `/web`, `/admin/*`, `/driver/*` — all untouched.

---

## 3. Architecture & files

| File | Purpose |
|---|---|
| `resources/views/landing/index.blade.php` (new) | Standalone shell: SEO meta + OG tags, Google Fonts preload, `window.__BRAND` injection, `<div id="landing-app">`, loads `/js/landing.js` |
| `resources/js/landing/app.js` (new) | `createApp(LandingApp).mount('#landing-app')` |
| `resources/js/landing/LandingApp.vue` (new) | Page composition (sections in order) |
| `resources/js/landing/components/*.vue` (new) | `NavBar`, `HeroSection`, `StatsBar`, `ServiceCards`, `HowItWorks`, `TrackingCta`, `SiteFooter`, `BrandLogo` |
| `webpack.mix.js` | append `mix.js('resources/js/landing/app.js', 'public/js/landing.js').vue({ version: 3 });` |
| `routes/web.php` | replace `/` closure per §2 |
| `tests/Feature/LandingPageTest.php` (new) | see §8 |

**Brand injection (the rename contract):**

```blade
{{-- landing/index.blade.php <head> --}}
<title>{{ config('app.name') }} — ขนส่งพัสดุด่วนทั่วไทย</title>
<script>window.__BRAND = @json(['name' => config('app.name')]);</script>
```

```js
// any Vue component
const brand = window.__BRAND?.name ?? 'TINY TRANSPORT'
```

- The wordmark/logo lives ONLY in `BrandLogo.vue` (text-based wordmark for now).
- Rename day: change `APP_NAME` in `.env` → done. New logo file? Swap one component.

---

## 4. Design system — "Tech Blue"

### Palette (CSS custom properties on `:root` of the page)

| Token | Value | Use |
|---|---|---|
| `--navy-950` | `#060B18` | hero background base |
| `--navy-900` | `#0A1628` | hero gradient stop, footer bg |
| `--blue-700` | `#1D4ED8` | primary buttons, links |
| `--blue-600` | `#2563EB` | primary brand blue |
| `--cyan-400` | `#22D3EE` | accent — gradient end, glows, highlights |
| `--sky-300` | `#7DD3FC` | hero subtext on dark |
| `--surface` | `#F8FAFC` | light section background |
| `--card` | `#FFFFFF` | cards |
| `--ink-900` | `#0F172A` | headings on light |
| `--ink-500` | `#64748B` | body text on light |
| `--line` | `#E2E8F0` | borders, dividers |

- **Signature gradient:** `linear-gradient(135deg, #1D4ED8 0%, #22D3EE 100%)` —
  used on primary CTAs, the tracking band, and as animated text-clip on the hero
  headline keyword.
- **Glow:** `box-shadow: 0 0 60px rgba(34, 211, 238, .25)` on hero accents.

### Typography

- **Noto Sans Thai** (300/400/500/600/700/800) — all Thai text. Already the
  `/web` page font; preconnect + preload in the shell.
- **Space Grotesk** (500/700) — Latin display only: the wordmark, big numbers in
  stats, tracking codes. Gives the "tech" voice without affecting Thai copy.
- Scale: hero h1 `clamp(2.2rem, 6vw, 4rem)` / section h2 `clamp(1.6rem, 4vw, 2.4rem)`
  / body `1.0625rem`, line-height 1.7.

### Shape language

- Cards `border-radius: 20px`; buttons `border-radius: 12px`; pills `9999px`.
- Card shadow `0 1px 3px rgba(2,6,23,.06), 0 12px 32px rgba(2,6,23,.07)`;
  hover lifts `translateY(-4px)` + deepens shadow (200ms ease).
- Glassmorphism reserved for the navbar only: `rgba(6,11,24,.72)` +
  `backdrop-filter: blur(12px)` once scrolled.

---

## 5. Page sections (top → bottom) + copy deck

### 5.1 NavBar (sticky, glass-on-scroll)
- Left: `BrandLogo` (wordmark, Space Grotesk, letter-spacing wide).
- Center links (smooth-scroll anchors): `บริการ` · `วิธีใช้งาน` · `ติดต่อเรา` +
  `ติดตามพัสดุ` (real link → `/web`).
- Right: **`เข้าสู่ระบบพนักงาน`** button (outline on dark, fills gradient on
  hover) → `/login`.
- Mobile: hamburger → full-screen slide-down menu (Vue transition).

### 5.2 Hero (100svh, dark navy, the "ว๊าว")
- **Background layers (all CSS/SVG, zero images):**
  1. radial gradient glow top-right (`--cyan-400` at 12% opacity);
  2. animated **gradient mesh blob** (two blurred `<div>`s slowly drifting with
     `@keyframes` translate/scale, 18s loop);
  3. faint **grid pattern** (SVG `<pattern>`, 1px lines at 5% white) with a CSS
     mask fading toward the bottom — the classic tech-blueprint texture;
  4. 5–6 floating "parcel dots" (tiny cyan squares) on slow parallax
     (`transform` driven by scroll position, max 24px shift).
- **Headline (Thai):** `ส่งไว ติดตามได้` — with `ทุกพัสดุ` as a
  gradient-clipped animated word.
- **Sub:** `บริการขนส่งพัสดุด่วน เก็บเงินปลายทาง ติดตามสถานะแบบเรียลไทม์ ครอบคลุมทุกพื้นที่`
- **Inline tracking search** (the hero's centerpiece):
  - Large pill input, placeholder `กรอกรหัสพัสดุ เช่น P2026XXXXXXX`,
    gradient `ติดตามพัสดุ` button attached on the right.
  - Submit → `window.location = '/web?q=' + encodeURIComponent(code)` —
    reuses the auto-search already built into `/web`. Supports comma-paste.
- **Secondary CTA row:** ghost button `ดูบริการของเรา` (anchor scroll) ·
  text-link `สำหรับพนักงาน →` (→ `/login`).
- **Right side (desktop ≥ lg):** a floating **mock tracking card** — a stylized
  mini version of the `/web` result card (status badge `กำลังจัดส่ง`, 3-dot
  timeline) tilted `rotate(-4deg)` with glow, gently bobbing (`6s ease-in-out
  infinite`). Sells the product visually with zero screenshots.

### 5.3 StatsBar (dark→light transition strip)
- 4 animated **count-up** numbers (IntersectionObserver triggers once):
  `99.2% ส่งสำเร็จ` · `77 จังหวัด` · `10K+ พัสดุ/เดือน` · `24 ชม. ติดตามได้`
- Numbers in Space Grotesk, gradient-clipped. *(Placeholder values — easy to
  edit; they live in one array in `StatsBar.vue`.)*

### 5.4 ServiceCards (`id="services"`, light surface)
- h2: `บริการของเรา` / sub: `ครบทุกความต้องการด้านขนส่ง`
- 4 cards (2×2 desktop, 1-col mobile), each: inline-SVG icon in a gradient-tinted
  rounded square, title, 2-line description:
  1. **ส่งพัสดุด่วน** — `รับ-ส่งถึงที่ รวดเร็ว ปลอดภัย ตรงเวลา`
  2. **เก็บเงินปลายทาง (COD)** — `เก็บเงินแทนคุณ โอนคืนไว ตรวจสอบยอดได้ทุกรายการ`
  3. **ติดตามเรียลไทม์** — `เช็กสถานะพัสดุได้ตลอด 24 ชม. ผ่านรหัสติดตาม`
  4. **ลาเบล & QR** — `พิมพ์ลาเบลพร้อม QR สแกนเช็กสถานะได้ทันที`
- Hover: lift + icon square rotates 6° + border ignites to `--cyan-400`.

### 5.5 HowItWorks (`id="how"`, white)
- h2: `ใช้งานง่ายใน 4 ขั้นตอน`
- Horizontal stepper (vertical on mobile) with an animated connecting line that
  "draws" itself on scroll (SVG `stroke-dashoffset` transition):
  1. `สร้างออเดอร์` → 2. `เข้ารอบจัดส่ง` → 3. `คนขับนำส่ง` → 4. `ลูกค้ารับของ`
- Each step: numbered gradient circle, title, one-line detail.

### 5.6 TrackingCta (full-width gradient band)
- Signature gradient background + subtle grid texture.
- `อยากรู้ว่าพัสดุถึงไหนแล้ว?` + white pill button `ติดตามพัสดุเลย → /web`.

### 5.7 SiteFooter (`id="contact"`, navy-900)
- 3 columns: brand + tagline / `ติดต่อเรา` (โทร, LINE, อีเมล — placeholder
  values in one config block) / quick links (ติดตามพัสดุ, เข้าสู่ระบบพนักงาน).
- Bottom strip: `© {year} {BRAND}. All rights reserved.` (year computed,
  brand from `window.__BRAND`).

---

## 6. Motion & "wow" spec (and its safety rails)

| Effect | Implementation | Rail |
|---|---|---|
| Scroll-reveal (sections fade-up) | One tiny `v-reveal` custom directive using IntersectionObserver, adds `.is-visible` | threshold .15, runs once |
| Hero gradient mesh drift | 2 blurred divs, pure CSS keyframes | `will-change: transform`, GPU-only props |
| Count-up stats | rAF loop in `StatsBar.vue`, 1.2s ease-out | triggers once per load |
| Stepper line draw | SVG `stroke-dashoffset` + IO | — |
| Parallax parcel dots | scroll listener → `transform` (passive, rAF-throttled) | max 24px, disabled on mobile |
| Navbar glass on scroll | toggle class at `scrollY > 24` | — |
| **`prefers-reduced-motion: reduce`** | global media query kills all of the above (opacity-only reveals, static mesh) | accessibility non-negotiable |

**Performance budget:** landing JS ≤ 120KB gzip (Vue runtime + components — no
router, no store, no axios; hero search uses plain `fetch`-free redirect).
Zero raster images. Fonts: 2 families, preconnect + `display=swap`.
Target Lighthouse (mobile): Performance ≥ 90, Accessibility ≥ 95, SEO ≥ 95.

**SEO note (accepted tradeoff):** content is client-rendered. Mitigation: full
meta description + OG tags + `<noscript>` fallback paragraph with the company
description and a plain link to `/web` in the Blade shell. SSR is out of scope
for a Laravel Mix stack — fine for a small company site.

---

## 7. Implementation phases

1. **Shell + route + tokens** — Blade shell (meta/fonts/brand injection), route
   swap per §2, Mix entry, CSS custom properties, `BrandLogo.vue`. Verify `/`
   renders for guest, redirects for authed staff/driver.
2. **NavBar + Hero** — incl. tracking quick-search → `/web?q=`, mobile menu,
   gradient mesh + grid + mock tracking card.
3. **Body sections** — StatsBar, ServiceCards, HowItWorks, TrackingCta, Footer.
4. **Motion pass** — reveals, count-up, line draw, parallax, reduced-motion.
5. **Tests + UI verification** — §8 + ui-test agent click-through + Lighthouse.

Each phase shippable; 1–2 is already a presentable page.

---

## 8. Testing

**Feature test (`LandingPageTest`):**
- guest `GET /` → 200, sees `id="landing-app"`, `window.__BRAND`, and the
  `/login` + `/web` links in the shell/noscript;
- authed admin `GET /` → redirect `/admin`; authed driver → `driver.dashboard`;
- `config(['app.name' => 'RENAMED'])` → response contains `RENAMED`
  (proves the rename contract).

**UI test checklist (agent):** load desktop+mobile screenshots; hero search with
a real code lands on `/web?q=CODE` and auto-searches; nav anchors smooth-scroll;
`เข้าสู่ระบบพนักงาน` reaches `/login`; mobile hamburger opens/closes;
zero console errors.

---

## 9. Risks & gotchas

| Risk | Mitigation |
|---|---|
| Staff bookmarked `/` expecting admin | Auth users keep the old redirect (§2) — landing is guest-only |
| Second Vue runtime duplicated across `/web` + `/` bundles | Accepted for now (~40KB gzip each). Future: `mix.extract(['vue'])` shared vendor — out of scope |
| Brand rename leaks (hardcoded name) | Name ONLY via `config('app.name')` / `window.__BRAND` / `BrandLogo.vue`; grep-test in CI is overkill, the feature test in §8 covers it |
| CSR SEO | meta + OG + noscript fallback (§6) |
| Animation jank on low-end phones | GPU-only transforms, parallax off on mobile, reduced-motion support |
| Thai font flash (FOUT) | `preconnect` + `display=swap`; hero h1 has a system-font fallback stack |

---

## 10. Out of scope (future)

- CMS/admin-editable content, blog, news.
- English i18n toggle.
- Real logo/illustration assets (text wordmark + SVG art for now).
- Price calculator, online booking form (natural next section once business
  rules exist).
- SSR / prerendering.

---

*Plan authored for the Tiny Transport public landing page. Implementation
pending approval — no source changed by this document.*
