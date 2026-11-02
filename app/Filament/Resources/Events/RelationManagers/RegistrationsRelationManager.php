<?php

namespace App\Filament\Resources\Events\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RegistrationsRelationManager extends RelationManager
{
    protected static string $relationship = 'registrations';

    protected static ?string $title = 'Registrations';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('status')->options(['registered' => 'Registered', 'attended' => 'Attended', 'cancelled' => 'Cancelled'])->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('phone')->toggleable(),
                TextColumn::make('organization')->toggleable(),
                TextColumn::make('status')->badge(),
                TextColumn::make('created_at')->dateTime('d M Y H:i')->label('Registered'),
            ])
            ->headerActions([
                Action::make('export')->label('Export CSV')->icon('heroicon-o-arrow-down-tray')->action(function () {
                    $rows = $this->getOwnerRecord()->registrations()->orderBy('created_at')->get();

                    return response()->streamDownload(function () use ($rows) {
                        $out = fopen('php://output', 'w');
                        fputcsv($out, ['Name', 'Email', 'Phone', 'Organization', 'Status', 'Registered at']);
                        foreach ($rows as $r) {
                            fputcsv($out, [$r->name, $r->email, $r->phone, $r->organization, $r->status, $r->created_at]);
                        }
                        fclose($out);
                    }, 'event-registrations.csv', ['Content-Type' => 'text/csv']);
                }),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
