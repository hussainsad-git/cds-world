<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class SeoSettings extends BaseSettingsPage
{
    protected static bool $isDiscovered = true;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected static string|UnitEnum|null $navigationGroup = 'SEO';

    protected static ?string $navigationLabel = 'SEO Settings';

    protected static ?string $title = 'SEO Settings';

    protected static ?int $navigationSort = 3;

    protected function groups(): array
    {
        return ['seo'];
    }

    protected function booleans(): array
    {
        return ['seo.sitemap_enabled'];
    }

    protected function permission(): string
    {
        return 'manage_seo';
    }

    public function form(Schema $schema): Schema
    {
        return $schema->statePath('data')->components([
            Section::make('Defaults')->columns(2)->schema([
                TextInput::make('seo.default_title_suffix')->label('Title suffix')->maxLength(60)->helperText('Appended to page titles, for example “ | CDS World”.'),
                FileUpload::make('seo.default_og_image')->label('Default social sharing image')->image()->disk('public')->directory('seo')->maxSize(2048),
            ]),
            Section::make('Crawling')->schema([
                Toggle::make('seo.sitemap_enabled')->label('Serve /sitemap.xml'),
                Textarea::make('seo.robots_extra')->label('Extra robots.txt lines')->rows(4)->helperText('Appended after the generated rules. The sitemap URL is added automatically.'),
            ]),
        ]);
    }
}
