<?php

namespace App\Filament\Resources\MediaOutlets\Pages;

use App\Filament\Resources\MediaOutlets\MediaOutletResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMediaOutlet extends EditRecord
{
    protected static string $resource = MediaOutletResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
