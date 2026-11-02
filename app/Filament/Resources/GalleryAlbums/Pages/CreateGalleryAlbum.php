<?php

namespace App\Filament\Resources\GalleryAlbums\Pages;

use App\Filament\Resources\GalleryAlbums\GalleryAlbumResource;
use App\Filament\Support\StampsUser;
use Filament\Resources\Pages\CreateRecord;

class CreateGalleryAlbum extends CreateRecord
{
    use StampsUser;

    protected static string $resource = GalleryAlbumResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->stampCreate($data);
    }
}
