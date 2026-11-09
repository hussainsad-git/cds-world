<?php

namespace App\Console\Commands;

use App\Enums\CategoryType;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * First-pass topical classification for imported articles.
 *
 * Everything came across from WordPress under three editorial buckets (Research &
 * Analysis, Insight, CDS Web Desk), leaving the seven thematic categories empty and
 * every research-area link on the homepage a dead end. This scores each article
 * against keyword sets and files it under its strongest themes. It is a starting
 * point for an editor, not a verdict: run --dry-run first and review in the admin.
 */
class ClassifyArticles extends Command
{
    protected $signature = 'cds:classify-articles {--dry-run : Report what would change without writing}
                                                  {--min=1 : Raise every theme\'s own minimum to at least this}
                                                  {--max=2 : Most themes to attach to one article}';

    protected $description = 'Assign imported articles to the thematic research categories';

    /** @var array<string, array<int, string>> theme slug => distinctive terms */
    /** @var array<string, array{0: int, 1: array<int, string>}> theme slug => [minimum hits, terms] */
    private const THEMES = [
        'cpec' => [1, ['cpec', 'gwadar', 'belt and road', 'china-pakistan economic corridor', 'karakoram']],
        'climate-security' => [2, ['climate', 'glacier', 'monsoon', 'drought', 'flood', 'carbon emission', 'water security', 'global warming', 'ecological', 'heatwave', 'environmental']],
        'economy-energy' => [2, ['energy security', 'tariff', 'inflation', 'fiscal', 'imf', 'pipeline', 'gdp', 'exports', 'currency', 'petroleum', 'electricity', 'budget deficit', 'foreign reserves', 'economic growth', 'trade deficit', 'taxation', 'debt servicing', 'investment climate', 'industrial']],
        'regional-security' => [2, ['terror', 'militan', 'insurgen', 'ttp', 'line of control', 'kashmir', 'proxy war', 'deterrence', 'ceasefire', 'baloch', 'cross-border', 'armed forces', 'military operation']],
        'international-relations' => [2, ['foreign policy', 'bilateral', 'diplomacy', 'diplomatic', 'united nations', 'treaty', 'alliance', 'ambassador', 'summit', 'multilateral', 'delegation', 'envoy']],
        'development-governance' => [2, ['governance', 'institutional reform', 'corruption', 'parliament', 'judiciary', 'democratic', 'education', 'youth', 'civil service', 'rule of law', 'public policy']],
        'geopolitics' => [2, ['geopolit', 'geostrateg', 'world order', 'multipolar', 'hegemon', 'superpower', 'great power', 'realignment', 'global order', 'balance of power', 'strategic competition', 'sphere of influence', 'bloc']],
    ];

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $min = max(1, (int) $this->option('min'));
        $max = max(1, (int) $this->option('max'));

        // A plain slug => id map, deliberately not an Eloquent collection keyed by slug:
        // Eloquent\Collection::only() matches PRIMARY KEYS and ignores keyBy(), so
        // selecting by slug through only() silently returns nothing and every sync
        // becomes a no-op that still reports success.
        $categories = Category::ofType(CategoryType::Article)
            ->whereIn('slug', array_keys(self::THEMES))
            ->pluck('id', 'slug')
            ->all();

        $missing = array_diff(array_keys(self::THEMES), array_keys($categories));
        if ($missing) {
            $this->error('Missing categories: '.implode(', ', $missing));

            return self::FAILURE;
        }

        $tally = array_fill_keys(array_keys(self::THEMES), 0);
        $unmatched = 0;
        $samples = [];

        Article::with('categories')->chunkById(200, function ($articles) use (
            $categories, $dry, $min, $max, &$tally, &$unmatched, &$samples
        ) {
            foreach ($articles as $article) {
                $hay = Str::lower(strip_tags($article->title.' '.$article->excerpt.' '.Str::limit((string) $article->body, 6000, '')));

                $scores = [];
                foreach (self::THEMES as $slug => [$floor, $terms]) {
                    $hits = 0;
                    foreach ($terms as $term) {
                        if (str_contains($hay, $term)) {
                            $hits++;
                        }
                    }
                    if ($hits >= max($floor, $min)) {
                        $scores[$slug] = $hits;
                    }
                }

                if (! $scores) {
                    $unmatched++;

                    continue;
                }

                arsort($scores);
                $picked = array_slice(array_keys($scores), 0, $max);

                foreach ($picked as $slug) {
                    $tally[$slug]++;
                }
                if (count($samples) < 12) {
                    $samples[] = Str::limit($article->title, 62).'  ->  '.implode(', ', $picked);
                }

                $ids = array_values(array_intersect_key($categories, array_flip($picked)));

                if (! $dry && $ids) {
                    // Attach, never replace: the WordPress buckets stay as they are.
                    $article->categories()->syncWithoutDetaching($ids);
                }
            }
        });

        $this->newLine();
        foreach ($tally as $slug => $count) {
            $this->line(str_pad($slug, 26).$count);
        }
        $this->line(str_pad('(no theme matched)', 26).$unmatched);

        $this->newLine();
        $this->line('Samples:');
        foreach ($samples as $s) {
            $this->line('  '.$s);
        }

        $this->newLine();
        $this->info($dry ? 'Dry run — nothing written.' : 'Categories attached. Review them in /admin.');

        return self::SUCCESS;
    }
}
