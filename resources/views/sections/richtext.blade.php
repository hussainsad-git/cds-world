<section class="section">
    <div class="container-cds container-prose">
        @if ($section->heading)<h2 class="section-title">{{ $section->heading }}</h2>@endif
        <div class="prose-cds">{!! clean((string) ($d['html'] ?? ''), 'default') !!}</div>
    </div>
</section>
