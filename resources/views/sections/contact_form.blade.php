<section class="section" id="contact-form">
    <div class="container-cds container-prose">
        @if ($section->heading)<h2 class="section-title">{{ $section->heading }}</h2>@endif
        @if (session('contact_sent'))<div class="alert alert-success" role="status">{{ session('contact_sent') }}</div>@endif
        @if ($errors->any() && old('subject') !== null)<div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
        <form method="POST" action="{{ route('contact.store') }}" class="contact-form">
            @csrf
            <div class="hp" aria-hidden="true"><label>Leave this empty <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label" for="c-name">Name</label><input class="form-control" id="c-name" name="name" value="{{ old('name') }}" required maxlength="120" autocomplete="name"></div>
                <div class="col-md-6"><label class="form-label" for="c-email">Email</label><input class="form-control" id="c-email" type="email" name="email" value="{{ old('email') }}" required maxlength="190" autocomplete="email"></div>
                <div class="col-md-6"><label class="form-label" for="c-phone">Phone (optional)</label><input class="form-control" id="c-phone" name="phone" value="{{ old('phone') }}" maxlength="40" autocomplete="tel"></div>
                <div class="col-md-6"><label class="form-label" for="c-subject">Subject</label><input class="form-control" id="c-subject" name="subject" value="{{ old('subject') }}" required maxlength="190"></div>
                <div class="col-12"><label class="form-label" for="c-message">Message</label><textarea class="form-control" id="c-message" name="message" rows="6" required minlength="10" maxlength="5000">{{ old('message') }}</textarea></div>
                <div class="col-12"><button class="btn btn-cds" type="submit">Send message</button></div>
            </div>
        </form>
    </div>
</section>
