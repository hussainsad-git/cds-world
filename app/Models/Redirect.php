<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Redirect extends Model
{
    protected $guarded = ['id'];

    protected static function booted(): void
    {
        $flush = fn () => \Illuminate\Support\Facades\Cache::forget('redirects.map');
        static::saved($flush);
        static::deleted($flush);
    }

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'last_hit_at' => 'datetime'];
    }

    public static function normalize(string $path): string
    {
        return '/'.trim(parse_url($path, PHP_URL_PATH) ?: $path, '/');
    }
}
