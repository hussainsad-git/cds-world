<?php

namespace App\Filament\Widgets;

use App\Models\Article;
use Filament\Widgets\ChartWidget;

class ArticlesPerMonthChart extends ChartWidget
{
    protected ?string $heading = 'Articles published per month';

    protected static ?int $sort = 2;

    protected ?string $maxHeight = '260px';

    public static function canView(): bool
    {
        return auth()->user()?->can('manage_all_articles') ?? false;
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $labels = [];
        $values = [];
        for ($i = 5; $i >= 0; $i--) {
            $start = now()->subMonths($i)->startOfMonth();
            $labels[] = $start->format('M Y');
            $values[] = Article::query()->where('status', 'published')->whereBetween('published_at', [$start, $start->copy()->endOfMonth()])->count();
        }

        return ['datasets' => [['label' => 'Published', 'data' => $values]], 'labels' => $labels];
    }
}
