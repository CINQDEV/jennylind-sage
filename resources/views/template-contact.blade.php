{{--
  Template Name: Contact
  Oxygen "Contact" (8414): enquiry form and map, with the page content (address,
  directions) in the right-hand column.
--}}
@extends('layouts.app')

@section('content')
  @while(have_posts()) @php(the_post())
    <div class="container-site two-col">
      <div class="two-col__main">
        <h2 class="mt-[30px]">{{ __('Email us', 'jennylind') }}</h2>
        <div class="w-full">
          {!! do_shortcode('[forminator_form id="8842"]') !!}
        </div>

        <h3 class="mt-[30px]">{{ __('Map', 'jennylind') }}</h3>
        <iframe
          class="h-[400px] w-full border-0"
          loading="lazy"
          title="{{ __('Map to The Jenny Lind Inn', 'jennylind') }}"
          src="https://maps.google.com/maps?q={{ rawurlencode('The Jenny Lind, 69 High St, Hastings, TN34 3EW') }}&z=14&output=embed"
        ></iframe>
      </div>

      <aside class="two-col__side two-col__side--ruled entry-content">
        @php(the_content())
      </aside>
    </div>
  @endwhile
@endsection
