<?php

namespace App\Filament\Widgets;

use App\Enums\ContentStatus;
use App\Filament\Resources\Articles\ArticleResource;
use App\Models\Article;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Str;

class LatestSubmissions extends TableWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->can('ViewAny:Article') ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Latest content submissions')
            ->query(ArticleResource::getEloquentQuery()->with('author')->whereIn('status', ['pending_review', 'draft', 'rejected'])->latest('updated_at')->limit(8))
            ->paginated(false)
            ->columns([
                TextColumn::make('title')->limit(60),
                TextColumn::make('author.name')->label('Author'),
                TextColumn::make('status')->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof ContentStatus ? $state->label() : Str::headline((string) $state))
                    ->color(fn ($state) => $state instanceof ContentStatus ? $state->color() : 'gray'),
                TextColumn::make('updated_at')->since()->label('Updated'),
            ])
            ->recordUrl(fn (Article $record) => ArticleResource::getUrl('edit', ['record' => $record]));
    }
}
