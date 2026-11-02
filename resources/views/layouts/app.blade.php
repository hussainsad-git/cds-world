@php
    $siteName = \App\Support\Site::name();
    $favicon = \App\Support\Site::file(\App\Support\Site::get('general', 'favicon'));
    $themeVersion = substr(md5(json_encode(\App\Models\WebsiteSetting::group('theme'))), 0, 8);
    $headingFont = \App\Support\Site::get('theme', 'font_heading', 'Source Serif 4');
    $bodyFont = \App\Support\Site::get('theme', 'font_body', 'Inter');
    $selfHosted = ['Source Serif 4', 'Inter'];
    $extraFonts = collect([$headingFont, $bodyFont])->unique()->reject(fn ($f) => in_array($f, $selfHosted))->values();
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-seo :seo="$seo ?? []" />
    <link rel="icon" href="{{ $favicon ?: asset('favicon.ico') }}">
    <link rel="alternate" type="application/rss+xml" title="{{ $siteName }}" href="{{ url('/sitemap.xml') }}">
    <link rel="preload" href="{{ asset('fonts/site/inter-latin-wght-normal.woff2') }}" as="font" type="font/woff2" crossorigin>
    @if ($extraFonts->isNotEmpty())
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?{{ $extraFonts->map(fn ($f) => 'family='.str_replace(' ', '+', $f).':wght@400;600;700')->implode('&') }}&display=swap">
    @endif
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap.min.css') }}">
    @stack('head')
    <link rel="stylesheet" href="{{ asset('css/site.css') }}?v={{ filemtime(public_path('css/site.css')) }}">
    <link rel="stylesheet" href="{{ route('theme.css') }}?v={{ $themeVersion }}">
</head>
<body class="@yield('body_class')">
    <a class="skip-link" href="#main">Skip to content</a>
    @include('partials.announcement')
    @include('partials.header')

    <main id="main">
        @hasSection('breadcrumb')
            @yield('breadcrumb')
        @elseif (!empty($crumbs))
            <x-breadcrumb :crumbs="$crumbs" />
        @endif
        @yield('content')
    </main>

    @include('partials.footer')
    @include('partials.popups')

    <script src="{{ asset('vendor/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap.bundle.min.js') }}"></script>
    @stack('scripts')
    <script src="{{ asset('js/site.js') }}?v={{ filemtime(public_path('js/site.js')) }}"></script>
</body>
</html>
