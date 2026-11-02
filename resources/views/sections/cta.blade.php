<section class="section section-alt">
    <div class="container-cds text-center">
        @if ($section->heading)<h2 class="section-title">{{ $section->heading }}</h2>@endif
        @if (!empty($d['text']))<p class="section-lead mx-auto">{{ $d['text'] }}</p>@endif
        <div class="mt-3 d-flex gap-2 justify-content-center flex-wrap">
            @foreach (($d['buttons'] ?? []) as $b)<a class="btn {{ ($b['style'] ?? 'primary') === 'outline' ? 'btn-outline-cds' : 'btn-cds' }}" href="{{ $b['url'] }}">{{ $b['label'] }}</a>@endforeach
        </div>
    </div>
</section>
