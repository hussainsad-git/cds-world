@extends('layouts.app')
@section('body_class', 'tpl-videos')
@section('content')
<x-listing-intro :page="$page" title="Videos & Interviews" lead="Interviews, panel discussions and recorded sessions." />
<section class="section">
    <div class="container-cds">
        <form class="filter-bar" method="GET" action="{{ url('/videos') }}" role="search">
            <div class="row g-2 align-items-end">
                <div class="col-md-6"><label class="form-label" for="v-q">Search videos</label><input id="v-q" class="form-control" type="search" name="q" value="{{ $q }}" placeholder="Title or presenter"></div>
                <div class="col-md-4"><label class="form-label" for="v-c">Category</label><select id="v-c" class="form-select" name="category"><option value="">All</option>@foreach ($categories as $c)<option value="{{ $c->slug }}" @selected($category === $c->slug)>{{ $c->name }}</option>@endforeach</select></div>
                <div class="col-md-2 d-grid"><button class="btn btn-cds" type="submit">Filter</button></div>
            </div>
        </form>
        @if ($videos->isEmpty())<p class="empty-state mt-4">No videos found.</p>
        @else
            <div class="grid-cards mt-4">@foreach ($videos as $v)<x-video-card :video="$v" />@endforeach</div>
            <div class="mt-5">{{ $videos->links() }}</div>
        @endif
    </div>
</section>
@endsection
