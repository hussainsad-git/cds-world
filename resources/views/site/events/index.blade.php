@extends('layouts.app')
@section('body_class', 'tpl-events')
@section('content')
<x-listing-intro :page="$page" title="Events" lead="Conferences, seminars and dialogues hosted by CDS World." />
<section class="section">
    <div class="container-cds">
        <div class="tabs-cds" role="tablist">
            <a href="{{ url('/events') }}" class="{{ $tab === 'upcoming' ? 'active' : '' }}" @if($tab==='upcoming') aria-current="page" @endif>Upcoming</a>
            <a href="{{ url('/events?when=past') }}" class="{{ $tab === 'past' ? 'active' : '' }}" @if($tab==='past') aria-current="page" @endif>Past events</a>
        </div>
        <form class="filter-bar mt-3" method="GET" action="{{ url('/events') }}" role="search">
            <input type="hidden" name="when" value="{{ $tab }}">
            <div class="row g-2 align-items-end">
                <div class="col-md-6"><label class="form-label" for="e-q">Search events</label><input id="e-q" class="form-control" type="search" name="q" value="{{ $q }}" placeholder="Title or venue"></div>
                <div class="col-md-4"><label class="form-label" for="e-cat">Category</label><select id="e-cat" class="form-select" name="category"><option value="">All</option>@foreach ($categories as $c)<option value="{{ $c->slug }}" @selected($category === $c->slug)>{{ $c->name }}</option>@endforeach</select></div>
                <div class="col-md-2 d-grid"><button class="btn btn-cds" type="submit">Filter</button></div>
            </div>
        </form>

        @if ($featured->isNotEmpty())
            <h2 class="section-title mt-5">Featured</h2>
            <div class="grid-cards">@foreach ($featured as $e)<x-event-card :event="$e" />@endforeach</div>
        @endif

        @if ($events->isEmpty())
            <p class="empty-state mt-4">{{ $tab === 'upcoming' ? 'No upcoming events are scheduled right now.' : 'No past events found.' }} @if($tab==='upcoming')<a href="{{ url('/events?when=past') }}">See past events</a>.@endif</p>
        @else
            <div class="grid-cards mt-4">@foreach ($events as $e)<x-event-card :event="$e" />@endforeach</div>
            <div class="mt-5">{{ $events->links() }}</div>
        @endif
    </div>
</section>
@endsection
