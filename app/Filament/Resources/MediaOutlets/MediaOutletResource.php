<?php

namespace App\Filament\Resources\MediaOutlets;

use App\Filament\Resources\MediaOutlets\Pages\CreateMediaOutlet;
use App\Filament\Resources\MediaOutlets\Pages\EditMediaOutlet;
use App\Filament\Resources\MediaOutlets\Pages\ListMediaOutlets;
use App\Models\MediaOutlet;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class MediaOutletResource extends Resource
{
    protected static ?string $model = MediaOutlet::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;

    protected static string|UnitEnum|null $navigationGroup = 'Media';

    protected static ?string $navigationLabel = 'Media Outlets';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->unique(ignoreRecord: true)->maxLength(255),
            TextInput::make('website_url')->url()->maxLength(2048),
            SpatieMediaLibraryFileUpload::make('logo')->collection('logo')->image(),
            Toggle::make('is_active')->default(true)->label('Active'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                SpatieMediaLibraryImageColumn::make('logo')->collection('logo')->conversion('thumb')->label(''),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('coverage_count')->counts('coverage')->label('Coverage items'),
                IconColumn::make('is_active')->boolean()->label('Active'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->before(function (DeleteAction $action, MediaOutlet $record) {
                    if ($record->coverage()->exists()) {
                        Notification::make()->danger()->title('Outlet is in use')->body('Reassign or delete its media coverage first.')->send();
                        $action->cancel();
                    }
                }),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMediaOutlets::route('/'),
            'create' => CreateMediaOutlet::route('/create'),
            'edit' => EditMediaOutlet::route('/{record}/edit'),
        ];
    }
}
