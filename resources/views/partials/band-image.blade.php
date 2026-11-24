{{--
  Photo behind a band, as a real <img> filling it. (Inline CSS backgrounds get
  rewritten by Smush's lazy-loader and lose their sizing.)
--}}
@if (! empty($src))
  <img src="{{ esc_url($src) }}" alt="" class="absolute inset-0 h-full w-full object-cover" fetchpriority="high" decoding="async" aria-hidden="true">
@endif
