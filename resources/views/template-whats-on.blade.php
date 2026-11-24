{{--
  Template Name: Whats On
  Oxygen "Page - Events - All" (7429): the next three events as large cards,
  then the following 48 in a grid.
--}}
@extends('layouts.app')

@section('content')
  @php
    $featured = App\events(3);
    $upcoming = App\events(48, 3);
  @endphp

  <section class="container-site px-4 lg:px-0">
    <div class="event-cards">
      @foreach ($featured as $event)
        <article class="event-card">
          <a class="event-image" href="{{ get_permalink($event) }}" aria-hidden="true" tabindex="-1"
             @if (has_post_thumbnail($event)) style="background-image: url('{{ get_the_post_thumbnail_url($event, 'large') }}')" @endif></a>
          <div class="event-meta">
            <span>{{ App\event_start($event, 'D jS M') }}</span>
            <span>{{ App\event_start($event, 'g:i A') }}</span>
          </div>
          <a class="event-link" href="{{ get_permalink($event) }}">
            <h3>{!! get_the_title($event) !!}</h3>
            <p>{{ App\event_excerpt($event) }}</p>
          </a>
        </article>
      @endforeach
    </div>
  </section>

  @if ($upcoming)
    <section class="container-site mt-[60px] px-4 lg:px-0">
      <div class="event-grid">
        @foreach ($upcoming as $event)
          <article class="event-tile">
            <a class="event-image" href="{{ get_permalink($event) }}" aria-hidden="true" tabindex="-1"
               @if (has_post_thumbnail($event)) style="background-image: url('{{ get_the_post_thumbnail_url($event, 'event-card') }}')" @endif></a>
            <a class="event-link" href="{{ get_permalink($event) }}">
              <div class="event-meta !px-0">
                <span>{{ App\event_start($event, 'D jS M - g:i A') }}</span>
              </div>
              <h3>{!! get_the_title($event) !!}</h3>
            </a>
          </article>
        @endforeach
      </div>
    </section>
  @endif

  @while(have_posts()) @php(the_post())
    @if (trim(get_the_content()))
      <div class="container-site entry-content mt-[60px] px-4 lg:px-0">
        @php(the_content())
      </div>
    @endif
  @endwhile
@endsection
