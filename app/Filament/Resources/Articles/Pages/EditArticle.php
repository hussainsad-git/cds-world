<?php

namespace App\Filament\Resources\Articles\Pages;

use App\Filament\Resources\Articles\ArticleResource;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditArticle extends EditRecord
{
    protected static string $resource = ArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ...ArticleResource::workflowActions(),
            ActionGroup::make([DeleteAction::make(), RestoreAction::make(), ForceDeleteAction::make()]),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = auth()->user();
        if (! $user->can('manage_all_articles')) {
            unset($data['author_id'], $data['is_featured'], $data['is_editors_choice']);
        }
        $data['updated_by'] = $user->id;

        return $data;
    }
}
