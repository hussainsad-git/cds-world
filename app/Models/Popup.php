<?php

namespace App\Models;

use App\Models\Concerns\HasCmsMedia;
use App\Models\Concerns\LogsContentActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;

class Popup extends Model implements HasMedia
{
    use HasCmsMedia, LogsContentActivity, SoftDeletes;

    protected $guarded = ['id'];

    protected array $loggedAttributes = ['title', 'is_active', 'starts_at', 'ends_at'];

    protected static function booted(): void
    {
        $flush = fn () => \Illuminate\Support\Facades\Cache::forget('popups.running');
        static::saved($flush);
        static::deleted($flush);

        static::saving(function (self $popup) {
            if (in_array($popup->type, ['text', 'html'], true) && $popup->content) {
                $popup->content = clean($popup->content, 'popup');
            }
        });
    }

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'target_paths' => 'array',
            'show_on_desktop' => 'boolean',
            'show_on_mobile' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->singleFile()->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function scopeRunning(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }

    /** Empty/null target list means "all pages"; patterns support * wildcards, e.g. articles/*. */
    public function matchesPath(string $path): bool
    {
        $targets = $this->target_paths ?: [];
        if ($targets === []) {
            return true;
        }
        $path = trim($path, '/');
        foreach ($targets as $pattern) {
            if (\Illuminate\Support\Str::is(trim((string) $pattern, '/') ?: '/', $path === '' ? '/' : $path)) {
                return true;
            }
        }

        return false;
    }
}
