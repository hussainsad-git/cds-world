<?php

namespace App\Support;

use App\Models\Article;
use App\Models\Event;
use App\Models\Video;

/** schema.org JSON-LD builders. */
class Schema
{
    public static function organization(): array
    {
        $social = array_values(array_filter(array_map(fn ($k) => Site::get('social', $k), ['facebook', 'instagram', 'x', 'youtube', 'linkedin'])));
        $logo = Site::file(Site::get('general', 'logo'));

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => Site::get('general', 'organization_name', Site::name()),
            'alternateName' => Site::name(),
            'url' => url('/'),
            'logo' => $logo ? Seo::abs($logo) : null,
            'description' => Site::get('general', 'organization_description'),
            'email' => Site::get('general', 'contact_email'),
            'sameAs' => $social ?: null,
        ]);
    }

    public static function website(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => Site::name(),
            'url' => url('/'),
            'potentialAction' => ['@type' => 'SearchAction', 'target' => url('/articles').'?q={search_term_string}', 'query-input' => 'required name=search_term_string'],
        ];
    }

    /** @param array<int, array{0: string, 1: string}> $crumbs [label, url] */
    public static function breadcrumb(array $crumbs): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($crumbs)->values()->map(fn ($c, $i) => ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $c[0], 'item' => Seo::abs($c[1])])->all(),
        ];
    }

    public static function article(Article $a, ?string $image): array
    {
        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => $a->schemaType(),
            'headline' => mb_substr($a->title, 0, 110),
            'description' => $a->excerpt,
            'image' => $image ? [Seo::abs($image)] : null,
            'datePublished' => $a->published_at?->toIso8601String(),
            'dateModified' => ($a->updated_at ?? $a->published_at)?->toIso8601String(),
            'author' => $a->author ? ['@type' => 'Person', 'name' => $a->author->name] : ['@type' => 'Organization', 'name' => Site::name()],
            'publisher' => ['@type' => 'Organization', 'name' => Site::get('general', 'organization_name', Site::name()), 'logo' => ($l = Site::file(Site::get('general', 'logo'))) ? ['@type' => 'ImageObject', 'url' => Seo::abs($l)] : null],
            'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => url('/articles/'.$a->slug)],
            'wordCount' => str_word_count(strip_tags((string) $a->body)),
            'keywords' => $a->relationLoaded('tags') ? $a->tags->pluck('name')->implode(', ') : null,
        ]);
    }

    public static function event(Event $e, ?string $image): array
    {
        $status = match ($e->lifecycle()) {
            'cancelled' => 'https://schema.org/EventCancelled',
            default => 'https://schema.org/EventScheduled',
        };

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Event',
            'name' => $e->title,
            'description' => $e->summary,
            'startDate' => $e->starts_at->toIso8601String(),
            'endDate' => $e->ends_at?->toIso8601String(),
            'eventStatus' => $status,
            'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
            'location' => $e->venue ? array_filter(['@type' => 'Place', 'name' => $e->venue, 'address' => $e->address ?: $e->venue]) : null,
            'image' => $image ? [Seo::abs($image)] : null,
            'organizer' => ['@type' => 'Organization', 'name' => Site::get('general', 'organization_name', Site::name()), 'url' => url('/')],
            'performer' => $e->relationLoaded('speakers') && $e->speakers->isNotEmpty() ? $e->speakers->map(fn ($s) => ['@type' => 'Person', 'name' => $s->name])->all() : null,
            'url' => url('/events/'.$e->slug),
        ]);
    }

    public static function video(Video $v, ?string $thumb): array
    {
        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'VideoObject',
            'name' => $v->title,
            'description' => $v->description ?: $v->title,
            'thumbnailUrl' => $thumb ? [Seo::abs($thumb)] : null,
            'uploadDate' => $v->published_at?->toIso8601String(),
            'duration' => $v->duration_seconds ? 'PT'.(int) $v->duration_seconds.'S' : null,
            'embedUrl' => $v->source?->value !== 'upload' ? $v->embedUrl() : null,
            'contentUrl' => $v->source?->value === 'upload' ? Seo::abs($v->getFirstMediaUrl('video_file')) : null,
        ]);
    }
}
