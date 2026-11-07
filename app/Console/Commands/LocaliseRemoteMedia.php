<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Event;
use App\Models\PageSection;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Copies images that rich-text content still loads from the old WordPress site into
 * this site's own storage and rewrites the references, so the content survives the
 * source site being retired.
 */
class LocaliseRemoteMedia extends Command
{
    protected $signature = 'cds:localise-media
        {--host=centrefordevelopmentandstability.com : The remote host to pull images away from}
        {--dry-run : Report what would change without downloading or saving}';

    protected $description = 'Re-host remote images referenced in article and event content, and rewrite their URLs';

    /** @var array<string, string> remote URL => local public path */
    private array $map = [];

    private int $downloaded = 0;

    private int $failed = 0;

    public function handle(): int
    {
        $host = (string) $this->option('host');
        $dry = (bool) $this->option('dry-run');
        $pattern = '~https?://(?:www\.)?'.preg_quote($host, '~').'/wp-content/uploads/[^\s"\'<>)\\\\]+~i';

        $targets = [
            ['label' => 'articles', 'rows' => Article::query()->select('id', 'body')->cursor(), 'field' => 'body'],
            ['label' => 'events', 'rows' => Event::query()->select('id', 'description')->cursor(), 'field' => 'description'],
        ];

        $changed = 0;

        foreach ($targets as $target) {
            $this->components->info('Scanning '.$target['label']);

            foreach ($target['rows'] as $row) {
                $field = $target['field'];
                $html = (string) $row->{$field};
                if ($html === '' || ! preg_match_all($pattern, $html, $m)) {
                    continue;
                }

                $urls = array_unique($m[0]);
                $replaced = $html;

                foreach ($urls as $url) {
                    $local = $dry ? '/storage/inline/(dry-run)' : $this->localise($url);
                    if ($local === null) {
                        continue;
                    }
                    $replaced = str_replace($url, $local, $replaced);
                }

                if ($replaced === $html) {
                    continue;
                }

                $changed++;
                if (! $dry) {
                    $row->{$field} = $replaced;
                    $row->saveQuietly();
                }
            }
        }

        // Rich-text page sections keep their HTML inside the JSON data column.
        foreach (PageSection::query()->cursor() as $section) {
            $data = $section->data ?? [];
            $html = (string) ($data['html'] ?? $data['body'] ?? '');
            if ($html === '' || ! preg_match_all($pattern, $html, $m)) {
                continue;
            }

            $replaced = $html;
            foreach (array_unique($m[0]) as $url) {
                $local = $dry ? '/storage/inline/(dry-run)' : $this->localise($url);
                if ($local !== null) {
                    $replaced = str_replace($url, $local, $replaced);
                }
            }

            if ($replaced !== $html && ! $dry) {
                $key = isset($data['html']) ? 'html' : 'body';
                $data[$key] = $replaced;
                $section->data = $data;
                $section->saveQuietly();
                $changed++;
            }
        }

        \Illuminate\Support\Facades\Cache::flush();

        $this->newLine();
        $this->components->info(sprintf(
            '%s: %d records %s, %d images downloaded, %d unreachable.',
            $dry ? 'Dry run' : 'Done',
            $changed,
            $dry ? 'would change' : 'rewritten',
            $this->downloaded,
            $this->failed,
        ));

        return self::SUCCESS;
    }

    /** Download once per distinct URL and return the local path, or null if it could not be fetched. */
    private function localise(string $url): ?string
    {
        if (isset($this->map[$url])) {
            return $this->map[$url];
        }

        $path = parse_url($url, PHP_URL_PATH) ?: '';
        $ext = Str::lower(pathinfo($path, PATHINFO_EXTENSION));
        if (! in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'], true)) {
            $ext = 'jpg';
        }

        $name = substr(sha1($url), 0, 16).'.'.$ext;
        $disk = Storage::disk('public');
        $target = 'inline/'.$name;
        $public = '/storage/'.$target;

        if ($disk->exists($target)) {
            return $this->map[$url] = $public;
        }

        try {
            $res = Http::timeout(45)->retry(2, 400, throw: false)
                ->withUserAgent('CDS-CMS-Importer/1.0')
                ->get($url);

            if (! $res->successful() || $res->body() === '') {
                throw new \RuntimeException('HTTP '.$res->status());
            }

            $disk->put($target, $res->body());
            $this->downloaded++;

            if ($this->downloaded % 50 === 0) {
                $this->line('  '.$this->downloaded.' images copied');
            }

            return $this->map[$url] = $public;
        } catch (Throwable $e) {
            $this->failed++;
            $this->components->warn('  could not fetch '.Str::limit($url, 80).' — '.Str::limit($e->getMessage(), 60));

            return null;
        }
    }
}
