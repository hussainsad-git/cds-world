@extends('layouts.app', ['seo' => ['title' => 'Page not found', 'noindex' => true]])
@section('content')
<section class="section"><div class="container-cds container-prose text-center">
    <p class="eyebrow">Error 404</p>
    <h1 class="section-title">This page could not be found</h1>
    <p class="section-lead mx-auto">The page may have moved or no longer exists. Try searching our research or return to the homepage.</p>
    <form class="d-flex gap-2 justify-content-center my-3" action="{{ url('/articles') }}" role="search"><label class="visually-hidden" for="e-q">Search</label><input id="e-q" class="form-control" style="max-width:360px" name="q" placeholder="Search research…"><button class="btn btn-cds">Search</button></form>
    <a class="link-arrow" href="{{ url('/') }}">Back to homepage →</a>
</div></section>
@endsection
