<?php

namespace App\Models;

use App\Enums\ContentStatus;
use App\Models\Concerns\HasCmsMedia;
use App\Models\Concerns\HasPublishing;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\HasTaxonomy;
use App\Models\Concerns\LogsContentActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class MediaCoverage extends Model implements HasMedia
{
    use HasCmsMedia, HasPublishing, HasSeo, HasSlug, HasTaxonomy, LogsContentActivity, SoftDeletes;

    protected $table = 'media_coverage';

    protected $guarded = ['id'];

    protected array $loggedAttributes = ['headline', 'status', 'published_at', 'is_featured', 'source_url'];

    protected function casts(): array
    {
        return [
            'status' => ContentStatus::class,
            'published_at' => 'datetime',
            'published_on' => 'date',
            'is_featured' => 'boolean',
        ];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('headline')->saveSlugsTo('slug')->preventOverwrite()->doNotGenerateSlugsOnUpdate();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('thumbnail')->singleFile()->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(MediaOutlet::class, 'media_outlet_id');
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
