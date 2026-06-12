<script setup>
import { computed } from 'vue'
import { brandName } from '../brand.js'

// The ONLY component that renders the wordmark. Brand name comes from
// window.__BRAND (config('app.name')); the fallback inside brand.js is the one
// allowed exception. Future rename = change APP_NAME in .env, nothing here.
const props = defineProps({
  // 'light' = for dark backgrounds (navbar/footer), 'dark' = for light bg.
  variant: { type: String, default: 'light' },
})

const name = computed(() => brandName())
</script>

<template>
  <span class="brand-logo" :class="`is-${variant}`" aria-label="โลโก้บริษัท">
    <span class="mark" aria-hidden="true">
      <svg viewBox="0 0 28 28" width="28" height="28" fill="none">
        <rect x="1.5" y="1.5" width="25" height="25" rx="7" stroke="url(#bl-grad)" stroke-width="2" />
        <path d="M8 14.5l3.5 3.5L20 9.5" stroke="url(#bl-grad)" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" />
        <defs>
          <linearGradient id="bl-grad" x1="0" y1="0" x2="28" y2="28" gradientUnits="userSpaceOnUse">
            <stop stop-color="#2563EB" />
            <stop offset="1" stop-color="#22D3EE" />
          </linearGradient>
        </defs>
      </svg>
    </span>
    <span class="wordmark">{{ name }}</span>
  </span>
</template>

<style scoped>
.brand-logo {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  user-select: none;
}
.mark {
  display: inline-flex;
  flex-shrink: 0;
}
.wordmark {
  font-family: var(--font-display);
  font-weight: 700;
  font-size: 1.05rem;
  letter-spacing: .12em;
  text-transform: uppercase;
  white-space: nowrap;
}
.is-light .wordmark { color: #fff; }
.is-dark .wordmark { color: var(--ink-900); }

@media (max-width: 520px) {
  .wordmark { font-size: .95rem; letter-spacing: .08em; }
}
</style>
