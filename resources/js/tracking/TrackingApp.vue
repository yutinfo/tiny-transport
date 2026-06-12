<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

const MAX_CODES = 10

// Brand rename contract: the name comes from window.__BRAND (injected by the
// Blade shell from config('app.name')). The fallback is only used if injection
// failed; per the contract this single fallback is allowed.
const brand = window.__BRAND?.name ?? 'TINY TRANSPORT'

const codes = ref([])
const draft = ref('')
const results = ref([])
const loading = ref(false)
const errorMessage = ref('')
const hasSearched = ref(false)
const copied = ref(false)
const scrolled = ref(false)

const year = new Date().getFullYear()

// Status palette — same 4 colors as before (waiting / in_transit / delivered / failed).
const statusMeta = {
  waiting: { color: '#64748B', bg: '#F1F5F9' },
  in_transit: { color: '#2563EB', bg: '#EFF6FF' },
  delivered: { color: '#16A34A', bg: '#F0FDF4' },
  failed: { color: '#DC2626', bg: '#FEF2F2' },
}

const atLimit = computed(() => codes.value.length >= MAX_CODES)

function normalizeCode(value) {
  return String(value).trim()
}

function addCode(value) {
  const code = normalizeCode(value)
  if (!code) return false
  if (codes.value.includes(code)) return false
  if (atLimit.value) {
    errorMessage.value = `ค้นหาได้สูงสุด ${MAX_CODES} รายการต่อครั้ง`
    return false
  }
  codes.value.push(code)
  return true
}

// Split on comma, newline, tab or whitespace runs — handles paste of many codes.
function splitInput(text) {
  return String(text)
    .split(/[\s,;\r\n\t]+/)
    .map((part) => part.trim())
    .filter(Boolean)
}

function commitDraft() {
  const parts = splitInput(draft.value)
  if (!parts.length) return
  parts.forEach((part) => addCode(part))
  draft.value = ''
}

function onKeydown(event) {
  if (event.key === 'Enter' || event.key === ',') {
    event.preventDefault()
    commitDraft()
  } else if (event.key === 'Backspace' && draft.value === '' && codes.value.length) {
    codes.value.pop()
  }
}

function onPaste(event) {
  const text = (event.clipboardData || window.clipboardData).getData('text')
  if (text && /[\s,;\n]/.test(text)) {
    event.preventDefault()
    const parts = splitInput(text)
    parts.forEach((part) => addCode(part))
    draft.value = ''
  }
}

function removeCode(index) {
  codes.value.splice(index, 1)
  errorMessage.value = ''
}

async function search() {
  commitDraft()
  errorMessage.value = ''

  if (!codes.value.length) {
    errorMessage.value = 'กรุณากรอกรหัสพัสดุ'
    return
  }

  loading.value = true
  hasSearched.value = true
  results.value = []

  try {
    const params = new URLSearchParams()
    codes.value.forEach((code) => params.append('codes[]', code))

    const response = await fetch(`/api/track?${params.toString()}`, {
      headers: { Accept: 'application/json' },
    })

    if (response.status === 422) {
      const data = await response.json().catch(() => ({}))
      errorMessage.value = firstValidationError(data) || 'ข้อมูลที่กรอกไม่ถูกต้อง'
      return
    }

    if (!response.ok) {
      errorMessage.value = 'เกิดข้อผิดพลาดในการเชื่อมต่อ กรุณาลองใหม่อีกครั้ง'
      return
    }

    results.value = await response.json()
    syncUrl()
  } catch (err) {
    errorMessage.value = 'เกิดข้อผิดพลาดในการเชื่อมต่อ กรุณาลองใหม่อีกครั้ง'
  } finally {
    loading.value = false
  }
}

function firstValidationError(data) {
  if (data && data.errors) {
    const firstKey = Object.keys(data.errors)[0]
    if (firstKey && Array.isArray(data.errors[firstKey])) {
      return data.errors[firstKey][0]
    }
  }
  return data && data.message ? data.message : ''
}

function shareLink() {
  return `${window.location.origin}/tracking?q=${encodeURIComponent(codes.value.join(','))}`
}

