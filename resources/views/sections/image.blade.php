@if (!empty($d['image']))
<section class="section"><div class="container-cds container-prose">
    @if ($section->heading)<h2 class="section-title">{{ $section->heading }}</h2>@endif
    <figure class="figure-cds"><img src="{{ \App\Support\Site::file($d['image']) }}" alt="{{ $d['alt'] ?? '' }}" loading="lazy">@if (!empty($d['caption']))<figcaption>{{ $d['caption'] }}</figcaption>@endif</figure>
</div></section>
@endif
