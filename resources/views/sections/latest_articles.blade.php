@if ($articles->isNotEmpty())
<section class="section">
    <div class="container-cds">
        <x-section-head :title="$section->heading" :text="$d['description'] ?? null" link="/articles" link-label="All articles" />
        <div class="grid-cards">
            @foreach ($articles as $a)<x-article-card :article="$a" />@endforeach
        </div>
    </div>
</section>
@endif
