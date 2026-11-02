@php
    $path = request()->path();
    $popups = \Illuminate\Support\Facades\Cache::remember('popups.running', 60, fn () => \App\Models\Popup::running()->with('media')->get());
    $popups = $popups->filter(fn ($p) => $p->matchesPath($path))->take(1);
@endphp
@foreach ($popups as $popup)
    <div class="cds-popup" id="popup-{{ $popup->id }}" hidden role="dialog" aria-modal="true" aria-labelledby="popup-title-{{ $popup->id }}"
         data-id="{{ $popup->id }}" data-frequency="{{ $popup->frequency }}" data-days="{{ (int) $popup->frequency_days }}" data-delay="{{ (int) $popup->delay_seconds }}"
         data-desktop="{{ $popup->show_on_desktop ? 1 : 0 }}" data-mobile="{{ $popup->show_on_mobile ? 1 : 0 }}" data-version="{{ $popup->updated_at?->timestamp }}">
        <div class="cds-popup-backdrop" data-popup-close></div>
        <div class="cds-popup-card">
            <button type="button" class="cds-popup-close" data-popup-close aria-label="Close">×</button>
            <h2 class="cds-popup-title" id="popup-title-{{ $popup->id }}">{{ $popup->title }}</h2>
            @if ($popup->type === 'image' && $popup->getFirstMediaUrl('image'))
                <img src="{{ $popup->getFirstMediaUrl('image') }}" alt="" loading="lazy">
            @else
                <div class="cds-popup-body">{!! clean((string) $popup->content, 'popup') !!}</div>
            @endif
            @if ($popup->cta_label && $popup->cta_url)
                <p class="mt-3"><a class="btn btn-cds" href="{{ $popup->cta_url }}">{{ $popup->cta_label }}</a></p>
            @endif
        </div>
    </div>
@endforeach
