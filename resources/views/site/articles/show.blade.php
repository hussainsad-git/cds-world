@extends('layouts.app')
@section('body_class', 'tpl-article')
@section('content')
<article class="article">
    <header class="article-head">
        <div class="container-cds container-prose">
            <div class="card-meta">
                @foreach ($article->categories as $c)<a class="chip" href="{{ url('/articles?category='.$c->slug) }}">{{ $c->name }}</a>@endforeach
            </div>
            <h1 class="article-title">{{ $article->title }}</h1>
            @if ($article->subtitle)<p class="article-subtitle">{{ $article->subtitle }}</p>@endif
            <div class="byline">
                @if ($article->author)
                    <div class="byline-author"><strong>{{ $article->author->name }}</strong>@if ($article->author->designation)<span>{{ $article->author->designation }}</span>@endif</div>
                @endif
                <div class="byline-meta">
                    <time datetime="{{ $article->published_at?->toDateString() }}">Published {{ $article->published_at?->format('d M Y') }}</time>
                    @if ($article->updated_at && $article->published_at && $article->updated_at->diffInDays($article->published_at) >= 1)<span>Updated <time datetime="{{ $article->updated_at->toDateString() }}">{{ $article->updated_at->format('d M Y') }}</time></span>@endif
                    <span>{{ $article->reading_time }} min read</span>
                </div>
            </div>
            <x-share :url="url('/articles/'.$article->slug)" :title="$article->title" />
        </div>
    </header>

    @if ($image)
        <div class="container-cds container-wide"><figure class="article-hero"><a class="glightbox" data-gallery="body" data-type="image" href="{{ $article->getFirstMediaUrl('featured') ?: $image }}" aria-label="Enlarge image"><img src="{{ $image }}" alt="{{ $article->featured_alt ?: $article->title }}" fetchpriority="high"></a></figure></div>
    @endif

    <div class="container-cds container-prose">
        @if ($article->excerpt)<p class="article-lead">{{ $article->excerpt }}</p>@endif

        @if ($article->video_source && $article->video_url)
            @php
                $vid = \App\Models\Video::parseExternalId($article->video_source, $article->video_url);
                $embed = $article->video_source->value === 'youtube' && $vid ? 'https://www.youtube-nocookie.com/embed/'.$vid.'?autoplay=1&rel=0' : 'https://www.facebook.com/plugins/video.php?show_text=false&autoplay=1&href='.urlencode($article->video_url);
            @endphp
            <x-video-facade :embed="$embed" :thumb="$vid ? 'https://i.ytimg.com/vi/'.$vid.'/hqdefault.jpg' : null" :title="$article->title" />
        @endif
        @if ($article->getFirstMediaUrl('video_file'))
            <video class="article-video" controls preload="none" @if($image) poster="{{ $image }}" @endif><source src="{{ $article->getFirstMediaUrl('video_file') }}" type="video/mp4"></video>
        @endif

        <div class="prose-cds">{!! $article->body !!}</div>
        {{-- Body and hero images open full-size, so the lightbox loads on every article. --}}
        @pushOnce('head', 'glightbox-css')<link rel="stylesheet" href="{{ asset('vendor/glightbox.min.css') }}">@endPushOnce
        @pushOnce('scripts', 'glightbox-js')<script src="{{ asset('vendor/glightbox.min.js') }}"></script>@endPushOnce

        @php $gallery = $article->getMedia('gallery'); @endphp
        @if ($gallery->isNotEmpty())
            <h2 class="h4 mt-5">Gallery</h2>
            <div class="gallery-strip">
                @foreach ($gallery as $m)
                    <a class="glightbox" data-gallery="article" href="{{ $m->getUrl('large') ?: $m->getUrl() }}"><x-media-img :url="$m->getUrl('thumb') ?: $m->getUrl()" :alt="$m->name" ratio="1x1" /></a>
                @endforeach
            </div>
        @endif

        @php $files = $article->getMedia('attachments'); @endphp
        @if ($files->isNotEmpty())
            <aside class="downloads" aria-labelledby="dl-h"><h2 id="dl-h" class="h5">Download</h2>
                <ul class="download-list">@foreach ($files as $f)<li><a href="{{ $f->getUrl() }}" download>{{ $f->file_name }} <span>{{ strtoupper(pathinfo($f->file_name, PATHINFO_EXTENSION)) }} · {{ number_format($f->size / 1048576, 1) }} MB</span></a></li>@endforeach</ul></aside>
        @endif

        @if (!empty($article->references))
            <aside class="references" aria-labelledby="ref-h"><h2 id="ref-h" class="h5">References &amp; sources</h2>
                <ol>@foreach ($article->references as $r)<li>@if (!empty($r['url']))<a href="{{ $r['url'] }}" rel="noopener nofollow" target="_blank">{{ $r['title'] }}</a>@else{{ $r['title'] }}@endif</li>@endforeach</ol></aside>
        @endif

        @if ($article->tags->isNotEmpty())
            <div class="tag-row">@foreach ($article->tags as $t)<a class="chip" href="{{ url('/articles?q='.urlencode($t->name)) }}">#{{ $t->name }}</a>@endforeach</div>
        @endif

        @if ($article->author)
            <aside class="author-box">
                <div><strong>{{ $article->author->name }}</strong>@if ($article->author->designation)<span class="role">{{ $article->author->designation }}</span>@endif
                    @if ($article->author->bio)<p>{{ trim(html_entity_decode(strip_tags($article->author->bio), ENT_QUOTES | ENT_HTML5, 'UTF-8')) }}</p>@endif
                    <a href="{{ url('/articles?author='.$article->author->id) }}">More from this author →</a></div>
            </aside>
        @endif

        <nav class="prev-next" aria-label="More articles">
            @if ($prev)<a class="prev" href="{{ url('/articles/'.$prev->slug) }}"><small>Previous</small><span>{{ $prev->title }}</span></a>@else<span></span>@endif
            @if ($next)<a class="next" href="{{ url('/articles/'.$next->slug) }}"><small>Next</small><span>{{ $next->title }}</span></a>@endif
        </nav>
    </div>
</article>

@if ($related->isNotEmpty())
<section class="section section-alt"><div class="container-cds">
    <x-section-head title="Related research" link="/articles" link-label="All articles" />
    <div class="grid-cards">@foreach ($related as $a)<x-article-card :article="$a" />@endforeach</div>
</div></section>
@endif
@endsection
