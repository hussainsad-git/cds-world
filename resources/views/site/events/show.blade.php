@extends('layouts.app')
@section('body_class', 'tpl-event')
@section('content')
@php $state = $event->lifecycle(); @endphp
<header class="page-hero event-hero">
    <div class="container-cds">
        <div class="card-meta"><span class="chip chip-{{ $state }}">{{ ucfirst($state) }}</span>@foreach ($event->categories as $c)<span class="chip">{{ $c->name }}</span>@endforeach</div>
        <h1>{{ $event->title }}</h1>
        @if ($event->summary)<p class="lead">{{ $event->summary }}</p>@endif
    </div>
</header>
<div class="container-cds mt-4">
    <div class="row g-5">
        <div class="col-lg-8">
            @if ($image)<figure class="article-hero"><img src="{{ $image }}" alt="{{ $event->banner_alt ?: $event->title }}" fetchpriority="high"></figure>@endif
            <div class="prose-cds">{!! clean((string) $event->description, 'default') !!}</div>

            @if ($event->livestream_url)<p class="mt-4"><a class="btn btn-cds" href="{{ $event->livestream_url }}" target="_blank" rel="noopener">Watch the livestream</a></p>@endif
            @if ($event->video_source && $event->video_url)
                @php $vid = \App\Models\Video::parseExternalId($event->video_source, $event->video_url);
                     $embed = $event->video_source === 'youtube' && $vid ? 'https://www.youtube-nocookie.com/embed/'.$vid.'?autoplay=1&rel=0' : 'https://www.facebook.com/plugins/video.php?show_text=false&autoplay=1&href='.urlencode($event->video_url); @endphp
                <h2 class="h4 mt-5">Recording</h2>
                <x-video-facade :embed="$embed" :thumb="$vid ? 'https://i.ytimg.com/vi/'.$vid.'/hqdefault.jpg' : null" :title="$event->title" />
            @endif

            @if ($event->speakers->isNotEmpty())
                <h2 class="h4 mt-5">Speakers &amp; guests</h2>
                <div class="team-grid">
                    @foreach ($event->speakers as $s)
                        <article class="team-card"><x-media-img :model="$s" collection="photo" conversion="thumb" :alt="$s->name" ratio="1x1" class="team-photo" />
                            <h3 class="h6">{{ $s->name }}</h3>
                            <p class="role">{{ collect([$s->designation, $s->organization])->filter()->implode(', ') }}</p>
                            <span class="chip">{{ ucfirst($s->role) }}</span></article>
                    @endforeach
                </div>
            @endif

            @php $gal = $event->getMedia('gallery'); @endphp
            @if ($gal->isNotEmpty())
                <h2 class="h4 mt-5">Photographs</h2>
                <div class="gallery-strip">@foreach ($gal as $m)<a class="glightbox" data-gallery="event" href="{{ $m->getUrl('large') ?: $m->getUrl() }}"><x-media-img :url="$m->getUrl('thumb') ?: $m->getUrl()" :alt="$event->title" ratio="1x1" /></a>@endforeach</div>
                @push('head')<link rel="stylesheet" href="{{ asset('vendor/glightbox.min.css') }}">@endpush
                @push('scripts')<script src="{{ asset('vendor/glightbox.min.js') }}"></script>@endpush
            @endif

            @php $docs = $event->getMedia('attachments'); @endphp
            @if ($docs->isNotEmpty())
                <aside class="downloads"><h2 class="h5">Documents</h2><ul class="download-list">@foreach ($docs as $f)<li><a href="{{ $f->getUrl() }}" download>{{ $f->file_name }} <span>PDF</span></a></li>@endforeach</ul></aside>
            @endif

            @if ($event->articles->isNotEmpty() || $event->mediaCoverage->isNotEmpty())
                <h2 class="h4 mt-5">Related coverage</h2>
                <ul class="plain-list">
                    @foreach ($event->articles as $a)<li><a href="{{ url('/articles/'.$a->slug) }}">{{ $a->title }}</a> <small>Article</small></li>@endforeach
                    @foreach ($event->mediaCoverage as $m)<li><a href="{{ $m->source_url }}" target="_blank" rel="noopener nofollow">{{ $m->headline }}</a> <small>{{ $m->outlet?->name }}</small></li>@endforeach
                </ul>
            @endif
        </div>

        <aside class="col-lg-4">
            <div class="event-facts">
                <h2 class="h5">Event details</h2>
                <dl>
                    <dt>Date</dt><dd>{{ $event->starts_at->format('l, d F Y') }}@if ($event->ends_at && !$event->ends_at->isSameDay($event->starts_at)) – {{ $event->ends_at->format('l, d F Y') }}@endif</dd>
                    <dt>Time</dt><dd>{{ $event->starts_at->format('H:i') }}@if ($event->ends_at) – {{ $event->ends_at->format('H:i') }}@endif <small>(PKT)</small></dd>
                    @if ($event->venue)<dt>Venue</dt><dd>{{ $event->venue }}@if ($event->address)<br>{{ $event->address }}@endif @if ($event->map_url)<br><a href="{{ $event->map_url }}" target="_blank" rel="noopener">View on Google Maps</a>@endif</dd>@endif
                    @if ($event->contact_email || $event->contact_phone)<dt>Contact</dt><dd>@if ($event->contact_email)<a href="mailto:{{ $event->contact_email }}">{{ $event->contact_email }}</a><br>@endif{{ $event->contact_phone }}</dd>@endif
                </dl>
                @if ($event->registration_url && in_array($state, ['upcoming', 'ongoing']))<a class="btn btn-cds w-100" href="{{ $event->registration_url }}" target="_blank" rel="noopener">Register</a>@endif
            </div>

            @if ($event->internal_registration && in_array($state, ['upcoming', 'ongoing']))
                <div class="event-facts mt-4" id="register">
                    <h2 class="h5">Register for this event</h2>
                    @if (session('registered'))<div class="alert alert-success" role="status">{{ session('registered') }}</div>
                    @else
                        @if ($errors->any())<div class="alert alert-danger" role="alert"><ul class="mb-0 ps-3">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
                        <form method="POST" action="{{ route('events.register', $event->slug) }}">
                            @csrf
                            <div class="hp" aria-hidden="true"><label>Leave this empty <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
                            <label class="form-label" for="r-name">Name</label><input class="form-control mb-2" id="r-name" name="name" value="{{ old('name') }}" required maxlength="120" autocomplete="name">
                            <label class="form-label" for="r-email">Email</label><input class="form-control mb-2" id="r-email" type="email" name="email" value="{{ old('email') }}" required maxlength="190" autocomplete="email">
                            <label class="form-label" for="r-org">Organization (optional)</label><input class="form-control mb-2" id="r-org" name="organization" value="{{ old('organization') }}" maxlength="190">
                            <label class="form-label" for="r-phone">Phone (optional)</label><input class="form-control mb-2" id="r-phone" name="phone" value="{{ old('phone') }}" maxlength="40" autocomplete="tel">
                            <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="consent" id="r-consent" value="1" required><label class="form-check-label small" for="r-consent">I agree that CDS World may contact me about this event. <a href="{{ url('/privacy-policy') }}">Privacy policy</a></label></div>
                            <button class="btn btn-cds w-100" type="submit">Register</button>
                        </form>
                    @endif
                </div>
            @endif
            <x-share class="mt-4" :url="url('/events/'.$event->slug)" :title="$event->title" />
        </aside>
    </div>
</div>
<div class="pb-5"></div>
@endsection
