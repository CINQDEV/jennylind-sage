{{-- Single event: poster, Event Organiser's details, description, what's next. --}}
@extends('layouts.app')

@section('eyebrow')
  {{ App\event_start(get_post(), 'l j F') }} · {{ App\event_time(get_post()) }}
@endsection

@section('content')
  @while(have_posts()) @php(the_post())
    <div class="container-site flex flex-col gap-12 py-12 md:py-16 lg:flex-row">
      <article @php(post_class('min-w-0 lg:w-[68%]'))>
        @if (has_post_thumbnail())
          <img src="{{ get_the_post_thumbnail_url(null, 'large') }}" alt="" class="mb-8 block max-h-[560px] w-auto rounded-lg">
        @endif

        @if (function_exists('eo_get_template_part'))
          <div class="event-details mb-8 rounded-lg bg-sand px-6 py-5">
            @php(eo_get_template_part('event-meta', 'event-single'))
          </div>
        @endif

        <div class="entry-content">
          @php(the_content())
        </div>
      </article>

      <aside class="lg:w-[32%]">
        @include('partials.events.side')
      </aside>
    </div>
  @endwhile
@endsection
