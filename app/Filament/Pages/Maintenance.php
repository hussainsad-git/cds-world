<?php

namespace App\Filament\Pages;

use App\Models\Article;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class Maintenance extends Page
{
    protected string $view = 'filament.pages.maintenance';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?string $navigationLabel = 'Backup & Maintenance';

    protected static ?string $title = 'Backup & Maintenance';

    protected static ?int $navigationSort = 6;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('manage_backups') ?? false;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('clearCaches')->label('Clear caches')->icon('heroicon-o-trash')->color('gray')->requiresConfirmation()
                ->action(function () {
                    Artisan::call('optimize:clear');
                    Notification::make()->success()->title('Caches cleared')->send();
                }),
            Action::make('rebuild')->label('Rebuild caches')->icon('heroicon-o-bolt')->requiresConfirmation()
                ->visible(fn () => app()->isProduction())
                ->action(function () {
                    Artisan::call('config:cache');
                    Artisan::call('route:cache');
                    Artisan::call('view:cache');
                    Artisan::call('event:cache');
                    Notification::make()->success()->title('Caches rebuilt')->send();
                }),
        ];
    }

    /** @return array<string, mixed> */
    protected function getViewData(): array
    {
        $path = storage_path();
        $free = @disk_free_space($path) ?: 0;
        $total = @disk_total_space($path) ?: 0;
        $failed = 0;
        try {
            $failed = DB::table('failed_jobs')->count();
            $pending = DB::table('jobs')->count();
        } catch (\Throwable) {
            $pending = 0;
        }

        return [
            'info' => [
                'Laravel' => app()->version(),
                'PHP' => PHP_VERSION,
                'Environment' => app()->environment(),
                'Debug mode' => config('app.debug') ? 'ON (disable in production)' : 'Off',
                'Disk free' => $total ? round($free / 1073741824, 1).' GB of '.round($total / 1073741824, 1).' GB ('.round(100 - $free / $total * 100).'% used)' : 'unknown',
                'Queued jobs' => $pending,
                'Failed jobs' => $failed,
                'Upload limit (PHP)' => ini_get('upload_max_filesize').' / post '.ini_get('post_max_size'),
            ],
        ];
    }
}
