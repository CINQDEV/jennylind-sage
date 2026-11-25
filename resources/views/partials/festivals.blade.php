{{-- Hastings festival year: compact grid, from this month round the year. --}}
@php($festivals = App\festivals())

@if ($festivals)
  <section class="festival-band" aria-labelledby="festivals-heading">
    <div class="container-site">
      <div class="mb-5 flex flex-wrap items-end justify-between gap-x-8 gap-y-2">
        <h2 id="festivals-heading" class="m-0 text-[28px] text-paper md:text-[34px]">{{ __('Hastings festival year', 'jennylind') }}</h2>
        <p class="m-0 text-[14px] text-soft">{{ __("The Old Town's big weekends – book a room early.", 'jennylind') }}</p>
      </div>

      <ol class="festival-grid">
        @foreach ($festivals as $f)
          <li>
            @if ($f['url'])<a @else<div @endif @class(['festival-tile', 'festival-tile--next' => $f['next']])
              @if ($f['url']) href="{{ $f['url'] }}" target="_blank" rel="noopener" @endif>
              <span class="festival-tile__month">{{ $f['month_label'] }}@if ($f['next']) · {{ __('Next', 'jennylind') }}@endif</span>
              <span class="festival-tile__name">{{ $f['name'] }}</span>
              <span class="festival-tile__when">{{ $f['when'] }}</span>
              @if ($f['jenny'])<span class="festival-tile__jenny">★ {{ __("We're part of it", 'jennylind') }}</span>@endif
            @if ($f['url'])</a>@else</div>@endif
          </li>
        @endforeach
      </ol>
    </div>
  </section>
@endif
