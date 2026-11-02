<?php

namespace App\Filament\Widgets;

use App\Models\Article;
use App\Models\Event;
use App\Models\GalleryAlbum;
use App\Models\MediaCoverage;
use App\Models\NewsletterSubscriber;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ContentStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Total articles', Article::count()),
            Stat::make('Published', Article::where('status', 'published')->count()),
            Stat::make('Drafts', Article::where('status', 'draft')->count()),
            Stat::make('Pending review', Article::where('status', 'pending_review')->count()),
            Stat::make('Events', Event::count())->description(Event::where('starts_at', '>=', now())->count().' upcoming'),
            Stat::make('Media coverage', MediaCoverage::count()),
            Stat::make('Gallery albums', GalleryAlbum::count()),
            Stat::make('Uploaded files', Media::count()),
            Stat::make('Newsletter subscribers', NewsletterSubscriber::where('status', 'active')->count()),
        ];
    }
}
