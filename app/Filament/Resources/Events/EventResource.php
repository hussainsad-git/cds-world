<?php

namespace App\Filament\Resources\Events;

use App\Filament\Resources\Events\Pages\CreateEvent;
use App\Filament\Resources\Events\Pages\EditEvent;
use App\Filament\Resources\Events\Pages\ListEvents;
use App\Filament\Resources\Events\RelationManagers\RegistrationsRelationManager;
use App\Filament\Support\PublishingFields;
use App\Filament\Support\SeoFields;
use App\Enums\CategoryType;
use App\Enums\ContentStatus;
use App\Enums\VideoSource;
use App\Models\Event;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use UnitEnum;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'title';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Event')->persistTabInQueryString()->columnSpanFull()->tabs([
                Tab::make('Details')->icon(Heroicon::OutlinedDocumentText)->columns(2)->schema([
                    TextInput::make('title')->required()->maxLength(255)->columnSpanFull(),
                    Textarea::make('summary')->rows(3)->maxLength(500)->columnSpanFull(),
                    RichEditor::make('description')->columnSpanFull(),
                    DateTimePicker::make('starts_at')->required()->seconds(false),
                    DateTimePicker::make('ends_at')->seconds(false)->after('starts_at'),
                    Toggle::make('is_cancelled')->label('Cancelled')->helperText('Overrides the automatic Upcoming / Ongoing / Completed status.'),
                ]),
                Tab::make('Location & registration')->icon(Heroicon::OutlinedMapPin)->columns(2)->schema([
                    TextInput::make('venue')->maxLength(255),
                    TextInput::make('address')->maxLength(255),
                    TextInput::make('map_url')->label('Google Maps link')->url()->maxLength(2048)->columnSpanFull(),
                    TextInput::make('registration_url')->label('External registration URL')->url()->maxLength(2048)->columnSpanFull(),
                    Toggle::make('internal_registration')->label('Accept registrations on this website')->helperText('Shows the built-in registration form; sign-ups appear under Registrations.'),
                    TextInput::make('contact_email')->email()->maxLength(255),
                    TextInput::make('contact_phone')->maxLength(40),
                ]),
                Tab::make('Media')->icon(Heroicon::OutlinedPhoto)->columns(2)->schema([
                    SpatieMediaLibraryFileUpload::make('banner')->collection('banner')->image()->imageEditor()->columnSpanFull(),
                    SpatieMediaLibraryFileUpload::make('gallery')->collection('gallery')->multiple()->reorderable()->image()->maxFiles(30)->maxSize(8192)->columnSpanFull(),
                    SpatieMediaLibraryFileUpload::make('attachments')->collection('attachments')->multiple()->reorderable()->acceptedFileTypes(['application/pdf'])->maxSize(20480)->label('PDF attachments')->columnSpanFull(),
                    Select::make('video_source')->options([VideoSource::YouTube->value => 'YouTube', VideoSource::Facebook->value => 'Facebook'])->placeholder('No video')->live(),
                    TextInput::make('video_url')->label('Recorded video URL')->url()->maxLength(2048)->visible(fn ($get) => filled($get('video_source'))),
                    TextInput::make('livestream_url')->label('Livestream URL')->url()->maxLength(2048)->columnSpanFull(),
                ]),
                Tab::make('Speakers & guests')->icon(Heroicon::OutlinedMicrophone)->schema([
                    Repeater::make('speakers')->relationship('speakers')->orderColumn('sort_order')->reorderable()->collapsible()->collapsed()
                        ->itemLabel(fn (array $state) => ($state['name'] ?? 'New speaker').(filled($state['role'] ?? null) ? ' · '.Str::headline($state['role']) : ''))
                        ->addActionLabel('Add speaker or guest')->defaultItems(0)->columns(2)->schema([
                            TextInput::make('name')->required()->maxLength(255),
                            Select::make('role')->options(['speaker' => 'Speaker', 'guest' => 'Guest', 'moderator' => 'Moderator', 'chair' => 'Chair', 'participant' => 'Participant'])->default('speaker')->required(),
                            TextInput::make('designation')->maxLength(255),
                            TextInput::make('organization')->maxLength(255),
                            Select::make('team_member_id')->label('Link to team member')->relationship('teamMember', 'name')->searchable()->preload()->placeholder('Not a team member'),
                            SpatieMediaLibraryFileUpload::make('photo')->collection('photo')->image(),
                            Textarea::make('bio')->rows(3)->columnSpanFull(),
                        ]),
                ]),
                Tab::make('Classification')->icon(Heroicon::OutlinedTag)->columns(2)->schema([
                    Select::make('categories')->relationship('categories', 'name', fn (Builder $query) => $query->where('type', CategoryType::Event->value)->where('is_active', true))->multiple()->preload()->searchable(),
                    Select::make('tags')->relationship('tags', 'name')->multiple()->preload()->searchable()
                        ->createOptionForm([TextInput::make('name')->required()->maxLength(80)]),
                ]),
                Tab::make('Publishing')->icon(Heroicon::OutlinedRocketLaunch)->columns(2)->schema(PublishingFields::make()),
                Tab::make('SEO')->icon(Heroicon::OutlinedMagnifyingGlass)->schema([SeoFields::make()])
                    ->visible(fn () => auth()->user()?->can('manage_seo') ?? false),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('starts_at', 'desc')
            ->columns([
                SpatieMediaLibraryImageColumn::make('banner')->collection('banner')->conversion('thumb')->label(''),
                TextColumn::make('title')->searchable()->sortable()->limit(60)->description(fn (Event $record) => $record->venue),
                TextColumn::make('starts_at')->dateTime('d M Y, H:i')->sortable()->label('Starts'),
                TextColumn::make('lifecycle')->label('Event status')->badge()->state(fn (Event $record) => Str::headline($record->lifecycle()))
                    ->color(fn (string $state) => match ($state) { 'Upcoming' => 'info', 'Ongoing' => 'success', 'Cancelled' => 'danger', default => 'gray' }),
                TextColumn::make('status')->label('Publication')->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof ContentStatus ? $state->label() : Str::headline((string) $state))
                    ->color(fn ($state) => $state instanceof ContentStatus ? $state->color() : 'gray'),
                IconColumn::make('is_featured')->boolean()->label('Featured'),
                TextColumn::make('registrations_count')->counts('registrations')->label('Registered')->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(ContentStatus::options()),
                Filter::make('upcoming')->label('Upcoming only')->query(fn (Builder $query) => $query->where('starts_at', '>=', now())->where('is_cancelled', false)),
                Filter::make('past')->label('Past only')->query(fn (Builder $query) => $query->where('starts_at', '<', now())),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                ActionGroup::make([
                    ReplicateAction::make()->label('Duplicate')
                        ->excludeAttributes(['slug', 'published_at', 'created_by', 'updated_by', 'registrations_count'])
                        ->beforeReplicaSaved(function (Event $replica) {
                            $replica->title = $replica->title.' (copy)';
                            $replica->status = ContentStatus::Draft;
                            $replica->is_featured = false;
                            $replica->created_by = auth()->id();
                        })
                        ->successRedirectUrl(fn (Event $replica) => static::getUrl('edit', ['record' => $replica])),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make(), RestoreBulkAction::make()])]);
    }

    public static function getRelations(): array
    {
        return [RegistrationsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEvents::route('/'),
            'create' => CreateEvent::route('/create'),
            'edit' => EditEvent::route('/{record}/edit'),
        ];
    }
}
