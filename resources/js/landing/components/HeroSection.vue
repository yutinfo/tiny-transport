<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const code = ref('')
const parallax = ref(null)
let ticking = false

const reducedMotion = typeof window !== 'undefined'
  && window.matchMedia
  && window.matchMedia('(prefers-reduced-motion: reduce)').matches

// Floating parcel dots — positions in %, each with its own parallax depth.
const dots = [
  { top: 18, left: 12, size: 10, depth: 0.06, delay: 0 },
  { top: 30, left: 82, size: 14, depth: 0.10, delay: 1.2 },
  { top: 64, left: 8, size: 8, depth: 0.08, delay: 0.6 },
  { top: 72, left: 70, size: 12, depth: 0.05, delay: 1.8 },
  { top: 44, left: 46, size: 7, depth: 0.12, delay: 2.4 },
  { top: 84, left: 30, size: 10, depth: 0.07, delay: 0.9 },
]

function submitTrack() {
  const value = code.value.trim()
  if (!value) return
  window.location = '/tracking?q=' + encodeURIComponent(value)
}

function onScroll() {
  if (ticking || !parallax.value) return
  ticking = true
  window.requestAnimationFrame(() => {
    const y = window.scrollY
    const nodes = parallax.value.querySelectorAll('[data-depth]')
    nodes.forEach((el) => {
      const depth = parseFloat(el.dataset.depth)
      const shift = Math.min(y * depth, 24) // hard cap 24px per spec
      el.style.transform = `translate3d(0, ${shift}px, 0)`
    })
    ticking = false
  })
}

onMounted(() => {
  // Parallax disabled on mobile and under reduced-motion.
  if (!reducedMotion && window.innerWidth > 860) {
    window.addEventListener('scroll', onScroll, { passive: true })
  }
})
onUnmounted(() => {
  window.removeEventListener('scroll', onScroll)
})
</script>

<template>
  <section id="top" class="hero">
    <!-- Background layers (all CSS/SVG, zero images) -->
    <div class="bg" aria-hidden="true">
      <div class="glow"></div>
      <div class="mesh mesh-a"></div>
      <div class="mesh mesh-b"></div>
      <svg class="grid" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
        <defs>
          <pattern id="hero-grid" width="44" height="44" patternUnits="userSpaceOnUse">
            <path d="M44 0H0V44" fill="none" stroke="rgba(255,255,255,.05)" stroke-width="1" />
          </pattern>
        </defs>
        <rect width="100%" height="100%" fill="url(#hero-grid)" />
      </svg>
      <div ref="parallax" class="dots">
        <span
          v-for="(d, i) in dots"
          :key="i"
          class="dot"
          :data-depth="d.depth"
          :style="{
            top: d.top + '%',
            left: d.left + '%',
            width: d.size + 'px',
            height: d.size + 'px',
            animationDelay: d.delay + 's',
          }"
        ></span>
      </div>
    </div>

    <div class="hero-inner">
      <div class="hero-copy">
        <span class="eyebrow">
          <span class="pulse"></span> ขนส่งพัสดุด่วน · ทั่วไทย
        </span>
        <h1 class="headline">
          ส่งไว ติดตามได้<br>
          <span class="accent">ทุกพัสดุ</span>
        </h1>
        <p class="sub">
          บริการขนส่งพัสดุด่วน เก็บเงินปลายทาง ติดตามสถานะแบบเรียลไทม์ ครอบคลุมทุกพื้นที่
        </p>

        <form class="track" @submit.prevent="submitTrack">
          <input
            v-model="code"
            type="text"
            class="track-input"
            placeholder="กรอกรหัสพัสดุ เช่น P2026XXXXXXX"
            aria-label="รหัสพัสดุ"
            autocomplete="off"
          />
          <button type="submit" class="track-btn">ติดตามพัสดุ</button>
        </form>

        <div class="cta-row">
          <a href="#services" class="ghost">ดูบริการของเรา</a>
          <a href="/login" class="textlink">สำหรับพนักงาน →</a>
        </div>
      </div>

      <!-- Mock tracking card -->
      <div class="hero-visual" aria-hidden="true">
        <div class="mock-card">
          <div class="mock-glow"></div>
          <div class="mock-head">
            <div>
              <div class="mock-label">รหัสพัสดุ</div>
              <div class="mock-code">P2026000142</div>
            </div>
            <span class="mock-badge">กำลังจัดส่ง</span>
          </div>
          <div class="mock-route">
            <span>กรุงเทพฯ</span>
            <span class="mock-arrow">→</span>
            <span>เชียงใหม่</span>
          </div>
          <ul class="mock-timeline">
            <li class="done"><span class="tl-dot"></span> รับพัสดุเข้าระบบ</li>
            <li class="done"><span class="tl-dot"></span> เข้ารอบจัดส่ง</li>
            <li class="active"><span class="tl-dot"></span> คนขับกำลังนำส่ง</li>
          </ul>
          <div class="mock-cod">COD ฿1,250</div>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.hero {
  position: relative;
  min-height: 100svh;
  background:
    radial-gradient(70% 60% at 85% -5%, rgba(34, 211, 238, .14), transparent 60%),
    linear-gradient(180deg, var(--navy-950) 0%, var(--navy-900) 100%);
  color: #fff;
  overflow: hidden;
  isolation: isolate;
}

