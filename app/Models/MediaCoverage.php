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
use Illuminate\Support\Str;
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

    /**
     * True when source_url leads to an outside publication. Records imported from the old
     * site point back at CDS's own domains, which is not a source anyone can read.
     */
    public function hasExternalSource(): bool
    {
        $host = Str::lower((string) parse_url((string) $this->source_url, PHP_URL_HOST));
        $own = array_filter([
            Str::lower((string) parse_url((string) config('app.url'), PHP_URL_HOST)),
            'cdsworld.org', 'www.cdsworld.org',
            'centrefordevelopmentandstability.com', 'www.centrefordevelopmentandstability.com',
        ]);

        return $host !== '' && ! in_array($host, $own, true);
    }

    /** A card goes straight to the outlet only when this site has nothing more to show than the link. */
    public function linksOut(): bool
    {
        return blank($this->body) && $this->hasExternalSource();
    }

    public function url(): string
    {
        return $this->linksOut() ? (string) $this->source_url : url('/media/'.$this->slug);
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
