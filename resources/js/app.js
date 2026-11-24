import.meta.glob([
  '../images/**',
  '../fonts/**',
]);

/**
 * Mobile menu.
 */
const toggle = document.querySelector('[data-menu-toggle]');
const mobileNav = document.querySelector('[data-mobile-nav]');

toggle?.addEventListener('click', () => {
  const open = mobileNav?.classList.toggle('is-open');
  toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
  toggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
});
