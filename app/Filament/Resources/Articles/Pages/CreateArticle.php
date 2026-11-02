<?php

namespace App\Filament\Resources\Articles\Pages;

use App\Enums\ContentStatus;
use App\Filament\Resources\Articles\ArticleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateArticle extends CreateRecord
{
    protected static string $resource = ArticleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        if (! $user->can('manage_all_articles') || empty($data['author_id'])) {
            $data['author_id'] = $user->id;
        }
        $data['status'] = ContentStatus::Draft;
        $data['created_by'] = $user->id;
        $data['updated_by'] = $user->id;

        return $data;
    }
}