/* ---- Background layers ---- */
.bg { position: absolute; inset: 0; z-index: 0; }
.glow {
  position: absolute;
  top: -20%;
  right: -10%;
  width: 60vw;
  height: 60vw;
  background: radial-gradient(circle, rgba(34, 211, 238, .12), transparent 60%);
  filter: blur(40px);
}
.mesh {
  position: absolute;
  border-radius: 50%;
  filter: blur(80px);
  opacity: .5;
  will-change: transform;
}
.mesh-a {
  width: 480px;
  height: 480px;
  top: -120px;
  left: -80px;
  background: radial-gradient(circle, rgba(37, 99, 235, .55), transparent 70%);
  animation: drift-a 18s ease-in-out infinite;
}
.mesh-b {
  width: 420px;
  height: 420px;
  bottom: -120px;
  right: 6%;
  background: radial-gradient(circle, rgba(34, 211, 238, .4), transparent 70%);
  animation: drift-b 18s ease-in-out infinite;
}
@keyframes drift-a {
  0%, 100% { transform: translate(0, 0) scale(1); }
  50% { transform: translate(60px, 40px) scale(1.12); }
}
@keyframes drift-b {
  0%, 100% { transform: translate(0, 0) scale(1); }
  50% { transform: translate(-50px, -30px) scale(1.1); }
}
.grid {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  -webkit-mask-image: linear-gradient(180deg, #000 0%, #000 45%, transparent 90%);
  mask-image: linear-gradient(180deg, #000 0%, #000 45%, transparent 90%);
}
.dots { position: absolute; inset: 0; }
.dot {
  position: absolute;
  background: var(--cyan-400);
  border-radius: 3px;
  box-shadow: 0 0 12px rgba(34, 211, 238, .6);
  opacity: .65;
  animation: bob 7s ease-in-out infinite;
}
@keyframes bob {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-12px); }
}

