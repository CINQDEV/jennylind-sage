@extends('layouts.app')

@section('content')
  <div class="container-site py-12 md:py-16">
    <p>{{ __('Sorry, but the page you are trying to view does not exist.', 'jennylind') }}</p>
    {!! get_search_form(false) !!}
  </div>
@endsection
