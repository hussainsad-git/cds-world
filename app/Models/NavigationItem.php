<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class NavigationItem extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['open_in_new_tab' => 'boolean', 'is_visible' => 'boolean'];
    }

    protected static function booted(): void
    {
        $flush = fn () => Cache::forget('nav.menus');
        static::saved($flush);
        static::deleted($flush);
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(NavigationMenu::class, 'navigation_menu_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->where('is_visible', true)->orderBy('sort_order');
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function href(): string
    {
        if ($this->type === 'page' && $this->page) {
            return url('/'.ltrim($this->page->slug === 'home' ? '' : $this->page->slug, '/'));
        }

        return (string) ($this->url ?: '#');
    }
}
