<?php

namespace App\Support;

use App\Enums\CategoryType;
use App\Models\Article;
use App\Models\Category;
use App\Models\Event;
use App\Models\GalleryAlbum;
use App\Models\GalleryItem;
use App\Models\MediaCoverage;
use App\Models\PageSection;
use App\Models\TeamMember;
use App\Models\Video;
use Illuminate\Support\Collection;

/** Loads the dynamic content behind automatic page sections (latest articles, events, etc.). */
class SectionData
{
    public static function for(PageSection $s): array
    {
        $d = $s->data ?? [];
        $limit = max(1, (int) ($d['limit'] ?? 6));

        return match ($s->type) {
            'ticker' => ['articles' => Article::live()->latestFirst()->limit($limit)->get(['id', 'title', 'slug', 'published_at'])],
            'hero' => [
                'stats' => ($d['show_stats'] ?? true) ? static::stats() : null,
                'collage' => static::heroCollage($d),
            ],
            'research_focus' => ['items' => static::focusItems($d)],
            'latest_articles' => ['articles' => static::articles($limit, (bool) ($d['featured_first'] ?? false))],
            'events' => ['events' => static::events($limit)],
            'videos' => ['videos' => Video::live()->with(['media', 'author'])->orderByDesc('is_featured')->latestFirst()->limit($limit)->get()],
            'media_coverage' => ['items' => MediaCoverage::live()->with(['outlet.media', 'media'])->orderByDesc('is_featured')->orderByDesc('published_on')->limit($limit)->get()],
            'gallery' => ['items' => static::galleryHighlights($limit)],
            'team' => ['members' => static::team($d)],
            default => [],
        };
    }

    /**
     * Focus areas as configured in the page builder; when none are set we fall back to the
     * live article categories so the section is never rendered empty.
     *
     * @return array<int, array{title: string, description: ?string, url: string}>
     */
    public static function focusItems(array $d): array
    {
        $items = collect($d['items'] ?? [])
            ->filter(fn ($i) => filled($i['title'] ?? null))
            ->map(fn ($i) => [
                'title' => $i['title'],
                'description' => $i['description'] ?? null,
                'url' => $i['url'] ?: '/articles',
            ])
            ->values();

        if ($items->isNotEmpty()) {
            return $items->all();
        }

        return Category::ofType(CategoryType::Article)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit(8)
            ->get()
            ->map(fn (Category $c) => [
                'title' => $c->name,
                'description' => $c->description,
                'url' => '/articles?category='.$c->slug,
            ])
            ->all();
    }

    /**
     * Images for the hero collage: whatever the admin uploaded, otherwise the newest
     * gallery photographs so the banner is never empty.
     */
    public static function heroCollage(array $d): array
    {
        $uploaded = collect($d['collage_images'] ?? [])
            ->filter()
            ->map(fn ($path) => Site::file($path))
            ->filter()
            ->values();

        if ($uploaded->isNotEmpty()) {
            return $uploaded->take(3)->all();
        }

        return GalleryItem::query()
            ->whereHas('album', fn ($query) => $query->live())
            ->with('media')
            ->latest('id')
            ->limit(12)
            ->get()
            ->map(fn (GalleryItem $item) => $item->getFirstMediaUrl('file', 'card') ?: $item->getFirstMediaUrl('file'))
            ->filter()
            ->unique()
            ->take(3)
            ->values()
            ->all();
    }

    public static function stats(): array
    {
        return [
            ['value' => Article::live()->count(), 'label' => 'Research articles'],
            ['value' => MediaCoverage::live()->count(), 'label' => 'Media features'],
            ['value' => GalleryAlbum::live()->count(), 'label' => 'Photo galleries'],
        ];
    }

    public static function articles(int $limit, bool $featuredFirst = false): Collection
    {
        $q = Article::live()->with(['author', 'categories', 'media']);
        if ($featuredFirst) {
            $q->orderByDesc('is_featured');
        }

        return $q->latestFirst()->limit($limit)->get();
    }

    public static function events(int $limit): Collection
    {
        $upcoming = Event::live()->upcoming()->with('media')->limit($limit)->get();
        if ($upcoming->count() >= $limit) {
            return $upcoming;
        }
        $past = Event::live()->past()->with('media')->limit($limit - $upcoming->count())->get();

        return $upcoming->concat($past)->values();
    }

    public static function galleryHighlights(int $limit): Collection
    {
        return GalleryItem::query()->where('type', 'image')
            ->whereHas('album', fn ($q) => $q->live())
            ->with(['media', 'album'])->latest('id')->limit($limit)->get();
    }

    public static function team(array $d): Collection
    {
        $q = TeamMember::active()->with('media');
        if (! empty($d['groups'])) {
            $q->whereIn('group', (array) $d['groups']);
        }
        if (! empty($d['home_only'])) {
            $q->where('show_on_home', true);
        }
        if (! empty($d['limit'])) {
            $q->limit((int) $d['limit']);
        }

        return $q->get();
    }
}
