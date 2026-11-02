<?php

namespace App\Models;

use App\Enums\ContentStatus;
use App\Models\Concerns\HasCmsMedia;
use App\Models\Concerns\HasPublishing;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\HasTaxonomy;
use App\Models\Concerns\LogsContentActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Event extends Model implements HasMedia
{
    use HasCmsMedia, HasPublishing, HasSeo, HasSlug, HasTaxonomy, LogsContentActivity, SoftDeletes;

    protected $guarded = ['id'];

    protected array $loggedAttributes = ['title', 'status', 'published_at', 'starts_at', 'is_cancelled', 'is_featured'];

    protected function casts(): array
    {
        return [
            'status' => ContentStatus::class,
            'published_at' => 'datetime',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_cancelled' => 'boolean',
            'is_featured' => 'boolean',
            'internal_registration' => 'boolean',
        ];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('title')->saveSlugsTo('slug')->preventOverwrite()->doNotGenerateSlugsOnUpdate();
    }

    public function registerMediaCollections(): void
    {
        $images = ['image/jpeg', 'image/png', 'image/webp'];
        $this->addMediaCollection('banner')->singleFile()->acceptsMimeTypes($images);
        $this->addMediaCollection('gallery')->acceptsMimeTypes($images);
        $this->addMediaCollection('attachments')->acceptsMimeTypes(['application/pdf']);
    }

    public function speakers(): HasMany
    {
        return $this->hasMany(EventSpeaker::class)->orderBy('sort_order');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    public function mediaCoverage(): HasMany
    {
        return $this->hasMany(MediaCoverage::class);
    }

    /** upcoming | ongoing | completed | cancelled — derived, never stored. */
    public function lifecycle(): string
    {
        if ($this->is_cancelled) {
            return 'cancelled';
        }
        $end = $this->ends_at ?? $this->starts_at->copy()->endOfDay();
        if ($this->starts_at->isFuture()) {
            return 'upcoming';
        }

        return $end->isFuture() ? 'ongoing' : 'completed';
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('is_cancelled', false)
            ->whereRaw('COALESCE(ends_at, starts_at) >= ?', [now()])
            ->orderBy('starts_at');
    }

    public function scopePast(Builder $query): Builder
    {
        return $query->whereRaw('COALESCE(ends_at, starts_at) < ?', [now()])->orderByDesc('starts_at');
    }
}
