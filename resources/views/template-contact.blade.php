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
        @php(the_content())

        <h2 class="mb-3">{{ __('Opening hours', 'jennylind') }}</h2>
        <dl class="m-0 grid grid-cols-[auto_1fr] gap-x-6 gap-y-1">
          @foreach (App\opening_hours() as [$days, $hours])
            <dt class="font-bold">{{ $days }}</dt>
            <dd class="m-0">{{ $hours }}</dd>
          @endforeach
        </dl>
        <p class="mt-2 mb-0 text-[15px] text-muted">{{ __('Under 18s welcome until 7pm', 'jennylind') }}</p>
      </aside>
    </div>
  @endwhile
@endsection
