import.meta.glob([
  '../images/**',
  '../fonts/**',
]);

/**
 * Sticky header: once the page scrolls past the hero, the overlay header
 * becomes the fixed blue bar (Oxygen "Header v2" sticky row).
 */
const header = document.querySelector('[data-site-header]');
const backToTop = document.querySelector('[data-back-to-top]');

const onScroll = () => {
  const y = window.scrollY;
  header?.classList.toggle('is-sticky', y > 300);
  backToTop?.classList.toggle('is-visible', y > 100);
};

window.addEventListener('scroll', onScroll, { passive: true });
onScroll();

backToTop?.addEventListener('click', () => {
  window.scrollTo({ top: 0, behavior: 'smooth' });
});

/**
 * Mobile menu (below 1280px the desktop menu is replaced by a hamburger).
 */
const toggle = document.querySelector('[data-menu-toggle]');
const mobileNav = document.querySelector('[data-mobile-nav]');

toggle?.addEventListener('click', () => {
  const open = mobileNav?.classList.toggle('is-open');
  toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
});
