@props(['title' => ''])
@php
    $t = \Illuminate\Support\Str::lower($title);
    $key = match (true) {
        str_contains($t, 'geopolit') => 'globe',
        str_contains($t, 'econom') || str_contains($t, 'energy') || str_contains($t, 'trade') => 'chart',
        str_contains($t, 'climate') || str_contains($t, 'environment') => 'leaf',
        str_contains($t, 'secur') || str_contains($t, 'defence') || str_contains($t, 'defense') || str_contains($t, 'peace') => 'shield',
        str_contains($t, 'cpec') || str_contains($t, 'connect') || str_contains($t, 'corridor') => 'network',
        str_contains($t, 'international') || str_contains($t, 'relation') || str_contains($t, 'foreign') || str_contains($t, 'diplo') => 'exchange',
        str_contains($t, 'govern') || str_contains($t, 'development') || str_contains($t, 'institut') || str_contains($t, 'policy') => 'building',
        default => 'document',
    };
    $icons = [
        'globe' => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18"/><path d="M12 3c2.5 2.6 3.8 5.6 3.8 9S14.5 18.4 12 21c-2.5-2.6-3.8-5.6-3.8-9S9.5 5.6 12 3z"/>',
        'chart' => '<path d="M3 20h18"/><path d="M6 17v-5"/><path d="M12 17V6"/><path d="M18 17v-8"/>',
        'leaf' => '<path d="M20 4c0 9-5.5 15-14 15H4c0-9 5.5-15 14-15h2z"/><path d="M8 20c1.8-5 4.6-8.2 8.5-10.4"/>',
        'shield' => '<path d="M12 3l8 3v6c0 4.8-3.3 8.1-8 9-4.7-.9-8-4.2-8-9V6l8-3z"/><path d="M9 12l2 2 4-4"/>',
        'network' => '<circle cx="5" cy="12" r="2.4"/><circle cx="19" cy="6" r="2.4"/><circle cx="19" cy="18" r="2.4"/><path d="M7.2 11.1l9.6-4"/><path d="M7.2 12.9l9.6 4"/>',
        'exchange' => '<path d="M4 8h13"/><path d="M14 5l3 3-3 3"/><path d="M20 16H7"/><path d="M10 13l-3 3 3 3"/>',
        'building' => '<rect x="4" y="4" width="16" height="16" rx="2"/><path d="M8 8h2"/><path d="M14 8h2"/><path d="M8 12h2"/><path d="M14 12h2"/><path d="M9 20v-4h6v4"/>',
        'document' => '<path d="M14 3H7a1 1 0 0 0-1 1v16a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V7z"/><path d="M14 3v4h4"/><path d="M9 13h6"/><path d="M9 17h6"/>',
    ];
@endphp
<svg class="focus-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">{!! $icons[$key] !!}</svg>
