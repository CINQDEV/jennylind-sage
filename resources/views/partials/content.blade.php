<article @php(post_class('border-t border-line pt-6'))>
  <p class="eyebrow mb-2">{{ get_the_date() }}</p>
  <h2 class="m-0 text-[32px] md:text-[40px]"><a class="no-underline" href="{{ get_permalink() }}">{!! get_the_title() !!}</a></h2>
  <div class="entry-content mt-3 text-muted">@php(the_excerpt())</div>
</article>
