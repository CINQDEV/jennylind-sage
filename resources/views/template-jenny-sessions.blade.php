{{--
  Template Name: Jenny Sessions
  Filmed gigs from YouTube: a featured session overlapping the photo band,
  then every session in a grid (filterable by style), then what's on next.
--}}
@extends('layouts.app')

@php
  $sessions = App\sessions();
  $featured = App\featured_session($sessions);
  $rest = array_values(array_filter($sessions, fn ($s) => $s['id'] !== ($featured['id'] ?? 0)));
  $styles = get_terms(['taxonomy' => 'session_style', 'hide_empty' => true]);
  $styles = is_array($styles) ? $styles : [];
  $upcoming = App\events(4);
@endphp

@section('eyebrow', __('Filmed live at the Jenny Lind', 'jennylind'))
@section('band-padding', $featured ? 'pb-40 md:pb-56' : 'pb-10 md:pb-12')

@section('content')
  @if ($featured)
    <section aria-label="{{ __('Featured session', 'jennylind') }}" class="relative z-10 -mt-28 md:-mt-40">
      <div class="container-site">
        <div class="session-feature">
          <div class="min-w-0 lg:flex-[1_1_62%]">
            @include('partials.sessions.player', ['s' => $featured])
          </div>
          <div class="flex flex-col justify-center gap-3 p-6 md:p-8 lg:flex-[1_1_38%] lg:p-10">
            <p class="m-0 flex flex-wrap items-center gap-2">
              <span class="badge">{{ __('Featured', 'jennylind') }} · #{{ str_pad($featured['number'], 2, '0', STR_PAD_LEFT) }}</span>
              @if ($featured['styles'])<span class="session-tag">{{ implode(' · ', $featured['styles']) }}</span>@endif
            </p>
            <h2 class="m-0 text-[40px] md:text-[56px]" style="line-height: .92">{{ $featured['title'] }}</h2>
            @if ($featured['date'])
              <p class="m-0 text-[15px] font-bold text-muted">{{ sprintf(__('Played %s', 'jennylind'), $featured['date']) }}</p>
            @endif
            @if ($featured['excerpt'])
              <p class="m-0 text-[16px] text-muted">{{ $featured['excerpt'] }}</p>
            @endif
            <a class="link-underline mt-1 self-start" href="{{ $featured['url'] }}">{{ __('More about this session', 'jennylind') }} →</a>
          </div>
        </div>
      </div>
    </section>
  @endif

  <section class="container-site pt-14 pb-20 md:pt-16 md:pb-24" aria-label="{{ __('All sessions', 'jennylind') }}">
    @while(have_posts()) @php(the_post())
      @if (trim(wp_strip_all_tags(get_the_content())))
        <div class="entry-content mb-10 max-w-3xl text-[18px]">@php(the_content())</div>
      @endif
    @endwhile

    @if ($rest)
      <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <h2 class="m-0 text-[40px] md:text-[56px]">{{ __('More sessions', 'jennylind') }}</h2>
        @if (count($styles) > 1)
          <div class="flex flex-wrap gap-2" role="group" aria-label="{{ __('Filter by style', 'jennylind') }}" data-session-filters>
            <button type="button" class="chip is-active" data-filter="" aria-pressed="true">{{ __('All', 'jennylind') }}</button>
            @foreach ($styles as $style)
              <button type="button" class="chip" data-filter="{{ $style->slug }}" aria-pressed="false">{{ $style->name }}</button>
            @endforeach
          </div>
        @endif
      </div>

      <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3" data-session-grid>
        @foreach ($rest as $s)
          @include('partials.sessions.card', ['s' => $s])
        @endforeach
      </div>
    @elseif (! $featured)
      <div class="rounded-lg bg-sand p-8 md:p-12">
        <h2 class="m-0 mb-2 text-[40px]">{{ __('First sessions on their way', 'jennylind') }}</h2>
        <p class="m-0 max-w-xl text-muted">{{ __("We're filming some of the best nights in the bar. Come and catch one live in the meantime.", 'jennylind') }}</p>
      </div>
    @endif
  </section>

  {{-- See them live --}}
  <section class="bg-sand py-16 md:py-20" aria-label="{{ __("What's on next", 'jennylind') }}">
    <div class="container-site flex flex-wrap gap-12">
      <div class="min-w-0 flex-[999_1_560px]">
        <p class="eyebrow mb-2">{{ __('Better in the room', 'jennylind') }}</p>
        <h2 class="mt-0 mb-4 text-[40px] md:text-[56px]">{{ __('See them live', 'jennylind') }}</h2>
        @foreach ($upcoming as $event)
          @include('partials.events.row', ['event' => $event])
        @endforeach
        <a class="link-underline mt-6 inline-block" href="{{ home_url('/whats-on/') }}">{{ __("Everything that's on", 'jennylind') }} →</a>
      </div>
      <div class="flex-[1_1_300px]">
        @include('partials.events.calendar')
      </div>
    </div>
  </section>

  @include('partials.sessions.modal')
@endsection
