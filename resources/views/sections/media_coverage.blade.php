@if ($items->isNotEmpty())
<section class="section section-alt">
    <div class="container-cds">
        <x-section-head :title="$section->heading" :text="$d['description'] ?? null" link="/media-coverage" link-label="All coverage" />
        <ul class="mc-list">
            @foreach ($items as $m)
                <li>
                    <a href="{{ $m->source_url }}" target="_blank" rel="noopener nofollow">
                        <span class="mc-headline">{{ $m->headline }}</span>
                        <span class="mc-meta">{{ $m->outlet?->name }}@if($m->outlet && $m->published_on) · @endif{{ $m->published_on?->format('d M Y') }} <span aria-hidden="true">&#8599;</span><span class="visually-hidden">(opens the original story in a new tab)</span></span>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</section>
@endif
