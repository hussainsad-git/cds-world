@props(['model' => null, 'collection' => 'featured', 'conversion' => 'card', 'alt' => '', 'class' => '', 'ratio' => '3x2', 'url' => null, 'eager' => false])
@php
    $src = $url ?: ($model ? $model->getFirstMediaUrl($collection, $conversion) : '');
    if (!$src && $model) { $src = $model->getFirstMediaUrl($collection); }
@endphp
<div class="media-frame ratio-{{ $ratio }} {{ $class }}">
    @if ($src)
        <img src="{{ $src }}" alt="{{ $alt }}" @if(!$eager) loading="lazy" decoding="async" @endif>
    @else
        <div class="media-placeholder" aria-hidden="true"><span>{{ \App\Support\Site::name() }}</span></div>
    @endif
</div>
