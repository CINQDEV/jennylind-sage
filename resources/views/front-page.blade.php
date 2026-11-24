{{-- Homepage (redesign): tonight's gig, this week, stay, bars, find us. --}}
@extends('layouts.app')

@php
  $next = App\events(1)[0] ?? null;
  $week = App\events(4, 1);
  $regulars = App\regular_nights();
  $session = function_exists('App\sessions') ? (App\sessions(1)[0] ?? null) : null;
  $offer = App\stay_offer();
@endphp

@section('hero')
  <section class="home-hero">
    @include('partials.band-image', ['src' => $heroImage ?: Vite::asset('resources/images/bar-crowd.jpg')])
    <div class="home-hero__shade relative">
      <div class="container-site pt-[128px] pb-8 md:pt-[172px] md:pb-[88px] lg:pt-[200px]">
      <p class="eyebrow eyebrow--light mb-4">69 High Street · Hastings Old Town</p>
      {{-- Tonight card's top lines up with the top of the headline --}}
      <div class="flex flex-wrap items-start justify-between gap-10">
        <div class="max-w-[680px] flex-[1_1_560px] text-paper">
          <h1 class="m-0 text-[60px] md:text-[104px]" style="line-height: 0.92">Live music,<br>real ale &amp;<br>rooms upstairs</h1>
          <p class="mt-6 mb-0 max-w-[520px] text-[18px] text-[#E6DED0] md:text-[20px]">A proper Old Town inn, two minutes from the beach and the Stade's net huts. Gigs most nights, cask ales on the hand-pulls, en-suite rooms above the bar.</p>
          <div class="mt-8 hidden flex-wrap gap-4 md:flex">
            <a class="btn btn-accent" href="{{ home_url('/whats-on/') }}">{{ __("See what's on", 'jennylind') }}</a>
            <a class="btn btn-outline-light" href="{{ home_url('/stay/#book') }}">{{ __('Book a room', 'jennylind') }}</a>
          </div>
        </div>

        @if ($next)
          <div class="w-full flex-[0_1_360px]">
            @include('partials.events.tonight', ['event' => $next, 'variant' => 'card'])
          </div>
        @endif
      </div>
      </div>
    </div>
  </section>

  <div class="bg-sea text-paper">
    <ul class="container-site m-0 grid list-none grid-cols-1 gap-y-1 py-4 font-display text-[18px] font-extrabold tracking-[0.05em] uppercase sm:grid-cols-2 lg:flex lg:justify-between lg:text-[20px]">
      @foreach (['Live music most nights', 'Real ales & ciders on hand-pull', 'En-suite rooms upstairs', 'Dogs welcome in the bar'] as $fact)
        <li class="flex items-center gap-3"><span aria-hidden="true" class="text-blush">★</span>{{ $fact }}</li>
      @endforeach
    </ul>
  </div>
@endsection

@section('content')
  {{-- This week --}}
  <section aria-labelledby="week" class="container-site pt-16 pb-12 md:pt-24">
    <div class="mb-8 flex flex-wrap items-end justify-between gap-6">
      <div>
        <p class="eyebrow mb-2">{{ __('This week at the Jenny', 'jennylind') }}</p>
        <h2 id="week" class="m-0 text-[44px] md:text-[72px]">{{ __("What's on", 'jennylind') }}</h2>
      </div>
      <a class="link-underline" href="{{ home_url('/whats-on/') }}">{{ __('Full listings', 'jennylind') }} →</a>
    </div>

    {{-- Cards from tablet up, compact rows on phones --}}
    <div class="hidden gap-6 sm:grid sm:grid-cols-2 lg:grid-cols-4">
      @foreach ($week as $event)
        @include('partials.events.card', ['event' => $event])
      @endforeach
    </div>
    <div class="sm:hidden">
      @foreach ($week as $event)
        @include('partials.events.row', ['event' => $event])
      @endforeach
    </div>

    @if ($regulars || $session)
      {{-- Regular nights, plus the latest Jenny Session --}}
      <div @class(['weekly-panel', 'lg:grid-cols-[minmax(0,1.55fr)_minmax(0,1fr)]' => $regulars && $session])>
        @if ($regulars)
          <div class="flex flex-col p-5 md:p-7">
            <h3 class="mt-0 mb-4 text-[26px] md:text-[30px]">{{ __('Every week', 'jennylind') }}</h3>
            <ul @class(['m-0 grid list-none gap-2.5 p-0', 'sm:grid-cols-2' => count($regulars) === 2, 'sm:grid-cols-3' => count($regulars) >= 3])>
              @foreach ($regulars as $night)
                <li>
                  <a class="weekly-night" href="{{ $night['url'] }}">
                    <span class="weekly-night__day">{{ $night['days'] }}</span>
                    <span class="weekly-night__title">{{ $night['title'] }}</span>
                    <span class="weekly-night__time">{{ $night['time'] }}</span>
                  </a>
                </li>
              @endforeach
            </ul>
            <p class="m-0 mt-5 flex flex-wrap gap-x-6 gap-y-2 text-[14px]">
              <a class="link-underline text-paper" href="{{ App\calendar_links()['webcal'] }}">{{ __('Add our gigs to your calendar', 'jennylind') }} →</a>
              <a class="link-underline text-paper" href="{{ home_url('/shanty/') }}">{{ __('Shanty songsheets', 'jennylind') }} →</a>
            </p>
          </div>
        @endif

        @if ($session)
          <div class="weekly-panel__sessions">
            <h3 class="mt-0 mb-4 text-[26px] md:text-[30px]">{{ __('The Jenny Sessions', 'jennylind') }}</h3>
            <a class="weekly-session" href="{{ $session['url'] }}"
              @if ($session['embed']) data-session-open data-embed="{{ $session['embed'] }}" data-title="{{ $session['title'] }}" data-page="{{ $session['url'] }}" data-thumb="{{ $session['thumb'] }}" data-fallback="{{ $session['thumb_fallback'] }}" data-watch="{{ $session['watch'] }}" data-source="{{ $session['source'] }}" data-number="{{ $session['number'] ? '#'.str_pad($session['number'], 2, '0', STR_PAD_LEFT) : '' }}" @endif>
              <span class="w-36 flex-none overflow-hidden rounded sm:w-40">@include('partials.sessions.thumb', ['s' => $session])</span>
              <span class="min-w-0">
                <span class="block text-[12px] font-bold tracking-[0.14em] text-blush uppercase">{{ __('Missed a gig?', 'jennylind') }}</span>
                <span class="mt-1 block font-display text-[19px] font-extrabold uppercase" style="line-height: 1.05">{{ $session['title'] }}</span>
                @if ($session['date'])<span class="mt-1 block text-[13px] text-soft">{{ sprintf(__('Played %s', 'jennylind'), $session['date']) }}</span>@endif
              </span>
            </a>
            <a class="link-underline mt-4 inline-block text-[14px] text-paper" href="{{ App\sessions_url() }}">{{ __('Watch all the sessions', 'jennylind') }} →</a>
          </div>
        @endif
      </div>
    @endif
  </section>

  {{-- Stay --}}
  <section aria-labelledby="stay" class="container-site pt-6 pb-16 md:pt-12 md:pb-24">
    <div class="grid overflow-hidden rounded-lg bg-ink text-paper lg:grid-cols-2">
      <img src="{{ Vite::asset('resources/images/room.jpg') }}" alt="A made-up double bed in one of the en-suite rooms" class="block h-60 w-full object-cover md:h-80 lg:h-full" loading="lazy">
      <div class="flex flex-col gap-5 p-6 md:px-14 md:pt-14 md:pb-12">
        <p class="eyebrow eyebrow--light">{{ __('Stay at the Jenny', 'jennylind') }}</p>
        <h2 id="stay" class="m-0 text-[40px] md:text-[64px]">Sleep above the bar, wake up by the sea</h2>
        <p class="m-0 text-soft">Family, double, twin and single rooms, all en-suite, with a private side entrance. Fridge, tea and coffee, TV and free Wi-Fi in every room. Breakfast is cooked across the road at The Land of Green Ginger.</p>

        @if ($offer)
          <div class="offer">
            <span class="offer__badge">{{ $offer['badge'] }}</span>
            <span>{{ $offer['text'] }} @if ($offer['code'])Book by phone with code <strong class="text-white">{{ $offer['code'] }}</strong>.@endif</span>
          </div>
        @endif

        <div class="rounded-md bg-paper p-4 text-ink">
          {!! App\booking_form() !!}
        </div>
        <p class="m-0 text-[14px] text-[#B9B0A1]">Best rates when you book direct · Check-in 4–9pm · Prefer to talk? <a class="text-paper" href="tel:+441424421392">01424 421392</a></p>
      </div>
    </div>
  </section>

  {{-- Bars --}}
  <section id="bars" aria-labelledby="bars-h" class="bg-sand">
    <div class="container-site py-16 md:py-24">
      <div class="mb-10 flex flex-wrap items-end gap-x-16 gap-y-6">
        <h2 id="bars-h" class="m-0 flex-[1_1_420px] text-[44px] md:text-[72px]">Two bars, a garden &amp; a log fire</h2>
        <p class="m-0 flex-[1_1_420px] text-muted">Real cask ale is a passion here: 'Best' from local brewery Long Man, Greene King Abbot, and an ever-changing line-up of guest ales from Sussex and Kent — plus at least three real ciders and craft beer.</p>
      </div>
      <div class="-mx-4 flex snap-x gap-4 overflow-x-auto px-4 pb-2 md:mx-0 md:grid md:grid-cols-3 md:gap-6 md:overflow-visible md:px-0">
        @foreach ([
          ['front-bar.jpg', 'A pint on the front bar', 'The front bar', 'High tables by the big windows, and where the bands play.'],
          ['back-bar.jpg', 'The back bar with its bar billiards table', 'The cosy back bar', 'Bar billiards and an open log fire.'],
          ['garden.jpg', 'Picnic tables in the tiered garden', 'The garden', 'Multi-tiered, with plenty of cover when it rains.'],
        ] as [$img, $alt, $title, $text])
          <figure class="m-0 flex w-[240px] flex-none snap-start flex-col gap-3.5 md:w-auto">
            <img src="{{ Vite::asset('resources/images/'.$img) }}" alt="{{ $alt }}" class="block aspect-[4/5] w-full rounded-md object-cover" loading="lazy">
            <figcaption>
              <strong class="block font-display text-[24px] font-extrabold uppercase md:text-[28px]">{{ $title }}</strong>
              <span class="text-muted">{{ $text }}</span>
            </figcaption>
          </figure>
        @endforeach
      </div>
      <p class="mt-8 mb-0 text-muted">Planning a party? Our upstairs function room has views over East Hill. <a class="font-semibold" href="{{ home_url('/bars/') }}">More about our bars →</a></p>
    </div>
  </section>

  {{-- Find us --}}
  <section id="visit" aria-labelledby="visit-h" class="container-site py-16 md:py-24">
    <div class="grid gap-12 lg:grid-cols-2">
      <div class="flex flex-col gap-6">
        <h2 id="visit-h" class="m-0 text-[44px] md:text-[72px]">{{ __('Find us', 'jennylind') }}</h2>
        <p class="m-0 text-[20px]">69 High Street, Hastings Old Town, East Sussex TN34 3EW</p>
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
          <div>
            <h3 class="mt-0 mb-1.5 text-[24px] font-extrabold">{{ __('Opening hours', 'jennylind') }}</h3>
            <p class="m-0 text-muted">Sun–Thu 12pm–11pm<br>Fri &amp; Sat 12pm–12am<br>Under 18s welcome until 7pm</p>
          </div>
          <div>
            <h3 class="mt-0 mb-1.5 text-[24px] font-extrabold">{{ __('Getting here', 'jennylind') }}</h3>
            <p class="m-0 text-muted">Hastings station is a mile away. Limited on-street parking in the Old Town.</p>
          </div>
        </div>
        <div class="flex flex-wrap gap-4">
          <a class="btn btn-ink" href="https://www.google.com/maps/search/?api=1&amp;query=The+Jenny+Lind+Inn+69+High+Street+Hastings+TN34+3EW" target="_blank" rel="noopener">{{ __('Get directions', 'jennylind') }}</a>
          <a class="btn btn-outline" href="tel:+441424421392">{{ __('Call 01424 421392', 'jennylind') }}</a>
        </div>
      </div>
      <img src="{{ Vite::asset('resources/images/old-town.jpg') }}" alt="Hastings Old Town from the East Hill" class="block h-[300px] w-full rounded-lg object-cover md:h-[420px] lg:h-full" loading="lazy">
    </div>
  </section>
  @if ($session)
    @include('partials.sessions.modal')
  @endif
@endsection
