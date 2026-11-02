@props(['source' => 'website'])
<form class="newsletter-form" method="POST" action="{{ route('newsletter.subscribe') }}" novalidate>
    @csrf
    <input type="hidden" name="source" value="{{ $source }}">
    <div class="hp" aria-hidden="true"><label>Leave this empty <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
    <div class="row g-2">
        <div class="col-sm-5"><label class="visually-hidden" for="nl-name-{{ $source }}">Your name</label><input id="nl-name-{{ $source }}" class="form-control" type="text" name="name" placeholder="Your name (optional)" maxlength="120" autocomplete="name"></div>
        <div class="col-sm-5"><label class="visually-hidden" for="nl-email-{{ $source }}">Email address</label><input id="nl-email-{{ $source }}" class="form-control" type="email" name="email" placeholder="Email address" required maxlength="190" autocomplete="email"></div>
        <div class="col-sm-2 d-grid"><button class="btn btn-cds" type="submit">Subscribe</button></div>
    </div>
    <div class="form-check mt-2">
        <input class="form-check-input" type="checkbox" name="consent" value="1" id="nl-consent-{{ $source }}" required>
        <label class="form-check-label small" for="nl-consent-{{ $source }}">{{ \App\Support\Site::get('newsletter', 'consent_text', 'I agree to receive occasional emails and understand I can unsubscribe at any time.') }} <a href="{{ url('/privacy-policy') }}">Privacy policy</a></label>
    </div>
    <div class="newsletter-msg small mt-2" role="status" aria-live="polite">
        @if (session('newsletter_ok'))<span class="text-ok">{{ session('newsletter_ok') }}</span>@endif
        @if (session('newsletter_error'))<span class="text-err">{{ session('newsletter_error') }}</span>@endif
    </div>
</form>
