<header class="site-header">
  <div class="site-header__inner">
    <a class="site-header__logo flex" href="{{ home_url('/') }}" aria-label="{{ $siteName }} – {{ __('home', 'jennylind') }}">
      <img src="{{ Vite::asset('resources/images/logo.svg') }}" alt="{{ $siteName }}" width="146" height="64">
    </a>

    <div class="flex items-center gap-8">
      @if (has_nav_menu('primary_navigation'))
        <nav class="primary-nav" aria-label="{{ __('Main', 'jennylind') }}">
          {!! wp_nav_menu(['theme_location' => 'primary_navigation', 'container' => false, 'menu_class' => 'menu', 'echo' => false]) !!}
        </nav>
      @endif

      <a class="btn btn-accent header-cta" href="{{ home_url('/stay/#book') }}">{{ __('Book a room', 'jennylind') }}</a>

      <button type="button" class="menu-toggle" data-menu-toggle aria-expanded="false" aria-controls="mobile-nav" aria-label="{{ __('Open menu', 'jennylind') }}">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
      </button>
    </div>
  </div>

  @if (has_nav_menu('primary_navigation'))
    <nav id="mobile-nav" class="mobile-nav" data-mobile-nav aria-label="{{ __('Mobile', 'jennylind') }}">
      {!! wp_nav_menu(['theme_location' => 'primary_navigation', 'container' => false, 'menu_class' => 'menu', 'echo' => false]) !!}
    </nav>
  @endif
</header>
