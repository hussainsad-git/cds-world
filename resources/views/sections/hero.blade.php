@php
    $bg = !empty($d['background_image']) ? \App\Support\Site::file($d['background_image']) : null;
    $focus = $focus ?? [];
    $collage = $collage ?? [];
    $showFocus = ($d['show_focus'] ?? true) && $focus && ! $bg;
@endphp
<section class="hero {{ $bg ? 'has-bg' : 'hero-light' }} {{ $showFocus ? 'has-focus' : ($collage && !$bg ? 'has-collage' : '') }}" @if($bg) style="background-image:linear-gradient(105deg,rgba(0,0,0,.72),rgba(0,0,0,.35)),url('{{ $bg }}')" @endif>
    <div class="container-cds hero-inner">
        <div class="hero-copy">
            <p class="eyebrow hero-pill">Think tank &middot; Pakistan</p>
            <h1 class="hero-title">{{ $section->heading }}</h1>
            @if (!empty($d['description']))<p class="hero-lead">{{ $d['description'] }}</p>@endif
            <div class="hero-actions">
                @if (!empty($d['cta_primary']['label']))<a class="btn btn-cds btn-lg" href="{{ $d['cta_primary']['url'] ?? '#' }}">{{ $d['cta_primary']['label'] }}</a>@endif
                @if (!empty($d['cta_secondary']['label']))<a class="btn btn-ghost btn-lg" href="{{ $d['cta_secondary']['url'] ?? '#' }}">{{ $d['cta_secondary']['label'] }}</a>@endif
            </div>
        </div>

        @if ($showFocus)
            <div class="hero-focus">
                <h2 class="focus-kicker">Areas of continuing work</h2>
                <ul class="focus-list">
                    @foreach ($focus as $i => $item)
                        <li>
                            <a href="{{ url($item['url'] ?? '/articles') }}">
                                <span class="focus-num">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                <span class="focus-name">{{ $item['title'] }}</span>
                                <span class="focus-go" aria-hidden="true">&rarr;</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @elseif ($collage && ! $bg)
            <div class="hero-collage" aria-hidden="true">
                @foreach (array_slice($collage, 0, 3) as $i => $src)
                    <img class="hc{{ $i + 1 }}" src="{{ $src }}" alt="" loading="eager" decoding="async">
                @endforeach
            </div>
        @endif
    </div>

    @if (!empty($stats))
        <div class="container-cds">
            <dl class="hero-stats">
                @foreach ($stats as $s)<div><dt>{{ $s['label'] }}</dt><dd>{{ number_format($s['value']) }}</dd></div>@endforeach
            </dl>
        </div>
    @endif
</section>
