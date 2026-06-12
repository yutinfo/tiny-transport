<script setup>
import NavBar from './components/NavBar.vue'
import HeroSection from './components/HeroSection.vue'
import StatsBar from './components/StatsBar.vue'
import ServiceCards from './components/ServiceCards.vue'
import HowItWorks from './components/HowItWorks.vue'
import TrackingCta from './components/TrackingCta.vue'
import SiteFooter from './components/SiteFooter.vue'
// v-reveal is imported and bound locally inside each section that uses it.
</script>

<template>
  <div class="landing">
    <NavBar />
    <main>
      <HeroSection />
      <StatsBar />
      <ServiceCards />
      <HowItWorks />
      <TrackingCta />
    </main>
    <SiteFooter />
  </div>
</template>

<style>
/* ---- Design tokens (Tech Blue) — page-global, not scoped ---- */
:root {
  --navy-950: #060B18;
  --navy-900: #0A1628;
  --blue-700: #1D4ED8;
  --blue-600: #2563EB;
  --cyan-400: #22D3EE;
  --sky-300: #7DD3FC;
  --surface: #F8FAFC;
  --card: #FFFFFF;
  --ink-900: #0F172A;
  --ink-500: #64748B;
  --line: #E2E8F0;

  --gradient: linear-gradient(135deg, #1D4ED8 0%, #22D3EE 100%);
  --shadow-card: 0 1px 3px rgba(2, 6, 23, .06), 0 12px 32px rgba(2, 6, 23, .07);
  --font-display: 'Space Grotesk', 'Noto Sans Thai', sans-serif;
}

.landing {
  background: var(--surface);
  overflow-x: clip;
}

/* ---- Scroll-reveal rail (v-reveal adds .reveal then .is-visible) ---- */
.reveal {
  opacity: 0;
  transform: translateY(24px);
  transition: opacity .7s cubic-bezier(.16, 1, .3, 1), transform .7s cubic-bezier(.16, 1, .3, 1);
  will-change: opacity, transform;
}
.reveal.is-visible {
  opacity: 1;
  transform: none;
}

/* ---- Accessibility: kill non-essential motion ---- */
@media (prefers-reduced-motion: reduce) {
  .landing *,
  .landing *::before,
  .landing *::after {
    animation-duration: .001ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: .001ms !important;
  }
  .reveal {
    opacity: 1 !important;
    transform: none !important;
  }
}
</style>
