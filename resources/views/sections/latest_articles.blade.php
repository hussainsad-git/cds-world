@if ($articles->isNotEmpty())
@php
    $lead = $articles->first();
    $column = $articles->slice(1, 5);
    $rest = $articles->count() >= 9 ? $articles->slice(6) : collect();
@endphp
<section class="section">
    <div class="container-cds">
        <x-section-head :title="$section->heading" :text="$d['description'] ?? null" link="/articles" link-label="All research" />

        <div class="latest-split">
            <article class="lead-article">
                <a class="lead-link" href="{{ url('/articles/'.$lead->slug) }}">
                    @if ($lead->categories->first())<span class="eyebrow">{{ $lead->categories->first()->name }}</span>@endif
                    <h3 class="lead-title">{{ $lead->title }}</h3>
                    @if ($lead->excerpt)<p class="lead-excerpt">{{ \Illuminate\Support\Str::limit($lead->excerpt, 220) }}</p>@endif
                    <p class="lead-meta">
                        @if ($lead->author){{ $lead->author->name }} &nbsp;&middot;&nbsp; @endif
                        <time datetime="{{ $lead->published_at?->toDateString() }}">{{ $lead->published_at?->format('d M Y') }}</time>
                        &nbsp;&middot;&nbsp; {{ $lead->reading_time }} min read
                    </p>
                </a>
            </article>

            @if ($column->isNotEmpty())
                <div class="latest-column">
                    @foreach ($column as $a)
                        <article class="mini-article">
                            <a href="{{ url('/articles/'.$a->slug) }}">
                                @if ($a->categories->first())<span class="eyebrow">{{ $a->categories->first()->name }}</span>@endif
                                <h3>{{ $a->title }}</h3>
                                <p class="mini-meta">
                                    @if ($a->author){{ $a->author->name }} &nbsp;&middot;&nbsp; @endif
                                    <time datetime="{{ $a->published_at?->toDateString() }}">{{ $a->published_at?->format('d M Y') }}</time>
                                </p>
                            </a>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>

        @if ($rest->isNotEmpty())
            <div class="grid-cards latest-rest">
                @foreach ($rest as $a)<x-article-card :article="$a" />@endforeach
            </div>
        @endif
    </div>
</section>
@endif
