{{-- A single Jenny Session: the video, its write-up, and more sessions. --}}
@extends('layouts.app')

@php
  $s = App\session(get_post());
  $more = array_slice(array_values(array_filter(App\sessions(), fn ($o) => $o['id'] !== $s['id'])), 0, 3);
  $event = $s['event'] ? get_post($s['event']) : null;
@endphp

@section('eyebrow')
  <a class="text-blush no-underline hover:text-paper" href="{{ App\sessions_url() }}">{{ __('The Jenny Sessions', 'jennylind') }}</a>
  @if ($s['number']) · {{ __('Session', 'jennylind') }} #{{ str_pad($s['number'], 2, '0', STR_PAD_LEFT) }} @endif
@endsection
@section('band-padding', 'pb-40 md:pb-64')

@section('content')
  <section class="relative z-10 -mt-28 md:-mt-48">
    <div class="container-site">
      <div class="mx-auto max-w-5xl overflow-hidden rounded-lg bg-ink shadow-[0_20px_50px_rgba(21,35,46,0.3)]">
        @include('partials.sessions.player', ['s' => $s])
      </div>
    </div>
  </section>

  <div class="container-site flex flex-col gap-12 py-12 md:py-16 lg:flex-row">
    <article @php(post_class('min-w-0 lg:w-[66%]'))>
      <dl class="session-meta">
        @if ($s['artist'] !== $s['title'])
          <div><dt>{{ __('Artist', 'jennylind') }}</dt><dd>{{ $s['artist'] }}</dd></div>
        @endif
        @if ($s['date'])
          <div><dt>{{ __('Played', 'jennylind') }}</dt><dd>{{ $s['date'] }}</dd></div>
        @endif
        @if ($s['styles'])
          <div><dt>{{ __('Style', 'jennylind') }}</dt><dd>{{ implode(', ', $s['styles']) }}</dd></div>
        @endif
        @if ($event)
          <div><dt>{{ __('The gig', 'jennylind') }}</dt><dd><a href="{{ get_permalink($event) }}">{!! get_the_title($event) !!}</a></dd></div>
        @endif
      </dl>

      @while(have_posts()) @php(the_post())
        <div class="entry-content">@php(the_content())</div>
      @endwhile

      @if ($s['watch'])
        <p class="mt-8 mb-0 flex flex-wrap gap-3">
          <a class="btn btn-ink" href="{{ $s['watch'] }}" target="_blank" rel="noopener">{{ __('Watch on YouTube', 'jennylind') }}</a>
          <a class="btn btn-outline" href="{{ App\sessions_url() }}">{{ __('All sessions', 'jennylind') }}</a>
        </p>
      @endif
    </article>

    <aside class="flex flex-col gap-6 lg:w-[34%]">
      @include('partials.events.side')
    </aside>
  </div>

  @if ($more)
    <section class="bg-sand py-16 md:py-20" aria-label="{{ __('More sessions', 'jennylind') }}">
      <div class="container-site">
        <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
          <h2 class="m-0 text-[40px] md:text-[56px]">{{ __('More sessions', 'jennylind') }}</h2>
          <a class="link-underline" href="{{ App\sessions_url() }}">{{ __('All sessions', 'jennylind') }} →</a>
        </div>
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          @foreach ($more as $o)
            @include('partials.sessions.card', ['s' => $o])
          @endforeach
        </div>
      </div>
    </section>
    @include('partials.sessions.modal')
  @endif
@endsection
