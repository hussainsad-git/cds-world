<?php

namespace App\Filament\Resources\ActivityLogs;

use App\Filament\Resources\ActivityLogs\Pages\ListActivityLogs;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Spatie\Activitylog\Models\Activity;
use UnitEnum;

class ActivityLogResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?string $navigationLabel = 'Activity Logs';

    protected static ?string $modelLabel = 'activity';

    protected static ?string $pluralModelLabel = 'activity logs';

    protected static ?int $navigationSort = 3;

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_activity_log') ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(2)->schema([
                TextEntry::make('created_at')->dateTime('d M Y H:i:s'),
                TextEntry::make('causer.name')->label('User')->placeholder('System'),
                TextEntry::make('log_name')->label('Area'),
                TextEntry::make('event')->placeholder('—'),
                TextEntry::make('description')->columnSpanFull(),
                TextEntry::make('subject_type')->label('Record type')->formatStateUsing(fn ($state) => $state ? class_basename($state) : '—'),
                TextEntry::make('subject_id')->label('Record ID'),
                TextEntry::make('properties')->label('Changes')->columnSpanFull()
                    ->state(fn (Activity $record) => json_encode($record->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
                    ->fontFamily('mono')->extraAttributes(['style' => 'white-space:pre-wrap']),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->with('causer'))
            ->columns([
                TextColumn::make('created_at')->dateTime('d M Y H:i')->sortable()->label('When'),
                TextColumn::make('causer.name')->label('Who')->placeholder('System')->searchable(),
                TextColumn::make('log_name')->label('Area')->badge(),
                TextColumn::make('description')->searchable()->limit(60),
                TextColumn::make('subject_type')->label('Record')->formatStateUsing(fn ($state, Activity $record) => $state ? class_basename($state).' #'.$record->subject_id : '—'),
            ])
            ->filters([
                SelectFilter::make('log_name')->label('Area')->options(fn () => Activity::query()->distinct()->pluck('log_name', 'log_name')->filter()->all()),
                SelectFilter::make('event')->options(['created' => 'Created', 'updated' => 'Updated', 'deleted' => 'Deleted']),
            ])
            ->recordActions([ViewAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ListActivityLogs::route('/')];
    }
}
