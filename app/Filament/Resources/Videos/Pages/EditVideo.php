<?php

namespace App\Filament\Resources\Videos\Pages;

use App\Filament\Resources\Videos\VideoResource;
use App\Filament\Support\StampsUser;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditVideo extends EditRecord
{
    use StampsUser;

    protected static string $resource = VideoResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->stampUpdate($data);
    }

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make(), RestoreAction::make()];
    }
}
