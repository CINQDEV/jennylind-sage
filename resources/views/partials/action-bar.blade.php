{{-- Fixed bar on phones: the two things people come for. --}}
<nav class="action-bar" aria-label="{{ __('Quick actions', 'jennylind') }}">
  <a class="is-secondary" href="{{ home_url('/whats-on/') }}">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>
    {{ __("What's on", 'jennylind') }}
  </a>
  <a class="is-primary" href="{{ home_url('/stay/#book') }}">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 18V8M3 14h18v4M21 18v-4a3 3 0 0 0-3-3h-7v3"/><circle cx="7" cy="11" r="2"/></svg>
    {{ __('Book a room', 'jennylind') }}
  </a>
</nav>
