<script setup>
import { computed } from 'vue'
import BrandLogo from './BrandLogo.vue'
import { brandName } from '../brand.js'

// Placeholder contact values — edit in this one block.
const contact = {
  phone: '02-123-4567',
  line: '@tinytransport',
  email: 'support@tinytransport.co.th',
}

const year = new Date().getFullYear()
const name = computed(() => brandName())
</script>

<template>
  <footer id="contact" class="footer">
    <div class="footer-inner">
      <div class="col col-brand">
        <BrandLogo variant="light" />
        <p class="tagline">บริการขนส่งพัสดุด่วน เก็บเงินปลายทาง ติดตามเรียลไทม์ ครอบคลุมทุกพื้นที่ทั่วไทย</p>
      </div>

      <div class="col">
        <h4>ติดต่อเรา</h4>
        <ul>
          <li><span class="k">โทร</span><a :href="`tel:${contact.phone}`">{{ contact.phone }}</a></li>
          <li><span class="k">LINE</span><span>{{ contact.line }}</span></li>
          <li><span class="k">อีเมล</span><a :href="`mailto:${contact.email}`">{{ contact.email }}</a></li>
        </ul>
      </div>

      <div class="col">
        <h4>ลิงก์ด่วน</h4>
        <ul>
          <li><a href="/tracking">ติดตามพัสดุ</a></li>
          <li><a href="/login">เข้าสู่ระบบพนักงาน</a></li>
          <li><a href="#services">บริการของเรา</a></li>
        </ul>
      </div>
    </div>

    <div class="footer-bottom">
      © {{ year }} {{ name }}. All rights reserved.
    </div>
  </footer>
</template>

<style scoped>
.footer {
  background: var(--navy-900);
  color: #94A3B8;
  padding: 64px 24px 0;
}
.footer-inner {
  max-width: 1080px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: 1.6fr 1fr 1fr;
  gap: 40px;
  padding-bottom: 48px;
}
.tagline {
  margin-top: 18px;
  font-size: .95rem;
  line-height: 1.7;
  max-width: 30em;
  color: #94A3B8;
}
.col h4 {
  color: #fff;
  font-size: 1rem;
  font-weight: 700;
  margin-bottom: 18px;
}
.col ul {
  list-style: none;
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.col li {
  display: flex;
  gap: 10px;
  font-size: .95rem;
}
.col .k {
  color: #64748B;
  min-width: 42px;
}
.col a {
  color: #CBD5E1;
  text-decoration: none;
  transition: color .2s ease;
}
.col a:hover { color: var(--cyan-400); }

.footer-bottom {
  max-width: 1080px;
  margin: 0 auto;
  padding: 22px 0;
  border-top: 1px solid rgba(255, 255, 255, .07);
  font-size: .88rem;
  color: #64748B;
  text-align: center;
}

@media (max-width: 760px) {
  .footer-inner { grid-template-columns: 1fr; gap: 36px; }
}
</style>
