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
 * What's On filters: All / Gigs / Weekly sessions.
 */
const filters = document.querySelector('[data-event-filters]');
const list = document.querySelector('[data-event-list]');

filters?.addEventListener('click', (e) => {
  const button = e.target.closest('[data-filter]');
  if (!button || !list) return;

  const kind = button.dataset.filter;
  filters.querySelectorAll('[data-filter]').forEach((b) => b.setAttribute('aria-pressed', b === button ? 'true' : 'false'));

  let shown = 0;
  list.querySelectorAll('[data-event-group]').forEach((group) => {
    let groupShown = 0;
    group.querySelectorAll('[data-kind]').forEach((row) => {
      const show = kind === 'all' || row.dataset.kind === kind;
      row.hidden = !show;
      groupShown += show ? 1 : 0;
    });
    group.hidden = groupShown === 0;
    shown += groupShown;
  });

  list.querySelector('[data-event-empty]')?.classList.toggle('hidden', shown > 0);
});
