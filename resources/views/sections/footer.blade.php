<footer class="site-footer">
  <div class="container-site flex flex-wrap justify-between gap-10 pt-16 pb-8">
    <a href="{{ home_url('/') }}" class="sign-swing flex" aria-label="{{ $siteName }} – {{ __('home', 'jennylind') }}">
      <img src="{{ Vite::asset('resources/images/logo.svg') }}" alt="{{ $siteName }}" width="182" height="80" class="h-20 w-auto">
    </a>

    <div class="flex flex-wrap gap-12">
      <div class="flex flex-col gap-2">
        <span class="footer-heading">{{ __('Visit', 'jennylind') }}</span>
        <span>69 High Street, Hastings Old Town<br>East Sussex TN34 3EW</span>
        <a href="tel:+441424421392">01424 421392</a>
      </div>

      <div class="flex flex-col gap-2">
        <span class="footer-heading">{{ __('Opening hours', 'jennylind') }}</span>
        <span>Sun–Thu 12pm–11pm<br>Fri &amp; Sat 12pm–12am</span>
        <span class="text-[14px] text-faint">Under 18s welcome until 7pm</span>
      </div>

      <div class="flex flex-col gap-2">
        <span class="footer-heading">{{ __('More', 'jennylind') }}</span>
        <a href="{{ home_url('/whats-on/') }}">{{ __("What's On", 'jennylind') }}</a>
        <a href="{{ home_url('/stay/') }}">{{ __('Rooms & booking', 'jennylind') }}</a>
        <a href="{{ home_url('/shanty/') }}">{{ __('Shanty songsheets', 'jennylind') }}</a>
        <a href="https://www.facebook.com/jennylindhastings/" target="_blank" rel="noopener">Facebook</a>
      </div>
    </div>
  </div>

  <div class="container-site flex flex-wrap justify-between gap-4 border-t border-[#2C3F4E] pt-5 pb-8 text-[14px] text-faint">
    <span class="flex flex-wrap gap-x-6 gap-y-1">
      <span>© {{ date('Y') }} The Jenny Lind Inn</span>
      <span>{{ __('Designed by', 'jennylind') }} <a class="text-faint underline decoration-1 underline-offset-4 hover:text-paper" href="https://cinq.co.uk" target="_blank" rel="noopener">CINQ<span class="sr-only"> {{ __('(opens in a new tab)', 'jennylind') }}</span></a></span>
    </span>
    @if (has_nav_menu('legal_navigation'))
      <nav aria-label="{{ __('Legal', 'jennylind') }}">
        {!! wp_nav_menu(['theme_location' => 'legal_navigation', 'container' => false, 'menu_class' => 'm-0 flex list-none flex-wrap gap-x-5 gap-y-1 p-0', 'echo' => false]) !!}
      </nav>
    @endif
  </div>
</footer>
