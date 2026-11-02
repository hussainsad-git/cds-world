@props(['embed', 'thumb' => null, 'title' => 'Video'])
{{-- Click-to-load: no player or third-party request until the visitor presses play. --}}
<div class="video-facade" data-embed="{{ $embed }}" data-title="{{ $title }}">
    <button type="button" class="video-play" aria-label="Play video: {{ $title }}">
        @if ($thumb)<img src="{{ $thumb }}" alt="" loading="lazy" decoding="async">@endif
        <span class="play-icon" aria-hidden="true"></span>
    </button>
</div>
