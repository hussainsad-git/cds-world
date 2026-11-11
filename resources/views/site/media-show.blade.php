@extends('layouts.app')
@section('body_class', 'tpl-article')
@section('content')
<article class="article">
    <header class="article-head">
        <div class="container-cds container-prose">
            <div class="card-meta">
                <a class="chip" href="{{ url('/media-coverage') }}">Media coverage</a>
                @if ($item->outlet)<a class="chip" href="{{ url('/media-coverage?outlet='.$item->outlet->id) }}">{{ $item->outlet->name }}</a>@endif
            </div>
            <h1 class="article-title">{{ $item->headline }}</h1>
            <div class="byline">
                <div class="byline-meta">
                    <time datetime="{{ $item->published_on?->toDateString() }}">{{ $item->published_on?->format('d M Y') }}</time>
                    @if ($item->outlet)<span>{{ $item->outlet->name }}</span>@endif
                </div>
            </div>
            <x-share :url="url('/media/'.$item->slug)" :title="$item->headline" />
        </div>
    </header>

    @if ($image)
        <div class="container-cds container-wide"><figure class="article-hero"><a class="glightbox" data-gallery="body" data-type="image" href="{{ $item->getFirstMediaUrl('thumbnail') ?: $image }}" aria-label="Enlarge image"><img src="{{ $image }}" alt="{{ $item->thumb_alt ?: $item->headline }}" fetchpriority="high"></a></figure></div>
    @endif

    <div class="container-cds container-prose">
        @if (filled($item->body))
            <div class="prose-cds">{!! clean((string) $item->body, 'default') !!}</div>
        @elseif ($item->summary)
            <p class="article-lead">{{ $item->summary }}</p>
        @endif

        @if ($item->hasExternalSource())
            <p class="mt-4"><a class="btn btn-cds" href="{{ $item->source_url }}" target="_blank" rel="noopener nofollow">Read the original{{ $item->outlet ? ' at '.$item->outlet->name : '' }} <span aria-hidden="true">&#8599;</span></a></p>
        @endif

        @if ($item->event)
            <p class="mt-4">Related event: <a href="{{ url('/events/'.$item->event->slug) }}">{{ $item->event->title }}</a></p>
        @endif

        @pushOnce('head', 'glightbox-css')<link rel="stylesheet" href="{{ asset('vendor/glightbox.min.css') }}">@endPushOnce
        @pushOnce('scripts', 'glightbox-js')<script src="{{ asset('vendor/glightbox.min.js') }}"></script>@endPushOnce
    </div>
</article>

@if ($related->isNotEmpty())
<section class="section section-alt"><div class="container-cds">
    <x-section-head title="More media coverage" link="/media-coverage" link-label="All coverage" />
    <div class="grid-cards">@foreach ($related as $r)<x-coverage-card :item="$r" />@endforeach</div>
</div></section>
@endif
@endsection
