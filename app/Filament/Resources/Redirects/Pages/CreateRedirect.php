<?php

namespace App\Filament\Resources\Redirects\Pages;

use App\Filament\Resources\Redirects\RedirectResource;
use App\Filament\Support\StampsUser;
use Filament\Resources\Pages\CreateRecord;

class CreateRedirect extends CreateRecord
{
    use StampsUser;

    protected static string $resource = RedirectResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->stampCreate($data);
    }
}
