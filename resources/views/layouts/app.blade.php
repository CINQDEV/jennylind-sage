<!doctype html>
<html @php(language_attributes())>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php(do_action('get_header'))
    @php(wp_head())

    @vite(['resources/css/app.css', 'resources/js/app.js'])
  </head>

  <body @php(body_class())>
    @php(wp_body_open())

    <div id="app">
      <a class="sr-only focus:not-sr-only" href="#main">
        {{ __('Skip to content', 'jennylind') }}
      </a>

      @include('sections.header')

      @hasSection('hero')
        @yield('hero')
      @else
        @include('partials.hero')
      @endif

      <main id="main" class="site-main">
        @yield('content')
      </main>

      @include('sections.footer')
    </div>

    <button type="button" class="back-to-top" data-back-to-top aria-label="{{ __('Back to top', 'jennylind') }}">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true"><path d="M6 15l6-6 6 6"/></svg>
    </button>

    @php(do_action('get_footer'))
    @php(wp_footer())
  </body>
</html>
