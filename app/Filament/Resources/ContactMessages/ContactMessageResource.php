<?php

namespace App\Filament\Resources\ContactMessages;

use App\Filament\Resources\ContactMessages\Pages\ListContactMessages;
use App\Filament\Resources\ContactMessages\Pages\ViewContactMessage;
use App\Models\ContactMessage;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

class ContactMessageResource extends Resource
{
    protected static ?string $model = ContactMessage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxArrowDown;

    protected static string|UnitEnum|null $navigationGroup = 'Engagement';

    protected static ?string $navigationLabel = 'Contact messages';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'subject';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $n = ContactMessage::where('status', 'new')->count();

        return $n ? (string) $n : null;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(2)->schema([
                TextEntry::make('name'),
                TextEntry::make('email')->copyable(),
                TextEntry::make('phone')->placeholder('—'),
                TextEntry::make('created_at')->dateTime('d M Y H:i'),
                TextEntry::make('subject')->columnSpanFull(),
                TextEntry::make('message')->columnSpanFull()->prose(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('subject')->limit(50)->searchable(),
                TextColumn::make('email')->toggleable(),
                TextColumn::make('status')->badge()->color(fn (string $state) => $state === 'new' ? 'warning' : 'gray'),
                TextColumn::make('created_at')->since()->sortable(),
            ])
            ->filters([SelectFilter::make('status')->options(['new' => 'New', 'read' => 'Read', 'archived' => 'Archived'])])
            ->recordActions([
                ViewAction::make()->after(fn (ContactMessage $record) => $record->status === 'new' ? $record->update(['status' => 'read', 'read_at' => now()]) : null),
                Action::make('archive')->icon('heroicon-o-archive-box')->color('gray')->visible(fn (ContactMessage $record) => $record->status !== 'archived')
                    ->action(fn (ContactMessage $record) => $record->update(['status' => 'archived'])),
                DeleteAction::make(),
            ])
            ->toolbarActions([BulkActionGroup::make([
                BulkAction::make('markRead')->label('Mark as read')->action(fn (Collection $records) => $records->each->update(['status' => 'read', 'read_at' => now()])),
                DeleteBulkAction::make(),
            ])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContactMessages::route('/'),
            'view' => ViewContactMessage::route('/{record}'),
        ];
    }
}
