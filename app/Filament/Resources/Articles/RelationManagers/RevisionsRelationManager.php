<?php

namespace App\Filament\Resources\Articles\RelationManagers;

use App\Filament\Resources\Articles\ArticleResource;
use App\Models\ArticleRevision;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class RevisionsRelationManager extends RelationManager
{
    protected static string $relationship = 'revisions';

    protected static ?string $title = 'Revision history';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        $user = auth()->user();

        return (bool) ($user && ($user->can('manage_all_articles') || $user->can('view', $ownerRecord)));
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->defaultSort('version', 'desc')
            ->columns([
                TextColumn::make('version')->label('#')->sortable(),
                TextColumn::make('title')->limit(60),
                TextColumn::make('user.name')->label('Edited by')->placeholder('System'),
                TextColumn::make('created_at')->dateTime('d M Y H:i')->label('Saved'),
            ])
            ->recordActions([
                Action::make('restore')
                    ->label('Restore this version')->icon('heroicon-o-arrow-uturn-left')->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn () => auth()->user()?->can('update', $this->getOwnerRecord()) ?? false)
                    ->action(function (ArticleRevision $record) {
                        $article = $this->getOwnerRecord();
                        $article->update([
                            'title' => $record->title, 'subtitle' => $record->subtitle,
                            'excerpt' => $record->excerpt, 'body' => $record->body, 'updated_by' => auth()->id(),
                        ]);
                        Notification::make()->title("Restored revision #{$record->version}")->success()->send();

                        return redirect(ArticleResource::getUrl('edit', ['record' => $article]));
                    }),
            ]);
    }
}
