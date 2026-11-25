{{-- Small "The Jenny Sessions" link card for sidebars. Uses the latest session's still, or a bar photo before any are published. --}}
@php($latestSession = function_exists('App\\sessions') ? (App\sessions(1)[0] ?? null) : null)

<a @class(['session-promo', $class ?? '']) href="{{ App\sessions_url() }}">
  <span class="session-promo__thumb">
    @if ($latestSession)
      @include('partials.sessions.thumb', ['s' => $latestSession])
    @else
      <span class="session-thumb"><img src="{{ Vite::asset('resources/images/bar-crowd.jpg') }}" alt="" loading="lazy"></span>
    @endif
  </span>
  <span class="min-w-0">
    <span class="block font-display text-[18px] font-extrabold uppercase" style="line-height: 1.05">{{ __('The Jenny Sessions', 'jennylind') }}</span>
    <span class="block text-[13px] leading-snug text-muted">{{ __('Our best nights, filmed live', 'jennylind') }}&nbsp;→</span>
  </span>
</a>
