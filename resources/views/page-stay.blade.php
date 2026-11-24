{{-- Stay page (redesign): booking panel in the hero, rooms, good to know. --}}
@extends('layouts.app')

@php
  $rooms = App\rooms();
  $offer = App\stay_offer();
  $facts = [
    ['Check in & out', 'Check-in 4pm–9pm, or by appointment until 10.30pm. Check-out 10am. Early bag drop by arrangement.'],
    ['Breakfast', 'Bed & breakfast rates include a freshly cooked breakfast across the road at The Land of Green Ginger.'],
    ['Parking', "Old Town parking is limited and we can't reserve spaces – ring us and we'll point you to the best car parks."],
    ['Payment', 'Visa, Mastercard, debit and credit cards (no American Express).'],
    ['Cancellation', 'Free up to 48 hours before arrival. See our hotel terms for the full policy.'],
    ['Dogs', 'Very welcome in the bar – but the rooms are for two-legged guests only.'],
  ];
@endphp

@section('hero')
  <section class="page-band">
    @include('partials.band-image', ['src' => $heroImage])
    <div class="page-band__shade relative">
      <div class="container-site flex flex-wrap items-center justify-between gap-12 py-12 md:py-[88px] lg:pt-32">
        <div class="max-w-[620px] flex-[1_1_520px]">
          <p class="eyebrow eyebrow--light mb-3.5">{{ __('Stay at the Jenny', 'jennylind') }}</p>
          <h1 class="m-0 text-[52px] md:text-[96px]" style="line-height: 0.9">Rooms in the heart of the Old Town</h1>
          <p class="mt-5 mb-0 text-[18px] text-[#E6DED0] md:text-[20px]">En-suite rooms above the pub, with a private side entrance and rooftop views – seagulls included. Two minutes from the beach.</p>
          <ul class="mt-6 mb-0 flex list-none flex-wrap gap-x-6 gap-y-2.5 p-0 text-[15px]">
            @foreach (['Best rates booking direct', 'Free Wi-Fi', 'Free cancellation up to 48 hours'] as $point)
              <li class="flex items-center gap-2"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#F2B9A8" stroke-width="2.4" aria-hidden="true"><path d="M5 12l5 5L20 7"/></svg>{{ $point }}</li>
            @endforeach
          </ul>
        </div>

        <div id="book" class="booking-panel w-full flex-[0_1_420px] scroll-mt-6">
          <h2 class="m-0 text-[36px]">{{ __('Check availability', 'jennylind') }}</h2>
          {!! App\booking_form() !!}
          @if ($offer)
            <div class="border-t border-dashed border-[#C9BDA9] pt-3.5 text-[15px] text-muted">
              <strong class="text-ink">{{ $offer['badge'] }}:</strong> {{ $offer['text'] }}
              Call <a class="font-semibold" href="tel:+441424421392">01424 421392</a>@if ($offer['code']) with code <strong class="text-ink">{{ $offer['code'] }}</strong>@endif.
            </div>
          @endif
        </div>
      </div>
    </div>
  </section>
@endsection

@section('content')
  @if ($rooms)
    <section aria-labelledby="rooms" class="container-site pt-16 pb-10 md:pt-[88px]">
      <h2 id="rooms" class="mt-0 mb-7 text-[40px] md:text-[72px]">{{ __('The rooms', 'jennylind') }}</h2>
      <div class="-mx-4 flex snap-x gap-4 overflow-x-auto px-4 pb-2 md:mx-0 md:grid md:grid-cols-4 md:gap-5 md:overflow-visible md:px-0">
        @foreach ($rooms as $room)
          <article class="flex w-[260px] flex-none snap-start flex-col overflow-hidden rounded-md border border-card-line bg-white md:w-auto">
            @if ($room['image'])
              <img src="{{ $room['image'] }}" alt="{{ $room['title'] }}" class="block aspect-[4/3] w-full object-cover" loading="lazy">
            @endif
            <div class="px-[18px] pt-4 pb-5">
              <h3 class="card-title m-0">{{ $room['title'] }}</h3>
              <p class="mt-1 mb-0 text-[15px] text-muted">
                @if ($room['text']){{ $room['text'] }}@else{{ $room['sleeps'] > 1 ? sprintf(__('Sleeps up to %d.', 'jennylind'), $room['sleeps']) : __('For solo stays.', 'jennylind') }}@endif
              </p>
            </div>
          </article>
        @endforeach
      </div>
      <p class="mt-5 mb-0 text-muted">Every room: en-suite, small fridge, tea &amp; coffee, flat-screen TV with Freeview, free Wi-Fi.</p>
    </section>
  @endif

  <section aria-labelledby="know" class="container-site pt-12 pb-20 md:pb-24">
    <h2 id="know" class="mt-0 mb-7 text-[40px] md:text-[56px]">{{ __('Good to know', 'jennylind') }}</h2>
    <div class="grid grid-cols-1 gap-x-10 gap-y-8 sm:grid-cols-2 lg:grid-cols-3">
      @foreach ($facts as [$title, $text])
        <div class="border-t-[3px] border-ink pt-3.5">
          <h3 class="mt-0 mb-1.5 text-[26px] font-extrabold">{{ $title }}</h3>
          <p class="m-0 text-muted">{{ $text }}@if ($title === 'Cancellation') <a href="{{ home_url('/hotel-terms-and-conditions/') }}">{{ __('Hotel terms', 'jennylind') }}</a>.@endif</p>
        </div>
      @endforeach
    </div>

    <div class="mt-12 flex flex-wrap items-center justify-between gap-5 rounded-lg bg-ink px-6 py-7 text-paper md:px-9 md:py-8">
      <p class="m-0 max-w-[640px] font-display text-[28px] font-extrabold uppercase md:text-[32px]" style="line-height: 1.1">Prefer the personal touch? Ring the pub and we'll find the right room.</p>
      <a class="btn btn-accent" href="tel:+441424421392">{{ __('Call 01424 421392', 'jennylind') }}</a>
    </div>
  </section>
@endsection

@section('action-bar')
  <nav class="action-bar" aria-label="{{ __('Quick actions', 'jennylind') }}">
    <a class="is-secondary" href="tel:+441424421392">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.7 2z"/></svg>
      {{ __('Call', 'jennylind') }}
    </a>
    <a class="is-primary" href="#book">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
      {{ __('Check dates', 'jennylind') }}
    </a>
  </nav>
@endsection
