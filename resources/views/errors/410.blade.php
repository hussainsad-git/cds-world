@extends('layouts.app', ['seo' => ['title' => 'Content removed', 'noindex' => true]])
@section('content')
<section class="section"><div class="container-cds container-prose text-center">
    <p class="eyebrow">Error 410</p>
    <h1 class="section-title">This content has been removed</h1>
    <p class="section-lead mx-auto">This page was permanently removed. You may find related material in our research archive.</p>
    <a class="btn btn-cds" href="{{ url('/articles') }}">Browse articles</a>
</div></section>
@endsection
