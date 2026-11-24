{{--
  "Whats on" side box (Oxygen reusable "Side events", 8604 / "Whats on side", 8385):
  the next event as a card, then the following four as compact rows.
--}}
@php
  $first = App\events(1);
  $more = App\events(4, 1);
@endphp

<div class="event-box">
  <a href="{{ home_url('/whats-on/') }}" class="block hover:no-underline">
    <h3 class="event-box__title">{{ __('whats on', 'jennylind') }}</h3>
  </a>

  <div class="event-box__list">
    @foreach ($first as $event)
      <div class="event-box__item">
        <div class="event-meta">
          <span>{{ App\event_start($event, 'D jS M') }}</span>
          <span>{{ App\event_start($event, 'g:i A') }}</span>
        </div>
        <a class="event-image" href="{{ get_permalink($event) }}" aria-hidden="true" tabindex="-1"
           @if (has_post_thumbnail($event)) style="background-image: url('{{ get_the_post_thumbnail_url($event, 'event-card') }}')" @endif></a>
        <a class="event-link" href="{{ get_permalink($event) }}">
          <h3>{!! get_the_title($event) !!}</h3>
          <p>{{ App\event_excerpt($event) }}</p>
        </a>
      </div>
    @endforeach

    @foreach ($more as $event)
      <div class="event-box__item">
        <div class="event-meta">
          <span>{{ App\event_start($event, 'D jS M') }}</span>
          <span>{{ App\event_start($event, 'g:i A') }}</span>
        </div>
        <a class="event-link" href="{{ get_permalink($event) }}">
          <h3>{!! get_the_title($event) !!}</h3>
        </a>
      </div>
    @endforeach
  </div>

  <div class="event-box__more">
    <a href="{{ home_url('/whats-on/') }}">{{ __('View all events', 'jennylind') }}</a>
  </div>
</div>
