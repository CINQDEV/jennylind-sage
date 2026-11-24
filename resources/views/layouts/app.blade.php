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

    <div id="app" class="flex min-h-dvh flex-col">
      <a class="sr-only focus:not-sr-only" href="#main">
        {{ __('Skip to content', 'jennylind') }}
      </a>

      @include('sections.header')

      <main id="main" class="flex-1">
        @hasSection('hero')
          @yield('hero')
        @else
          @include('partials.page-band')
        @endif

        @yield('content')
      </main>

      @include('sections.footer')
    </div>

    @hasSection('action-bar')
      @yield('action-bar')
    @else
      @include('partials.action-bar')
    @endif

    @php(do_action('get_footer'))
    @php(wp_footer())
  </body>
</html>
