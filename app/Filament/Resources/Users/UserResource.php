<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\Rules\Password;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Account')->columns(2)->schema([
                TextInput::make('name')->required()->maxLength(255),
                TextInput::make('email')->email()->required()->unique(ignoreRecord: true)->maxLength(255),
                TextInput::make('password')->password()->revealable()
                    ->required(fn (string $operation) => $operation === 'create')
                    ->rule(Password::min(10)->letters()->numbers())
                    ->dehydrated(fn (?string $state) => filled($state))
                    ->helperText('Minimum 10 characters with letters and numbers. Leave empty to keep the current password.'),
                Select::make('roles')->relationship('roles', 'name', fn (Builder $query) => auth()->user()?->hasRole('super_admin') ? $query : $query->where('name', '!=', 'super_admin'))
                    ->multiple()->preload()->required()
                    ->helperText('A user with no role cannot sign in to the admin panel.'),
                Toggle::make('is_active')->label('Active')->default(true)->helperText('Inactive users cannot sign in.'),
            ]),
            Section::make('Public profile (used for article author boxes)')->columns(2)->collapsed()->schema([
                TextInput::make('designation')->maxLength(255),
                Textarea::make('bio')->rows(3)->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->modifyQueryUsing(fn (Builder $query) => $query->with('roles'))
            ->columns([
                TextColumn::make('name')->searchable()->sortable()->description(fn (User $record) => $record->email),
                TextColumn::make('roles.name')->badge()->label('Roles'),
                IconColumn::make('is_active')->boolean()->label('Active'),
                TextColumn::make('last_login_at')->since()->placeholder('Never')->sortable(),
            ])
            ->filters([
                SelectFilter::make('role')->relationship('roles', 'name'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->hidden(fn (User $record) => $record->id === auth()->id()),
                RestoreAction::make(),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
