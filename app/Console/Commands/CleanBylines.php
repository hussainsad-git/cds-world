<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Removes author names that leaked into article text during the WordPress import.
 *
 * On the old site authors typed their name as the first line of the post. WordPress
 * then built its automatic excerpt from that body, so the name runs straight into the
 * standfirst ("Nuzhat Nazar America's changing military footprint...") and repeats as
 * a stray first paragraph under a byline that already shows it.
 *
 * Only an exact match on the article's own author is touched. Other leading lines are
 * real subtitles and are left alone. Every change is written to a JSON log so it can
 * be reversed, and updated_at is preserved so articles do not all show "Updated today".
 */
class CleanBylines extends Command
{
    protected $signature = 'cds:clean-bylines {--dry-run : Report what would change without writing}';

    protected $description = 'Strip author names that leaked into excerpts and the top of article bodies';

    /** Ranks and honorifics that appear in display names but are optional in typed bylines. */
    private const TITLES = ['brig', 'r', 'retd', 'dr', 'gen', 'lt', 'col', 'maj', 'prof', 'mr', 'mrs', 'ms', 'engr', 'amb', 'ambassador'];

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $log = [];
        $excerpts = 0;
        $bodies = 0;
        $samples = [];

        Article::with('author')->whereNotNull('author_id')->chunkById(200, function ($articles) use ($dry, &$log, &$excerpts, &$bodies, &$samples) {
            foreach ($articles as $article) {
                $name = $this->nameTokens((string) $article->author?->name);

                // A one-word name ("CDS") is too easy to confuse with the opening word of a sentence.
                if (count($name) < 2) {
                    continue;
                }

                $body = (string) $article->body;
                $block = $this->leadingNameBlock($body, $name);
                $excerpt = (string) $article->excerpt;
                $cleanExcerpt = $this->stripName($excerpt, $name, $block !== null);

                $changes = [];
                if ($block !== null) {
                    $changes['body'] = ltrim(Str::replaceFirst($block, '', $body));
                    $bodies++;
                }
                if ($cleanExcerpt !== null) {
                    $changes['excerpt'] = $cleanExcerpt;
                    $excerpts++;
                }
                if (! $changes) {
                    continue;
                }

                if (count($samples) < 6 && $cleanExcerpt !== null) {
                    $samples[] = Str::limit($excerpt, 46, '...').'  =>  '.Str::limit($cleanExcerpt, 40, '...');
                }

                $log[] = ['id' => $article->id, 'slug' => $article->slug, 'old_excerpt' => $cleanExcerpt !== null ? $excerpt : null, 'removed_block' => $block];

                if (! $dry) {
                    // Query builder on purpose: no model events, and updated_at stays as it was.
                    DB::table('articles')->where('id', $article->id)->update($changes);
                }
            }
        });

        $this->line('excerpts cleaned:        '.$excerpts);
        $this->line('body name lines removed: '.$bodies);
        foreach ($samples as $s) {
            $this->line('  '.$s);
        }

        if (! $dry && $log) {
            $path = storage_path('app/byline-cleanup-'.now()->format('Ymd-His').'.json');
            file_put_contents($path, json_encode($log, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $this->line('change log: '.$path);
        }

        $this->info($dry ? 'Dry run - nothing written.' : 'Done.');

        return self::SUCCESS;
    }

    /** @return array<int, string> lower-case name words with ranks and honorifics removed */
    private function nameTokens(string $text): array
    {
        $text = Str::lower(str_replace("\u{00A0}", ' ', html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5)));
        $words = preg_split('/[^\p{L}]+/u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if (($words[0] ?? null) === 'by') {
            array_shift($words);
        }

        return array_values(array_filter($words, fn ($w) => ! in_array($w, self::TITLES, true)));
    }

    /** The first block of the body when it is nothing but the author's name, exactly as it appears in the HTML. */
    private function leadingNameBlock(string $body, array $name): ?string
    {
        $skip = '(?:<!--.*?-->\s*|<p[^>]*>(?:\s|&nbsp;|<br\s*/?>)*</p>\s*)*';
        if (! preg_match('~^\s*'.$skip.'(<(p|h[1-6])\b[^>]*>(.*?)</\2>)~isu', $body, $m)) {
            return null;
        }

        return $this->nameTokens($m[3]) === $name ? $m[1] : null;
    }

    /** The excerpt with a leading author name removed, or null when there is nothing to remove. */
    private function stripName(string $excerpt, array $name, bool $nameLeadsBody): ?string
    {
        $title = '(?:(?:'.implode('|', self::TITLES).')\.?|\((?:r|retd)\.?\))';
        $pattern = '~^\s*(?:by\s+)?(?:'.$title.'[\s.]*)*'.implode('[\s.\'-]+', array_map('preg_quote', $name)).'\b[\s:|,\x{2013}\x{2014}-]*~iu';

        if (! preg_match($pattern, $excerpt, $m)) {
            return null;
        }

        $rest = ltrim(mb_substr($excerpt, mb_strlen($m[0])));
        if ($rest === '') {
            return null;
        }

        // "Nuzhat Nazar writes that..." is a sentence about the author, not a leaked byline:
        // unless the body confirms the leak, only strip when a new sentence starts after the name.
        if (! $nameLeadsBody && ! preg_match('~^[\p{Lu}\d"\'\x{2018}\x{201C}(]~u', $rest)) {
            return null;
        }

        return $rest;
    }
}
