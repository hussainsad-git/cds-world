<section class="section section-alt">
    <div class="container-cds">
        <x-section-head :title="$section->heading" :text="$d['description'] ?? null" />
        @if (!empty($items))
            <div class="focus-row">
                @foreach ($items as $i => $item)
                    <a class="focus-cell" href="{{ url($item['url'] ?? '/articles') }}">
                        <span class="focus-num">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="focus-name">{{ $item['title'] }}</span>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</section>
