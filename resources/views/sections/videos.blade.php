@if ($videos->isNotEmpty())
<section class="section">
    <div class="container-cds">
        <x-section-head :title="$section->heading" :text="$d['description'] ?? null" link="/videos" link-label="All videos" />
        <div class="grid-cards">
            @foreach ($videos as $v)<x-video-card :video="$v" />@endforeach
        </div>
    </div>
</section>
@endif
