<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import BrandLogo from './BrandLogo.vue'

const scrolled = ref(false)
const menuOpen = ref(false)
let ticking = false

const links = [
  { label: 'บริการ', href: '#services' },
  { label: 'วิธีใช้งาน', href: '#how' },
  { label: 'ติดต่อเรา', href: '#contact' },
]

function onScroll() {
  if (ticking) return
  ticking = true
  window.requestAnimationFrame(() => {
    scrolled.value = window.scrollY > 24
    ticking = false
  })
}

function closeMenu() {
  menuOpen.value = false
}

onMounted(() => {
  window.addEventListener('scroll', onScroll, { passive: true })
  onScroll()
})
onUnmounted(() => {
  window.removeEventListener('scroll', onScroll)
})
</script>

<template>
  <header class="nav" :class="{ 'is-scrolled': scrolled }">
    <div class="nav-inner">
      <a href="#top" class="nav-brand" @click="closeMenu">
        <BrandLogo variant="light" />
      </a>

      <nav class="nav-links" aria-label="เมนูหลัก">
        <a v-for="l in links" :key="l.href" :href="l.href" class="nav-link">{{ l.label }}</a>
        <a href="/tracking" class="nav-link nav-link--track">ติดตามพัสดุ</a>
      </nav>

      <div class="nav-actions">
        <a href="/login" class="btn-login">เข้าสู่ระบบพนักงาน</a>
        <button
          type="button"
          class="hamburger"
          :class="{ 'is-open': menuOpen }"
          :aria-expanded="menuOpen"
          aria-label="เปิดเมนู"
          @click="menuOpen = !menuOpen"
        >
          <span></span><span></span><span></span>
        </button>
      </div>
    </div>

    <Transition name="sheet">
      <div v-if="menuOpen" class="mobile-menu">
        <a v-for="l in links" :key="l.href" :href="l.href" class="mobile-link" @click="closeMenu">{{ l.label }}</a>
        <a href="/tracking" class="mobile-link" @click="closeMenu">ติดตามพัสดุ</a>
        <a href="/login" class="mobile-login" @click="closeMenu">เข้าสู่ระบบพนักงาน</a>
      </div>
    </Transition>
  </header>
</template>

<style scoped>
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
  max-width: 1160px;
  margin: 0 auto;
  padding: 16px 24px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
}
.nav-brand { text-decoration: none; }

.nav-links {
  display: flex;
  align-items: center;
  gap: 28px;
}
.nav-link {
  color: #CBD5E1;
  text-decoration: none;
  font-size: .95rem;
  font-weight: 500;
  position: relative;
  transition: color .2s ease;
}
.nav-link::after {
  content: '';
  position: absolute;
  left: 0;
  bottom: -6px;
  height: 2px;
  width: 0;
  background: var(--gradient);
  transition: width .25s ease;
}
.nav-link:hover { color: #fff; }
.nav-link:hover::after { width: 100%; }
.nav-link--track {
  color: var(--cyan-400);
}

.nav-actions {
  display: flex;
  align-items: center;
  gap: 12px;
}
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
  transition: background .25s ease, color .2s ease, border-color .25s ease, transform .15s ease;
}
.btn-login:hover {
  background: var(--gradient);
  border-color: transparent;
  color: #fff;
  transform: translateY(-1px);
}

/* Hamburger */
.hamburger {
  display: none;
  flex-direction: column;
  justify-content: center;
  gap: 5px;
  width: 40px;
  height: 40px;
  border: 1px solid rgba(148, 163, 184, .3);
  border-radius: 10px;
  background: transparent;
  cursor: pointer;
  padding: 0 9px;
}
.hamburger span {
  display: block;
  height: 2px;
  width: 100%;
  background: #E2E8F0;
  border-radius: 2px;
  transition: transform .25s ease, opacity .2s ease;
}
.hamburger.is-open span:nth-child(1) { transform: translateY(7px) rotate(45deg); }
.hamburger.is-open span:nth-child(2) { opacity: 0; }
.hamburger.is-open span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }

/* Mobile menu sheet */
.mobile-menu {
  display: none;
  flex-direction: column;
  gap: 4px;
  padding: 8px 24px 24px;
  background: rgba(6, 11, 24, .96);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border-top: 1px solid rgba(255, 255, 255, .06);
}
.mobile-link {
  color: #E2E8F0;
  text-decoration: none;
  font-size: 1.05rem;
  font-weight: 500;
  padding: 14px 4px;
  border-bottom: 1px solid rgba(255, 255, 255, .05);
}
.mobile-login {
  margin-top: 12px;
  text-align: center;
  text-decoration: none;
  color: #fff;
  font-weight: 600;
  padding: 14px;
  border-radius: 12px;
  background: var(--gradient);
}

.sheet-enter-active, .sheet-leave-active {
  transition: opacity .25s ease, transform .25s ease;
}
.sheet-enter-from, .sheet-leave-to {
  opacity: 0;
  transform: translateY(-12px);
}

@media (max-width: 860px) {
  .nav-links { display: none; }
  .btn-login { display: none; }
  .hamburger { display: flex; }
  .mobile-menu { display: flex; }
}
</style>
