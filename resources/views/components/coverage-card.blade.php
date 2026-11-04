@props(['item'])
<article class="card-article card-coverage">
    <a class="card-media" href="{{ $item->source_url }}" target="_blank" rel="noopener nofollow" tabindex="-1" aria-hidden="true">
        <x-media-img :model="$item" collection="thumbnail" conversion="card" :alt="$item->thumb_alt ?: ''" />
        @if ($item->outlet)<b class="card-outlet">{{ $item->outlet->name }}</b>@endif
    </a>
    <div class="card-body-cds">
        <h3 class="card-title"><a href="{{ $item->source_url }}" target="_blank" rel="noopener nofollow">{{ $item->headline }} <span class="ext" aria-hidden="true">&#8599;</span><span class="visually-hidden">(opens the original story in a new tab)</span></a></h3>
        @if ($item->summary)<p class="card-excerpt">{{ \Illuminate\Support\Str::limit($item->summary, 140) }}</p>@endif
        <div class="card-foot">
            <time datetime="{{ $item->published_on?->toDateString() }}">{{ $item->published_on?->format('d M Y') }}</time>
            @if ($item->is_featured)<span class="dot">·</span><span>Featured</span>@endif
        </div>
    </div>
</article>
