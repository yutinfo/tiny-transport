<script setup>
// v-reveal directive: imported as `vReveal`, usable as `v-reveal` in template.
import { vReveal } from '../reveal.js'

const services = [
  {
    title: 'ส่งพัสดุด่วน',
    desc: 'รับ-ส่งถึงที่ รวดเร็ว ปลอดภัย ตรงเวลา',
    icon: 'truck',
  },
  {
    title: 'เก็บเงินปลายทาง (COD)',
    desc: 'เก็บเงินแทนคุณ โอนคืนไว ตรวจสอบยอดได้ทุกรายการ',
    icon: 'cash',
  },
  {
    title: 'ติดตามเรียลไทม์',
    desc: 'เช็กสถานะพัสดุได้ตลอด 24 ชม. ผ่านรหัสติดตาม',
    icon: 'radar',
  },
  {
    title: 'ลาเบล & QR',
    desc: 'พิมพ์ลาเบลพร้อม QR สแกนเช็กสถานะได้ทันที',
    icon: 'qr',
  },
]
</script>

<template>
  <section id="services" class="services">
    <div class="services-inner">
      <header v-reveal class="head">
        <h2>บริการของเรา</h2>
        <p>ครบทุกความต้องการด้านขนส่ง</p>
      </header>

      <div class="grid">
        <article
          v-for="(s, i) in services"
          :key="s.title"
          v-reveal
          class="card"
          :style="{ transitionDelay: (i * 80) + 'ms' }"
        >
          <span class="icon">
            <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <template v-if="s.icon === 'truck'">
                <path d="M3 6h11v9H3z" />
                <path d="M14 9h4l3 3v3h-7z" />
                <circle cx="7" cy="18" r="1.6" />
                <circle cx="17" cy="18" r="1.6" />
              </template>
              <template v-else-if="s.icon === 'cash'">
                <rect x="2.5" y="6" width="19" height="12" rx="2" />
                <circle cx="12" cy="12" r="2.6" />
                <path d="M6 9.5h.01M18 14.5h.01" />
              </template>
              <template v-else-if="s.icon === 'radar'">
                <circle cx="12" cy="12" r="8.5" />
                <circle cx="12" cy="12" r="4.5" />
                <circle cx="12" cy="12" r="1" fill="currentColor" />
                <path d="M12 12l5-5" />
              </template>
              <template v-else>
                <rect x="3.5" y="3.5" width="7" height="7" rx="1" />
                <rect x="13.5" y="3.5" width="7" height="7" rx="1" />
                <rect x="3.5" y="13.5" width="7" height="7" rx="1" />
                <path d="M13.5 13.5h3v3M20.5 16.5v4M16.5 20.5h4" />
              </template>
            </svg>
          </span>
          <h3>{{ s.title }}</h3>
          <p>{{ s.desc }}</p>
        </article>
      </div>
    </div>
  </section>
</template>

<style scoped>
.services {
  background: var(--surface);
  padding: 72px 24px 80px;
}
.services-inner {
  max-width: 1080px;
  margin: 0 auto;
}
.head { text-align: center; margin-bottom: 48px; }
.head h2 {
  font-size: clamp(1.6rem, 4vw, 2.4rem);
  font-weight: 800;
  color: var(--ink-900);
}
.head p {
  margin-top: 12px;
  font-size: 1.0625rem;
  color: var(--ink-500);
}
.grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 24px;
}
.card {
  background: var(--card);
  border: 1px solid var(--line);
  border-radius: 20px;
  padding: 32px;
  box-shadow: var(--shadow-card);
  transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease,
              opacity .7s cubic-bezier(.16,1,.3,1);
}
.card:hover {
  transform: translateY(-4px);
  border-color: var(--cyan-400);
  box-shadow: 0 1px 3px rgba(2,6,23,.06), 0 22px 48px rgba(2,6,23,.12);
}
.icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 56px;
  height: 56px;
  border-radius: 16px;
  color: var(--blue-600);
  background: linear-gradient(135deg, rgba(37,99,235,.12), rgba(34,211,238,.12));
  transition: transform .25s ease, color .25s ease;
}
.card:hover .icon {
  transform: rotate(6deg);
  color: var(--cyan-400);
}
.card h3 {
  margin-top: 22px;
  font-size: 1.2rem;
  font-weight: 700;
  color: var(--ink-900);
}
.card p {
  margin-top: 10px;
  font-size: .97rem;
  line-height: 1.65;
  color: var(--ink-500);
}
@media (max-width: 640px) {
  .grid { grid-template-columns: 1fr; }
  .card { padding: 26px; }
}
</style>
