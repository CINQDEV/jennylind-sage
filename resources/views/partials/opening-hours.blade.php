{{-- Opening hours block for the Contact sidebar. --}}
<h2 class="mb-3">{{ __('Opening hours', 'jennylind') }}</h2>
<dl class="m-0 grid grid-cols-[auto_1fr] gap-x-6 gap-y-1">
  @foreach (App\opening_hours() as [$days, $hours])
    <dt class="font-bold">{{ $days }}</dt>
    <dd class="m-0">{{ $hours }}</dd>
  @endforeach
</dl>
<p class="mt-2 mb-0 text-[15px] text-muted">{{ __('Under 18s welcome until 7pm', 'jennylind') }}</p>
