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

/**
 * What's On: reveal two more weeks of listings per click.
 */
const loadMore = document.querySelector('[data-load-more]');

loadMore?.addEventListener('click', () => {
  const hiddenWeeks = [...document.querySelectorAll('[data-more-week][hidden]')];
  const next = hiddenWeeks.slice(0, 2);

  next.forEach((week) => { week.hidden = false; });
  next[0]?.querySelector('a')?.focus({ preventScroll: true });

  if (hiddenWeeks.length <= 2) {
    loadMore.remove();
  }
});

/**
 * Copy-to-clipboard buttons (e.g. the calendar feed link).
 */
document.querySelectorAll('[data-copy]').forEach((button) => {
  const label = button.textContent;

  button.addEventListener('click', async () => {
    try {
      await navigator.clipboard.writeText(button.dataset.copy);
      button.textContent = button.dataset.copiedText || 'Copied';
    } catch {
      window.prompt('Copy this link:', button.dataset.copy);
      return;
    }
    setTimeout(() => { button.textContent = label; }, 2500);
  });
});

/**
 * Fixed header: solid background and smaller sign once the page scrolls.
 */
const header = document.querySelector('.site-header');

if (header) {
  const onScroll = () => header.classList.toggle('is-scrolled', window.scrollY > 24);
  onScroll();
  window.addEventListener('scroll', onScroll, { passive: true });
}