/* ---- Layout ---- */
.hero-inner {
  position: relative;
  z-index: 1;
  max-width: 1160px;
  margin: 0 auto;
  padding: 132px 24px 80px;
  display: grid;
  grid-template-columns: 1.05fr .95fr;
  align-items: center;
  gap: 48px;
  min-height: 100svh;
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
  margin-top: 22px;
  font-size: clamp(2.2rem, 6vw, 4rem);
  font-weight: 800;
  line-height: 1.12;
  letter-spacing: -.01em;
}
.accent {
  background: linear-gradient(135deg, #7DD3FC 0%, #22D3EE 50%, #2563EB 100%);
  background-size: 200% auto;
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
  color: transparent;
  animation: shimmer-text 5s linear infinite;
}
@keyframes shimmer-text {
  to { background-position: 200% center; }
}

.sub {
  margin-top: 20px;
  max-width: 30em;
  font-size: 1.0625rem;
  line-height: 1.7;
  color: #94A3B8;
}

/* ---- Tracking search ---- */
.track {
  margin-top: 32px;
  display: flex;
  align-items: stretch;
  gap: 8px;
  background: rgba(255, 255, 255, .05);
  border: 1px solid rgba(148, 163, 184, .25);
  border-radius: 9999px;
  padding: 7px 7px 7px 8px;
  max-width: 520px;
  box-shadow: 0 0 60px rgba(34, 211, 238, .12);
  transition: border-color .25s ease, box-shadow .25s ease;
}
.track:focus-within {
  border-color: rgba(34, 211, 238, .55);
  box-shadow: 0 0 60px rgba(34, 211, 238, .22);
}
.track-input {
  flex: 1;
  min-width: 0;
  background: transparent;
  border: none;
  outline: none;
  color: #fff;
  font-family: inherit;
  font-size: 1rem;
  padding: 12px 16px;
}
.track-input::placeholder { color: #64748B; }
.track-btn {
  flex-shrink: 0;
  border: none;
  cursor: pointer;
  font-family: inherit;
  font-size: .95rem;
  font-weight: 700;
  color: #fff;
  background: var(--gradient);
  border-radius: 9999px;
  padding: 12px 26px;
  transition: transform .15s ease, filter .2s ease;
}
.track-btn:hover { filter: brightness(1.08); transform: translateY(-1px); }
.track-btn:active { transform: scale(.98); }

.cta-row {
  margin-top: 24px;
  display: flex;
  align-items: center;
  gap: 22px;
  flex-wrap: wrap;
}
.ghost {
  color: #E2E8F0;
  text-decoration: none;
  font-weight: 600;
  font-size: .95rem;
  border: 1px solid rgba(148, 163, 184, .3);
  padding: 11px 22px;
  border-radius: 12px;
  transition: background .2s ease, border-color .2s ease;
}
.ghost:hover {
  background: rgba(255, 255, 255, .05);
  border-color: rgba(148, 163, 184, .5);
}
.textlink {
  color: var(--sky-300);
  text-decoration: none;
  font-weight: 600;
  font-size: .95rem;
  transition: color .2s ease;
}
.textlink:hover { color: var(--cyan-400); }

/* ---- Mock tracking card ---- */
.hero-visual {
  display: flex;
  justify-content: center;
  perspective: 1200px;
}
.mock-card {
  position: relative;
  width: 340px;
  max-width: 100%;
  background: rgba(13, 22, 42, .72);
  border: 1px solid rgba(148, 163, 184, .18);
  border-radius: 22px;
  padding: 24px;
  backdrop-filter: blur(8px);
  box-shadow: 0 30px 80px rgba(2, 6, 23, .6), 0 0 60px rgba(34, 211, 238, .12);
  transform: rotate(-4deg);
  animation: bob-card 6s ease-in-out infinite;
}
@keyframes bob-card {
  0%, 100% { transform: rotate(-4deg) translateY(0); }
  50% { transform: rotate(-4deg) translateY(-12px); }
}
.mock-glow {
  position: absolute;
  inset: -1px;
  border-radius: 22px;
  background: linear-gradient(135deg, rgba(37, 99, 235, .25), rgba(34, 211, 238, .25));
  filter: blur(20px);
  z-index: -1;
}
.mock-head {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
}
.mock-label { font-size: .7rem; color: #64748B; letter-spacing: .05em; }
.mock-code {
  font-family: var(--font-display);
  font-weight: 700;
  font-size: 1.1rem;
  color: #fff;
  margin-top: 4px;
  letter-spacing: .04em;
}
.mock-badge {
  font-size: .72rem;
  font-weight: 700;
  color: #0A1628;
  background: var(--cyan-400);
  border-radius: 9999px;
  padding: 5px 12px;
  white-space: nowrap;
}
.mock-route {
  margin-top: 18px;
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: .9rem;
  color: #CBD5E1;
  font-weight: 500;
}
.mock-arrow { color: var(--cyan-400); }
.mock-timeline {
  margin-top: 20px;
  list-style: none;
  display: flex;
  flex-direction: column;
  gap: 14px;
}
.mock-timeline li {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: .85rem;
  color: #64748B;
}
.tl-dot {
  width: 11px;
  height: 11px;
  border-radius: 50%;
  background: #334155;
  flex-shrink: 0;
}
.mock-timeline .done { color: #94A3B8; }
.mock-timeline .done .tl-dot { background: #2563EB; }
.mock-timeline .active { color: #fff; font-weight: 600; }
.mock-timeline .active .tl-dot {
  background: var(--cyan-400);
  box-shadow: 0 0 0 4px rgba(34, 211, 238, .2);
}
.mock-cod {
  margin-top: 20px;
  display: inline-flex;
  font-size: .82rem;
  font-weight: 700;
  color: #FDBA74;
  background: rgba(251, 146, 60, .12);
  border-radius: 9999px;
  padding: 6px 14px;
}

@media (max-width: 980px) {
  .hero-inner { grid-template-columns: 1fr; gap: 56px; }
  .hero-visual { order: -1; }
  .mock-card { width: 300px; }
}
@media (max-width: 860px) {
  .hero-visual { display: none; }
  .hero-inner { padding-top: 112px; min-height: auto; }
}
@media (max-width: 520px) {
  .track { flex-direction: column; border-radius: 20px; padding: 10px; }
  .track-btn { border-radius: 12px; padding: 14px; }
}
</style>
