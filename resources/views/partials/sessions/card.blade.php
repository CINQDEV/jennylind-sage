{{-- Session card. Opens the video in a pop-up player; the link still works without JavaScript. --}}
<a class="session-card" href="{{ $s['url'] }}"
  @if ($s['embed']) data-session-open data-embed="{{ $s['embed'] }}" data-title="{{ $s['title'] }}" data-page="{{ $s['url'] }}" @endif
  data-styles="{{ $s['style_slugs'] }}">
  @include('partials.sessions.thumb', ['s' => $s])
  <span class="flex flex-col gap-1.5 p-5">
    @if ($s['styles'])
      <span class="session-tag">{{ implode(' · ', $s['styles']) }}</span>
    @endif
    <span class="session-card__title">{{ $s['title'] }}</span>
    @if ($s['date'])
      <span class="text-[14px] text-muted">{{ sprintf(__('Played %s', 'jennylind'), $s['date']) }}</span>
    @endif
  </span>
</a>
