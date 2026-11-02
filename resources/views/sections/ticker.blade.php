@if ($articles->isNotEmpty())
<section class="ticker" aria-label="{{ $section->heading ?: 'Top news' }}">
    <div class="container-cds ticker-inner">
        <span class="ticker-label">{{ $section->heading ?: 'Top news' }}</span>
        <div class="ticker-track">
            <ul class="ticker-list">
                @foreach ($articles as $a)<li><a href="{{ url('/articles/'.$a->slug) }}">{{ $a->title }}</a></li>@endforeach
            </ul>
        </div>
    </div>
</section>
@endif
