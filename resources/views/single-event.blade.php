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
          <div class="mb-8 rounded-lg bg-sand p-5 [&_h4]:m-0 [&_h4]:text-[22px] [&_ul]:mt-2 [&_ul]:mb-0 [&_ul]:list-none [&_ul]:p-0">
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
