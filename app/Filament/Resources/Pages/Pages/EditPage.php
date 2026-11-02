<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Resources\Pages\PageResource;
use App\Filament\Support\StampsUser;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditPage extends EditRecord
{
    use StampsUser;

    protected static string $resource = PageResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->stampUpdate($data);
    }

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()->hidden(fn () => $this->getRecord()->is_system), RestoreAction::make()];
    }
}
