<?php

namespace App\Filament\Resources\Categories;

use App\Enums\CategoryType;
use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Filament\Support\SeoFields;
use App\Models\Category;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
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
use UnitEnum;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Category')->columns(2)->schema([
                Select::make('type')->options(CategoryType::options())->required()->live()
                    ->helperText('Where this category is used. A category belongs to exactly one content type.'),
                TextInput::make('name')->required()->maxLength(255),
                Select::make('parent_id')->label('Parent category')
                    ->options(fn (Get $get, ?Category $record) => Category::query()
                        ->where('type', $get('type') ?: '__none__')
                        ->when($record, fn ($q) => $q->where('id', '!=', $record->id))
                        ->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()->placeholder('None (top level)'),
                TextInput::make('slug')->maxLength(255)->helperText('Generated from the name on create. Edit with care: changing it changes public URLs.')
                    ->visibleOn('edit'),
                Textarea::make('description')->rows(3)->columnSpanFull(),
                TextInput::make('sort_order')->numeric()->default(0),
                Toggle::make('is_active')->label('Active')->default(true),
                SpatieMediaLibraryFileUpload::make('cover')->collection('cover')->image()->label('Category image')->columnSpanFull(),
                TextInput::make('cover_alt')->label('Image alt text')->maxLength(255)->columnSpanFull(),
            ]),
            Section::make('SEO')->collapsed()->schema([SeoFields::make()])
                ->visible(fn () => auth()->user()?->canAny(['manage_seo']) ?? false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('type')
            ->modifyQueryUsing(fn (Builder $query) => $query->with('parent'))
            ->columns([
                TextColumn::make('name')->searchable()->sortable()
                    ->description(fn (Category $record) => $record->parent?->name ? 'Child of '.$record->parent->name : null),
                TextColumn::make('type')->badge()->formatStateUsing(fn ($state) => $state instanceof CategoryType ? $state->label() : $state)->sortable(),
                TextColumn::make('slug')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('usage')->label('Used by')->state(fn (Category $record) => $record->usageCount().' items'),
                IconColumn::make('is_active')->boolean()->label('Active'),
                TextColumn::make('sort_order')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')->options(CategoryType::options()),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->modalDescription(fn (Category $record) => $record->usageCount() > 0
                        ? "This category is used by {$record->usageCount()} item(s). Deleting it detaches it from them. Continue?"
                        : 'This category is not used anywhere.')
                    ->before(function (DeleteAction $action, Category $record) {
                        if ($record->children()->exists()) {
                            Notification::make()->danger()->title('Cannot delete')->body('This category has child categories. Move or delete them first.')->send();
                            $action->cancel();
                        }
                    }),
                RestoreAction::make(),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCategories::route('/'),
            'create' => CreateCategory::route('/create'),
            'edit' => EditCategory::route('/{record}/edit'),
        ];
    }
}
