<?php

namespace App\Filament\Resources\Videos;

use App\Enums\CategoryType;
use App\Enums\ContentStatus;
use App\Enums\VideoSource;
use App\Filament\Resources\Videos\Pages\CreateVideo;
use App\Filament\Resources\Videos\Pages\EditVideo;
use App\Filament\Resources\Videos\Pages\ListVideos;
use App\Filament\Support\PublishingFields;
use App\Filament\Support\SeoFields;
use App\Models\Video;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreBulkAction;
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

class VideoResource extends Resource
{
    protected static ?string $model = Video::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedVideoCamera;

    protected static string|UnitEnum|null $navigationGroup = 'Media';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'title';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Video')->columns(2)->schema([
                TextInput::make('title')->required()->maxLength(255)->columnSpanFull(),
                Textarea::make('description')->rows(4)->columnSpanFull(),
                Select::make('source')->label('Video source')->options(VideoSource::options())->default(VideoSource::YouTube->value)->required()->live(),
                TextInput::make('video_url')->label('Video URL')->url()->maxLength(2048)
                    ->visible(fn (Get $get) => in_array($get('source'), ['youtube', 'facebook'], true))
                    ->required(fn (Get $get) => in_array($get('source'), ['youtube', 'facebook'], true))
                    ->helperText('Paste the public YouTube or Facebook video link (watch, youtu.be, embed or fb.watch).'),
                SpatieMediaLibraryFileUpload::make('video_file')->collection('video_file')->label('Video file (MP4 / WebM, up to 200 MB)')
                    ->acceptedFileTypes(['video/mp4', 'video/webm'])->maxSize(204800)
                    ->visible(fn (Get $get) => $get('source') === 'upload')->columnSpanFull()
                    ->helperText('Large files use disk space on the server. YouTube links are recommended for long recordings.'),
                SpatieMediaLibraryFileUpload::make('thumbnail')->collection('thumbnail')->image()->imageEditor()->columnSpanFull(),
                TextInput::make('thumb_alt')->label('Thumbnail alt text')->maxLength(255)->columnSpanFull(),
                TextInput::make('presenter')->label('Presenter / interviewee')->maxLength(255),
                Select::make('author_id')->label('Author')->relationship('author', 'name')->searchable()->preload()->default(fn () => auth()->id()),
                TextInput::make('duration_seconds')->numeric()->minValue(0)->label('Duration (seconds)'),
                Select::make('event_id')->label('Related event')->relationship('event', 'title')->searchable()->preload()->placeholder('None'),
                Select::make('categories')->relationship('categories', 'name', fn (Builder $query) => $query->where('type', CategoryType::Video->value)->where('is_active', true))->multiple()->preload(),
                Select::make('tags')->relationship('tags', 'name')->multiple()->preload()->searchable()
                    ->createOptionForm([TextInput::make('name')->required()->maxLength(80)]),
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
                SpatieMediaLibraryImageColumn::make('thumbnail')->collection('thumbnail')->conversion('thumb')->label(''),
                TextColumn::make('title')->searchable()->limit(60)->description(fn (Video $record) => $record->presenter),
                TextColumn::make('source')->badge()->formatStateUsing(fn ($state) => $state instanceof VideoSource ? $state->label() : Str::headline((string) $state)),
                TextColumn::make('published_at')->dateTime('d M Y')->sortable()->label('Published'),
                TextColumn::make('status')->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof ContentStatus ? $state->label() : Str::headline((string) $state))
                    ->color(fn ($state) => $state instanceof ContentStatus ? $state->color() : 'gray'),
                IconColumn::make('is_featured')->boolean()->label('Featured'),
            ])
            ->filters([
                SelectFilter::make('source')->options(VideoSource::options()),
                SelectFilter::make('status')->options(ContentStatus::options()),
                TrashedFilter::make(),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make(), RestoreBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVideos::route('/'),
            'create' => CreateVideo::route('/create'),
            'edit' => EditVideo::route('/{record}/edit'),
        ];
    }
}
