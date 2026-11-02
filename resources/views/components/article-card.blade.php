@props(['article', 'featured' => false])
<article class="card-article {{ $featured ? 'is-featured' : '' }}">
    <a href="{{ url('/articles/'.$article->slug) }}" class="card-media" tabindex="-1" aria-hidden="true">
        <x-media-img :model="$article" collection="featured" conversion="card" :alt="$article->featured_alt ?: ''" />
    </a>
    <div class="card-body-cds">
        <div class="card-meta">
            @if ($article->categories->first())
                <a class="chip" href="{{ url('/articles?category='.$article->categories->first()->slug) }}">{{ $article->categories->first()->name }}</a>
            @endif
            @if ($article->is_featured)<span class="chip chip-accent">Featured</span>@endif
        </div>
        <h3 class="card-title"><a href="{{ url('/articles/'.$article->slug) }}">{{ $article->title }}</a></h3>
        @if ($article->excerpt)<p class="card-excerpt">{{ \Illuminate\Support\Str::limit($article->excerpt, 150) }}</p>@endif
        <div class="card-foot">
            @if ($article->author)<span>{{ $article->author->name }}</span><span class="dot">·</span>@endif
            <time datetime="{{ $article->published_at?->toDateString() }}">{{ $article->published_at?->format('d M Y') }}</time>
            <span class="dot">·</span><span>{{ $article->reading_time }} min read</span>
        </div>
    </div>
</article>
