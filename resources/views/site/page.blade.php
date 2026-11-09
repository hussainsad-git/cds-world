@extends('layouts.app')
@section('body_class', 'tpl-'.$page->template)
@section('content')
    @unless ($isHome || $page->visibleSections->first()?->type === 'hero')
        <header class="page-hero">
            <div class="container-cds">
                <h1>{{ $page->title }}</h1>
                @if ($page->excerpt)<p class="lead">{{ $page->excerpt }}</p>@endif
            </div>
        </header>
    @endunless

    @php
        $heroSection = $page->visibleSections->firstWhere('type', 'hero');
        $heroShowsFocus = $heroSection && ($heroSection->data['show_focus'] ?? true);
    @endphp

    @foreach ($page->visibleSections as $section)
        @continue($heroShowsFocus && $section->type === 'research_focus')
        @php($d = $section->data ?? [])
        @includeIf('sections.'.$section->type, array_merge(['section' => $section, 'd' => $d, 'page' => $page], \App\Support\SectionData::for($section)))
    @endforeach
@endsection
