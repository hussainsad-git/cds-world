<?php

namespace App\Filament\Resources\Popups\Pages;

use App\Filament\Resources\Popups\PopupResource;
use App\Filament\Support\StampsUser;
use Filament\Resources\Pages\CreateRecord;

class CreatePopup extends CreateRecord
{
    use StampsUser;

    protected static string $resource = PopupResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->stampCreate($data);
    }
}
