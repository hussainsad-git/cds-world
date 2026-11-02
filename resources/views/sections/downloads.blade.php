@if (!empty($d['files']))
<section class="section"><div class="container-cds container-prose">
    @if ($section->heading)<h2 class="section-title">{{ $section->heading }}</h2>@endif
    <ul class="download-list">
        @foreach ($d['files'] as $f)<li><a href="{{ \App\Support\Site::file($f['file'] ?? null) }}" download>{{ $f['title'] ?? 'Download' }} <span>PDF</span></a></li>@endforeach
    </ul>
</div></section>
@endif
