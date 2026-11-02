<?php

namespace App\Models;

use App\Enums\ContentStatus;
use App\Enums\VideoSource;
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

class Video extends Model implements HasMedia
{
    use HasCmsMedia, HasPublishing, HasSeo, HasSlug, HasTaxonomy, LogsContentActivity, SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status' => ContentStatus::class,
            'source' => VideoSource::class,
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Video $video) {
            if ($video->source !== VideoSource::Upload && $video->video_url) {
                $video->external_id = static::parseExternalId($video->source, $video->video_url);
            }
        });
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('title')->saveSlugsTo('slug')->preventOverwrite()->doNotGenerateSlugsOnUpdate();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('thumbnail')->singleFile()->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
        $this->addMediaCollection('video_file')->singleFile()->acceptsMimeTypes(['video/mp4', 'video/webm']);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /** Extract the provider video id from a pasted URL (or return null when unrecognised). */
    public static function parseExternalId(VideoSource|string|null $source, string $url): ?string
    {
        $source = $source instanceof VideoSource ? $source : VideoSource::tryFrom((string) $source);

        if ($source === VideoSource::YouTube) {
            if (preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/))([A-Za-z0-9_-]{11})~', $url, $m)) {
                return $m[1];
            }

            return null;
        }

        if ($source === VideoSource::Facebook) {
            return $url;
        }

        return null;
    }

    /** Privacy-friendly embed URL, loaded only on click (no autoload). */
    public function embedUrl(): ?string
    {
        return match ($this->source) {
            VideoSource::YouTube => $this->external_id ? 'https://www.youtube-nocookie.com/embed/'.$this->external_id.'?autoplay=1&rel=0' : null,
            VideoSource::Facebook => $this->video_url
                ? 'https://www.facebook.com/plugins/video.php?show_text=false&autoplay=1&href='.urlencode($this->video_url)
                : null,
            default => null,
        };
    }
}
