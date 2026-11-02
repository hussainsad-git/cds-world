<?php

namespace App\Filament\Resources\Events\Pages;

use App\Filament\Resources\Events\EventResource;
use App\Filament\Support\StampsUser;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditEvent extends EditRecord
{
    use StampsUser;

    protected static string $resource = EventResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->stampUpdate($data);
    }

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make(), RestoreAction::make()];
    }
}
