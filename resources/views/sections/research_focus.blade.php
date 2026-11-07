<section class="section section-alt">
    <div class="container-cds">
        <x-section-head :title="$section->heading" :text="$d['description'] ?? null" />
        @if (!empty($items))
            <div class="focus-grid">
                @foreach ($items as $item)
                    <a class="focus-card" href="{{ url($item['url'] ?? '/articles') }}">
                        <span class="focus-figure">
                            @if (!empty($item['image']))
                                <img src="{{ \App\Support\Site::file($item['image']) }}" alt="" loading="lazy" decoding="async">
                            @else
                                <x-focus-icon :title="$item['title']" />
                            @endif
                        </span>
                        <h3>{{ $item['title'] }}</h3>
                        @if (!empty($item['description']))<p>{{ $item['description'] }}</p>@endif
                        <span class="link-arrow">Explore <span aria-hidden="true">&rarr;</span></span>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</section>
