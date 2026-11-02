<?php

namespace App\Filament\Resources\NavigationMenus\RelationManagers;

use App\Models\NavigationItem;
use App\Models\Page;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'allItems';

    protected static ?string $title = 'Menu items';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('label')->required()->maxLength(120),
            Select::make('type')->options(['url' => 'Custom link / path', 'page' => 'CMS page'])->default('url')->required()->live(),
            TextInput::make('url')->label('URL or path')->maxLength(2048)->placeholder('/articles or https://…')
                ->visible(fn (Get $get) => $get('type') !== 'page')->required(fn (Get $get) => $get('type') !== 'page'),
            Select::make('page_id')->label('Page')->options(fn () => Page::orderBy('title')->pluck('title', 'id')->all())->searchable()
                ->visible(fn (Get $get) => $get('type') === 'page')->required(fn (Get $get) => $get('type') === 'page'),
            Select::make('parent_id')->label('Dropdown parent')->placeholder('Top level')
                ->options(fn (?NavigationItem $record) => NavigationItem::query()
                    ->where('navigation_menu_id', $this->getOwnerRecord()->getKey())->whereNull('parent_id')
                    ->when($record, fn ($q) => $q->where('id', '!=', $record->id))->orderBy('sort_order')->pluck('label', 'id')->all()),
            Toggle::make('open_in_new_tab')->label('Open in new tab'),
            Toggle::make('is_visible')->label('Visible')->default(true),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->modifyQueryUsing(fn ($query) => $query->with('parent'))
            ->columns([
                TextColumn::make('label')->searchable()->description(fn (NavigationItem $record) => $record->parent ? 'Under '.$record->parent->label : null),
                TextColumn::make('type')->badge(),
                TextColumn::make('url')->label('Target')->state(fn (NavigationItem $record) => $record->type === 'page' ? ($record->page?->title ?? '—') : $record->url)->limit(50),
                IconColumn::make('is_visible')->boolean()->label('Visible'),
            ])
            ->headerActions([CreateAction::make()->mutateDataUsing(function (array $data) {
                $data['sort_order'] = (int) $this->getOwnerRecord()->allItems()->max('sort_order') + 1;

                return $data;
            })])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
