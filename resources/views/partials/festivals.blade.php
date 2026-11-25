{{-- Hastings festival year: a scrolling strip, from this month round the year. --}}
@php($festivals = App\festivals())

@if ($festivals)
  <section class="festival-band" aria-labelledby="festivals-heading">
    <div class="container-site">
      <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
          <p class="eyebrow eyebrow--light mb-2">{{ __('Around town', 'jennylind') }}</p>
          <h2 id="festivals-heading" class="m-0 text-[40px] text-paper md:text-[56px]">{{ __('Hastings festival year', 'jennylind') }}</h2>
        </div>
        <p class="m-0 max-w-md text-[15px] text-soft">{{ __("The Old Town's big weekends. We're a two-minute walk from most of them – book a room early.", 'jennylind') }}</p>
      </div>
    </div>

    <ol class="festival-strip" data-festival-strip>
      @foreach ($festivals as $f)
        <li @class(['festival-card', 'festival-card--next' => $f['next']])>
          <div class="flex items-center justify-between gap-3">
            <span class="festival-card__month">{{ $f['month_label'] }}</span>
            @if ($f['next'])
              <span class="festival-card__flag">{{ __('Next up', 'jennylind') }}</span>
            @elseif ($f['jenny'])
              <span class="festival-card__flag festival-card__flag--jenny">{{ __("We're part of it", 'jennylind') }}</span>
            @endif
          </div>
          <h3 class="festival-card__name">{{ $f['name'] }}</h3>
          <p class="festival-card__when">{{ $f['dates'] }}</p>
          @if ($f['when'])<p class="m-0 text-[13px] text-soft">{{ $f['when'] }}</p>@endif
          @if ($f['blurb'])<p class="festival-card__blurb">{{ $f['blurb'] }}</p>@endif
          <div class="mt-auto flex flex-wrap items-center gap-x-4 gap-y-1 pt-3">
            @if ($f['next'] && $f['jenny'])
              <span class="festival-card__flag festival-card__flag--jenny">{{ __("We're part of it", 'jennylind') }}</span>
            @endif
            @if ($f['url'])
              <a class="festival-card__link" href="{{ $f['url'] }}" target="_blank" rel="noopener">{{ __('Festival site', 'jennylind') }} ↗<span class="sr-only"> {{ __('(opens in a new tab)', 'jennylind') }}</span></a>
            @endif
          </div>
        </li>
      @endforeach
    </ol>

    <div class="container-site mt-4 flex items-center justify-between gap-4">
      <span></span>
      <div class="hidden gap-2 md:flex" data-festival-nav>
        <button type="button" class="festival-nav" data-festival-prev aria-label="{{ __('Earlier festivals', 'jennylind') }}">←</button>
        <button type="button" class="festival-nav" data-festival-next aria-label="{{ __('Later festivals', 'jennylind') }}">→</button>
      </div>
    </div>
  </section>
@endif
