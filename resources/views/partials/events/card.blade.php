{{-- Event card with poster and date badge (homepage "This week"). --}}
<a class="event-card" href="{{ get_permalink($event) }}">
  <div class="relative">
    @if (has_post_thumbnail($event))
      <img class="event-card__img" src="{{ get_the_post_thumbnail_url($event, 'event-card') }}" alt="" loading="lazy">
    @else
      <div class="event-card__img"></div>
    @endif
    <div class="date-badge absolute top-3 left-3">
      <span class="date-badge__day">{{ strtoupper(App\event_start($event, 'D')) }}</span>
      <span class="date-badge__num">{{ App\event_start($event, 'j') }}</span>
    </div>
  </div>
  <div class="flex flex-col gap-1.5 px-[18px] pt-4 pb-5">
    <span class="text-[14px] font-semibold text-accent">{{ App\event_time($event) }}@if (App\event_is_regular($event)) · {{ __('every week', 'jennylind') }}@endif</span>
    <h3 class="card-title m-0">{!! get_the_title($event) !!}</h3>
  </div>
</a>
