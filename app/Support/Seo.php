<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/** Builds the per-page SEO payload consumed by <x-seo>. */
class Seo
{
    /**
     * @param  array{title?: string, description?: ?string, image?: ?string, type?: string, jsonld?: array<int, array>, canonical?: ?string, noindex?: bool, published?: ?string, modified?: ?string}  $o
     * @return array<string, mixed>
     */
    public static function make(?Model $model = null, array $o = []): array
    {
        $seo = $model && method_exists($model, 'seo') ? $model->seo : null;
        $siteName = Site::name();
        $suffix = (string) Site::get('seo', 'default_title_suffix', ' | '.$siteName);

        $base = $seo?->title ?: ($o['title'] ?? $siteName);
        $title = ($o['bare_title'] ?? false) || Str::contains($base, $siteName) ? $base : $base.$suffix;

        $description = $seo?->description ?: ($o['description'] ?? Site::get('general', 'organization_description'));
        $description = Str::limit(trim(strip_tags((string) $description)), 300, '');

        $image = $o['image'] ?? null;
        if ($seo?->og_image) {
            $image = Site::file($seo->og_image);
        }
        $image = static::abs($image ?: Site::file(Site::get('seo', 'default_og_image')));

        $page = (int) request()->query('page', 1);
        $canonical = $seo?->canonical_url ?: ($o['canonical'] ?? url()->current().($page > 1 ? '?page='.$page : ''));

        $robots = [];
        $robots[] = ($seo && ! $seo->robots_index) || ($o['noindex'] ?? false) ? 'noindex' : 'index';
        $robots[] = $seo && ! $seo->robots_follow ? 'nofollow' : 'follow';

        return [
            'title' => $title,
            'description' => $description,
            'canonical' => $canonical,
            'robots' => implode(', ', $robots).', max-image-preview:large',
            'og' => [
                'title' => $seo?->og_title ?: $base,
                'description' => $seo?->og_description ?: $description,
                'image' => $image,
                'type' => $o['type'] ?? 'website',
                'site_name' => $siteName,
                'published' => $o['published'] ?? null,
                'modified' => $o['modified'] ?? null,
            ],
            'focus_keyword' => $seo?->focus_keyword,
            'jsonld' => $o['jsonld'] ?? [],
        ];
    }

    public static function abs(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        return Str::startsWith($url, ['http://', 'https://']) ? $url : url($url);
    }
}
