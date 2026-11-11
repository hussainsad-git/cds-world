@extends('layouts.app')
@section('body_class', 'tpl-album')
@push('head')<link rel="stylesheet" href="{{ asset('vendor/glightbox.min.css') }}">@endpush
@push('scripts')<script src="{{ asset('vendor/glightbox.min.js') }}"></script>@endpush
@section('content')
<header class="page-hero"><div class="container-cds"><h1>{{ $album->title }}</h1>@if ($album->description)<p class="lead">{{ $album->description }}</p>@endif
    @if ($album->event)<p><a class="link-arrow" href="{{ url('/events/'.$album->event->slug) }}">From the event: {{ $album->event->title }} →</a></p>@endif</div></header>
<section class="section">
    <div class="container-cds">
        <div class="masonry">
            @if ($items->isEmpty() && $cover)
                {{-- An album whose photos have not been added yet still shows its cover, so the page is never blank. --}}
                <div class="masonry-item"><a class="glightbox" data-gallery="album" data-type="image" href="{{ $album->getFirstMediaUrl('cover') ?: $cover }}"><img src="{{ $cover }}" alt="{{ $album->cover_alt ?: $album->title }}" loading="lazy" decoding="async"></a></div>
            @endif
            @foreach ($items as $it)
                @php $full = $it->getFirstMediaUrl('file', 'large') ?: $it->getFirstMediaUrl('file'); @endphp
                @if ($it->type === 'video')
                    @if ($it->video_source && $it->video_url)
                        @php $vid = \App\Models\Video::parseExternalId($it->video_source, $it->video_url);
                             $embed = $it->video_source === 'youtube' && $vid ? 'https://www.youtube-nocookie.com/embed/'.$vid.'?autoplay=1&rel=0' : 'https://www.facebook.com/plugins/video.php?show_text=false&autoplay=1&href='.urlencode($it->video_url); @endphp
                        <div class="masonry-item"><x-video-facade :embed="$embed" :thumb="$vid ? 'https://i.ytimg.com/vi/'.$vid.'/hqdefault.jpg' : null" :title="$it->title ?: $album->title" />@if ($it->caption)<p class="caption">{{ $it->caption }}</p>@endif</div>
                    @elseif ($full)
                        <div class="masonry-item"><video controls preload="none"><source src="{{ $full }}"></video>@if ($it->caption)<p class="caption">{{ $it->caption }}</p>@endif</div>
                    @endif
                @elseif ($full)
                    <div class="masonry-item"><a class="glightbox" data-gallery="album" data-description="{{ $it->caption }}" href="{{ $full }}"><img src="{{ $it->getFirstMediaUrl('file', 'card') ?: $full }}" alt="{{ $it->alt_text ?: ($it->caption ?: $album->title) }}" loading="lazy" decoding="async"></a>@if ($it->caption)<p class="caption">{{ $it->caption }}</p>@endif</div>
                @endif
            @endforeach
        </div>
        <div class="mt-5">{{ $items->links() }}</div>
        <p class="mt-4"><a class="link-arrow" href="{{ url('/gallery') }}">← All albums</a></p>
    </div>
</section>
@endsection
