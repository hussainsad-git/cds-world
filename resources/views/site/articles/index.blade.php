@extends('layouts.app')
@section('body_class', 'tpl-articles')
@section('content')
<x-listing-intro :page="$page" title="Articles & Research" lead="Research, analysis and commentary from CDS World." />

<section class="section">
    <div class="container-cds">
        <form class="filter-bar" method="GET" action="{{ url('/articles') }}" role="search">
            <div class="row g-2 align-items-end">
                <div class="col-lg-4"><label class="form-label" for="f-q">Search</label><input id="f-q" class="form-control" type="search" name="q" value="{{ $filters['q'] }}" placeholder="Title, keyword or author"></div>
                <div class="col-6 col-lg-2"><label class="form-label" for="f-cat">Category</label>
                    <select id="f-cat" class="form-select" name="category"><option value="">All</option>
                        @foreach ($categories as $c)
                            <option value="{{ $c->slug }}" @selected($filters['category'] === $c->slug)>{{ $c->name }}</option>
                            @foreach ($c->children as $ch)<option value="{{ $ch->slug }}" @selected($filters['category'] === $ch->slug)>— {{ $ch->name }}</option>@endforeach
                        @endforeach
                    </select></div>
                <div class="col-6 col-lg-2"><label class="form-label" for="f-author">Author</label>
                    <select id="f-author" class="form-select" name="author"><option value="">All</option>@foreach ($authors as $a)<option value="{{ $a->id }}" @selected((int)$filters['author'] === $a->id)>{{ $a->name }}</option>@endforeach</select></div>
                <div class="col-6 col-lg-2"><label class="form-label" for="f-year">Year</label>
                    <select id="f-year" class="form-select" name="year"><option value="">Any</option>@foreach ($years as $y)<option value="{{ $y }}" @selected((int)$filters['year'] === (int)$y)>{{ $y }}</option>@endforeach</select></div>
                <div class="col-6 col-lg-2 d-flex gap-2"><button class="btn btn-cds flex-fill" type="submit">Filter</button>@if ($filtered)<a class="btn btn-outline-cds" href="{{ url('/articles') }}">Reset</a>@endif</div>
            </div>
        </form>

        @if ($featured->isNotEmpty())
            <h2 class="section-title mt-5">Featured &amp; editor's choice</h2>
            <div class="grid-cards">@foreach ($featured as $a)<x-article-card :article="$a" :featured="true" />@endforeach</div>
            <h2 class="section-title mt-5">Latest articles</h2>
        @elseif ($filtered)
            <p class="results-note mt-4">{{ $articles->total() }} {{ \Illuminate\Support\Str::plural('result', $articles->total()) }}@if ($filters['q']) for “{{ $filters['q'] }}”@endif</p>
        @endif

        @if ($articles->isEmpty())
            <p class="empty-state">No articles found. Try a different search or <a href="{{ url('/articles') }}">view all articles</a>.</p>
        @else
            <div class="grid-cards mt-3">@foreach ($articles as $a)<x-article-card :article="$a" />@endforeach</div>
            <div class="mt-5">{{ $articles->links() }}</div>
        @endif
    </div>
</section>
@endsection
