<?php

namespace App\Filament\Resources\Redirects;

use App\Filament\Resources\Redirects\Pages\CreateRedirect;
use App\Filament\Resources\Redirects\Pages\EditRedirect;
use App\Filament\Resources\Redirects\Pages\ListRedirects;
use App\Models\Redirect;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

class RedirectResource extends Resource
{
    protected static ?string $model = Redirect::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUturnRight;

    protected static string|UnitEnum|null $navigationGroup = 'SEO';

    protected static ?string $navigationLabel = 'Redirects';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'from_path';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('from_path')->label('Old path')->required()->maxLength(191)->unique(ignoreRecord: true)
                ->placeholder('/old-page')->startsWith('/')->dehydrateStateUsing(fn ($state) => '/'.trim((string) $state, '/')),
            Select::make('status_code')->label('Response')->options([
                301 => '301 Moved permanently',
                302 => '302 Found (temporary)',
                307 => '307 Temporary redirect',
                308 => '308 Permanent redirect',
                410 => '410 Gone (content removed)',
            ])->default(301)->required()->live(),
            TextInput::make('to_url')->label('New URL or path')->maxLength(2048)->placeholder('/articles or https://…')
                ->visible(fn (Get $get) => (int) $get('status_code') !== 410)->required(fn (Get $get) => (int) $get('status_code') !== 410),
            TextInput::make('note')->maxLength(255),
            Toggle::make('is_active')->default(true)->label('Active'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('from_path')->searchable()->label('From'),
                TextColumn::make('to_url')->label('To')->limit(50)->placeholder('—'),
                TextColumn::make('status_code')->badge()->label('Code'),
                TextColumn::make('hits')->sortable(),
                TextColumn::make('last_hit_at')->since()->placeholder('Never'),
                ToggleColumn::make('is_active')->label('Active'),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRedirects::route('/'),
            'create' => CreateRedirect::route('/create'),
            'edit' => EditRedirect::route('/{record}/edit'),
        ];
    }
}
