<?php

namespace App\Models;

use App\Enums\CategoryType;
use App\Models\Concerns\HasCmsMedia;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\LogsContentActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Category extends Model implements HasMedia
{
    use HasCmsMedia, HasSeo, HasSlug, LogsContentActivity, SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['type' => CategoryType::class, 'is_active' => 'boolean'];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('name')->saveSlugsTo('slug')->preventOverwrite()->doNotGenerateSlugsOnUpdate();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile()->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function scopeOfType(Builder $query, CategoryType|string $type): Builder
    {
        return $query->where('type', $type instanceof CategoryType ? $type->value : $type);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** Count of records of any type attached to this category (used to block/confirm deletion). */
    public function usageCount(): int
    {
        return (int) \DB::table('categoryables')->where('category_id', $this->id)->count();
    }
}
