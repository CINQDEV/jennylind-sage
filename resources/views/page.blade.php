{{-- Default page (Oxygen "Page", 7214): the content, full width. --}}
@extends('layouts.app')

@section('content')
  @while(have_posts()) @php(the_post())
    <div class="container-site px-4 lg:px-0">
      <div class="entry-content">
        @php(the_content())
      </div>
    </div>
  @endwhile
@endsection
