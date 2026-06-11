<script setup>
import { ref, computed, onMounted } from 'vue'

const MAX_CODES = 10

const codes = ref([])
const draft = ref('')
const results = ref([])
const loading = ref(false)
const errorMessage = ref('')
const hasSearched = ref(false)
const copied = ref(false)

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
  return `${window.location.origin}/web?q=${encodeURIComponent(codes.value.join(','))}`
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

onMounted(() => {
  const params = new URLSearchParams(window.location.search)
  const q = params.get('q')
  if (q) {
    splitInput(q).slice(0, MAX_CODES).forEach((part) => addCode(part))
    if (codes.value.length) {
      search()
    }
  }
})
</script>

<template>
  <div class="page">
    <header class="hero">
      <div class="brand">TINY TRANSPORT</div>
      <p class="subtitle">ติดตามพัสดุของคุณ</p>

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
      <p v-else-if="codes.length" class="hint">
        {{ codes.length }} / {{ MAX_CODES }} รายการ
      </p>
    </header>

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
  </div>
</template>

<style scoped>
.page {
  max-width: 720px;
  margin: 0 auto;
  padding: 0 16px 64px;
}

/* Hero */
.hero {
  padding: 56px 0 24px;
  text-align: center;
}
.brand {
  font-size: 22px;
  font-weight: 700;
  letter-spacing: 0.28em;
  color: #0F172A;
}
.subtitle {
  margin-top: 8px;
  font-size: 15px;
  font-weight: 400;
  color: #64748B;
}

/* Search bar */
.search-bar {
  margin-top: 28px;
}
.chips {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 8px;
  background: #fff;
  border: 1px solid #E2E8F0;
  border-radius: 16px;
  padding: 10px 10px 10px 16px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, .06), 0 4px 16px rgba(0, 0, 0, .04);
  transition: box-shadow .2s ease, border-color .2s ease;
}
.chips:focus-within {
  border-color: #BFDBFE;
  box-shadow: 0 0 0 4px rgba(37, 99, 235, .08), 0 4px 16px rgba(0, 0, 0, .04);
}
.chip {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #EFF6FF;
  color: #1D4ED8;
  border-radius: 9999px;
  padding: 4px 12px;
  font-size: 13px;
  font-weight: 500;
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
  flex: 1 1 120px;
  min-width: 120px;
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
  background: #2563EB;
  color: #fff;
  font-family: inherit;
  font-size: 14px;
  font-weight: 600;
  border-radius: 12px;
  padding: 10px 20px;
  cursor: pointer;
  white-space: nowrap;
  transition: background .15s ease, transform .05s ease;
}
.search-btn:hover { background: #1D4ED8; }
.search-btn:active { transform: scale(.98); }
.search-btn:disabled { background: #93C5FD; cursor: default; }

.error {
  margin-top: 12px;
  font-size: 13px;
  color: #DC2626;
}
.hint {
  margin-top: 12px;
  font-size: 13px;
  color: #94A3B8;
}

/* Cards */
.results { margin-top: 12px; }
.card-list { display: flex; flex-direction: column; gap: 16px; }
.card {
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, .08), 0 4px 16px rgba(0, 0, 0, .04);
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
  font-size: 17px;
  font-weight: 600;
  color: #0F172A;
  margin-top: 2px;
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

/* Timeline */
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
  background: #2563EB;
  box-shadow: 0 0 0 4px rgba(37, 99, 235, .15);
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

/* Empty / not found */
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
  font-size: 14px;
  font-weight: 500;
  color: #2563EB;
  word-break: break-all;
}
.empty-text {
  margin-top: 6px;
  font-size: 13px;
  color: #94A3B8;
}

/* Share */
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

/* Skeleton shimmer */
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

/* Card enter / leave transitions */
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

@media (max-width: 480px) {
  .hero { padding-top: 40px; }
  .brand { font-size: 19px; letter-spacing: .22em; }
  .card { padding: 20px; }
  .search-btn { padding: 10px 16px; }
}
</style>
