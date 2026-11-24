{{-- Default page: title band, then the page content. --}}
@extends('layouts.app')

@section('content')
  @while(have_posts()) @php(the_post())
    <div class="container-site py-12 md:py-16">
      <div class="entry-content">
        @php(the_content())
      </div>
    </div>
  @endwhile
@endsection
