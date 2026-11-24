@extends('layouts.app')

@section('content')
  <div class="container-site py-12 md:py-16">
    @if (! have_posts())
      <p>{{ __('Sorry, no results were found.', 'jennylind') }}</p>
      {!! get_search_form(false) !!}
    @endif

    <div class="grid gap-8">
      @while(have_posts()) @php(the_post())
        @includeFirst(['partials.content-' . get_post_type(), 'partials.content'])
      @endwhile
    </div>

    <div class="mt-10">{!! get_the_posts_navigation() !!}</div>
  </div>
@endsection
