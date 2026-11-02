<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Articles\ArticleResource;
use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Events\EventResource;
use App\Filament\Resources\GalleryAlbums\GalleryAlbumResource;
use App\Filament\Resources\MediaCoverages\MediaCoverageResource;
use App\Filament\Resources\Pages\PageResource;
use App\Filament\Resources\Videos\VideoResource;
use App\Models\Article;
use App\Models\Category;
use App\Models\Event;
use App\Models\GalleryAlbum;
use App\Models\MediaCoverage;
use App\Models\Page;
use App\Models\Video;
use BackedEnum;
use Filament\Pages\Page as FilamentPage;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class SeoDashboard extends FilamentPage
{
    protected string $view = 'filament.pages.seo-dashboard';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|UnitEnum|null $navigationGroup = 'SEO';

    protected static ?string $navigationLabel = 'SEO Dashboard';

    protected static ?string $title = 'SEO Dashboard';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('manage_seo') ?? false;
    }

    /** @return array<string, array{model: class-string, resource: class-string, title: string, alt?: string}> */
    private function sources(): array
    {
        return [
            'Articles' => ['model' => Article::class, 'resource' => ArticleResource::class, 'title' => 'title', 'alt' => 'featured_alt', 'media' => 'featured'],
            'Pages' => ['model' => Page::class, 'resource' => PageResource::class, 'title' => 'title'],
            'Events' => ['model' => Event::class, 'resource' => EventResource::class, 'title' => 'title', 'alt' => 'banner_alt', 'media' => 'banner'],
            'Media coverage' => ['model' => MediaCoverage::class, 'resource' => MediaCoverageResource::class, 'title' => 'headline'],
            'Gallery albums' => ['model' => GalleryAlbum::class, 'resource' => GalleryAlbumResource::class, 'title' => 'title'],
            'Categories' => ['model' => Category::class, 'resource' => CategoryResource::class, 'title' => 'name'],
            'Videos' => ['model' => Video::class, 'resource' => VideoResource::class, 'title' => 'title'],
        ];
    }

    /** @return array<string, mixed> */
    protected function getViewData(): array
    {
        $summary = [];
        $issues = [];

        foreach ($this->sources() as $label => $src) {
            /** @var \Illuminate\Database\Eloquent\Model $model */
            $model = $src['model'];
            $records = $model::query()->with('seo')->get();

            $row = ['label' => $label, 'total' => $records->count(), 'no_title' => 0, 'no_desc' => 0, 'long_desc' => 0, 'noindex' => 0, 'no_alt' => 0];

            foreach ($records as $r) {
                $seo = $r->seo;
                $problems = [];
                if (blank($seo?->title)) { $row['no_title']++; $problems[] = 'No SEO title'; }
                if (blank($seo?->description)) { $row['no_desc']++; $problems[] = 'No meta description'; }
                elseif (mb_strlen($seo->description) > 160) { $row['long_desc']++; $problems[] = 'Meta description over 160 characters'; }
                if ($seo && ! $seo->robots_index) { $row['noindex']++; $problems[] = 'Set to noindex'; }
                if (isset($src['alt']) && $r->getFirstMedia($src['media']) && blank($r->{$src['alt']})) { $row['no_alt']++; $problems[] = 'Image without alt text'; }

                if ($problems && count($issues) < 60) {
                    $issues[] = [
                        'type' => $label,
                        'title' => (string) $r->{$src['title']},
                        'problems' => $problems,
                        'url' => $src['resource']::getUrl('edit', ['record' => $r]),
                    ];
                }
            }
            $summary[] = $row;
        }

        return ['summary' => $summary, 'issues' => $issues];
    }
}
