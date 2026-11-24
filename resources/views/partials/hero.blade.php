{{-- Oxygen "Header v2" hero section: featured image background with the title in a red label. --}}
<section class="hero" @if ($heroImage) style="background-image: url('{{ esc_url($heroImage) }}')" @endif>
  <div class="hero__inner">
    <h1 class="hero__title">{!! $heroTitle !!}</h1>
  </div>
</section>
