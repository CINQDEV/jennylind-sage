{{-- "Add to your calendar": subscribe to the Event Organiser iCal feed. --}}
@php($cal = App\calendar_links())

<div class="calendar-card">
  <div>
    <p class="m-0 mb-1.5 flex items-center gap-2 text-[13px] font-bold tracking-[0.08em] text-accent-dark uppercase">
      <svg class="flex-none" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4.5" width="18" height="16" rx="2"/><path d="M3 9.5h18M8 2.5v4M16 2.5v4"/><path d="M12 13v5M9.5 15.5h5"/></svg>
      {{ __('Add to your calendar', 'jennylind') }}
    </p>
    <h2 class="m-0 text-[32px] leading-none">{{ __('Never miss a gig', 'jennylind') }}</h2>
  </div>

  <p class="m-0 text-[15px] text-muted">
    {{ __('Put our listings in the calendar on your phone. Subscribe once and new gigs and sessions turn up by themselves – no app, no sign-up.', 'jennylind') }}
  </p>

  <div class="flex flex-col gap-2.5">
    <a class="btn btn-accent justify-center" href="{{ $cal['webcal'] }}">{{ __('Subscribe', 'jennylind') }}</a>
    <p class="m-0 -mt-1 text-center text-[13px] text-muted">{{ __('iPhone, iPad, Mac & Outlook', 'jennylind') }}</p>
    <a class="btn btn-outline mt-1 justify-center" href="{{ $cal['google'] }}" target="_blank" rel="noopener">{{ __('Add to Google Calendar', 'jennylind') }}</a>
    <p class="m-0 -mt-1 text-center text-[13px] text-muted">{{ __('Google Calendar & Android', 'jennylind') }}</p>
  </div>

  <details class="calendar-card__help">
    <summary>{{ __('How does this work?', 'jennylind') }}</summary>
    <dl>
      <dt>{{ __('iPhone, iPad & Mac', 'jennylind') }}</dt>
      <dd>{{ __('Tap Subscribe, then Subscribe again to confirm. Our events appear as their own calendar, which you can switch on and off.', 'jennylind') }}</dd>
      <dt>{{ __('Google Calendar & Android', 'jennylind') }}</dt>
      <dd>{{ __('Tap Add to Google Calendar, then Add. It syncs to your Android phone too. Google checks for new events about once a day.', 'jennylind') }}</dd>
      <dt>{{ __('Outlook', 'jennylind') }}</dt>
      <dd>{{ __('Tap Subscribe, or copy the link below and choose Add calendar → Subscribe from web.', 'jennylind') }}</dd>
      <dt>{{ __('Changed your mind?', 'jennylind') }}</dt>
      <dd>{{ __('Delete or unsubscribe from the “Jenny Lind Inn – What’s on” calendar in your app. Your own events aren’t touched.', 'jennylind') }}</dd>
    </dl>
  </details>

  <div class="border-t border-card-line pt-4 text-[14px]">
    <button type="button" class="calendar-card__link" data-copy="{{ $cal['feed'] }}" data-copied-text="{{ __('Link copied', 'jennylind') }}">{{ __('Copy calendar link', 'jennylind') }}</button>
  </div>
</div>
