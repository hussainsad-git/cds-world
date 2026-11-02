<?php

namespace App\Models\Concerns;

use App\Enums\ContentStatus;
use Illuminate\Database\Eloquent\Builder;

trait HasPublishing
{
    protected static function bootHasPublishing(): void
    {
        static::saving(function ($model) {
            $status = $model->status instanceof ContentStatus ? $model->status : ContentStatus::tryFrom((string) $model->status);
            if ($status === ContentStatus::Published && ! $model->published_at) {
                $model->published_at = now();
            }
        });
    }

    /** Live = published/scheduled AND publication time reached. */
    public function scopeLive(Builder $query): Builder
    {
        return $query
            ->whereIn($this->qualifyColumn('status'), [ContentStatus::Published->value, ContentStatus::Scheduled->value])
            ->where($this->qualifyColumn('published_at'), '<=', now());
    }

    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query->orderByDesc($this->qualifyColumn('published_at'));
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where($this->qualifyColumn('is_featured'), true);
    }

    public function isLive(): bool
    {
        $status = $this->status instanceof ContentStatus ? $this->status : ContentStatus::tryFrom((string) $this->status);

        return in_array($status, [ContentStatus::Published, ContentStatus::Scheduled], true)
            && $this->published_at && $this->published_at->lte(now());
    }
}
