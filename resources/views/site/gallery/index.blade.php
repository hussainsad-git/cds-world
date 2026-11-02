@extends('layouts.app')
@section('body_class', 'tpl-gallery')
@section('content')
<x-listing-intro :page="$page" title="Gallery" lead="Photographs and videos from CDS World events and activities." />
<section class="section">
    <div class="container-cds">
        @if ($categories->isNotEmpty())
            <div class="tabs-cds"><a href="{{ url('/gallery') }}" class="{{ $category === '' ? 'active' : '' }}">All</a>@foreach ($categories as $c)<a href="{{ url('/gallery?category='.$c->slug) }}" class="{{ $category === $c->slug ? 'active' : '' }}">{{ $c->name }}</a>@endforeach</div>
        @endif
        @if ($albums->isEmpty())<p class="empty-state mt-4">No albums yet.</p>
        @else
            <div class="grid-cards mt-4">
                @foreach ($albums as $a)
                    @php $first = $a->items->first(); @endphp
                    <article class="card-article card-album">
                        <a href="{{ url('/gallery/'.$a->slug) }}" class="card-media" tabindex="-1" aria-hidden="true">
                            <x-media-img :url="$a->getFirstMediaUrl('cover', 'card') ?: ($first?->getFirstMediaUrl('file', 'card') ?: '')" :alt="$a->cover_alt ?: $a->title" />
                        </a>
                        <div class="card-body-cds">
                            <h2 class="card-title h5"><a href="{{ url('/gallery/'.$a->slug) }}">{{ $a->title }}</a></h2>
                            <div class="card-foot"><span>{{ $a->items_count }} {{ \Illuminate\Support\Str::plural('item', $a->items_count) }}</span>@if ($a->published_at)<span class="dot">·</span><time datetime="{{ $a->published_at->toDateString() }}">{{ $a->published_at->format('d M Y') }}</time>@endif</div>
                        </div>
                    </article>
                @endforeach
            </div>
            <div class="mt-5">{{ $albums->links() }}</div>
        @endif
    </div>
</section>
@endsection
