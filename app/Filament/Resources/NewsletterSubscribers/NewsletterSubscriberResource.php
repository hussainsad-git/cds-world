<?php

namespace App\Filament\Resources\NewsletterSubscribers;

use App\Filament\Resources\NewsletterSubscribers\Pages\CreateNewsletterSubscriber;
use App\Filament\Resources\NewsletterSubscribers\Pages\EditNewsletterSubscriber;
use App\Filament\Resources\NewsletterSubscribers\Pages\ListNewsletterSubscribers;
use App\Models\NewsletterSubscriber;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

class NewsletterSubscriberResource extends Resource
{
    protected static ?string $model = NewsletterSubscriber::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static string|UnitEnum|null $navigationGroup = 'Engagement';

    protected static ?string $navigationLabel = 'Newsletter';

    protected static ?string $modelLabel = 'subscriber';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'email';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('email')->email()->required()->unique(ignoreRecord: true)->maxLength(255),
            TextInput::make('name')->maxLength(255),
            Select::make('status')->options(['pending' => 'Pending confirmation', 'active' => 'Active', 'unsubscribed' => 'Unsubscribed'])->default('active')->required()
                ->helperText('Subscribers added by an admin should have consented to receive email; keep a record.'),
        ]);
    }

    /** @param iterable<NewsletterSubscriber> $rows */
    public static function csv(iterable $rows, string $filename)
    {
        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Email', 'Name', 'Status', 'Subscribed at', 'Confirmed at', 'Unsubscribed at', 'Source']);
            foreach ($rows as $s) {
                fputcsv($out, [$s->email, $s->name, $s->status, $s->created_at, $s->confirmed_at, $s->unsubscribed_at, $s->source]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('email')->searchable()->copyable(),
                TextColumn::make('name')->searchable()->toggleable(),
                TextColumn::make('status')->badge()->color(fn (string $state) => match ($state) { 'active' => 'success', 'pending' => 'warning', default => 'gray' }),
                TextColumn::make('source')->toggleable(),
                TextColumn::make('created_at')->dateTime('d M Y')->label('Subscribed')->sortable(),
            ])
            ->filters([SelectFilter::make('status')->options(['pending' => 'Pending', 'active' => 'Active', 'unsubscribed' => 'Unsubscribed'])])
            ->headerActions([
                Action::make('export')->label('Export CSV')->icon('heroicon-o-arrow-down-tray')
                    ->action(fn () => static::csv(NewsletterSubscriber::query()->orderBy('created_at')->cursor(), 'newsletter-subscribers.csv')),
            ])
            ->recordActions([
                Action::make('unsubscribe')->icon('heroicon-o-no-symbol')->color('gray')->requiresConfirmation()
                    ->visible(fn (NewsletterSubscriber $record) => $record->status !== 'unsubscribed')
                    ->action(fn (NewsletterSubscriber $record) => $record->update(['status' => 'unsubscribed', 'unsubscribed_at' => now()])),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([BulkActionGroup::make([
                BulkAction::make('exportSelected')->label('Export selected (CSV)')->icon('heroicon-o-arrow-down-tray')
                    ->action(fn (Collection $records) => static::csv($records, 'newsletter-selected.csv')),
                DeleteBulkAction::make(),
            ])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNewsletterSubscribers::route('/'),
            'create' => CreateNewsletterSubscriber::route('/create'),
            'edit' => EditNewsletterSubscriber::route('/{record}/edit'),
        ];
    }
}
