{{-- Event list row: date, poster thumbnail, title, time. --}}
<a class="event-row" href="{{ get_permalink($event) }}" data-kind="{{ App\event_is_regular($event) ? 'weekly' : 'gig' }}">
  <div class="event-row__date">
    <span class="block text-[13px] font-extrabold tracking-[0.1em] text-accent md:text-[15px]">{{ strtoupper(App\event_start($event, 'D')) }}</span>
    <span class="block text-[34px] font-black md:text-[44px]">{{ App\event_start($event, 'j') }}</span>
    <span class="hidden text-[13px] font-semibold tracking-[0.1em] md:block">{{ strtoupper(App\event_start($event, 'M')) }}</span>
  </div>
  @if (has_post_thumbnail($event))
    <img class="event-row__img" src="{{ get_the_post_thumbnail_url($event, 'thumbnail') }}" alt="" loading="lazy">
  @else
    <span class="event-row__img" aria-hidden="true"></span>
  @endif
  <div class="flex min-w-0 flex-1 flex-col gap-0.5">
    <span class="event-row__title">{!! get_the_title($event) !!}</span>
    <span class="text-[14px] text-muted md:text-[15px]">{{ App\event_time($event) }}@if (App\event_is_regular($event)) · {{ __('every week', 'jennylind') }}@endif</span>
  </div>
  <span aria-hidden="true" class="hidden flex-none text-[24px] text-accent md:block">→</span>
</a>
