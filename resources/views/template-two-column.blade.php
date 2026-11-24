{{--
  Template Name: Two Column + Whats On
  Oxygen "Page - 2 col" (7217): title and content, Whats On box alongside.
--}}
@extends('layouts.app')

@section('content')
  @while(have_posts()) @php(the_post())
    <div class="container-site two-col">
      <div class="two-col__main">
        <h1 class="my-[30px]">{!! get_the_title() !!}</h1>
        <div class="entry-content">
          @php(the_content())
        </div>
      </div>

      <aside class="two-col__side">
        @include('partials.events.side')
      </aside>
    </div>
  @endwhile
@endsection
