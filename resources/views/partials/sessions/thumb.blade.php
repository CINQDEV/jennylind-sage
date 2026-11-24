{{-- 16:9 video still with a play button. $s = App\session(), $size = 'lg'|'sm' --}}
<span class="session-thumb">
  @if ($s['thumb'])
    <img src="{{ $s['thumb'] }}" alt="" loading="{{ ($eager ?? false) ? 'eager' : 'lazy' }}" decoding="async"
      @if ($s['thumb_fallback']) data-fallback="{{ $s['thumb_fallback'] }}" onerror="this.onerror=null;this.src=this.dataset.fallback" @endif>
  @endif
  <span @class(['session-thumb__play', 'session-thumb__play--lg' => ($size ?? 'sm') === 'lg'])>
    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5.5v13l11-6.5z" fill="currentColor"/></svg>
  </span>
  @if ($s['number'])
    <span class="session-thumb__num">#{{ str_pad($s['number'], 2, '0', STR_PAD_LEFT) }}</span>
  @endif
</span>
