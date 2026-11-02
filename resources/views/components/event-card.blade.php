@props(['event'])
@php $state = $event->lifecycle(); @endphp
<article class="card-event">
    <a href="{{ url('/events/'.$event->slug) }}" class="card-media" tabindex="-1" aria-hidden="true">
        <x-media-img :model="$event" collection="banner" conversion="card" :alt="$event->banner_alt ?: ''" />
        <span class="date-badge"><strong>{{ $event->starts_at->format('d') }}</strong>{{ $event->starts_at->format('M Y') }}</span>
    </a>
    <div class="card-body-cds">
        <div class="card-meta"><span class="chip chip-{{ $state }}">{{ ucfirst($state) }}</span></div>
        <h3 class="card-title"><a href="{{ url('/events/'.$event->slug) }}">{{ $event->title }}</a></h3>
        @if ($event->summary)<p class="card-excerpt">{{ \Illuminate\Support\Str::limit($event->summary, 140) }}</p>@endif
        <div class="card-foot">
            <span>{{ $event->starts_at->format('D, d M Y · H:i') }}</span>
            @if ($event->venue)<span class="dot">·</span><span>{{ $event->venue }}</span>@endif
        </div>
    </div>
</article>
