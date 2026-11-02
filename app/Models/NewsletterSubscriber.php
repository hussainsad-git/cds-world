<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NewsletterSubscriber extends Model
{
    protected $fillable = [
        'email', 'name', 'status', 'consented_at', 'consent_text', 'source',
        'confirmed_at', 'unsubscribed_at', 'ip_hash',
    ];

    protected $hidden = ['confirm_token', 'unsubscribe_token', 'ip_hash'];

    protected function casts(): array
    {
        return ['consented_at' => 'datetime', 'confirmed_at' => 'datetime', 'unsubscribed_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::creating(function (self $s) {
            $s->email = Str::lower(trim($s->email));
            $s->unsubscribe_token ??= Str::random(48);
            if ($s->status === 'pending') {
                $s->confirm_token ??= Str::random(48);
            }
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
