{{--
  Click-to-play YouTube player. Shows the still until pressed, so YouTube
  (and its cookies) only load when someone wants to watch.
--}}
@if ($s['embed'])
  <div class="session-player" data-player data-embed="{{ $s['embed'] }}" data-title="{{ $s['title'] }}">
    <button type="button" class="session-player__button" aria-label="{{ sprintf(__('Play %s', 'jennylind'), $s['title']) }}">
      @include('partials.sessions.thumb', ['s' => $s, 'size' => 'lg', 'eager' => true])
    </button>
    <noscript><a class="session-player__noscript" href="{{ $s['watch'] }}">{{ __('Watch on YouTube', 'jennylind') }}</a></noscript>
  </div>
@endif
