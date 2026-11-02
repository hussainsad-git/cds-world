<?php

namespace App\Filament\Resources\MediaOutlets\Pages;

use App\Filament\Resources\MediaOutlets\MediaOutletResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMediaOutlets extends ListRecords
{
    protected static string $resource = MediaOutletResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
