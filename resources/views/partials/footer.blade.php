@php
    $logo = \App\Support\Site::file(\App\Support\Site::get('general', 'logo_light'));
    $cols = \App\Support\Site::footerMenus();
    $phones = (array) \App\Support\Site::get('general', 'phones', []);
    $social = collect(['facebook' => 'Facebook', 'instagram' => 'Instagram', 'x' => 'X', 'youtube' => 'YouTube', 'linkedin' => 'LinkedIn', 'whatsapp' => 'WhatsApp'])
        ->filter(fn ($l, $k) => \App\Support\Site::get('social', $k));
@endphp
<footer class="site-footer footer-{{ \App\Support\Site::get('theme', 'footer_style', 'dark') }}">
    <div class="container-cds">
        <div class="footer-grid">
            <div class="footer-about">
                @if ($logo)<img src="{{ $logo }}" alt="{{ \App\Support\Site::name() }}" height="44" loading="lazy">@else<div class="footer-brand">{{ \App\Support\Site::name() }}</div>@endif
                <p>{{ \App\Support\Site::get('general', 'organization_description') }}</p>
                @if ($social->isNotEmpty())
                    <div class="social-row">@foreach ($social as $k => $label)<a href="{{ \App\Support\Site::get('social', $k) }}" rel="noopener" target="_blank" aria-label="{{ $label }}">{{ $label }}</a>@endforeach</div>
                @endif
            </div>
            @foreach ($cols as $slug => $col)
                <nav class="footer-col" aria-label="{{ $col['title'] }}">
                    <h2 class="footer-heading">{{ $col['title'] }}</h2>
                    <ul>@foreach ($col['items'] as $i)<li><a href="{{ $i['href'] }}" @if($i['new_tab']) target="_blank" rel="noopener" @endif>{{ $i['label'] }}</a></li>@endforeach</ul>
                </nav>
            @endforeach
            <div class="footer-col">
                <h2 class="footer-heading">Contact</h2>
                <ul class="footer-contact">
                    @if (\App\Support\Site::get('general', 'contact_email'))<li><a href="mailto:{{ \App\Support\Site::get('general', 'contact_email') }}">{{ \App\Support\Site::get('general', 'contact_email') }}</a></li>@endif
                    @foreach ($phones as $p)<li><a href="tel:{{ preg_replace('/[^+\d]/', '', $p) }}">{{ $p }}</a></li>@endforeach
                    @if (\App\Support\Site::get('general', 'address'))<li>{{ \App\Support\Site::get('general', 'address') }}</li>@endif
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <span>{{ \App\Support\Site::copyright() }}</span>
            <span><a href="{{ url('/privacy-policy') }}">Privacy</a> · <a href="{{ url('/terms-conditions') }}">Terms</a> · <a href="{{ url('/sitemap.xml') }}">Sitemap</a></span>
        </div>
    </div>
</footer>
