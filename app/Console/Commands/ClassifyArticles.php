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
                                                  {--min=2 : Minimum distinct keyword hits for a theme to count}
                                                  {--max=2 : Most themes to attach to one article}';

    protected $description = 'Assign imported articles to the thematic research categories';

    /** @var array<string, array<int, string>> theme slug => distinctive terms */
    private const THEMES = [
        'cpec' => ['cpec', 'gwadar', 'belt and road', 'bri ', 'economic corridor', 'karakoram'],
        'climate-security' => ['climate', 'glacier', 'monsoon', 'drought', 'flood', 'carbon', 'el nino', 'el niño', 'water scarcity', 'environment'],
        'economy-energy' => ['economy', 'economic', 'energy', 'tariff', 'inflation', 'budget', 'imf', 'fiscal', 'pipeline', 'oil', 'gas ', 'trade', 'investment', 'debt', 'remittance'],
        'regional-security' => ['terror', 'militan', 'insurgen', 'ttp', 'line of control', 'kashmir', 'proxy war', 'deterrence', 'nuclear', 'ceasefire', 'cross-border', 'baloch'],
        'international-relations' => ['foreign policy', 'bilateral', 'diplomacy', 'diplomatic', 'united nations', 'treaty', 'alliance', 'ambassador', 'summit', 'multilateral'],
        'development-governance' => ['governance', 'institutional', 'reform', 'corruption', 'parliament', 'judiciary', 'democracy', 'education', 'public health', 'youth'],
        'geopolitics' => ['geopolit', 'world order', 'multipolar', 'hegemon', 'superpower', 'great power', 'realignment', 'global order', 'sphere of influence'],
    ];

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $min = max(1, (int) $this->option('min'));
        $max = max(1, (int) $this->option('max'));

        $categories = Category::ofType(CategoryType::Article)
            ->whereIn('slug', array_keys(self::THEMES))
            ->get()
            ->keyBy('slug');

        $missing = array_diff(array_keys(self::THEMES), $categories->keys()->all());
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
                $hay = Str::lower(strip_tags($article->title.' '.$article->excerpt.' '.Str::limit((string) $article->body, 4000, '')));

                $scores = [];
                foreach (self::THEMES as $slug => $terms) {
                    $hits = 0;
                    foreach ($terms as $term) {
                        if (str_contains($hay, $term)) {
                            $hits++;
                        }
                    }
                    if ($hits >= $min) {
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

                if (! $dry) {
                    // Attach, never replace: the WordPress buckets stay as they are.
                    $article->categories()->syncWithoutDetaching(
                        $categories->only($picked)->pluck('id')->all()
                    );
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
