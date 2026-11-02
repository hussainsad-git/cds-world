<?php

namespace App\Filament\Resources\NewsletterCampaigns;

use App\Filament\Resources\NewsletterCampaigns\Pages\CreateNewsletterCampaign;
use App\Filament\Resources\NewsletterCampaigns\Pages\EditNewsletterCampaign;
use App\Filament\Resources\NewsletterCampaigns\Pages\ListNewsletterCampaigns;
use App\Models\NewsletterCampaign;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

/** Campaign drafts. Sending is intentionally not wired yet (needs a mail provider); the data model is ready. */
class NewsletterCampaignResource extends Resource
{
    protected static ?string $model = NewsletterCampaign::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaperAirplane;

    protected static string|UnitEnum|null $navigationGroup = 'Engagement';

    protected static ?string $navigationLabel = 'Campaign drafts';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'subject';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('subject')->required()->maxLength(255),
            TextInput::make('preheader')->maxLength(255),
            RichEditor::make('body')->columnSpanFull(),
            DateTimePicker::make('scheduled_at')->seconds(false)->helperText('Reserved for the sending integration.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('subject')->searchable(),
                TextColumn::make('status')->badge(),
                TextColumn::make('scheduled_at')->dateTime('d M Y H:i')->placeholder('—'),
                TextColumn::make('created_at')->since(),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNewsletterCampaigns::route('/'),
            'create' => CreateNewsletterCampaign::route('/create'),
            'edit' => EditNewsletterCampaign::route('/{record}/edit'),
        ];
    }
}
