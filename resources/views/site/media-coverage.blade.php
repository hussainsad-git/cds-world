@extends('layouts.app')
@section('body_class', 'tpl-media')
@section('content')
<x-listing-intro :page="$page" title="Media Coverage" lead="How CDS World and its experts are covered in the media." />
<section class="section">
    <div class="container-cds">
        <form class="filter-bar" method="GET" action="{{ url('/media-coverage') }}" role="search">
            <div class="row g-2 align-items-end">
                <div class="col-lg-4"><label class="form-label" for="m-q">Search</label><input id="m-q" class="form-control" type="search" name="q" value="{{ $filters['q'] }}" placeholder="Headline or outlet"></div>
                <div class="col-6 col-lg-3"><label class="form-label" for="m-o">Outlet</label><select id="m-o" class="form-select" name="outlet"><option value="">All</option>@foreach ($outlets as $o)<option value="{{ $o->id }}" @selected((int)$filters['outlet'] === $o->id)>{{ $o->name }}</option>@endforeach</select></div>
                <div class="col-6 col-lg-2"><label class="form-label" for="m-y">Year</label><select id="m-y" class="form-select" name="year"><option value="">Any</option>@foreach ($years as $y)<option value="{{ $y }}" @selected((int)$filters['year'] === (int)$y)>{{ $y }}</option>@endforeach</select></div>
                <div class="col-6 col-lg-2"><label class="form-label" for="m-c">Category</label><select id="m-c" class="form-select" name="category"><option value="">All</option>@foreach ($categories as $c)<option value="{{ $c->slug }}" @selected($filters['category'] === $c->slug)>{{ $c->name }}</option>@endforeach</select></div>
                <div class="col-6 col-lg-1 d-grid"><button class="btn btn-cds" type="submit">Go</button></div>
            </div>
        </form>
        @if ($items->isEmpty())
            <p class="empty-state mt-4">No media coverage found.</p>
        @else
            <div class="grid-cards mt-4">@foreach ($items as $m)<x-coverage-card :item="$m" />@endforeach</div>
            <div class="mt-5">{{ $items->links() }}</div>
        @endif
    </div>
</section>
@endsection
