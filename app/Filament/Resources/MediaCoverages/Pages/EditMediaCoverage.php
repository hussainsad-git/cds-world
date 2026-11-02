<?php

namespace App\Filament\Resources\MediaCoverages\Pages;

use App\Filament\Resources\MediaCoverages\MediaCoverageResource;
use App\Filament\Support\StampsUser;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditMediaCoverage extends EditRecord
{
    use StampsUser;

    protected static string $resource = MediaCoverageResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->stampUpdate($data);
    }

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make(), RestoreAction::make()];
    }
}
