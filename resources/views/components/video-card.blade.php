@props(['video'])
<article class="card-article card-video">
    <a href="{{ url('/videos/'.$video->slug) }}" class="card-media" tabindex="-1" aria-hidden="true">
        @php $yt = ($video->source?->value === 'youtube' && $video->external_id) ? 'https://i.ytimg.com/vi/'.$video->external_id.'/hqdefault.jpg' : null; @endphp
        <x-media-img :model="$video" collection="thumbnail" conversion="card" :alt="$video->thumb_alt ?: ''" :url="$video->getFirstMediaUrl('thumbnail', 'card') ?: $yt" />
        <span class="play-icon" aria-hidden="true"></span>
    </a>
    <div class="card-body-cds">
        <div class="card-meta">@if ($video->categories->first())<span class="chip">{{ $video->categories->first()->name }}</span>@endif</div>
        <h3 class="card-title"><a href="{{ url('/videos/'.$video->slug) }}">{{ $video->title }}</a></h3>
        <div class="card-foot">
            @if ($video->presenter)<span>{{ $video->presenter }}</span><span class="dot">·</span>@endif
            <time datetime="{{ $video->published_at?->toDateString() }}">{{ $video->published_at?->format('d M Y') }}</time>
        </div>
    </div>
</article>
