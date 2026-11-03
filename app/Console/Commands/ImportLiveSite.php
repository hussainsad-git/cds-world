<?php

namespace App\Console\Commands;

use App\Enums\CategoryType;
use App\Enums\ContentStatus;
use App\Enums\TeamGroup;
use App\Models\Article;
use App\Models\Category;
use App\Models\Event;
use App\Models\GalleryAlbum;
use App\Models\GalleryItem;
use App\Models\MediaCoverage;
use App\Models\MediaOutlet;
use App\Models\Tag;
use App\Models\TeamMember;
use App\Models\User;
use Carbon\Carbon;
use DOMDocument;
use DOMXPath;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

/**
 * Imports the content shown on https://cdsworld.org into the CMS.
 * Source of truth: the public WordPress REST API behind it (full bodies, authors, images).
 * Idempotent: records are matched by slug and skipped if they already exist.
 */
class ImportLiveSite extends Command
{
    protected $signature = 'cds:import-live
        {--only=* : Sections to import: articles, events, media, gallery, team}
        {--limit=0 : Max records per section (0 = all shown on the live site)}
        {--no-images : Skip image downloads}
        {--fresh : Delete previously imported content before importing}';

    protected $description = 'Import articles, events, media coverage, galleries and team from the live cdsworld.org site';

    private const LIVE = 'https://cdsworld.org';
    private const WP = 'https://centrefordevelopmentandstability.com/wp-json/wp/v2';

    private bool $images = true;
    private int $limit = 0;

    public function handle(): int
    {
        $this->images = ! $this->option('no-images');
        $this->limit = (int) $this->option('limit');
        $only = $this->option('only') ?: ['team', 'articles', 'events', 'media', 'gallery'];

        if ($this->option('fresh')) {
            $this->components->warn('Clearing previously imported content');
            \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0');
            foreach ([
                'media', 'gallery_items', 'gallery_albums', 'media_coverage', 'media_outlets',
                'event_speakers', 'event_registrations', 'events', 'article_revisions', 'articles',
                'team_members', 'categoryables', 'taggables',
            ] as $table) {
                if (\Illuminate\Support\Facades\Schema::hasTable($table)) {
                    \Illuminate\Support\Facades\DB::table($table)->truncate();
                }
            }
            \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1');
            User::where('email', 'like', '%@import.cdsworld.invalid')->forceDelete();
        }

        foreach ($only as $section) {
            $this->components->info('Importing '.$section);
            match ($section) {
                'team' => $this->team(),
                'articles' => $this->articles(),
                'events' => $this->events(),
                'media' => $this->media(),
                'gallery' => $this->gallery(),
                default => $this->components->warn("Unknown section $section"),
            };
        }

        \Illuminate\Support\Facades\Cache::flush();
        $this->components->info('Done. Review everything in /admin.');

        return self::SUCCESS;
    }

    // ---------------------------------------------------------------- sources

    private function http()
    {
        return Http::timeout(60)->retry(2, 500, throw: false)->withUserAgent('CDS-CMS-Importer/1.0');
    }

    private function liveHtml(string $path): string
    {
        $r = $this->http()->get(self::LIVE.$path);

        return $r->successful() ? $r->body() : '';
    }

    /** @return string[] slugs linked from a live listing page, newest first. */
    private function slugsFrom(string $path, string $prefix): array
    {
        $slugs = [];
        $html = $this->liveHtml($path);
        preg_match_all('~href="(?:https://cdsworld\.org)?/'.$prefix.'/([^/"?\#]+)/?"~', $html, $m);
        foreach ($m[1] as $s) {
            $slugs[urldecode($s)] = true;
        }
        $slugs = array_keys($slugs);

        return $this->limit ? array_slice($slugs, 0, $this->limit) : $slugs;
    }

    private function post(string $slug): ?array
    {
        $r = $this->http()->get(self::WP.'/posts', ['slug' => $slug, '_embed' => 1]);
        $j = $r->json();

        return is_array($j) && ! empty($j[0]) ? $j[0] : null;
    }

    // ---------------------------------------------------------------- helpers

