@php
  $items = App\menu_items('primary_navigation');
  $half = (int) ceil(count($items) / 2);
  $left = array_slice($items, 0, $half);
  $right = array_slice($items, $half);
@endphp

{{-- Centred logo on a plaque that hangs below the bar, like a pub sign. --}}
<header class="site-header">
  <div class="site-header__inner">
    <nav class="primary-nav justify-self-start" aria-label="{{ __('Main', 'jennylind') }}">
      <ul>
        @foreach ($left as $item)
          <li @class(['current-menu-item' => $item['current']])><a href="{{ $item['url'] }}" @if ($item['current']) aria-current="page" @endif>{{ $item['title'] }}</a></li>
        @endforeach
      </ul>
    </nav>

    <a class="site-header__logo sign-swing" href="{{ home_url('/') }}" aria-label="{{ $siteName }} – {{ __('home', 'jennylind') }}">
      <span class="site-header__plaque">
        <img src="{{ Vite::asset('resources/images/logo.svg') }}" alt="{{ $siteName }}" width="273" height="120">
      </span>
    </a>

    <div class="flex items-center justify-self-end gap-5 xl:gap-8">
      <nav class="primary-nav" aria-label="{{ __('Main, continued', 'jennylind') }}">
        <ul>
          @foreach ($right as $item)
            <li @class(['current-menu-item' => $item['current']])><a href="{{ $item['url'] }}" @if ($item['current']) aria-current="page" @endif>{{ $item['title'] }}</a></li>
          @endforeach
        </ul>
      </nav>

      <a class="btn btn-accent header-cta" href="{{ home_url('/stay/#book') }}">{{ __('Book a room', 'jennylind') }}</a>

      <button type="button" class="menu-toggle" data-menu-toggle aria-expanded="false" aria-controls="mobile-nav" aria-label="{{ __('Open menu', 'jennylind') }}">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
      </button>
    </div>
  </div>

  @if ($items)
    <nav id="mobile-nav" class="mobile-nav" data-mobile-nav aria-label="{{ __('Mobile', 'jennylind') }}">
      <ul>
        @foreach ($items as $item)
          <li><a href="{{ $item['url'] }}" @if ($item['current']) aria-current="page" @endif>{{ $item['title'] }}</a></li>
        @endforeach
        <li><a href="{{ home_url('/stay/#book') }}">{{ __('Book a room', 'jennylind') }}</a></li>
      </ul>
    </nav>
  @endif
</header>
