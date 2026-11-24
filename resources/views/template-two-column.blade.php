{{--
  Template Name: Two Column + Whats On
  Page content with the What's On list alongside.
--}}
@extends('layouts.app')

@section('content')
  @while(have_posts()) @php(the_post())
    <div class="container-site flex flex-col gap-12 py-12 md:py-16 lg:flex-row">
      <div class="entry-content min-w-0 lg:w-[68%]">
        @php(the_content())
      </div>
      <aside class="lg:w-[32%]">
        @include('partials.events.side')
      </aside>
    </div>
  @endwhile
@endsection