function syncUrl() {
  if (!codes.value.length) return
  const url = `${window.location.pathname}?q=${encodeURIComponent(codes.value.join(','))}`
  window.history.replaceState(null, '', url)
}

async function copyLink() {
  const link = shareLink()
  try {
    await navigator.clipboard.writeText(link)
  } catch (err) {
    const ta = document.createElement('textarea')
    ta.value = link
    document.body.appendChild(ta)
    ta.select()
    document.execCommand('copy')
    document.body.removeChild(ta)
  }
  copied.value = true
  setTimeout(() => { copied.value = false }, 2000)
}

function statusStyle(status) {
  const meta = statusMeta[status] || statusMeta.waiting
  return { color: meta.color, background: meta.bg }
}

function formatCod(amount) {
  return Number(amount || 0).toLocaleString('th-TH', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 2,
  })
}

function formatDateTime(value) {
  if (!value) return ''
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return value
  return date.toLocaleString('th-TH', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

let ticking = false
function onScroll() {
  if (ticking) return
  ticking = true
  window.requestAnimationFrame(() => {
    scrolled.value = window.scrollY > 24
    ticking = false
  })
}

onMounted(() => {
  window.addEventListener('scroll', onScroll, { passive: true })
  onScroll()

  const params = new URLSearchParams(window.location.search)
  const q = params.get('q')
  if (q) {
    splitInput(q).slice(0, MAX_CODES).forEach((part) => addCode(part))
    if (codes.value.length) {
      search()
    }
  }
})

onUnmounted(() => {
  window.removeEventListener('scroll', onScroll)
})
</script>

<template>
  <div class="page">
    <!-- Top bar — landing NavBar language: dark navy, glass-on-scroll -->
    <header class="nav" :class="{ 'is-scrolled': scrolled }">
      <div class="nav-inner">
        <span class="brand" aria-label="โลโก้บริษัท">
          <span class="brand-mark" aria-hidden="true">
            <svg viewBox="0 0 28 28" width="26" height="26" fill="none">
              <rect x="1.5" y="1.5" width="25" height="25" rx="7" stroke="url(#ts-grad)" stroke-width="2" />
              <path d="M8 14.5l3.5 3.5L20 9.5" stroke="url(#ts-grad)" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" />
              <defs>
                <linearGradient id="ts-grad" x1="0" y1="0" x2="28" y2="28" gradientUnits="userSpaceOnUse">
                  <stop stop-color="#2563EB" />
                  <stop offset="1" stop-color="#22D3EE" />
                </linearGradient>
              </defs>
            </svg>
          </span>
          <span class="brand-word">{{ brand }}</span>
        </span>

        <nav class="nav-actions" aria-label="เมนู">
          <a href="/" class="nav-back">← กลับหน้าแรก</a>
          <a href="/login" class="btn-login">เข้าสู่ระบบพนักงาน</a>
        </nav>
      </div>
    </header>

    <!-- Compact dark hero with the search bar relocated into it -->
    <section class="hero">
      <div class="hero-bg" aria-hidden="true">
        <div class="hero-glow"></div>
        <svg class="hero-grid" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
          <defs>
            <pattern id="track-grid" width="44" height="44" patternUnits="userSpaceOnUse">
              <path d="M44 0H0V44" fill="none" stroke="rgba(255,255,255,.05)" stroke-width="1" />
            </pattern>
          </defs>
          <rect width="100%" height="100%" fill="url(#track-grid)" />
        </svg>
      </div>

      <div class="hero-inner">
        <span class="eyebrow">
          <span class="pulse"></span> ติดตามสถานะแบบเรียลไทม์
        </span>
        <h1 class="headline">ติดตามพัสดุ</h1>
        <p class="sub">กรอกรหัสพัสดุเพื่อเช็กสถานะการจัดส่ง — ใส่ได้สูงสุด {{ MAX_CODES }} รายการต่อครั้ง</p>

        <div class="search-bar">
          <div class="chips">
            <span v-for="(code, index) in codes" :key="code" class="chip">
              {{ code }}
              <button type="button" class="chip-remove" aria-label="ลบ" @click="removeCode(index)">×</button>
            </span>
            <input
              v-model="draft"
              class="chip-input"
              type="text"
              :placeholder="codes.length ? 'เพิ่มรหัสพัสดุ…' : 'กรอกรหัสพัสดุ แล้วกด Enter'"
              :disabled="atLimit"
              aria-label="รหัสพัสดุ"
              autocomplete="off"
              @keydown="onKeydown"
              @paste="onPaste"
            />
            <button type="button" class="search-btn" :disabled="loading" @click="search">
              <span v-if="!loading">ค้นหา</span>
              <span v-else>กำลังค้นหา…</span>
            </button>
          </div>
        </div>

        <p v-if="errorMessage" class="error">{{ errorMessage }}</p>
        <p v-else-if="codes.length" class="hint">{{ codes.length }} / {{ MAX_CODES }} รายการ</p>
      </div>
    </section>

    <!-- Light body -->
    <main class="results">
      <!-- Skeleton loaders -->
      <template v-if="loading">
        <div v-for="n in codes.length || 1" :key="'sk-' + n" class="card skeleton-card">
          <div class="shimmer bar w-40"></div>
          <div class="shimmer bar w-70"></div>
          <div class="shimmer bar w-90"></div>
          <div class="shimmer bar w-60"></div>
        </div>
      </template>

      <!-- Result cards -->
      <TransitionGroup v-else name="fade" tag="div" class="card-list">
        <article v-for="result in results" :key="result.code" class="card">
          <template v-if="result.found">
            <div class="card-head">
              <div>
                <div class="code-label">รหัสพัสดุ</div>
                <div class="code-value">{{ result.code }}</div>
              </div>
              <span class="status-badge" :style="statusStyle(result.status)">
                {{ result.status_label }}
              </span>
            </div>

            <div class="receiver" v-if="result.receive_name || result.receive_address">
              <div v-if="result.receive_name" class="receiver-name">{{ result.receive_name }}</div>
              <div v-if="result.receive_address" class="receiver-address">{{ result.receive_address }}</div>
            </div>

            <div class="meta-row">
              <span v-if="result.cod_amount > 0" class="cod-badge">
                COD ฿{{ formatCod(result.cod_amount) }}
              </span>
            </div>

            <div v-if="result.timeline && result.timeline.length" class="timeline">
              <div
                v-for="(event, i) in result.timeline"
                :key="i"
                class="timeline-item"
                :class="{ 'is-latest': i === result.timeline.length - 1 }"
              >
                <span class="timeline-dot"></span>
                <div class="timeline-body">
                  <div class="timeline-event">{{ event.event }}</div>
                  <div v-if="event.note" class="timeline-note">{{ event.note }}</div>
                  <div class="timeline-time">{{ formatDateTime(event.datetime) }}</div>
                </div>
              </div>
            </div>
            <p v-else class="no-timeline">ยังไม่มีประวัติการเคลื่อนไหว</p>
          </template>

          <template v-else>
            <div class="empty">
              <div class="empty-icon">📦</div>
              <div class="empty-title">ไม่พบพัสดุ</div>
              <div class="empty-code">{{ result.code }}</div>
              <p class="empty-text">กรุณาตรวจสอบรหัสพัสดุอีกครั้ง</p>
            </div>
          </template>
        </article>
      </TransitionGroup>

      <div v-if="!loading && hasSearched && results.length" class="share">
        <button type="button" class="share-btn" @click="copyLink">
          <span v-if="!copied">คัดลอกลิงก์</span>
          <span v-else>คัดลอกแล้ว ✓</span>
        </button>
      </div>
    </main>

    <!-- Slim footer matching the landing footer -->
    <footer class="footer">
      <span class="footer-brand">{{ brand }}</span>
      <span class="footer-copy">© {{ year }} {{ brand }}. All rights reserved.</span>
    </footer>
  </div>
</template>

<style scoped>
/* ---- Design tokens (Tech Blue) — copied from the landing system ---- */
.page {
  --navy-950: #060B18;
  --navy-900: #0A1628;
  --blue-700: #1D4ED8;
  --blue-600: #2563EB;
  --cyan-400: #22D3EE;
  --sky-300: #7DD3FC;
  --surface: #F8FAFC;
  --ink-900: #0F172A;
  --ink-500: #64748B;
  --line: #E2E8F0;
  --gradient: linear-gradient(135deg, #1D4ED8 0%, #22D3EE 100%);
  --shadow-card: 0 1px 3px rgba(2, 6, 23, .06), 0 12px 32px rgba(2, 6, 23, .07);
  --font-display: 'Space Grotesk', 'Noto Sans Thai', sans-serif;

  background: var(--surface);
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  overflow-x: clip;
}

/* ---- Top bar (glass-on-scroll), landing NavBar language ---- */
.nav {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 50;
  transition: background .3s ease, box-shadow .3s ease, backdrop-filter .3s ease;
}
.nav.is-scrolled {
  background: rgba(6, 11, 24, .72);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  box-shadow: 0 1px 0 rgba(255, 255, 255, .06), 0 8px 24px rgba(2, 6, 23, .4);
}
.nav-inner {
  max-width: 1080px;
  margin: 0 auto;
  padding: 16px 24px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
}
.brand {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  user-select: none;
}
.brand-mark { display: inline-flex; flex-shrink: 0; }
.brand-word {
  font-family: var(--font-display);
  font-weight: 700;
  font-size: 1.05rem;
  letter-spacing: .12em;
  text-transform: uppercase;
  white-space: nowrap;
  color: #fff;
}
.nav-actions {
  display: flex;
  align-items: center;
  gap: 14px;
}
.nav-back {
  color: #CBD5E1;
  text-decoration: none;
  font-size: .9rem;
  font-weight: 500;
  transition: color .2s ease;
  white-space: nowrap;
}
.nav-back:hover { color: #fff; }
.btn-login {
  display: inline-flex;
  align-items: center;
  border: 1px solid rgba(125, 211, 252, .45);
  color: #E0F2FE;
  text-decoration: none;
  font-size: .9rem;
  font-weight: 600;
  padding: 9px 18px;
  border-radius: 12px;
  background: rgba(34, 211, 238, .04);
  white-space: nowrap;
  transition: background .25s ease, color .2s ease, border-color .25s ease, transform .15s ease;
}
.btn-login:hover {
  background: var(--gradient);
  border-color: transparent;
  color: #fff;
  transform: translateY(-1px);
}

/* ---- Compact dark hero ---- */
.hero {
  position: relative;
  background:
    radial-gradient(70% 60% at 85% -10%, rgba(34, 211, 238, .14), transparent 60%),
    linear-gradient(180deg, var(--navy-950) 0%, var(--navy-900) 100%);
  color: #fff;
  overflow: hidden;
  isolation: isolate;
}
.hero-bg { position: absolute; inset: 0; z-index: 0; }
.hero-glow {
  position: absolute;
  top: -30%;
  right: -8%;
  width: 50vw;
  height: 50vw;
  max-width: 520px;
  max-height: 520px;
  background: radial-gradient(circle, rgba(34, 211, 238, .12), transparent 60%);
  filter: blur(40px);
}
.hero-grid {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  -webkit-mask-image: linear-gradient(180deg, #000 0%, #000 40%, transparent 95%);
  mask-image: linear-gradient(180deg, #000 0%, #000 40%, transparent 95%);
}
.hero-inner {
  position: relative;
  z-index: 1;
  max-width: 720px;
  margin: 0 auto;
  padding: 124px 24px 56px;
  text-align: center;
}
.eyebrow {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: .85rem;
  font-weight: 500;
  color: var(--sky-300);
  background: rgba(125, 211, 252, .08);
  border: 1px solid rgba(125, 211, 252, .2);
  padding: 6px 14px;
  border-radius: 9999px;
}
.pulse {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: var(--cyan-400);
  box-shadow: 0 0 0 0 rgba(34, 211, 238, .6);
  animation: pulse 2s ease-out infinite;
}
@keyframes pulse {
  0% { box-shadow: 0 0 0 0 rgba(34, 211, 238, .5); }
  70% { box-shadow: 0 0 0 8px rgba(34, 211, 238, 0); }
  100% { box-shadow: 0 0 0 0 rgba(34, 211, 238, 0); }
}
.headline {
  margin-top: 20px;
  font-size: clamp(1.9rem, 5vw, 3rem);
  font-weight: 800;
  line-height: 1.15;
  letter-spacing: -.01em;
}
.sub {
  margin-top: 14px;
  font-size: 1.0625rem;
  line-height: 1.7;
  color: #94A3B8;
}

/* ---- Search bar (chips) — white pill on dark ---- */
.search-bar { margin-top: 28px; }
.chips {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 8px;
  background: rgba(255, 255, 255, .96);
  border: 1px solid rgba(148, 163, 184, .25);
  border-radius: 16px;
  padding: 10px 10px 10px 16px;
  box-shadow: 0 0 60px rgba(34, 211, 238, .12);
  transition: box-shadow .25s ease, border-color .25s ease;
  text-align: left;
}
.chips:focus-within {
  border-color: rgba(34, 211, 238, .55);
  box-shadow: 0 0 0 4px rgba(34, 211, 238, .12), 0 0 60px rgba(34, 211, 238, .22);
}
.chip {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #EFF6FF;
  color: #1D4ED8;
  border-radius: 9999px;
  padding: 4px 12px;
  font-family: var(--font-display);
  font-size: 13px;
  font-weight: 500;
  letter-spacing: .02em;
  white-space: nowrap;
}
.chip-remove {
  border: none;
  background: transparent;
  color: #1D4ED8;
  cursor: pointer;
  font-size: 16px;
  line-height: 1;
  padding: 0;
  opacity: .65;
}
.chip-remove:hover { opacity: 1; }
.chip-input {
  flex: 1 1 140px;
  min-width: 140px;
  border: none;
  outline: none;
  font-family: inherit;
  font-size: 15px;
  color: #0F172A;
  background: transparent;
  padding: 6px 4px;
}
.chip-input::placeholder { color: #94A3B8; }
.search-btn {
  border: none;
  background: var(--gradient);
  color: #fff;
  font-family: inherit;
  font-size: 14px;
  font-weight: 700;
  border-radius: 12px;
  padding: 11px 22px;
  cursor: pointer;
  white-space: nowrap;
  transition: filter .2s ease, transform .15s ease;
}
.search-btn:hover { filter: brightness(1.08); transform: translateY(-1px); }
.search-btn:active { transform: scale(.98); }
.search-btn:disabled { filter: grayscale(.3) brightness(.95); cursor: default; transform: none; }

.error {
  margin-top: 14px;
  font-size: 13px;
  color: #FCA5A5;
}
.hint {
  margin-top: 14px;
  font-size: 13px;
  color: #64748B;
}

/* ---- Light body ---- */
.results {
  flex: 1;
  width: 100%;
  max-width: 720px;
  margin: 0 auto;
  padding: 28px 16px 64px;
}
.card-list { display: flex; flex-direction: column; gap: 16px; }
.card {
  background: #fff;
  border-radius: 20px;
  box-shadow: var(--shadow-card);
  padding: 24px;
}
.card-head {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 12px;
}
.code-label {
  font-size: 11px;
  color: #94A3B8;
  letter-spacing: .04em;
}
.code-value {
  font-family: var(--font-display);
  font-size: 17px;
  font-weight: 700;
  letter-spacing: .03em;
  color: #0F172A;
  margin-top: 3px;
  word-break: break-all;
}
.status-badge {
  flex-shrink: 0;
  font-size: 13px;
  font-weight: 600;
  border-radius: 9999px;
  padding: 6px 14px;
  white-space: nowrap;
}

.receiver { margin-top: 16px; }
.receiver-name {
  font-size: 15px;
  font-weight: 500;
  color: #1E293B;
}
.receiver-address {
  margin-top: 4px;
  font-size: 14px;
  color: #64748B;
  line-height: 1.5;
}

.meta-row {
  margin-top: 12px;
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}
.cod-badge {
  display: inline-flex;
  align-items: center;
  background: #FFF7ED;
  color: #C2410C;
  font-size: 13px;
  font-weight: 600;
  border-radius: 9999px;
  padding: 5px 12px;
}

/* ---- Timeline (gradient accent on the latest dot) ---- */
.timeline {
  margin-top: 20px;
  padding-left: 4px;
  border-left: 2px solid #E2E8F0;
}
.timeline-item {
  position: relative;
  padding: 0 0 18px 22px;
}
.timeline-item:last-child { padding-bottom: 0; }
.timeline-dot {
  position: absolute;
  left: -7px;
  top: 3px;
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: #CBD5E1;
  border: 2px solid #fff;
}
.timeline-item.is-latest .timeline-dot {
  background: var(--gradient);
  border-color: #fff;
  box-shadow: 0 0 0 4px rgba(34, 211, 238, .18);
}
.timeline-event {
  font-size: 14px;
  font-weight: 500;
  color: #1E293B;
}
.timeline-note {
  margin-top: 2px;
  font-size: 13px;
  color: #64748B;
}
.timeline-time {
  margin-top: 2px;
  font-size: 12px;
  color: #94A3B8;
}
.no-timeline {
  margin-top: 16px;
  font-size: 13px;
  color: #94A3B8;
}

/* ---- Empty / not found ---- */
.empty {
  text-align: center;
  padding: 16px 0;
}
.empty-icon { font-size: 34px; }
.empty-title {
  margin-top: 8px;
  font-size: 16px;
  font-weight: 600;
  color: #334155;
}
.empty-code {
  margin-top: 4px;
  font-family: var(--font-display);
  font-size: 14px;
  font-weight: 500;
  letter-spacing: .03em;
  color: #2563EB;
  word-break: break-all;
}
.empty-text {
  margin-top: 6px;
  font-size: 13px;
  color: #94A3B8;
}

/* ---- Share ---- */
.share {
  margin-top: 20px;
  text-align: center;
}
.share-btn {
  border: 1px solid #E2E8F0;
  background: #fff;
  color: #475569;
  font-family: inherit;
  font-size: 14px;
  font-weight: 500;
  border-radius: 12px;
  padding: 10px 22px;
  cursor: pointer;
  transition: border-color .15s ease, color .15s ease;
}
.share-btn:hover {
  border-color: #2563EB;
  color: #2563EB;
}

/* ---- Skeleton shimmer ---- */
.skeleton-card { display: flex; flex-direction: column; gap: 14px; }
.bar {
  height: 14px;
  border-radius: 7px;
}
.w-40 { width: 40%; }
.w-60 { width: 60%; }
.w-70 { width: 70%; }
.w-90 { width: 90%; }
.shimmer {
  background: linear-gradient(90deg, #EEF2F7 25%, #E2E8F0 37%, #EEF2F7 63%);
  background-size: 400% 100%;
  animation: shimmer 1.4s ease infinite;
}
@keyframes shimmer {
  0% { background-position: 100% 50%; }
  100% { background-position: 0 50%; }
}

/* ---- Card enter / leave transitions ---- */
.fade-enter-active,
.fade-leave-active {
  transition: opacity .35s ease, transform .35s ease;
}
.fade-enter-from {
  opacity: 0;
  transform: translateY(12px);
}
.fade-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}

/* ---- Slim footer (landing footer language) ---- */
.footer {
  background: var(--navy-900);
  color: #64748B;
  padding: 22px 24px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  flex-wrap: wrap;
}
.footer-brand {
  font-family: var(--font-display);
  font-weight: 700;
  font-size: .95rem;
  letter-spacing: .12em;
  text-transform: uppercase;
  color: #fff;
}
.footer-copy {
  font-size: .85rem;
  color: #64748B;
}

@media (max-width: 520px) {
  .nav-back { display: none; }
  .hero-inner { padding-top: 104px; }
  .card { padding: 20px; }
  .footer { justify-content: center; text-align: center; }
}

/* ---- Accessibility: kill non-essential motion ---- */
@media (prefers-reduced-motion: reduce) {
  .page *,
  .page *::before,
  .page *::after {
    animation-duration: .001ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: .001ms !important;
  }
}
</style>
