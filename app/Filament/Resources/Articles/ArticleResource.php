<?php

namespace App\Filament\Resources\Articles;

use App\Enums\CategoryType;
use App\Enums\ContentStatus;
use App\Enums\VideoSource;
use App\Filament\Resources\Articles\Concerns\HasArticleWorkflow;
use App\Filament\Resources\Articles\Pages\CreateArticle;
use App\Filament\Resources\Articles\Pages\EditArticle;
use App\Filament\Resources\Articles\Pages\ListArticles;
use App\Filament\Resources\Articles\RelationManagers\RevisionsRelationManager;
use App\Models\Article;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Group;
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

class ArticleResource extends Resource
{
    use HasArticleWorkflow;

    protected static ?string $model = Article::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'title';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
        $user = auth()->user();

        // Writers only ever see their own articles.
        if ($user && ! $user->can('manage_all_articles')) {
            $query->where('author_id', $user->id);
        }

        return $query;
    }

    public static function getNavigationBadge(): ?string
    {
        if (! auth()->user()?->can('approve_article')) {
            return null;
        }
        $count = Article::where('status', ContentStatus::PendingReview->value)->count();

        return $count ? (string) $count : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Articles awaiting review';
    }

    public static function form(Schema $schema): Schema
    {
        $can = fn (string $p) => fn () => auth()->user()?->can($p) ?? false;

        return $schema->components([
            Tabs::make('Article')->columnSpanFull()->persistTabInQueryString()->tabs([
                Tab::make('Content')->icon(Heroicon::OutlinedPencilSquare)->schema([
                    TextInput::make('title')->required()->maxLength(255)->columnSpanFull(),
                    TextInput::make('subtitle')->maxLength(255)->columnSpanFull(),
                    TextInput::make('slug')->maxLength(191)->unique(ignoreRecord: true)
                        ->helperText('Leave blank to generate from the title. Changing it later changes the public URL.')
                        ->dehydrateStateUsing(fn ($state) => filled($state) ? Str::slug($state) : null),
                    Select::make('kind')->label('Article type')->options(['research' => 'Research / analysis', 'news' => 'News', 'blog' => 'Blog / commentary'])->default('research')->required()
                        ->helperText('Controls the structured-data type (Article, NewsArticle, BlogPosting).'),
                    Textarea::make('excerpt')->rows(3)->maxLength(400)->helperText('Summary shown on cards and in search results.')->columnSpanFull(),
                    RichEditor::make('body')->required()->columnSpanFull()->extraInputAttributes(['style' => 'min-height: 24rem;'])
                        ->fileAttachmentsDisk('public')->fileAttachmentsDirectory('articles/inline')->fileAttachmentsVisibility('public'),
                    SpatieMediaLibraryFileUpload::make('featured')->collection('featured')->label('Featured image')->image()->imageEditor()
                        ->maxSize(5120)->conversion('card')->columnSpanFull(),
                    TextInput::make('featured_alt')->label('Featured image alt text')->maxLength(255)->helperText('Describe the image for accessibility and SEO.')->columnSpanFull(),
                    SpatieMediaLibraryFileUpload::make('gallery')->collection('gallery')->label('Image gallery')->image()->multiple()->reorderable()->maxFiles(20)->maxSize(5120)->conversion('thumb')->columnSpanFull(),
                ])->columns(2),

                Tab::make('Media & references')->icon(Heroicon::OutlinedPaperClip)->schema([
                    SpatieMediaLibraryFileUpload::make('attachments')->collection('attachments')->label('PDF / documents')->multiple()->reorderable()->downloadable()
                        ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                        ->maxSize(20480)->maxFiles(10)->columnSpanFull(),
                    Select::make('video_source')->label('Video')->options(VideoSource::options())->placeholder('No video')->live(),
                    TextInput::make('video_url')->label('Video URL')->url()->maxLength(2048)->visible(fn ($get) => in_array($get('video_source'), ['youtube', 'facebook'], true))
                        ->helperText('Paste the public YouTube or Facebook video link.'),
                    SpatieMediaLibraryFileUpload::make('video_file')->collection('video_file')->label('Video file (MP4/WebM)')
                        ->acceptedFileTypes(['video/mp4', 'video/webm'])->maxSize(204800)->visible(fn ($get) => $get('video_source') === 'upload')
                        ->helperText('Max 200 MB. Prefer YouTube/Facebook links to save server space.')->columnSpanFull(),
                    Repeater::make('references')->label('References & external sources')->schema([
                        TextInput::make('title')->required()->maxLength(255),
                        TextInput::make('url')->url()->maxLength(2048),
                    ])->columns(2)->collapsed()->itemLabel(fn (array $state) => $state['title'] ?? null)->defaultItems(0)->columnSpanFull(),
                ])->columns(2),

                Tab::make('Classification')->icon(Heroicon::OutlinedTag)->schema([
                    Select::make('author_id')->label('Author')->relationship('author', 'name')->searchable()->preload()->required()
                        ->default(fn () => auth()->id())->visible($can('manage_all_articles')),
                    Select::make('event_id')->label('Related event')->relationship('event', 'title')->searchable()->preload(),
                    Select::make('categories')->relationship('categories', 'name', fn (Builder $query) => $query->where('type', CategoryType::Article->value)->where('is_active', true)->orderBy('sort_order'))
                        ->multiple()->preload()->searchable()->required(),
                    Select::make('tags')->relationship('tags', 'name')->multiple()->preload()->searchable()
                        ->createOptionForm([TextInput::make('name')->required()->maxLength(60)]),
                    Toggle::make('is_featured')->label('Featured article')->visible($can('feature_article')),
                    Toggle::make('is_editors_choice')->label("Editor's choice")->visible($can('feature_article')),
                ])->columns(2),

                Tab::make('SEO')->icon(Heroicon::OutlinedMagnifyingGlass)
                    ->visible(fn () => auth()->user()?->canAny(['manage_seo', 'manage_all_articles']) ?? false)
                    ->schema([
                        Group::make()->relationship('seo')->columns(2)->schema([
                            TextInput::make('title')->label('SEO title')->maxLength(120)->helperText('Aim for 60 characters or fewer.'),
                            TextInput::make('focus_keyword')->maxLength(120),
                            Textarea::make('description')->label('Meta description')->rows(3)->maxLength(320)->helperText('Aim for 155 characters or fewer.')->columnSpanFull(),
                            TextInput::make('canonical_url')->url()->maxLength(2048)->columnSpanFull(),
                            TextInput::make('og_title')->label('Open Graph title')->maxLength(120),
                            TextInput::make('og_description')->label('Open Graph description')->maxLength(320),
                            FileUpload::make('og_image')->label('Social sharing image')->image()->disk('public')->directory('seo')->maxSize(2048)->columnSpanFull(),
                            Toggle::make('robots_index')->label('Allow indexing')->default(true),
                            Toggle::make('robots_follow')->label('Allow following links')->default(true),
                        ]),
                    ]),

                Tab::make('Workflow')->icon(Heroicon::OutlinedClipboardDocumentCheck)->visibleOn('edit')->schema([
                    Placeholder::make('status_info')->label('Status')->content(fn (?Article $record) => $record?->status?->label()),
                    Placeholder::make('published_info')->label('Publication date')->content(fn (?Article $record) => $record?->published_at?->format('d M Y H:i') ?? 'Not published'),
                    Placeholder::make('review_info')->label('Editor notes')->content(fn (?Article $record) => $record?->review_notes ?: 'None')->columnSpanFull(),
                ])->columns(2),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                SpatieMediaLibraryImageColumn::make('featured')->collection('featured')->conversion('thumb')->label('')->square()->size(56),
                TextColumn::make('title')->searchable(['title', 'subtitle', 'excerpt'])->limit(60)->wrap()->weight('medium')
                    ->description(fn (Article $r) => $r->author?->name),
                TextColumn::make('categories.name')->badge()->color('gray')->limitList(2),
                TextColumn::make('status')->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof ContentStatus ? $state->label() : $state)
                    ->color(fn ($state) => $state instanceof ContentStatus ? $state->color() : 'gray'),
                TextColumn::make('published_at')->dateTime('d M Y H:i')->sortable()->placeholder('—'),
                IconColumn::make('is_featured')->label('Featured')->boolean()->toggleable(),
                TextColumn::make('reading_time')->suffix(' min')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')->options(ContentStatus::options()),
                SelectFilter::make('category')->label('Category')->multiple()
                    ->options(fn () => \App\Models\Category::query()->where('type', CategoryType::Article->value)->orderBy('name')->pluck('name', 'id')->all())
                    ->query(fn (Builder $query, array $data) => filled($data['values'] ?? null)
                        ? $query->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $data['values']))
                        : $query),
                SelectFilter::make('author_id')->label('Author')->searchable()
                    ->options(fn () => \App\Models\User::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->visible(fn () => auth()->user()?->can('manage_all_articles') ?? false),
                Filter::make('published_between')->label('Published')->schema([
                    DatePicker::make('from'),
                    DatePicker::make('until'),
                ])->query(fn (Builder $query, array $data) => $query
                    ->when($data['from'] ?? null, fn ($q, $d) => $q->whereDate('published_at', '>=', $d))
                    ->when($data['until'] ?? null, fn ($q, $d) => $q->whereDate('published_at', '<=', $d))),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                ActionGroup::make(self::workflowActions()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make(), RestoreBulkAction::make(), ForceDeleteBulkAction::make()]),
            ]);
    }

    public static function getRelations(): array
    {
        return [RevisionsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListArticles::route('/'),
            'create' => CreateArticle::route('/create'),
            'edit' => EditArticle::route('/{record}/edit'),
        ];
    }
}
