@extends('layouts.app')

@section('content')
  @while(have_posts()) @php(the_post())
    <article @php(post_class('container-site two-col'))>
      <div class="two-col__main entry-content">
        <p class="text-sm text-blue">{{ get_the_date() }}</p>
        @php(the_content())
      </div>
      <aside class="two-col__side">
        @include('partials.events.side')
      </aside>
    </article>
  @endwhile
@endsection
