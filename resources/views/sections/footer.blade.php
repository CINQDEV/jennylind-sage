{{-- Oxygen "Footer" (template 6734). --}}
<footer class="site-footer">
  {{-- Light strip with wave into the main area --}}
  <section class="relative h-5 bg-powder">
    <div class="footer-wave text-honeydew">
      @include('partials.wave', ['path' => 'a'])
    </div>
  </section>

  {{-- Address / Legal / Opening hours --}}
  <section class="relative bg-blue px-2.5 py-[60px]">
    <div class="footer-wave text-powder">
      @include('partials.wave', ['path' => 'a'])
    </div>

    <div class="container-site grid gap-8 md:grid-cols-3">
      <div class="md:pl-[30px]">
        <h1 class="footer-heading">{{ __('The Jenny Lind Inn', 'jennylind') }}</h1>
        <p class="m-0 text-[16px] text-honeydew">69 High St, Hastings, TN34 3EW</p>
        <h1 class="footer-heading"><a class="text-honeydew hover:text-powder" href="tel:+441424421392">01424 421392</a></h1>
      </div>

      <div>
        <h3>{{ __('Legal', 'jennylind') }}</h3>
        @if (has_nav_menu('legal_navigation'))
          <nav class="footer-legal" aria-label="{{ __('Legal', 'jennylind') }}">
            {!! wp_nav_menu(['theme_location' => 'legal_navigation', 'container' => false, 'menu_class' => 'm-0 list-none p-0', 'echo' => false]) !!}
          </nav>
        @endif
      </div>

      <div>
        <h3>{{ __('Opening Hours', 'jennylind') }}</h3>
        <p class="m-0 text-powder">Sun - Thurs | 12pm - 11pm</p>
        <p class="m-0 text-powder">Fri &amp; Sat | 12pm - 12am</p>
        <p class="m-0 text-[11px] text-powder">Under 18's welcome until 7pm</p>
      </div>
    </div>
  </section>

  {{-- Social + copyright --}}
  <section class="relative bg-navy px-2.5 pt-[60px] pb-[70px]">
    <div class="container-site">
      <div class="social-icons flex gap-2.5">
        <a href="https://www.facebook.com/jennylindhastings/" target="_blank" rel="noopener" aria-label="{{ __('Visit our Facebook', 'jennylind') }}">
          <svg width="30" height="30" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M22 2H2v20h10.7v-7.7H10V11.3h2.7V9c0-2.6 1.6-4 3.9-4 1.1 0 2.1.1 2.3.1v2.7h-1.6c-1.3 0-1.5.6-1.5 1.5v2h3l-.4 3h-2.6V22H22z"/></svg>
        </a>
        <a href="https://instagram.com" target="_blank" rel="noopener" aria-label="{{ __('Visit our Instagram', 'jennylind') }}">
          <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor"/></svg>
        </a>
        <a href="https://twitter.com" target="_blank" rel="noopener" aria-label="{{ __('Visit our Twitter', 'jennylind') }}">
          <svg width="30" height="30" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M23 4.6a9 9 0 0 1-2.6.7 4.5 4.5 0 0 0 2-2.5 9 9 0 0 1-2.9 1.1 4.5 4.5 0 0 0-7.7 4.1A12.8 12.8 0 0 1 1.5 3.3a4.5 4.5 0 0 0 1.4 6 4.5 4.5 0 0 1-2-.6v.1a4.5 4.5 0 0 0 3.6 4.4 4.5 4.5 0 0 1-2 .1 4.5 4.5 0 0 0 4.2 3.1A9 9 0 0 1 0 18.3a12.8 12.8 0 0 0 6.9 2c8.3 0 12.8-6.9 12.8-12.8v-.6A9 9 0 0 0 23 4.6z"/></svg>
        </a>
      </div>
      <a class="text-honeydew hover:text-powder" href="{{ home_url('/shanty/') }}">{{ __('Shanty Songsheets', 'jennylind') }}</a>
    </div>

    <p class="mt-5 mb-[5px] text-center text-[12px] text-blue">
      {{ sprintf(__('Copyright © %s All Rights Reserved', 'jennylind'), date('Y')) }}
    </p>

    <div class="footer-wave text-blue [&_svg]:h-[50px]">
      @include('partials.wave', ['path' => 'b'])
    </div>
  </section>
</footer>
