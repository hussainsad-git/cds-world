<?php

namespace App\Filament\Resources\GalleryAlbums\Pages;

use App\Filament\Resources\GalleryAlbums\GalleryAlbumResource;
use App\Filament\Support\StampsUser;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditGalleryAlbum extends EditRecord
{
    use StampsUser;

    protected static string $resource = GalleryAlbumResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->stampUpdate($data);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('bulkUpload')->label('Bulk upload photos')->icon('heroicon-o-arrow-up-tray')->color('primary')
                ->modalHeading('Upload photos to this album')
                ->schema([
                    FileUpload::make('files')->label('Photos')->multiple()->image()->reorderable()->appendFiles()
                        ->disk('local')->directory('tmp/gallery-uploads')->visibility('private')
                        ->maxFiles(50)->maxSize(8192)->required()
                        ->helperText('JPG, PNG or WebP, up to 8 MB each, 50 at a time. Captions can be edited afterwards.'),
                ])
                ->action(function (array $data) {
                    $album = $this->getRecord();
                    $order = (int) $album->items()->max('sort_order');
                    $disk = Storage::disk('local');
                    $count = 0;

                    foreach ($data['files'] ?? [] as $path) {
                        if (! $disk->exists($path)) {
                            continue;
                        }
                        $item = $album->items()->create([
                            'type' => 'image',
                            'alt_text' => $album->title,
                            'sort_order' => ++$order,
                        ]);
                        $item->addMedia($disk->path($path))->toMediaCollection('file');
                        $count++;
                    }

                    Notification::make()->success()->title("{$count} photo(s) added")->send();
                    $this->fillForm();
                }),
            DeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
