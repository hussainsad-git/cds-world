@php
    $bg = !empty($d['background_image']) ? \App\Support\Site::file($d['background_image']) : null;
@endphp
<section class="hero {{ $bg ? 'has-bg' : '' }}" @if($bg) style="background-image:linear-gradient(105deg,rgba(0,0,0,.72),rgba(0,0,0,.35)),url('{{ $bg }}')" @endif>
    <div class="container-cds hero-inner">
        <p class="eyebrow">Think tank · Pakistan</p>
        <h1 class="hero-title">{{ $section->heading }}</h1>
        @if (!empty($d['description']))<p class="hero-lead">{{ $d['description'] }}</p>@endif
        <div class="hero-actions">
            @if (!empty($d['cta_primary']['label']))<a class="btn btn-cds btn-lg" href="{{ $d['cta_primary']['url'] ?? '#' }}">{{ $d['cta_primary']['label'] }}</a>@endif
            @if (!empty($d['cta_secondary']['label']))<a class="btn btn-ghost btn-lg" href="{{ $d['cta_secondary']['url'] ?? '#' }}">{{ $d['cta_secondary']['label'] }}</a>@endif
        </div>
        @if (!empty($stats))
            <dl class="hero-stats">
                @foreach ($stats as $s)<div><dt>{{ $s['label'] }}</dt><dd>{{ number_format($s['value']) }}</dd></div>@endforeach
            </dl>
        @endif
    </div>
</section>
