// Brand rename contract — single source of truth in JS land.
// The name comes from window.__BRAND (injected by the Blade shell from
// config('app.name')). The fallback string is only used if the shell failed to
// inject; per the contract this fallback is allowed to live here.
export function brandName() {
  return window.__BRAND?.name ?? 'TINY TRANSPORT'
}
