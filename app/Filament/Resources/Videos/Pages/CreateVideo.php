<?php

namespace App\Filament\Resources\Videos\Pages;

use App\Filament\Resources\Videos\VideoResource;
use App\Filament\Support\StampsUser;
use Filament\Resources\Pages\CreateRecord;

class CreateVideo extends CreateRecord
{
    use StampsUser;

    protected static string $resource = VideoResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->stampCreate($data);
    }
}
