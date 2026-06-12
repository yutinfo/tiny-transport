<script setup>
// v-reveal directive: imported as `vReveal`, usable as `v-reveal` in template.
import { vReveal } from '../reveal.js'
</script>

<template>
  <section class="cta">
    <div class="cta-bg" aria-hidden="true"></div>
    <div v-reveal class="cta-inner">
      <h2>อยากรู้ว่าพัสดุถึงไหนแล้ว?</h2>
      <p>กรอกรหัสติดตามเพื่อเช็กสถานะพัสดุของคุณได้ทันที ตลอด 24 ชั่วโมง</p>
      <a href="/tracking" class="cta-btn">ติดตามพัสดุเลย →</a>
    </div>
  </section>
</template>

<style scoped>
.cta {
  position: relative;
  padding: 80px 24px;
  background: var(--gradient);
  overflow: hidden;
  isolation: isolate;
}
/* subtle grid texture over the gradient */
.cta-bg {
  position: absolute;
  inset: 0;
  z-index: 0;
  background-image:
    linear-gradient(rgba(255, 255, 255, .08) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255, 255, 255, .08) 1px, transparent 1px);
  background-size: 44px 44px;
  -webkit-mask-image: radial-gradient(70% 70% at 50% 50%, #000, transparent 75%);
  mask-image: radial-gradient(70% 70% at 50% 50%, #000, transparent 75%);
}
.cta-inner {
  position: relative;
  z-index: 1;
  max-width: 720px;
  margin: 0 auto;
  text-align: center;
  color: #fff;
}
.cta-inner h2 {
  font-size: clamp(1.6rem, 4vw, 2.4rem);
  font-weight: 800;
}
.cta-inner p {
  margin-top: 16px;
  font-size: 1.0625rem;
  line-height: 1.7;
  color: rgba(255, 255, 255, .9);
}
.cta-btn {
  display: inline-flex;
  align-items: center;
  margin-top: 32px;
  background: #fff;
  color: var(--blue-700);
  font-weight: 700;
  font-size: 1.02rem;
  text-decoration: none;
  padding: 15px 34px;
  border-radius: 9999px;
  box-shadow: 0 12px 30px rgba(2, 6, 23, .25);
  transition: transform .18s ease, box-shadow .2s ease;
}
.cta-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 18px 40px rgba(2, 6, 23, .32);
}
</style>
