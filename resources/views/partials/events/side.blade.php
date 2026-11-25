{{-- Compact "What's on" list for sidebars (two-column pages, posts). --}}
@php($upcoming = App\events(5))

<div class="rounded-lg bg-ink p-6 text-paper">
  <h2 class="m-0 mb-2 text-[36px]">{{ __("What's on", 'jennylind') }}</h2>
  @foreach ($upcoming as $event)
    <a class="flex items-baseline gap-3 border-t border-ink-line py-3 text-paper no-underline hover:text-blush" href="{{ get_permalink($event) }}">
      <span class="w-[72px] flex-none text-[13px] font-bold tracking-[0.08em] text-blush uppercase">{{ App\event_start($event, 'D j') }}</span>
      <span class="font-display text-[20px] font-extrabold uppercase" style="line-height: 1.05">{!! get_the_title($event) !!}</span>
    </a>
  @endforeach
  <a class="link-underline mt-3 inline-block text-paper" href="{{ home_url('/whats-on/') }}">{{ __('Full listings', 'jennylind') }} →</a>
</div>

{{-- Not on a session's own page: it already links to all the sessions. --}}
@unless (is_singular('jl_session'))
  @include('partials.sessions.promo', ['class' => 'mt-6'])
@endunless
