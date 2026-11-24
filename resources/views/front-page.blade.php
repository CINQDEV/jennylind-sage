{{-- Homepage (Oxygen "Homepage", 7225 + the Welcome page layout): content beside the Whats On box. --}}
@extends('layouts.app')

@section('content')
  @while(have_posts()) @php(the_post())
    <div class="container-site flex flex-col gap-0 px-4 lg:flex-row lg:px-0">
      <div class="entry-content w-full py-[30px] lg:w-2/3 lg:pr-10">
        @php(the_content())
      </div>

      <aside class="w-full pt-[30px] lg:w-1/3">
        @include('partials.events.side')
      </aside>
    </div>
  @endwhile
@endsection
