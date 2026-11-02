<?php

namespace App\Filament\Resources\NavigationMenus;

use App\Filament\Resources\NavigationMenus\Pages\CreateNavigationMenu;
use App\Filament\Resources\NavigationMenus\Pages\EditNavigationMenu;
use App\Filament\Resources\NavigationMenus\Pages\ListNavigationMenus;
use App\Filament\Resources\NavigationMenus\RelationManagers\ItemsRelationManager;
use App\Models\NavigationMenu;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class NavigationMenuResource extends Resource
{
    protected static ?string $model = NavigationMenu::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBars3;

    protected static string|UnitEnum|null $navigationGroup = 'Site';

    protected static ?string $navigationLabel = 'Navigation Menus';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(120),
            TextInput::make('slug')->required()->alphaDash()->unique(ignoreRecord: true)->maxLength(120)
                ->helperText('Used by the website to find the menu, for example header or footer-explore.'),
            TextInput::make('title')->label('Heading shown above the menu (footer columns)')->maxLength(120),
            Select::make('location')->options(['header' => 'Header', 'footer' => 'Footer column', 'other' => 'Other'])->placeholder('Unassigned'),
            Toggle::make('is_active')->label('Active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('slug'),
                TextColumn::make('location')->badge(),
                TextColumn::make('items_count')->counts('allItems')->label('Items'),
                IconColumn::make('is_active')->boolean(),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getRelations(): array
    {
        return [ItemsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNavigationMenus::route('/'),
            'create' => CreateNavigationMenu::route('/create'),
            'edit' => EditNavigationMenu::route('/{record}/edit'),
        ];
    }
}
