<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class WebsiteSetting extends Model
{
    protected $guarded = ['id'];

    protected static function booted(): void
    {
        $flush = fn (self $s) => Cache::forget('settings.'.$s->group);
        static::saved($flush);
        static::deleted($flush);
    }

    /** All settings of a group as key => value (cached). */
    public static function group(string $group): array
    {
        return Cache::rememberForever('settings.'.$group, function () use ($group) {
            return static::query()->where('group', $group)->get()
                ->mapWithKeys(fn ($s) => [$s->key => $s->type === 'json' ? json_decode((string) $s->value, true) : $s->value])
                ->all();
        });
    }

    public static function get(string $group, string $key, mixed $default = null): mixed
    {
        return static::group($group)[$key] ?? $default;
    }

    public static function put(string $group, string $key, mixed $value): void
    {
        $isJson = is_array($value);
        static::updateOrCreate(
            ['group' => $group, 'key' => $key],
            ['value' => $isJson ? json_encode($value) : $value, 'type' => $isJson ? 'json' : 'string'],
        );
    }
}
