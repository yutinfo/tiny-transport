// v-reveal — one IntersectionObserver-backed scroll-reveal directive.
// Adds `.is-visible` when the element scrolls into view (once), so components
// can fade/slide their content in via CSS. Fully gated behind
// prefers-reduced-motion: when the user opts out, content is shown immediately.

const REDUCED = typeof window !== 'undefined'
  && window.matchMedia
  && window.matchMedia('(prefers-reduced-motion: reduce)').matches

const supportsIO = typeof window !== 'undefined' && 'IntersectionObserver' in window

const observer = (!REDUCED && supportsIO)
  ? new IntersectionObserver((entries, obs) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible')
          obs.unobserve(entry.target)
        }
      })
    }, { threshold: 0.15 })
  : null

export const vReveal = {
  mounted(el) {
    // Reduced motion or no IO support: reveal immediately, no animation.
    if (!observer) {
      el.classList.add('is-visible')
      return
    }
    el.classList.add('reveal')
    observer.observe(el)
  },
  unmounted(el) {
    if (observer) observer.unobserve(el)
  },
}
