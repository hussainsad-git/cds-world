<?php

namespace App\Filament\Resources\MediaCoverages;

use App\Enums\CategoryType;
use App\Enums\ContentStatus;
use App\Filament\Resources\MediaCoverages\Pages\CreateMediaCoverage;
use App\Filament\Resources\MediaCoverages\Pages\EditMediaCoverage;
use App\Filament\Resources\MediaCoverages\Pages\ListMediaCoverages;
use App\Filament\Support\PublishingFields;
use App\Filament\Support\SeoFields;
use App\Models\MediaCoverage;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
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

class MediaCoverageResource extends Resource
{
    protected static ?string $model = MediaCoverage::class;

    protected static ?string $slug = 'media-coverage';

    protected static ?string $modelLabel = 'media coverage';

    protected static ?string $pluralModelLabel = 'media coverage';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    protected static string|UnitEnum|null $navigationGroup = 'Media';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'headline';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Coverage')->columns(2)->schema([
                TextInput::make('headline')->required()->maxLength(255)->columnSpanFull(),
                Select::make('media_outlet_id')->label('Media outlet')->relationship('outlet', 'name')->searchable()->preload()->required()
                    ->createOptionForm([
                        TextInput::make('name')->required()->unique('media_outlets', 'name')->maxLength(255),
                        TextInput::make('website_url')->url()->maxLength(2048),
                    ]),
                DatePicker::make('published_on')->label('Date published by the outlet')->required()->maxDate(now()->addDay()),
                TextInput::make('source_url')->label('Link to the original story')->url()->required()->maxLength(2048)->columnSpanFull(),
                Textarea::make('summary')->rows(3)->maxLength(600)->columnSpanFull(),
                RichEditor::make('body')->label('About this coverage')->helperText('Shown on the item\'s own page. Leave empty for a card that links straight to the original story.')->columnSpanFull(),
                Repeater::make('clips')->label('Video clips')->helperText('Shown at the top of the item\'s page in place of the image. Paste a YouTube link, or the address of an uploaded video file.')
                    ->schema([
                        Select::make('type')->options(['youtube' => 'YouTube', 'file' => 'Video file (MP4)'])->default('youtube')->required(),
                        TextInput::make('url')->label('Link')->required()->maxLength(2048),
                    ])->columns(2)->defaultItems(0)->addActionLabel('Add a clip')->columnSpanFull(),
                SpatieMediaLibraryFileUpload::make('thumbnail')->collection('thumbnail')->image()->imageEditor()->columnSpanFull(),
                TextInput::make('thumb_alt')->label('Thumbnail alt text')->maxLength(255)->columnSpanFull(),
                Select::make('categories')->relationship('categories', 'name', fn (Builder $query) => $query->where('type', CategoryType::MediaCoverage->value)->where('is_active', true))->multiple()->preload(),
                Select::make('event_id')->label('Related event')->relationship('event', 'title')->searchable()->preload()->placeholder('None'),
            ]),
            Section::make('Publishing')->columns(2)->schema(PublishingFields::make()),
            Section::make('SEO')->collapsed()->schema([SeoFields::make()])->visible(fn () => auth()->user()?->can('manage_seo') ?? false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('published_on', 'desc')
            ->columns([
                SpatieMediaLibraryImageColumn::make('thumbnail')->collection('thumbnail')->conversion('thumb')->label(''),
                TextColumn::make('headline')->searchable()->limit(70)->description(fn (MediaCoverage $record) => $record->outlet?->name),
                TextColumn::make('published_on')->date('d M Y')->sortable()->label('Published'),
                TextColumn::make('status')->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof ContentStatus ? $state->label() : Str::headline((string) $state))
                    ->color(fn ($state) => $state instanceof ContentStatus ? $state->color() : 'gray'),
                IconColumn::make('is_featured')->boolean()->label('Featured'),
            ])
            ->filters([
                SelectFilter::make('media_outlet_id')->label('Outlet')->options(fn () => \App\Models\MediaOutlet::orderBy('name')->pluck('name', 'id')->all()),
                SelectFilter::make('status')->options(ContentStatus::options()),
                TrashedFilter::make(),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make(), RestoreBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMediaCoverages::route('/'),
            'create' => CreateMediaCoverage::route('/create'),
            'edit' => EditMediaCoverage::route('/{record}/edit'),
        ];
    }
}
