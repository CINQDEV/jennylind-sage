@extends('layouts.app')

@section('content')
  <div class="container-site px-4 lg:px-0">
    <p>{{ __('Sorry, but the page you are trying to view does not exist.', 'jennylind') }}</p>
    {!! get_search_form(false) !!}
  </div>
@endsection
