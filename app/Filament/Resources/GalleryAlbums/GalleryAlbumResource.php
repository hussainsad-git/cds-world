<?php

namespace App\Filament\Resources\GalleryAlbums;

use App\Enums\CategoryType;
use App\Enums\ContentStatus;
use App\Filament\Resources\GalleryAlbums\Pages\CreateGalleryAlbum;
use App\Filament\Resources\GalleryAlbums\Pages\EditGalleryAlbum;
use App\Filament\Resources\GalleryAlbums\Pages\ListGalleryAlbums;
use App\Filament\Support\PublishingFields;
use App\Filament\Support\SeoFields;
use App\Models\GalleryAlbum;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use UnitEnum;

class GalleryAlbumResource extends Resource
{
    protected static ?string $model = GalleryAlbum::class;

    protected static ?string $modelLabel = 'album';

    protected static ?string $navigationLabel = 'Gallery';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|UnitEnum|null $navigationGroup = 'Media';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'title';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Album')->columns(2)->schema([
                TextInput::make('title')->required()->maxLength(255)->columnSpanFull(),
                Textarea::make('description')->rows(3)->columnSpanFull(),
                SpatieMediaLibraryFileUpload::make('cover')->collection('cover')->image()->imageEditor()->label('Cover image (optional, first image is used if empty)')->columnSpanFull(),
                TextInput::make('cover_alt')->label('Cover alt text')->maxLength(255)->columnSpanFull(),
                Select::make('event_id')->label('Event album for')->relationship('event', 'title')->searchable()->preload()->placeholder('Not linked to an event'),
                Select::make('categories')->relationship('categories', 'name', fn (Builder $query) => $query->where('type', CategoryType::Gallery->value)->where('is_active', true))->multiple()->preload(),
                TextInput::make('sort_order')->numeric()->default(0),
            ]),
            Section::make('Photos & videos')->description('Use “Bulk upload” on the top right to add many photos at once, then edit captions here. Drag items to reorder.')
                ->visibleOn('edit')->schema([
                    Repeater::make('items')->relationship('items')->orderColumn('sort_order')->reorderable()->collapsible()->collapsed()
                        ->itemLabel(fn (array $state) => $state['title'] ?? ($state['caption'] ?? null) ?: 'Item')
                        ->addActionLabel('Add single item')->defaultItems(0)->columns(2)->schema([
                            Select::make('type')->options(['image' => 'Image', 'video' => 'Video'])->default('image')->required()->live(),
                            TextInput::make('title')->maxLength(255),
                            SpatieMediaLibraryFileUpload::make('file')->collection('file')->columnSpanFull()
                                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'video/mp4', 'video/webm'])->maxSize(204800)
                                ->visible(fn (Get $get) => $get('type') === 'image' || filled($get('video_source')) === false),
                            Select::make('video_source')->options(['youtube' => 'YouTube', 'facebook' => 'Facebook'])->placeholder('Uploaded file')
                                ->visible(fn (Get $get) => $get('type') === 'video')->live(),
                            TextInput::make('video_url')->url()->maxLength(2048)->visible(fn (Get $get) => $get('type') === 'video' && filled($get('video_source'))),
                            TextInput::make('caption')->maxLength(255)->columnSpanFull(),
                            TextInput::make('alt_text')->label('Alt text (for SEO & accessibility)')->maxLength(255)->columnSpanFull(),
                        ]),
                ]),
            Section::make('Publishing')->columns(2)->schema(PublishingFields::make()),
            Section::make('SEO')->collapsed()->schema([SeoFields::make()])->visible(fn () => auth()->user()?->can('manage_seo') ?? false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('published_at', 'desc')
            ->columns([
                SpatieMediaLibraryImageColumn::make('cover')->collection('cover')->conversion('thumb')->label(''),
                TextColumn::make('title')->searchable()->limit(60)->description(fn (GalleryAlbum $record) => $record->event?->title),
                TextColumn::make('items_count')->counts('items')->label('Items'),
                TextColumn::make('status')->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof ContentStatus ? $state->label() : Str::headline((string) $state))
                    ->color(fn ($state) => $state instanceof ContentStatus ? $state->color() : 'gray'),
                IconColumn::make('is_featured')->boolean()->label('Featured'),
            ])
            ->filters([SelectFilter::make('status')->options(ContentStatus::options()), TrashedFilter::make()])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make(), RestoreBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGalleryAlbums::route('/'),
            'create' => CreateGalleryAlbum::route('/create'),
            'edit' => EditGalleryAlbum::route('/{record}/edit'),
        ];
    }
}
