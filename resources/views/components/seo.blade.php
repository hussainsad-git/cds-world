@props(['seo'])
@php
    $seo = is_array($seo) ? $seo : [];
    $title = $seo['title'] ?? \App\Support\Site::name();
    $desc = $seo['description'] ?? null;
    $og = $seo['og'] ?? [];
@endphp
<title>{{ $title }}</title>
@if ($desc)<meta name="description" content="{{ $desc }}">@endif
<meta name="robots" content="{{ $seo['robots'] ?? (($seo['noindex'] ?? false) ? 'noindex, follow' : 'index, follow, max-image-preview:large') }}">
@if (!empty($seo['canonical']))<link rel="canonical" href="{{ $seo['canonical'] }}">@endif
<meta property="og:locale" content="en_US">
<meta property="og:type" content="{{ $og['type'] ?? 'website' }}">
<meta property="og:site_name" content="{{ $og['site_name'] ?? \App\Support\Site::name() }}">
<meta property="og:title" content="{{ $og['title'] ?? $title }}">
@if (!empty($og['description']))<meta property="og:description" content="{{ $og['description'] }}">@endif
@if (!empty($seo['canonical']))<meta property="og:url" content="{{ $seo['canonical'] }}">@endif
@if (!empty($og['image']))<meta property="og:image" content="{{ $og['image'] }}">@endif
@if (!empty($og['published']))<meta property="article:published_time" content="{{ $og['published'] }}">@endif
@if (!empty($og['modified']))<meta property="article:modified_time" content="{{ $og['modified'] }}">@endif
<meta name="twitter:card" content="{{ !empty($og['image']) ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $og['title'] ?? $title }}">
@if (!empty($og['description']))<meta name="twitter:description" content="{{ $og['description'] }}">@endif
@if (!empty($og['image']))<meta name="twitter:image" content="{{ $og['image'] }}">@endif
@foreach (($seo['jsonld'] ?? []) as $block)
<script type="application/ld+json">{!! json_encode($block, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
@endforeach
