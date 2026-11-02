@extends('layouts.app')
@section('content')
<section class="section"><div class="container-cds container-prose text-center">
    <h1 class="section-title">{{ $heading }}</h1>
    <p class="section-lead mx-auto">{{ $body }}</p>
    <a class="btn btn-cds" href="{{ url('/') }}">Back to homepage</a>
</div></section>
@endsection