    private function text(?string $html): string
    {
        return trim(html_entity_decode(strip_tags((string) $html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    /** Column-safe title: WordPress titles can exceed varchar(255). */
    private function title(string $value): string
    {
        return Str::limit($value, 240, '');
    }

    private function when(array $p): Carbon
    {
        return Carbon::parse($p['date_gmt'] ?? $p['date'], 'UTC')->setTimezone(config('app.timezone'));
    }

    private function author(array $p): ?User
    {
        $name = $p['_embedded']['author'][0]['name'] ?? null;
        if (! $name) {
            return null;
        }
        $email = Str::slug($name).'@import.cdsworld.invalid';

        return User::withTrashed()->firstOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => Str::random(40), 'is_active' => false],
        );
    }

    private function category(CategoryType $type, string $name): Category
    {
        return Category::firstOrCreate(
            ['type' => $type->value, 'slug' => Str::slug($name)],
            ['name' => $name, 'is_active' => true],
        );
    }

    /** Extra WP categories/tags -> CMS categories (generic WP buckets are ignored). */
    private function attachTaxonomy($model, array $p, CategoryType $type, string $fallback): void
    {
        $skip = ['articles', 'events', 'gallery', 'media coverage', 'uncategorized'];
        $ids = [];
        foreach ($p['_embedded']['wp:term'][0] ?? [] as $t) {
            if (! in_array(Str::lower($t['name']), $skip, true)) {
                $ids[] = $this->category($type, html_entity_decode($t['name']))->id;
            }
        }
        if (! $ids) {
            $ids[] = $this->category($type, $fallback)->id;
        }
        $model->categories()->syncWithoutDetaching($ids);

        $tagIds = [];
        foreach ($p['_embedded']['wp:term'][1] ?? [] as $t) {
            $tagIds[] = Tag::firstOrCreate(['slug' => Str::slug($t['name'])], ['name' => html_entity_decode($t['name'])])->id;
        }
        if ($tagIds && method_exists($model, 'tags')) {
            $model->tags()->syncWithoutDetaching($tagIds);
        }
    }

    private function attachImage($model, ?string $url, string $collection, ?string $alt = null): void
    {
        if (! $this->images || ! $url) {
            return;
        }
        try {
            // allow_url_fopen is off on many shared hosts, so fetch over curl rather than
            // letting media-library open the URL as a stream.
            $res = $this->http()->get($url);
            if (! $res->successful()) {
                throw new \RuntimeException('HTTP '.$res->status());
            }
            $name = urldecode(basename((string) parse_url($url, PHP_URL_PATH))) ?: 'image.jpg';
            $media = $model->addMediaFromString($res->body())
                ->usingFileName($name)
                ->usingName(pathinfo($name, PATHINFO_FILENAME))
                ->toMediaCollection($collection);
            if ($alt) {
                $media->setCustomProperty('alt', $alt)->save();
            }
        } catch (Throwable $e) {
            $this->components->warn('  image failed: '.Str::limit($url, 70).' — '.$e->getMessage());
        }
    }

    private function featuredUrl(array $p): ?string
    {
        return $p['_embedded']['wp:featuredmedia'][0]['source_url'] ?? null;
    }

    private function seoFrom($model, array $p): void
    {
        $title = $this->text($p['title']['rendered'] ?? '');
        $desc = Str::limit($this->text($p['excerpt']['rendered'] ?? ''), 300, '');
        if (method_exists($model, 'seo') && $desc) {
            try {
                $model->seo()->updateOrCreate([], ['title' => Str::limit($title, 110, ''), 'description' => $desc]);
            } catch (Throwable) {
            }
        }
    }

    // ---------------------------------------------------------------- sections

    private function articles(): void
    {
        foreach ($this->slugsFrom('/articles', 'articles') as $slug) {
            if (Article::withTrashed()->where('slug', $slug)->exists()) {
                $this->line("  skip $slug");
                continue;
            }
            if (! ($p = $this->post($slug))) {
                $this->components->warn("  not found in source: $slug");
                continue;
            }
            $a = new Article([
                'title' => $this->title($this->text($p['title']['rendered'])),
                'slug' => $slug,
                'excerpt' => Str::limit($this->text($p['excerpt']['rendered']), 480, '…'),
                'body' => $p['content']['rendered'],
                'kind' => 'research',
                'status' => ContentStatus::Published,
                'published_at' => $this->when($p),
                'author_id' => $this->author($p)?->id,
                'featured_alt' => $this->title($this->text($p['title']['rendered'])),
            ]);
            $a->save();
            $this->attachTaxonomy($a, $p, CategoryType::Article, 'Research & Analysis');
            $this->attachImage($a, $this->featuredUrl($p), 'featured', $a->title);
            $this->seoFrom($a, $p);
            $this->line("  + article: {$a->title}");
        }
    }

    private function events(): void
    {
        foreach ($this->slugsFrom('/events', 'events') as $slug) {
            if (Event::withTrashed()->where('slug', $slug)->exists()) {
                $this->line("  skip $slug");
                continue;
            }
            if (! ($p = $this->post($slug))) {
                $this->components->warn("  not found in source: $slug");
                continue;
            }
            $when = $this->when($p);
            $e = new Event([
                'title' => $this->title($this->text($p['title']['rendered'])),
                'slug' => $slug,
                'summary' => Str::limit($this->text($p['excerpt']['rendered']), 480, '…'),
                'description' => \Mews\Purifier\Facades\Purifier::clean($p['content']['rendered']),
                'starts_at' => $when,
                'status' => ContentStatus::Published,
                'published_at' => $when,
                'banner_alt' => $this->title($this->text($p['title']['rendered'])),
            ]);
            $e->save();
            $this->attachTaxonomy($e, $p, CategoryType::Event, 'Events');
            $this->attachImage($e, $this->featuredUrl($p), 'banner', $e->title);
            $this->line("  + event: {$e->title}");
        }
    }

    private function media(): void
    {
        foreach ($this->slugsFrom('/media-coverage', 'media') as $slug) {
            if (MediaCoverage::withTrashed()->where('slug', $slug)->exists()) {
                $this->line("  skip $slug");
                continue;
            }
            if (! ($p = $this->post($slug))) {
                $this->components->warn("  not found in source: $slug");
                continue;
            }
            $content = $p['content']['rendered'];
            // Source link: first external (non-CDS) link in the body, else the live page itself.
            $source = self::LIVE.'/media/'.$slug.'/';
            if (preg_match_all('~href="(https?://[^"]+)"~', $content, $m)) {
                foreach ($m[1] as $href) {
                    if (! str_contains($href, 'cdsworld.org') && ! str_contains($href, 'centrefordevelopmentandstability.com')) {
                        $source = $href;
                        break;
                    }
                }
            }
            $title = $this->title($this->text($p['title']['rendered']));
            $outletName = preg_match('~(?:coverage by|by)\s+([A-Z][\w .&\'-]{2,40})$~i', $title, $mm) ? trim($mm[1]) : (parse_url($source, PHP_URL_HOST) ?: 'CDS World');
            $outlet = MediaOutlet::firstOrCreate(['name' => Str::limit(Str::title(preg_replace('~^www\.~', '', $outletName)), 120, '')], ['is_active' => true]);

            $c = new MediaCoverage([
                'media_outlet_id' => $outlet->id,
                'headline' => $title,
                'slug' => $slug,
                'summary' => Str::limit($this->text($p['excerpt']['rendered']) ?: $this->text($content), 300, '…'),
                'source_url' => $source,
                'published_on' => $this->when($p)->toDateString(),
                'status' => ContentStatus::Published,
                'published_at' => $this->when($p),
                'thumb_alt' => $title,
            ]);
            $c->save();
            $this->attachTaxonomy($c, $p, CategoryType::MediaCoverage, 'Media Coverage');
            $this->attachImage($c, $this->featuredUrl($p), 'thumbnail', $title);
            $this->line("  + media: {$c->headline}");
        }
    }

    private function gallery(): void
    {
        foreach ($this->slugsFrom('/gallery', 'gallery') as $slug) {
            if (GalleryAlbum::withTrashed()->where('slug', $slug)->exists()) {
                $this->line("  skip $slug");
                continue;
            }
            if (! ($p = $this->post($slug))) {
                $this->components->warn("  not found in source: $slug");
                continue;
            }
            $title = $this->title($this->text($p['title']['rendered']));
            $album = new GalleryAlbum([
                'title' => $title,
                'slug' => $slug,
                'description' => Str::limit($this->text($p['excerpt']['rendered']), 480, '…'),
                'status' => ContentStatus::Published,
                'published_at' => $this->when($p),
            ]);
            $album->save();
            $this->attachTaxonomy($album, $p, CategoryType::Gallery, 'Photo Galleries');
            $cover = $this->featuredUrl($p);
            $this->attachImage($album, $cover, 'cover', $title);

            // Every unique image in the post body becomes a gallery item (full-size URL, not the -WxH crop).
            preg_match_all('~<img[^>]+src="([^"]+)"~i', $p['content']['rendered'], $m);
            $urls = collect($m[1])->map(fn ($u) => preg_replace('~-\d+x\d+(\.\w+)$~', '$1', $u))->unique()->values();
            if ($this->limit) {
                $urls = $urls->take(max($this->limit, 1) * 10);
            }
            foreach ($urls as $i => $url) {
                $item = GalleryItem::create([
                    'gallery_album_id' => $album->id,
                    'type' => 'image',
                    'alt_text' => $title,
                    'sort_order' => $i,
                ]);
                $this->attachImage($item, $url, 'file');
            }
            $this->line("  + album: {$title} ({$urls->count()} images)");
        }
    }

    private function team(): void
    {
        // Board of Directors page: one .row per person.
        $this->peopleFrom('/board-of-directors', TeamGroup::Board);

        // Home page "people" cards (name + designation + photo).
        $dom = $this->dom($this->liveHtml('/'));
        $x = new DOMXPath($dom);
        $order = 100;
        foreach ($x->query('//*[contains(concat(" ",normalize-space(@class)," ")," au ")]') as $card) {
            $name = trim($x->evaluate('string(.//h3)', $card));
            $role = trim($x->evaluate('string(.//p)', $card));
            $img = $x->evaluate('string(.//img/@src)', $card);
            $this->saveMember($name, $role, null, $img, TeamGroup::Research, $order++);
        }
    }

    private function peopleFrom(string $path, TeamGroup $group): void
    {
        $dom = $this->dom($this->liveHtml($path));
        $x = new DOMXPath($dom);
        $order = 1;
        foreach ($x->query('//main//h2') as $h2) {
            $row = $h2;
            while ($row && ! str_contains(' '.($row->attributes?->getNamedItem('class')?->nodeValue ?? '').' ', ' row ')) {
                $row = $row->parentNode;
            }
            if (! $row) {
                continue;
            }
            $name = trim($h2->textContent);
            $prev = $h2->previousSibling;
            while ($prev && ($prev->nodeType !== XML_ELEMENT_NODE)) {
                $prev = $prev->previousSibling;
            }
            $role = $prev ? trim($prev->textContent) : null;
            $bio = '';
            foreach ($x->query('.//p', $row) as $p) {
                $bio .= '<p>'.e(trim($p->textContent)).'</p>';
            }
            $img = $x->evaluate('string(.//img/@src)', $row);
            $this->saveMember($name, $role, $bio, $img, $group, $order++);
        }
    }

    private function saveMember(string $name, ?string $role, ?string $bio, ?string $img, TeamGroup $group, int $order): void
    {
        if ($name === '' || TeamMember::withTrashed()->where('slug', Str::slug($name))->exists()) {
            return;
        }
        $m = TeamMember::create([
            'name' => $this->title($name),
            'slug' => Str::slug($name),
            'designation' => $role ? Str::limit($role, 240, '') : null,
            'bio' => $bio ?: null,
            'group' => $group,
            'sort_order' => $order,
            'is_active' => true,
            'show_on_home' => true,
        ]);
        $this->attachImage($m, $img ?: null, 'photo', $name);
        $this->line("  + team: $name");
    }

    private function dom(string $html): DOMDocument
    {
        $d = new DOMDocument();
        libxml_use_internal_errors(true);
        $d->loadHTML('<?xml encoding="UTF-8">'.$html);
        libxml_clear_errors();

        return $d;
    }
}
