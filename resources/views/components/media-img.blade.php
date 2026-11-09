@props(['model' => null, 'collection' => 'featured', 'conversion' => 'card', 'alt' => '', 'class' => '', 'ratio' => '3x2', 'url' => null, 'eager' => false, 'sizes' => '(max-width: 900px) 100vw, 400px'])
@php
    $src = $url ?: ($model ? $model->getFirstMediaUrl($collection, $conversion) : '');
    if (!$src && $model) { $src = $model->getFirstMediaUrl($collection); }

    // Width descriptors come straight from the registered conversions in HasCmsMedia:
    // thumb is a 400x260 crop and card an 800x520 crop, so both are exact.
    $set = [];
    if (!$url && $model) {
        foreach (['thumb' => 400, 'card' => 800] as $name => $width) {
            $candidate = $model->getFirstMediaUrl($collection, $name);
            if ($candidate) {
                $set[] = $candidate.' '.$width.'w';
            }
        }
    }
@endphp
<div class="media-frame ratio-{{ $ratio }} {{ $class }}">
    @if ($src)
        <img src="{{ $src }}"
             @if (count($set) > 1) srcset="{{ implode(', ', $set) }}" sizes="{{ $sizes }}" @endif
             alt="{{ $alt }}" @if(!$eager) loading="lazy" decoding="async" @endif>
    @else
        <div class="media-placeholder" aria-hidden="true"><span>{{ \App\Support\Site::name() }}</span></div>
    @endif
</div>
