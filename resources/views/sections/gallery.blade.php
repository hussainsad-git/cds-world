@if ($items->isNotEmpty())
<section class="section">
    <div class="container-cds">
        <x-section-head :title="$section->heading" :text="$d['description'] ?? null" link="/gallery" link-label="Full gallery" />
        <div class="gallery-strip">
            @foreach ($items as $it)
                <a href="{{ $it->getFirstMediaUrl('file', 'large') ?: $it->getFirstMediaUrl('file') }}" class="glightbox" data-gallery="home" data-description="{{ $it->caption }}">
                    <x-media-img :model="$it" collection="file" conversion="thumb" :alt="$it->alt_text ?: ($it->album->title ?? '')" ratio="1x1" />
                </a>
            @endforeach
        </div>
    </div>
</section>
@push('head')<link rel="stylesheet" href="{{ asset('vendor/glightbox.min.css') }}">@endpush
@push('scripts')<script src="{{ asset('vendor/glightbox.min.js') }}"></script>@endpush
@endif
