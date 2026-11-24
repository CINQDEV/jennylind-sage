{{-- Pop-up player used by session cards. --}}
<dialog class="session-modal" data-session-modal aria-label="{{ __('Session video', 'jennylind') }}">
  <div class="session-modal__inner">
    <div class="mb-3 flex items-center justify-between gap-4">
      <p class="m-0 font-display text-[24px] font-extrabold text-paper uppercase md:text-[30px]" data-session-modal-title></p>
      <button type="button" class="session-modal__close" data-session-close aria-label="{{ __('Close video', 'jennylind') }}">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
      </button>
    </div>
    <div class="aspect-video w-full overflow-hidden rounded-md bg-black" data-session-modal-frame></div>
    <a class="link-underline mt-4 inline-block text-paper" href="#" data-session-modal-page>{{ __('About this session', 'jennylind') }} →</a>
  </div>
</dialog>
