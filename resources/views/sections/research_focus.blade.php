<section class="section section-alt">
    <div class="container-cds">
        <x-section-head :title="$section->heading" :text="$d['description'] ?? null" />
        @if (!empty($items))
        <div class="focus-grid">
            @foreach (($items ?? []) as $i => $item)
                <a class="focus-card" href="{{ url($item['url'] ?? '/articles') }}">
                    <span class="focus-num">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                    <h3>{{ $item['title'] }}</h3>
                    @if (!empty($item['description']))<p>{{ $item['description'] }}</p>@endif
                    <span class="link-arrow">Explore <span aria-hidden="true">→</span></span>
                </a>
            @endforeach
        </div>
        @endif
    </div>
</section>
