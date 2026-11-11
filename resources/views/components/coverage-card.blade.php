@props(['item'])
<article class="card-article card-coverage">
    <a class="card-media" href="{{ $item->url() }}" @if ($item->linksOut()) target="_blank" rel="noopener nofollow" @endif tabindex="-1" aria-hidden="true">
        <x-media-img :model="$item" collection="thumbnail" conversion="card" :alt="$item->thumb_alt ?: ''" />
        @if ($item->outlet)<b class="card-outlet">{{ $item->outlet->name }}</b>@endif
        @if ($item->hasVideo())<span class="card-play" aria-hidden="true"></span>@endif
    </a>
    <div class="card-body-cds">
        <h3 class="card-title"><a href="{{ $item->url() }}" @if ($item->linksOut()) target="_blank" rel="noopener nofollow" @endif>{{ $item->headline }}@if ($item->linksOut()) <span class="ext" aria-hidden="true">&#8599;</span><span class="visually-hidden">(opens the original story in a new tab)</span>@endif</a></h3>
        @if ($item->summary)<p class="card-excerpt">{{ \Illuminate\Support\Str::limit($item->summary, 140) }}</p>@endif
        <div class="card-foot">
            <time datetime="{{ $item->published_on?->toDateString() }}">{{ $item->published_on?->format('d M Y') }}</time>
            @if ($item->is_featured)<span class="dot">·</span><span>Featured</span>@endif
        </div>
    </div>
</article>
