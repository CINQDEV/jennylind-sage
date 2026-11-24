<article @php(post_class('border-b border-dashed border-powder pb-8'))>
  <h2 class="m-0"><a href="{{ get_permalink() }}">{!! get_the_title() !!}</a></h2>
  <p class="text-sm text-blue">{{ get_the_date() }}</p>
  <div class="entry-content">@php(the_excerpt())</div>
</article>
