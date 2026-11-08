<?php

namespace App\Models;

use App\Enums\ContentStatus;
use App\Enums\VideoSource;
use App\Models\Concerns\HasCmsMedia;
use App\Models\Concerns\HasPublishing;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\HasTaxonomy;
use App\Models\Concerns\LogsContentActivity;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Article extends Model implements HasMedia
{
    use HasCmsMedia, HasPublishing, HasSeo, HasSlug, HasTaxonomy, LogsContentActivity, SoftDeletes;

    /** Research outputs editors attach: reports, briefs and seminar decks. */
    public const DOCUMENT_MIMES = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    ];

    protected $guarded = ['id'];

    protected array $loggedAttributes = ['title', 'status', 'published_at', 'is_featured', 'is_editors_choice', 'author_id'];

    protected function casts(): array
    {
        return [
            'status' => ContentStatus::class,
            'video_source' => VideoSource::class,
            'published_at' => 'datetime',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'is_featured' => 'boolean',
            'is_editors_choice' => 'boolean',
            'references' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Article $article) {
            $words = str_word_count(strip_tags((string) $article->body));
            $article->reading_time = max(1, (int) ceil($words / 200));
        });
    }

    /** Sanitise rich text on write so stored HTML is always safe to output. */
    protected function body(): Attribute
    {
        return Attribute::make(set: fn (?string $value) => $value === null ? null : \Mews\Purifier\Facades\Purifier::clean($value));
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('title')->saveSlugsTo('slug')->preventOverwrite()->doNotGenerateSlugsOnUpdate();
    }

    public function registerMediaCollections(): void
    {
        $images = ['image/jpeg', 'image/png', 'image/webp'];
        $this->addMediaCollection('featured')->singleFile()->acceptsMimeTypes($images);
        $this->addMediaCollection('gallery')->acceptsMimeTypes($images);
        $this->addMediaCollection('attachments')->acceptsMimeTypes(self::DOCUMENT_MIMES);
        $this->addMediaCollection('video_file')->singleFile()->acceptsMimeTypes(['video/mp4', 'video/webm']);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(ArticleRevision::class)->orderByDesc('version');
    }

    public function schemaType(): string
    {
        return match ($this->kind) {
            'news' => 'NewsArticle',
            'blog' => 'BlogPosting',
            default => 'Article',
        };
    }
}
