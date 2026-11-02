@php
    $logo = \App\Support\Site::file(\App\Support\Site::get('general', 'logo'));
    $menu = \App\Support\Site::menu('header');
    $social = collect(['facebook' => 'Facebook', 'instagram' => 'Instagram', 'x' => 'X', 'youtube' => 'YouTube', 'linkedin' => 'LinkedIn'])
        ->filter(fn ($l, $k) => \App\Support\Site::get('social', $k));
@endphp
<div class="topbar"><div class="container-cds topbar-inner">
    <span>{{ \App\Support\Site::get('general', 'tagline_short', 'Independent insights for sustainable development') }}</span>
    <span class="topbar-social">@foreach ($social as $k => $label)<a href="{{ \App\Support\Site::get('social', $k) }}" target="_blank" rel="noopener">{{ $label }}</a>@endforeach</span>
</div></div>
<header class="site-header header-{{ \App\Support\Site::get('theme', 'header_style', 'light') }}">
    <div class="container-cds header-inner">
        <a class="brand" href="{{ url('/') }}" aria-label="{{ \App\Support\Site::name() }} home">
            @if ($logo)
                <img src="{{ $logo }}" alt="{{ \App\Support\Site::name() }}" height="44">
            @else
                <span class="brand-mark" aria-hidden="true">CDS</span>
                <span class="brand-text"><strong>{{ \App\Support\Site::name() }}</strong><small>Centre for Development and Stability</small></span>
            @endif
        </a>

        <button class="nav-toggle" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileNav" aria-controls="mobileNav" aria-label="Open menu">
            <span></span><span></span><span></span>
        </button>

        <nav class="primary-nav" aria-label="Primary">
            <ul>
                @foreach ($menu as $item)
                    @php $active = \App\Support\Site::isActive($item['href']) || collect($item['children'])->contains(fn ($c) => \App\Support\Site::isActive($c['href'])); @endphp
                    <li class="{{ count($item['children']) ? 'has-children' : '' }} {{ $active ? 'active' : '' }}">
                        <a href="{{ $item['href'] }}" @if($item['new_tab']) target="_blank" rel="noopener" @endif @if($active) aria-current="page" @endif>{{ $item['label'] }}@if(count($item['children']))<span class="caret" aria-hidden="true"></span>@endif</a>
                        @if (count($item['children']))
                            <ul class="dropdown-cds">
                                @foreach ($item['children'] as $child)
                                    <li><a href="{{ $child['href'] }}" @if($child['new_tab']) target="_blank" rel="noopener" @endif>{{ $child['label'] }}</a></li>
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @endforeach
            </ul>
            <form class="header-search" action="{{ url('/articles') }}" method="GET" role="search">
                <label class="visually-hidden" for="hs-q">Search articles</label>
                <input id="hs-q" type="search" name="q" placeholder="Search research…" maxlength="100">
                <button type="submit" aria-label="Search"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg></button>
            </form>
        </nav>
    </div>
</header>

<div class="offcanvas offcanvas-end" tabindex="-1" id="mobileNav" aria-labelledby="mobileNavLabel">
    <div class="offcanvas-header">
        <span class="offcanvas-title" id="mobileNavLabel">{{ \App\Support\Site::name() }}</span>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form class="mobile-search" action="{{ url('/articles') }}" method="GET" role="search">
            <label class="visually-hidden" for="ms-q">Search articles</label>
            <input id="ms-q" class="form-control" type="search" name="q" placeholder="Search research…">
        </form>
        <ul class="mobile-menu">
            @foreach ($menu as $item)
                <li>
                    <a href="{{ $item['href'] }}">{{ $item['label'] }}</a>
                    @if (count($item['children']))
                        <ul>@foreach ($item['children'] as $child)<li><a href="{{ $child['href'] }}">{{ $child['label'] }}</a></li>@endforeach</ul>
                    @endif
                </li>
            @endforeach
        </ul>
        @if ($social->isNotEmpty())
            <div class="social-row mt-4">
                @foreach ($social as $k => $label)<a href="{{ \App\Support\Site::get('social', $k) }}" rel="noopener" target="_blank">{{ $label }}</a>@endforeach
            </div>
        @endif
    </div>
</div>
