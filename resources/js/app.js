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

/**
 * The Jenny Sessions: click-to-play players, pop-up player, style filter.
 */
const iframeFor = (embed, title) => {
  const frame = document.createElement('iframe');
  frame.src = embed;
  frame.title = title || 'Video';
  frame.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
  frame.allowFullscreen = true;
  frame.referrerPolicy = 'strict-origin-when-cross-origin';
  return frame;
};

document.querySelectorAll('[data-player]').forEach((player) => {
  player.querySelector('button')?.addEventListener('click', () => {
    const frame = iframeFor(player.dataset.embed, player.dataset.title);
    player.replaceChildren(frame);
    frame.focus();
  });
});

const modal = document.querySelector('[data-session-modal]');

if (modal) {
  const frameBox = modal.querySelector('[data-session-modal-frame]');
  const close = () => modal.close();

  modal.addEventListener('close', () => frameBox.replaceChildren());
  modal.querySelector('[data-session-close]')?.addEventListener('click', close);
  // Click on the dark backdrop closes it.
  modal.addEventListener('click', (event) => { if (event.target === modal) close(); });

  document.querySelectorAll('[data-session-open]').forEach((card) => {
    card.addEventListener('click', (event) => {
      if (event.metaKey || event.ctrlKey || event.shiftKey || event.button !== 0) return;
      event.preventDefault();
      modal.querySelector('[data-session-modal-title]').textContent = card.dataset.title;
      modal.querySelector('[data-session-modal-page]').href = card.dataset.page;
      // Fallback for when a browser or ad blocker stops the embedded player.
      const watch = modal.querySelector('[data-session-modal-watch]');
      watch.href = card.dataset.watch || '#';
      watch.textContent = card.dataset.watch ? `Video not showing? Watch on ${card.dataset.source} ↗` : '';
      // Show the still with a big play button; the video loads when it's pressed.
      const play = document.createElement('button');
      play.type = 'button';
      play.className = 'session-player__button h-full';
      play.setAttribute('aria-label', `Play ${card.dataset.title}`);
      play.innerHTML = `<span class="session-thumb h-full">
        ${card.dataset.thumb ? `<img src="${card.dataset.thumb}" alt="">` : ''}
        <span class="session-thumb__play session-thumb__play--lg"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5.5v13l11-6.5z" fill="currentColor"/></svg></span>
        ${card.dataset.number ? `<span class="session-thumb__num">${card.dataset.number}</span>` : ''}
      </span>`;
      const img = play.querySelector('img');
      if (img && card.dataset.fallback) {
        img.addEventListener('error', () => { img.src = card.dataset.fallback; }, { once: true });
      }
      play.addEventListener('click', () => {
        frameBox.replaceChildren(iframeFor(card.dataset.embed, card.dataset.title));
      });
      frameBox.replaceChildren(play);
      modal.showModal();
      play.focus();
    });
  });
}

const filters = document.querySelector('[data-session-filters]');

filters?.addEventListener('click', (event) => {
  const chip = event.target.closest('[data-filter]');
  if (!chip) return;

  filters.querySelectorAll('[data-filter]').forEach((c) => {
    c.classList.toggle('is-active', c === chip);
    c.setAttribute('aria-pressed', c === chip ? 'true' : 'false');
  });

  document.querySelectorAll('[data-session-grid] .session-card').forEach((card) => {
    card.hidden = chip.dataset.filter !== '' && !card.dataset.styles.split(' ').includes(chip.dataset.filter);
  });
});

/**
 * Festival strip: arrow buttons scroll it a card at a time.
 */
const strip = document.querySelector('[data-festival-strip]');

if (strip) {
  const step = () => (strip.querySelector('li')?.offsetWidth || 280) + 16;
  document.querySelector('[data-festival-prev]')?.addEventListener('click', () => strip.scrollBy({ left: -step(), behavior: 'smooth' }));
  document.querySelector('[data-festival-next]')?.addEventListener('click', () => strip.scrollBy({ left: step(), behavior: 'smooth' }));
}
