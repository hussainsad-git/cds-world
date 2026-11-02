@props(['title' => null, 'text' => null, 'link' => null, 'linkLabel' => 'View all', 'tag' => 'h2'])
@if ($title)
<div class="section-head">
    <div>
        <{{ $tag }} class="section-title">{{ $title }}</{{ $tag }}>
        @if ($text)<p class="section-lead">{{ $text }}</p>@endif
    </div>
    @if ($link)<a class="link-arrow" href="{{ url($link) }}">{{ $linkLabel }} <span aria-hidden="true">→</span></a>@endif
</div>
@endif
