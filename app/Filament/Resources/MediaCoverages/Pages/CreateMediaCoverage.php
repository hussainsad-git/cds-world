<?php

namespace App\Filament\Resources\MediaCoverages\Pages;

use App\Filament\Resources\MediaCoverages\MediaCoverageResource;
use App\Filament\Support\StampsUser;
use Filament\Resources\Pages\CreateRecord;

class CreateMediaCoverage extends CreateRecord
{
    use StampsUser;

    protected static string $resource = MediaCoverageResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->stampCreate($data);
    }
}
