<section class="section newsletter-band" id="newsletter">
    <div class="container-cds container-prose text-center">
        <h2 class="section-title">{{ $section->heading ?: 'Stay informed' }}</h2>
        @if (!empty($d['description']))<p class="section-lead mx-auto">{{ $d['description'] }}</p>@endif
        <div class="mt-3 text-start"><x-newsletter-form :source="$page->slug ?? 'page'" /></div>
    </div>
</section>
