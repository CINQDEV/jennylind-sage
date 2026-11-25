{{--
  Template Name: Contact
  Enquiry form and map, with the page content (address, directions) alongside.
--}}
@extends('layouts.app')

@section('eyebrow', '69 High Street · Hastings Old Town')

@section('content')
  @while(have_posts()) @php(the_post())
    <div class="container-site flex flex-col gap-12 py-12 md:py-16 lg:flex-row">
      <div class="min-w-0 lg:w-[62%]">
        <h2 class="mt-0 mb-4 text-[36px] md:text-[48px]">{{ __('Email us', 'jennylind') }}</h2>
        {!! do_shortcode('[forminator_form id="8842"]') !!}

        <h2 class="mt-12 mb-4 text-[36px] md:text-[48px]">{{ __('Map', 'jennylind') }}</h2>
        <iframe
          class="h-[400px] w-full rounded-lg border-0"
          loading="lazy"
          title="{{ __('Map to The Jenny Lind Inn', 'jennylind') }}"
          src="https://maps.google.com/maps?q={{ rawurlencode('The Jenny Lind, 69 High St, Hastings, TN34 3EW') }}&z=15&output=embed"
        ></iframe>
      </div>

      <aside class="entry-content rounded-lg bg-sand p-6 md:p-8 lg:w-[38%] lg:self-start [&_h2]:mt-0 [&_h2:not(:first-child)]:mt-8 [&_h2]:text-[32px]">
        {{-- Opening hours go straight after the first section (Address). --}}
        @php(ob_start())
        @php(the_content())
        @php([$before, $after] = App\split_after_first_section((string) ob_get_clean()))
        {!! $before !!}
        @include('partials.opening-hours')
        {!! $after !!}
      </aside>
    </div>
  @endwhile
@endsection
