<section class="section">
    <div class="container-cds container-prose">
        @if ($section->heading)<h2 class="section-title">{{ $section->heading }}</h2>@endif
        <div class="prose-cds">{!! clean((string) ($d['html'] ?? ''), 'default') !!}</div>
    </div>
</section>
@pushOnce('head', 'glightbox-css')<link rel="stylesheet" href="{{ asset('vendor/glightbox.min.css') }}">@endPushOnce
@pushOnce('scripts', 'glightbox-js')<script src="{{ asset('vendor/glightbox.min.js') }}"></script>@endPushOnce
