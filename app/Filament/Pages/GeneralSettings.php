<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class GeneralSettings extends BaseSettingsPage
{
    protected static bool $isDiscovered = true;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Website Settings';

    protected static ?string $title = 'Website Settings';

    protected static ?int $navigationSort = 3;

    protected function groups(): array
    {
        return ['general', 'social', 'newsletter'];
    }

    protected function booleans(): array
    {
        return ['general.announcement_enabled'];
    }

    protected function permission(): string
    {
        return 'manage_settings';
    }

    public function form(Schema $schema): Schema
    {
        return $schema->statePath('data')->components([
            Section::make('Identity')->columns(2)->schema([
                TextInput::make('general.site_name')->label('Website name')->required()->maxLength(120),
                TextInput::make('general.tagline')->maxLength(255),
                TextInput::make('general.organization_name')->label('Organization legal name')->maxLength(255),
                FileUpload::make('general.logo')->label('Logo (light backgrounds)')->image()->disk('public')->directory('branding')->maxSize(1024),
                FileUpload::make('general.logo_light')->label('Logo (dark backgrounds)')->image()->disk('public')->directory('branding')->maxSize(1024),
                FileUpload::make('general.favicon')->label('Favicon')->image()->disk('public')->directory('branding')->maxSize(512),
                Textarea::make('general.organization_description')->rows(3)->columnSpanFull(),
            ]),
            Section::make('Contact')->columns(2)->schema([
                TextInput::make('general.contact_email')->email()->maxLength(255),
                TagsInput::make('general.phones')->label('Phone numbers')->placeholder('Add number'),
                Textarea::make('general.address')->rows(2)->columnSpanFull(),
                TextInput::make('general.copyright')->maxLength(255)->helperText('Use {year} for the current year.')->columnSpanFull(),
            ]),
            Section::make('Announcement bar')->columns(2)->schema([
                Toggle::make('general.announcement_enabled')->label('Show announcement bar'),
                TextInput::make('general.announcement_text')->maxLength(255),
                TextInput::make('general.announcement_url')->maxLength(2048)->label('Link (optional)'),
            ]),
            Section::make('Social media')->columns(2)->schema([
                TextInput::make('social.facebook')->url(),
                TextInput::make('social.instagram')->url(),
                TextInput::make('social.x')->label('X (Twitter)')->url(),
                TextInput::make('social.youtube')->url(),
                TextInput::make('social.linkedin')->url(),
                TextInput::make('social.whatsapp')->url(),
            ]),
            Section::make('Newsletter')->columns(2)->schema([
                TextInput::make('newsletter.admin_notify_email')->email()->label('Notify this address on new subscribers'),
                Textarea::make('newsletter.consent_text')->rows(2)->label('Consent text shown on the form')->columnSpanFull(),
            ]),
        ]);
    }
}
