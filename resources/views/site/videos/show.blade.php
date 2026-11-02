@extends('layouts.app')
@section('body_class', 'tpl-video')
@section('content')
<article class="container-cds container-prose pt-4">
    <h1 class="article-title">{{ $video->title }}</h1>
    <div class="byline-meta mb-3">
        @if ($video->presenter)<span>{{ $video->presenter }}</span>@endif
        <time datetime="{{ $video->published_at?->toDateString() }}">{{ $video->published_at?->format('d M Y') }}</time>
        @foreach ($video->categories as $c)<span class="chip">{{ $c->name }}</span>@endforeach
    </div>

    @if ($video->source?->value === 'upload' && $video->getFirstMediaUrl('video_file'))
        <video class="article-video" controls preload="none" @if($thumb) poster="{{ $thumb }}" @endif><source src="{{ $video->getFirstMediaUrl('video_file') }}" type="video/mp4"></video>
    @elseif ($video->embedUrl())
        <x-video-facade :embed="$video->embedUrl()" :thumb="$thumb" :title="$video->title" />
    @endif

    @if ($video->description)<div class="prose-cds mt-4">{!! nl2br(e($video->description)) !!}</div>@endif
    <x-share :url="url('/videos/'.$video->slug)" :title="$video->title" />
</article>
@if ($more->isNotEmpty())
<section class="section section-alt mt-5"><div class="container-cds"><x-section-head title="More videos" link="/videos" />
    <div class="grid-cards">@foreach ($more as $v)<x-video-card :video="$v" />@endforeach</div></div></section>
@endif
@endsection
