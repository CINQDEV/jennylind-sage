@extends('layouts.app')

@section('eyebrow', get_the_date())

@section('content')
  @while(have_posts()) @php(the_post())
    <div class="container-site flex flex-col gap-12 py-12 md:py-16 lg:flex-row">
      <article @php(post_class('entry-content min-w-0 lg:w-[68%]'))>
        @php(the_content())
      </article>
      <aside class="lg:w-[32%]">
        @include('partials.events.side')
      </aside>
    </div>
  @endwhile
@endsection
