<?php

namespace App\Filament\Resources\Pages;

use App\Enums\ContentStatus;
use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Filament\Resources\Pages\Pages\EditPage;
use App\Filament\Resources\Pages\Pages\ListPages;
use App\Filament\Support\PublishingFields;
use App\Filament\Support\SeoFields;
use App\Models\Page;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use UnitEnum;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocument;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'title';

    public const SECTION_TYPES = [
        'hero' => 'Hero banner',
        'ticker' => 'News ticker',
        'richtext' => 'Rich text (headings, paragraphs, lists, tables, quotes, images, links)',
        'cta' => 'Call to action (buttons)',
        'image' => 'Image',
        'video' => 'Video (YouTube / Facebook)',
        'downloads' => 'Downloadable files',
        'research_focus' => 'Research focus cards',
        'latest_articles' => 'Latest articles (automatic)',
        'events' => 'Events (automatic)',
        'videos' => 'Videos (automatic)',
        'media_coverage' => 'Media coverage (automatic)',
        'gallery' => 'Gallery highlights (automatic)',
        'about' => 'About CDS introduction',
        'team' => 'Team members (automatic)',
        'newsletter' => 'Newsletter sign-up',
        'contact_form' => 'Contact form',
    ];

    public const TEMPLATES = [
        'default' => 'Standard page',
        'home' => 'Homepage',
        'about' => 'About',
        'team' => 'Team / people',
        'listing' => 'Listing introduction',
        'contact' => 'Contact',
    ];

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Page')->persistTabInQueryString()->columnSpanFull()->tabs([
                Tab::make('Page')->icon(Heroicon::OutlinedDocumentText)->columns(2)->schema([
                    TextInput::make('title')->required()->maxLength(255),
                    TextInput::make('slug')->maxLength(191)->alphaDash()->unique(ignoreRecord: true)
                        ->disabled(fn (?Page $record) => (bool) $record?->is_system)
                        ->helperText('Changing a live slug changes its URL; add a redirect if needed. Leave empty on create to generate it from the title.')
                        ->visibleOn('edit'),
                    Select::make('template')->options(self::TEMPLATES)->default('default')->required(),
                    Select::make('parent_id')->label('Parent page')->relationship('parent', 'title')->searchable()->preload()->placeholder('None'),
                    Textarea::make('excerpt')->rows(2)->maxLength(500)->columnSpanFull(),
                    SpatieMediaLibraryFileUpload::make('featured')->collection('featured')->image()->imageEditor()->label('Featured image')->columnSpanFull(),
                    TextInput::make('featured_alt')->label('Featured image alt text')->maxLength(255)->columnSpanFull(),
                    Toggle::make('show_in_nav')->label('Eligible for navigation menus'),
                ]),
                Tab::make('Sections')->icon(Heroicon::OutlinedQueueList)->schema([
                    Repeater::make('sections')->relationship('sections')->orderColumn('sort_order')->reorderable()->collapsible()->collapsed()->cloneable()
                        ->itemLabel(fn (array $state) => (self::SECTION_TYPES[$state['type'] ?? ''] ?? 'New section').(filled($state['heading'] ?? null) ? ' — '.Str::limit($state['heading'], 50) : '').(($state['is_visible'] ?? true) ? '' : '  (hidden)'))
                        ->addActionLabel('Add section')->defaultItems(0)->columns(2)->schema(self::sectionFields()),
                ]),
                Tab::make('Publishing')->icon(Heroicon::OutlinedRocketLaunch)->columns(2)->schema(PublishingFields::make(false)),
                Tab::make('SEO')->icon(Heroicon::OutlinedMagnifyingGlass)->schema([SeoFields::make()])
                    ->visible(fn () => auth()->user()?->can('manage_seo') ?? false),
            ]),
        ]);
    }

    /** @return array<int, \Filament\Schemas\Components\Component> */
    private static function sectionFields(): array
    {
        $is = fn (string ...$types) => fn (Get $get) => in_array($get('type'), $types, true);
        $auto = ['latest_articles', 'events', 'videos', 'media_coverage', 'gallery'];

        return [
            Select::make('type')->options(self::SECTION_TYPES)->required()->live()->columnSpan(1),
            Toggle::make('is_visible')->label('Visible')->default(true)->inline(false)->columnSpan(1),
            TextInput::make('heading')->label('Section heading')->maxLength(255)->columnSpanFull(),

            Group::make()->statePath('data')->columnSpanFull()->columns(2)->visible($is('hero'))->schema([
                Textarea::make('description')->rows(3)->columnSpanFull(),
                TextInput::make('cta_primary.label')->label('Primary button text'),
                TextInput::make('cta_primary.url')->label('Primary button link'),
                TextInput::make('cta_secondary.label')->label('Secondary button text'),
                TextInput::make('cta_secondary.url')->label('Secondary button link'),
                Select::make('background_type')->options(['image' => 'Image', 'video' => 'Video', 'color' => 'Solid colour'])->default('image'),
                FileUpload::make('background_image')->image()->disk('public')->directory('sections')->maxSize(4096),
                FileUpload::make('collage_images')->label('Banner photographs (up to 3)')->image()->multiple()->reorderable()->maxFiles(3)
                    ->disk('public')->directory('sections')->maxSize(4096)->columnSpanFull()
                    ->helperText('Shown beside the hero heading. Leave empty to use the newest gallery photographs automatically.'),
                TextInput::make('background_video_url')->url()->label('Background video URL (MP4)'),
                Toggle::make('show_stats')->label('Show counters (articles / media features / galleries)')->default(true),
            ]),
            Group::make()->statePath('data')->columnSpanFull()->visible($is('ticker'))->schema([
                TextInput::make('limit')->numeric()->default(6)->minValue(1)->maxValue(20)->label('Headlines to show'),
            ]),
            Group::make()->statePath('data')->columnSpanFull()->visible($is('richtext'))->schema([
                RichEditor::make('html')->label('Content')->fileAttachmentsDisk('public')->fileAttachmentsDirectory('page-content')->columnSpanFull(),
            ]),
            Group::make()->statePath('data')->columnSpanFull()->visible($is('cta'))->schema([
                Textarea::make('text')->rows(2),
                Repeater::make('buttons')->defaultItems(1)->columns(3)->schema([
                    TextInput::make('label')->required(),
                    TextInput::make('url')->required(),
                    Select::make('style')->options(['primary' => 'Primary', 'outline' => 'Outline'])->default('primary'),
                ]),
            ]),
            Group::make()->statePath('data')->columnSpanFull()->columns(2)->visible($is('image'))->schema([
                FileUpload::make('image')->image()->disk('public')->directory('sections')->maxSize(4096)->required(),
                TextInput::make('alt')->label('Alt text')->required()->maxLength(255),
                TextInput::make('caption')->maxLength(255),
            ]),
            Group::make()->statePath('data')->columnSpanFull()->columns(2)->visible($is('video'))->schema([
                Select::make('source')->options(['youtube' => 'YouTube', 'facebook' => 'Facebook'])->default('youtube')->required(),
                TextInput::make('url')->url()->required()->label('Video URL'),
                TextInput::make('caption')->columnSpanFull(),
            ]),
            Group::make()->statePath('data')->columnSpanFull()->visible($is('downloads'))->schema([
                Repeater::make('files')->columns(2)->defaultItems(0)->addActionLabel('Add file')->schema([
                    TextInput::make('title')->required(),
                    FileUpload::make('file')->disk('public')->directory('downloads')->acceptedFileTypes(['application/pdf'])->maxSize(20480)->required(),
                ]),
            ]),
            Group::make()->statePath('data')->columnSpanFull()->visible($is('research_focus'))->schema([
                Textarea::make('description')->rows(2),
                Repeater::make('items')->columns(3)->defaultItems(0)->addActionLabel('Add focus area')->schema([
                    TextInput::make('title')->required(),
                    TextInput::make('url')->label('Link'),
                    FileUpload::make('image')->label('Picture (optional)')->image()->disk('public')->directory('sections')->maxSize(2048)
                        ->helperText('Leave empty to show the matching icon.'),
                    Textarea::make('description')->rows(2)->columnSpanFull(),
                ]),
            ]),
            Group::make()->statePath('data')->columnSpanFull()->columns(2)->visible($is(...$auto))->schema([
                Textarea::make('description')->rows(2)->columnSpanFull(),
                TextInput::make('limit')->numeric()->default(6)->minValue(1)->maxValue(24),
                Toggle::make('featured_first')->label('Featured items first')->visible($is('latest_articles')),
            ]),
            Group::make()->statePath('data')->columnSpanFull()->visible($is('about'))->schema([
                Textarea::make('text')->rows(3),
                TextInput::make('link.label')->label('Link text'),
                TextInput::make('link.url')->label('Link URL'),
            ]),
            Group::make()->statePath('data')->columnSpanFull()->columns(2)->visible($is('team'))->schema([
                Textarea::make('description')->rows(2)->columnSpanFull(),
                CheckboxList::make('groups')->options(\App\Enums\TeamGroup::options())->columns(2),
                TextInput::make('limit')->numeric()->minValue(1)->maxValue(60)->helperText('Leave empty to show everyone.'),
                Toggle::make('home_only')->label('Only members marked “Show on homepage”'),
            ]),
            Group::make()->statePath('data')->columnSpanFull()->visible($is('newsletter', 'contact_form'))->schema([
                Textarea::make('description')->rows(2),
            ]),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('title')
            ->columns([
                TextColumn::make('title')->searchable()->sortable()->description(fn (Page $record) => '/'.($record->slug === 'home' ? '' : $record->slug)),
                TextColumn::make('template')->badge()->formatStateUsing(fn ($state) => self::TEMPLATES[$state] ?? $state),
                TextColumn::make('status')->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof ContentStatus ? $state->label() : Str::headline((string) $state))
                    ->color(fn ($state) => $state instanceof ContentStatus ? $state->color() : 'gray'),
                IconColumn::make('is_system')->boolean()->label('Core')->toggleable(),
                TextColumn::make('updated_at')->since()->sortable(),
            ])
            ->filters([SelectFilter::make('status')->options(ContentStatus::options()), SelectFilter::make('template')->options(self::TEMPLATES), TrashedFilter::make()])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->hidden(fn (Page $record) => $record->is_system)->modalDescription('The page is soft-deleted and can be restored from the Trashed filter.'),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make(), RestoreBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPages::route('/'),
            'create' => CreatePage::route('/create'),
            'edit' => EditPage::route('/{record}/edit'),
        ];
    }
}
