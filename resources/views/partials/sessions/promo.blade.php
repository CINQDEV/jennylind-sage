{{-- "Missed it? The Jenny Sessions" sidebar card, shown once a session is published. --}}
@if ($latestSession = (function_exists('App\\sessions') ? (App\sessions(1)[0] ?? null) : null))
  <a @class(['session-promo', $class ?? '']) href="{{ App\sessions_url() }}">
    @include('partials.sessions.thumb', ['s' => $latestSession])
    <span class="block p-6">
      <span class="eyebrow mb-1 block">{{ __('Missed it?', 'jennylind') }}</span>
      <span class="block font-display text-[32px] font-extrabold uppercase" style="line-height: .95">{{ __('The Jenny Sessions', 'jennylind') }}</span>
      <span class="mt-2 block text-[15px] text-muted">{{ __('Watch some of our best nights, filmed live in the bar.', 'jennylind') }}</span>
    </span>
  </a>
@endif
