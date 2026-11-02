@props(['page' => null, 'title', 'lead' => null])
<header class="page-hero">
    <div class="container-cds">
        <h1>{{ $page?->title ?: $title }}</h1>
        @if ($page?->excerpt)<p class="lead">{{ $page->excerpt }}</p>@elseif ($lead)<p class="lead">{{ $lead }}</p>@endif
    </div>
</header>
@if ($page)
    @foreach ($page->visibleSections->where('type', 'richtext') as $s)
        <section class="section pb-0"><div class="container-cds container-prose"><div class="prose-cds">{!! clean((string) ($s->data['html'] ?? ''), 'default') !!}</div></div></section>
    @endforeach
@endif
