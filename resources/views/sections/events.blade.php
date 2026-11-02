@if ($events->isNotEmpty())
<section class="section section-alt">
    <div class="container-cds">
        <x-section-head :title="$section->heading" :text="$d['description'] ?? null" link="/events" link-label="All events" />
        <div class="grid-cards">
            @foreach ($events as $e)<x-event-card :event="$e" />@endforeach
        </div>
    </div>
</section>
@endif
