{{--
  Template Name: Whats On
  Redesign: "Tonight" feature, then upcoming events grouped by week, with
  the weekly nights alongside.
--}}
@extends('layouts.app')

@php
  $next = App\events(1)[0] ?? null;
  $upcoming = App\events(60, 1);
  $groups = collect($upcoming)->groupBy(fn ($event) => App\event_week_label($event));
  $regulars = App\regular_nights();
  $offer = App\stay_offer();
@endphp

@section('eyebrow', __('Gigs, sessions & nights out', 'jennylind'))
@section('band-padding', 'pb-40 md:pb-56')

@section('content')
  @if ($next)
    {{-- Overlaps the photo band above (see band-padding) --}}
    <section aria-label="{{ __('Next event', 'jennylind') }}" class="relative z-10 -mt-28 md:-mt-40">
      <div class="container-site">
        @include('partials.events.tonight', ['event' => $next, 'variant' => 'feature'])
      </div>
    </section>
  @endif

  <section aria-label="{{ __('Upcoming events', 'jennylind') }}" class="container-site flex flex-wrap gap-12 pt-14 pb-20 md:pt-16 md:pb-24">
    <div class="min-w-0 flex-[999_1_640px]" data-load-more-list>
      @forelse ($groups->values() as $i => $events)
        {{-- This week and next week show straight away; later weeks load on demand --}}
        <div class="mb-10" @if ($i > 1) data-more-week hidden @endif>
          <h2 class="mt-0 mb-2 text-[32px] md:text-[40px]">{{ $groups->keys()[$i] }}</h2>
          @foreach ($events as $event)
            @include('partials.events.row', ['event' => $event])
          @endforeach
        </div>
      @empty
        <p>{{ __('No events listed just now – check back soon.', 'jennylind') }}</p>
      @endforelse

      @if ($groups->count() > 2)
        <button type="button" class="btn btn-outline w-full sm:w-auto" data-load-more>{{ __('Load more events', 'jennylind') }}</button>
        <noscript><style>[data-more-week]{display:block!important}[data-load-more]{display:none}</style></noscript>
      @endif
    </div>

    <aside class="flex flex-[1_1_300px] flex-col gap-6">
      @if ($regulars)
        <div class="rounded-lg bg-ink p-7 text-paper">
          <h2 class="mt-0 mb-3 text-[36px]">{{ __('Every week', 'jennylind') }}</h2>
          <dl class="m-0 grid grid-cols-[auto_1fr] gap-x-4 gap-y-2.5 text-[15px]">
            @foreach ($regulars as $night)
              <dt class="font-bold text-blush">{{ $night['day'] }}</dt>
              <dd class="m-0">{{ $night['title'] }}, {{ $night['time'] }}</dd>
            @endforeach
          </dl>
          <a class="link-underline mt-5 inline-block text-paper" href="{{ home_url('/shanty/') }}">{{ __('Shanty songsheets', 'jennylind') }} →</a>
        </div>
      @endif

      @if ($latestSession = App\sessions(1)[0] ?? null)
        <a class="session-promo" href="{{ App\sessions_url() }}">
          @include('partials.sessions.thumb', ['s' => $latestSession])
          <span class="block p-6">
            <span class="eyebrow mb-1 block">{{ __('Missed it?', 'jennylind') }}</span>
            <span class="block font-display text-[32px] font-extrabold uppercase" style="line-height: .95">{{ __('The Jenny Sessions', 'jennylind') }}</span>
            <span class="mt-2 block text-[15px] text-muted">{{ __('Watch some of our best nights, filmed live in the bar.', 'jennylind') }}</span>
          </span>
        </a>
      @endif

      @include('partials.events.calendar')

      <div class="flex flex-col gap-3 rounded-lg bg-sand p-7">
        <h2 class="m-0 text-[32px]">{{ __('Make a night of it', 'jennylind') }}</h2>
        <p class="m-0 text-[15px] text-muted">Rooms upstairs mean no last-train dash.@if ($offer) {{ $offer['badge'] }} – see the Stay page.@endif</p>
        <a class="btn btn-accent self-start" href="{{ home_url('/stay/#book') }}">{{ __('Book a room', 'jennylind') }}</a>
      </div>
    </aside>
  </section>

  @include('partials.festivals')

  @while(have_posts()) @php(the_post())
    @if (trim(wp_strip_all_tags(get_the_content())))
      <div class="container-site entry-content pb-16">
        @php(the_content())
      </div>
    @endif
  @endwhile
@endsection
