@props(['item'])
<article class="coverage-item">
    <a class="coverage-thumb" href="{{ $item->url() }}" @if ($item->linksOut()) target="_blank" rel="noopener nofollow" @endif tabindex="-1" aria-hidden="true">
        <x-media-img :model="$item" collection="thumbnail" conversion="thumb" :alt="$item->thumb_alt ?: ''" ratio="4x3" />
    </a>
    <div class="coverage-body">
        <div class="card-meta">
            @if ($item->outlet)<span class="outlet">{{ $item->outlet->name }}</span>@endif
            <time datetime="{{ $item->published_on?->toDateString() }}">{{ $item->published_on?->format('d M Y') }}</time>
            @if ($item->is_featured)<span class="chip chip-accent">Featured</span>@endif
        </div>
        <h3 class="card-title"><a href="{{ $item->url() }}" @if ($item->linksOut()) target="_blank" rel="noopener nofollow" @endif>{{ $item->headline }}@if ($item->linksOut()) <span class="ext" aria-hidden="true">↗</span><span class="visually-hidden">(opens the original story in a new tab)</span>@endif</a></h3>
        @if ($item->summary)<p class="card-excerpt">{{ \Illuminate\Support\Str::limit($item->summary, 200) }}</p>@endif
    </div>
</article>
