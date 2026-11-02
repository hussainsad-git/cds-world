@if ($items->isNotEmpty())
<section class="section section-alt">
    <div class="container-cds">
        <x-section-head :title="$section->heading" :text="$d['description'] ?? null" link="/media-coverage" link-label="All coverage" />
        <div class="coverage-list">
            @foreach ($items as $m)<x-coverage-item :item="$m" />@endforeach
        </div>
    </div>
</section>
@endif
