{{--
  The next event, labelled "Tonight" when it's today.
  $event: WP_Post, $variant: 'card' (homepage hero) | 'feature' (What's On)
--}}
@php
  $isTonight = App\event_date($event) === App\today();
  $label = $isTonight ? __('Tonight', 'jennylind') : __('Next up', 'jennylind');
  $when = App\event_start($event, 'D j M').' · '.App\event_time($event);
@endphp

@if (($variant ?? 'card') === 'feature')
  <article class="flex flex-wrap overflow-hidden rounded-lg bg-white shadow-[0_20px_50px_rgba(21,35,46,0.25)]">
    @if (has_post_thumbnail($event))
      <img src="{{ get_the_post_thumbnail_url($event, 'large') }}" alt="" class="block aspect-square w-full flex-none object-cover md:w-[420px]">
    @endif
    <div class="flex flex-[1_1_420px] flex-col justify-center gap-4 p-6 md:px-12 md:py-10">
      <span class="self-start rounded-sm bg-accent px-3 py-1.5 text-[13px] font-bold tracking-[0.14em] text-white uppercase">{{ $label }} · {{ $when }}</span>
      <h2 class="m-0 text-[44px] md:text-[64px]">{!! get_the_title($event) !!}</h2>
      <p class="m-0 max-w-xl text-muted">{{ App\event_excerpt($event, 45) }}</p>
      <div class="mt-1 flex flex-wrap gap-3">
        <a class="btn btn-ink" href="{{ get_permalink($event) }}">{{ __('Event details', 'jennylind') }}</a>
        <a class="btn btn-outline" href="{{ home_url('/whats-on/') }}">{{ __('All events', 'jennylind') }}</a>
      </div>
    </div>
  </article>
@else
  <a href="{{ get_permalink($event) }}" class="flex w-full flex-col overflow-hidden rounded-md bg-paper text-ink no-underline shadow-[0_20px_50px_rgba(0,0,0,0.35)] hover:text-ink">
    <div class="flex justify-between bg-accent px-[18px] py-2.5 text-[14px] font-semibold tracking-[0.12em] text-white uppercase">
      <span>{{ $label }}</span><span>{{ $when }}</span>
    </div>
    @if (has_post_thumbnail($event))
      <img src="{{ get_the_post_thumbnail_url($event, 'event-card') }}" alt="" class="block h-[200px] w-full object-cover md:h-[220px]">
    @endif
    <div class="px-5 pt-4 pb-5">
      <h2 class="m-0 text-[30px] font-extrabold md:text-[34px]" style="line-height: 1">{!! get_the_title($event) !!}</h2>
      <p class="mt-2.5 mb-0 text-[15px] text-muted">{{ App\event_excerpt($event, 22) }}</p>
    </div>
  </a>
@endif
