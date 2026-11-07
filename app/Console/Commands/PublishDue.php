<?php

namespace App\Console\Commands;

use App\Enums\ContentStatus;
use App\Models\Article;
use App\Models\Event;
use App\Models\GalleryAlbum;
use App\Models\MediaCoverage;
use App\Models\Page;
use App\Models\Video;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Promotes content whose scheduled publication time has passed.
 *
 * Public pages already treat "scheduled and due" as live, so this does not gate
 * visibility — it keeps the stored status honest so the admin lists, dashboard
 * counts and activity log reflect what is actually published.
 */
class PublishDue extends Command
{
    protected $signature = 'cds:publish-due';

    protected $description = 'Mark scheduled articles, events, videos, coverage, albums and pages as published once their time has passed';

    public function handle(): int
    {
        $models = [
            'articles' => Article::class,
            'events' => Event::class,
            'videos' => Video::class,
            'media coverage' => MediaCoverage::class,
            'gallery albums' => GalleryAlbum::class,
            'pages' => Page::class,
        ];

        $total = 0;

        foreach ($models as $label => $model) {
            $count = $model::query()
                ->where('status', ContentStatus::Scheduled->value)
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->update([
                    'status' => ContentStatus::Published->value,
                    'updated_at' => now(),
                ]);

            if ($count > 0) {
                $this->line("  published {$count} {$label}");
                $total += $count;
            }
        }

        if ($total > 0) {
            Cache::flush();
            $this->components->info("Published {$total} scheduled item(s).");
        }

        return self::SUCCESS;
    }
}
