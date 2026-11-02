<?php

namespace App\Filament\Resources\MediaCoverages\Pages;

use App\Filament\Resources\MediaCoverages\MediaCoverageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMediaCoverages extends ListRecords
{
    protected static string $resource = MediaCoverageResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
