<?php

namespace App\Models\Concerns;

use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait HasCmsMedia
{
    use InteractsWithMedia;

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->format('webp')->fit(Fit::Crop, 400, 260)->nonQueued();
        $this->addMediaConversion('card')->format('webp')->fit(Fit::Crop, 800, 520)->performOnCollections('featured', 'cover', 'thumbnail', 'photo', 'file', 'gallery');
        $this->addMediaConversion('large')->format('webp')->fit(Fit::Max, 1600, 1600)->performOnCollections('featured', 'cover', 'thumbnail', 'file', 'gallery', 'hero');
    }
}
