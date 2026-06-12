<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

// Placeholder values — edit here in one place.
// `target` drives the count-up; `suffix`/`prefix` wrap it; `decimals` for %.
const stats = [
  { prefix: '', target: 99.2, decimals: 1, suffix: '%', label: 'ส่งสำเร็จ' },
  { prefix: '', target: 77, decimals: 0, suffix: '', label: 'จังหวัด' },
  { prefix: '', target: 10, decimals: 0, suffix: 'K+', label: 'พัสดุ/เดือน' },
  { prefix: '', target: 24, decimals: 0, suffix: ' ชม.', label: 'ติดตามได้' },
]

const display = ref(stats.map(() => 0))
const root = ref(null)
let observer = null

const reducedMotion = typeof window !== 'undefined'
  && window.matchMedia
  && window.matchMedia('(prefers-reduced-motion: reduce)').matches

function format(value, decimals) {
  return value.toLocaleString('en-US', {
    minimumFractionDigits: decimals,
    maximumFractionDigits: decimals,
  })
}

function runCountUp() {
  const duration = 1200
  const start = performance.now()
  function tick(now) {
    const t = Math.min((now - start) / duration, 1)
    const eased = 1 - Math.pow(1 - t, 3) // ease-out cubic
    display.value = stats.map((s) => s.target * eased)
    if (t < 1) requestAnimationFrame(tick)
    else display.value = stats.map((s) => s.target)
  }
  requestAnimationFrame(tick)
}

onMounted(() => {
  if (reducedMotion || !('IntersectionObserver' in window)) {
    display.value = stats.map((s) => s.target)
    return
  }
  observer = new IntersectionObserver((entries, obs) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        runCountUp()
        obs.disconnect()
      }
    })
  }, { threshold: 0.4 })
  if (root.value) observer.observe(root.value)
})
onUnmounted(() => {
  if (observer) observer.disconnect()
})
</script>

<template>
  <section ref="root" class="stats">
    <div class="stats-inner">
      <div v-for="(s, i) in stats" :key="i" class="stat">
        <div class="stat-num">{{ s.prefix }}{{ format(display[i], s.decimals) }}{{ s.suffix }}</div>
        <div class="stat-label">{{ s.label }}</div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.stats {
  position: relative;
  background: linear-gradient(180deg, var(--navy-900) 0%, var(--surface) 100%);
  padding: 8px 24px 56px;
}
.stats-inner {
  max-width: 1000px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
  background: var(--card);
  border: 1px solid var(--line);
  border-radius: 24px;
  padding: 36px 24px;
  box-shadow: var(--shadow-card);
}
.stat { text-align: center; }
.stat-num {
  font-family: var(--font-display);
  font-weight: 700;
  font-size: clamp(1.8rem, 4vw, 2.6rem);
  line-height: 1;
  background: var(--gradient);
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
  color: transparent;
}
.stat-label {
  margin-top: 10px;
  font-size: .92rem;
  color: var(--ink-500);
  font-weight: 500;
}
@media (max-width: 680px) {
  .stats-inner { grid-template-columns: repeat(2, 1fr); gap: 28px 16px; }
}
</style>
