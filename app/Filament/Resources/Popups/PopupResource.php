<?php

namespace App\Filament\Resources\Popups;

use App\Filament\Resources\Popups\Pages\CreatePopup;
use App\Filament\Resources\Popups\Pages\EditPopup;
use App\Filament\Resources\Popups\Pages\ListPopups;
use App\Models\Popup;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use UnitEnum;

class PopupResource extends Resource
{
    protected static ?string $model = Popup::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWindow;

    protected static string|UnitEnum|null $navigationGroup = 'Engagement';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Content')->columns(2)->schema([
                TextInput::make('title')->required()->maxLength(255)->helperText('Shown as the popup heading for text popups; also used as its internal name.'),
                Select::make('type')->options(['text' => 'Text', 'image' => 'Image', 'html' => 'Custom HTML (sanitised)'])->default('text')->required()->live(),
                RichEditor::make('content')->columnSpanFull()->visible(fn (Get $get) => $get('type') === 'text'),
                Textarea::make('content')->label('HTML')->rows(8)->columnSpanFull()->visible(fn (Get $get) => $get('type') === 'html')
                    ->helperText('Scripts, iframes, forms and inline event handlers are stripped on save.'),
                SpatieMediaLibraryFileUpload::make('image')->collection('image')->image()->columnSpanFull()
                    ->visible(fn (Get $get) => $get('type') === 'image'),
                TextInput::make('cta_label')->label('Button text')->maxLength(80),
                TextInput::make('cta_url')->label('Button link')->maxLength(2048),
            ]),
            Section::make('Schedule & display')->columns(2)->schema([
                DateTimePicker::make('starts_at')->seconds(false),
                DateTimePicker::make('ends_at')->seconds(false)->after('starts_at'),
                Select::make('frequency')->options([
                    'once_per_session' => 'Once per session',
                    'once_per_visitor' => 'Once per visitor (remember for a long time)',
                    'every_n_days' => 'Every N days',
                    'every_visit' => 'Every page load',
                ])->default('once_per_session')->required()->live(),
                TextInput::make('frequency_days')->numeric()->minValue(1)->maxValue(365)->visible(fn (Get $get) => $get('frequency') === 'every_n_days')->required(fn (Get $get) => $get('frequency') === 'every_n_days'),
                TextInput::make('delay_seconds')->numeric()->minValue(0)->maxValue(120)->default(2)->label('Delay before showing (seconds)'),
                TagsInput::make('target_paths')->label('Target pages')->placeholder('/articles, articles/*, /')
                    ->helperText('Leave empty to show on every page. Use * as a wildcard, e.g. articles/*'),
                Toggle::make('show_on_desktop')->default(true),
                Toggle::make('show_on_mobile')->default(true),
                Toggle::make('is_active')->label('Enabled')->default(false),
            ]),
        ]);
    }

    public static function previewHtml(Popup $popup): HtmlString
    {
        $body = match ($popup->type) {
            'image' => '<img src="'.e($popup->getFirstMediaUrl('image')).'" alt="" style="max-width:100%;border-radius:8px">',
            'html' => clean((string) $popup->content, 'popup'),
            default => clean((string) $popup->content, 'popup'),
        };
        $cta = $popup->cta_label ? '<p style="margin-top:16px"><a href="'.e($popup->cta_url ?: '#').'" style="display:inline-block;padding:10px 18px;background:#12284c;color:#fff;border-radius:6px;text-decoration:none">'.e($popup->cta_label).'</a></p>' : '';

        return new HtmlString('<div style="padding:8px"><h3 style="font-size:1.25rem;font-weight:600;margin-bottom:12px">'.e($popup->title).'</h3>'.$body.$cta.'</div>');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('title')->searchable(),
                TextColumn::make('type')->badge(),
                TextColumn::make('starts_at')->dateTime('d M Y H:i')->placeholder('—'),
                TextColumn::make('ends_at')->dateTime('d M Y H:i')->placeholder('—'),
                ToggleColumn::make('is_active')->label('Enabled'),
                IconColumn::make('show_on_mobile')->boolean()->label('Mobile')->toggleable(),
                IconColumn::make('show_on_desktop')->boolean()->label('Desktop')->toggleable(),
            ])
            ->recordActions([
                Action::make('preview')->icon('heroicon-o-eye')->modalHeading('Popup preview')->modalSubmitAction(false)->modalCancelActionLabel('Close')
                    ->modalContent(fn (Popup $record) => static::previewHtml($record)),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPopups::route('/'),
            'create' => CreatePopup::route('/create'),
            'edit' => EditPopup::route('/{record}/edit'),
        ];
    }
}
