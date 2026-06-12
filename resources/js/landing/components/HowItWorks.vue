<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
// v-reveal directive: imported as `vReveal`, usable as `v-reveal` in template.
import { vReveal } from '../reveal.js'

const steps = [
  { n: '01', title: 'สร้างออเดอร์', detail: 'กรอกข้อมูลผู้รับและพัสดุในไม่กี่ขั้นตอน' },
  { n: '02', title: 'เข้ารอบจัดส่ง', detail: 'พัสดุถูกจัดเข้ารอบและมอบหมายคนขับ' },
  { n: '03', title: 'คนขับนำส่ง', detail: 'ติดตามสถานะการจัดส่งได้แบบเรียลไทม์' },
  { n: '04', title: 'ลูกค้ารับของ', detail: 'ยืนยันการรับและเก็บเงินปลายทางอัตโนมัติ' },
]

const line = ref(null)
let observer = null

const reducedMotion = typeof window !== 'undefined'
  && window.matchMedia
  && window.matchMedia('(prefers-reduced-motion: reduce)').matches

onMounted(() => {
  if (reducedMotion || !('IntersectionObserver' in window) || !line.value) {
    if (line.value) line.value.classList.add('is-drawn')
    return
  }
  observer = new IntersectionObserver((entries, obs) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        line.value.classList.add('is-drawn')
        obs.disconnect()
      }
    })
  }, { threshold: 0.5 })
  observer.observe(line.value)
})
onUnmounted(() => {
  if (observer) observer.disconnect()
})
</script>

<template>
  <section id="how" class="how">
    <div class="how-inner">
      <header v-reveal class="head">
        <h2>ใช้งานง่ายใน 4 ขั้นตอน</h2>
      </header>

      <div class="stepper">
        <!-- Connecting line that draws on scroll -->
        <svg ref="line" class="connector" viewBox="0 0 1000 4" preserveAspectRatio="none" aria-hidden="true">
          <line x1="0" y1="2" x2="1000" y2="2" stroke="url(#how-grad)" stroke-width="3" />
          <defs>
            <linearGradient id="how-grad" x1="0" y1="0" x2="1000" y2="0" gradientUnits="userSpaceOnUse">
              <stop stop-color="#2563EB" />
              <stop offset="1" stop-color="#22D3EE" />
            </linearGradient>
          </defs>
        </svg>

        <div
          v-for="(s, i) in steps"
          :key="s.n"
          v-reveal
          class="step"
          :style="{ transitionDelay: (i * 90) + 'ms' }"
        >
          <span class="circle">{{ s.n }}</span>
          <h3>{{ s.title }}</h3>
          <p>{{ s.detail }}</p>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.how {
  background: var(--card);
  padding: 80px 24px;
}
.how-inner {
  max-width: 1080px;
  margin: 0 auto;
}
.head { text-align: center; margin-bottom: 60px; }
.head h2 {
  font-size: clamp(1.6rem, 4vw, 2.4rem);
  font-weight: 800;
  color: var(--ink-900);
}

.stepper {
  position: relative;
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 24px;
}
.connector {
  position: absolute;
  top: 28px;
  left: 12.5%;
  width: 75%;
  height: 4px;
  z-index: 0;
}
.connector line {
  stroke-dasharray: 1000;
  stroke-dashoffset: 1000;
  transition: stroke-dashoffset 1.4s ease;
}
.connector.is-drawn line {
  stroke-dashoffset: 0;
}

.step {
  position: relative;
  z-index: 1;
  text-align: center;
}
.circle {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 56px;
  height: 56px;
  border-radius: 50%;
  font-family: var(--font-display);
  font-weight: 700;
  font-size: 1.05rem;
  color: #fff;
  background: var(--gradient);
  box-shadow: 0 8px 24px rgba(37, 99, 235, .3);
  border: 4px solid var(--card);
}
.step h3 {
  margin-top: 18px;
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--ink-900);
}
.step p {
  margin-top: 8px;
  font-size: .93rem;
  line-height: 1.6;
  color: var(--ink-500);
}

@media (max-width: 760px) {
  .stepper {
    grid-template-columns: 1fr;
    gap: 36px;
    max-width: 360px;
    margin: 0 auto;
  }
  .connector { display: none; }
  .step {
    display: grid;
    grid-template-columns: 56px 1fr;
    gap: 18px;
    text-align: left;
    align-items: start;
  }
  .step h3 { margin-top: 6px; }
}
</style>
