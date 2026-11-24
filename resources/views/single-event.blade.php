{{-- Single event: Event Organiser's "Event Details" box, then the description. --}}
@extends('layouts.app')

@section('content')
  @while(have_posts()) @php(the_post())
    <article @php(post_class('container-site px-4 lg:px-0'))>
      @if (function_exists('eo_get_template_part'))
        <div class="event-details mb-6 border-y border-powder py-4 [&_h4]:text-[17px] [&_ul]:mt-2 [&_ul]:list-disc [&_ul]:pl-6">
          @php(eo_get_template_part('event-meta', 'event-single'))
        </div>
      @endif

      <div class="entry-content">
        @php(the_content())
      </div>

      <p class="mt-10"><a href="{{ home_url('/whats-on/') }}">&larr; {{ __('All events', 'jennylind') }}</a></p>
    </article>
  @endwhile
@endsection
