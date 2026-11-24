{{-- Oxygen "Header v2" (template 8221): overlay header, sticky blue bar on scroll. --}}
<header class="site-header" data-site-header>
  <div class="site-header__bar">
    <div class="site-header__inner">
      <a class="site-header__logo" href="{{ home_url('/') }}" aria-label="{{ $siteName }}">
        <img src="{{ Vite::asset('resources/images/logo.svg') }}" alt="{{ $siteName }}" width="228" height="100">
      </a>

      @if (has_nav_menu('primary_navigation'))
        <nav class="primary-nav" aria-label="{{ wp_get_nav_menu_name('primary_navigation') }}">
          {!! wp_nav_menu(['theme_location' => 'primary_navigation', 'container' => false, 'menu_class' => 'menu', 'echo' => false]) !!}
        </nav>

        <button type="button" class="menu-toggle" data-menu-toggle aria-expanded="false" aria-controls="mobile-nav">
          <span></span><span></span><span></span>
          <span class="sr-only">{{ __('Menu', 'jennylind') }}</span>
        </button>
      @endif
    </div>
  </div>

  @if (has_nav_menu('primary_navigation'))
    <nav id="mobile-nav" class="mobile-nav" data-mobile-nav aria-label="{{ __('Mobile', 'jennylind') }}">
      {!! wp_nav_menu(['theme_location' => 'primary_navigation', 'container' => false, 'menu_class' => 'menu', 'echo' => false]) !!}
    </nav>
  @endif
</header>
