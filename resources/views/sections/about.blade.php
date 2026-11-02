<section class="section about-band">
    <div class="container-cds container-prose text-center">
        <p class="eyebrow">{{ \App\Support\Site::name() }}</p>
        <h2 class="section-title">{{ $section->heading }}</h2>
        @if (!empty($d['text']))<p class="section-lead mx-auto">{{ $d['text'] }}</p>@endif
        <div class="pillars">
            <div><strong>Youth engagement</strong></div><div><strong>National dialogue</strong></div><div><strong>Policy development</strong></div>
        </div>
        @if (!empty($d['link']['url']))<a class="btn btn-cds mt-3" href="{{ url($d['link']['url']) }}">{{ $d['link']['label'] ?? 'Learn more' }}</a>@endif
    </div>
</section>
