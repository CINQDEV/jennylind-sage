{{-- Inner-page header: dark band with the title, over the featured image when there is one. --}}
<section class="page-band" @if ($heroImage) style="background-image: url('{{ esc_url($heroImage) }}')" @endif>
  <div class="page-band__shade">
    <div class="page-band__inner @yield('band-padding', 'pb-10 md:pb-12')">
      <div class="max-w-4xl">
        @hasSection('eyebrow')
          <p class="eyebrow eyebrow--light mb-3">@yield('eyebrow')</p>
        @endif
        <h1>{!! $heroTitle !!}</h1>
      </div>
      @yield('band-aside')
    </div>
  </div>
</section>
