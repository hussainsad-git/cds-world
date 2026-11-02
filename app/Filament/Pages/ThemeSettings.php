<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ThemeSettings extends BaseSettingsPage
{
    protected static bool $isDiscovered = true;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSwatch;

    protected static ?string $navigationLabel = 'Design & Theme';

    protected static ?string $title = 'Design & Theme';

    protected static ?int $navigationSort = 4;

    public const FONTS = ['Source Serif 4', 'Playfair Display', 'Merriweather', 'Lora', 'Libre Baskerville', 'Inter', 'Source Sans 3', 'Open Sans', 'Roboto', 'Lato', 'Poppins'];

    public const PRESETS = [
        'cds-green' => ['label' => 'CDS Green (matches current site)', 'color_primary' => '#01411c', 'color_secondary' => '#2e6b4a', 'color_accent' => '#c49a2c', 'color_bg' => '#ffffff', 'color_bg_alt' => '#f4f7f3', 'color_text' => '#1b241f', 'color_muted' => '#56625b', 'font_heading' => 'Source Serif 4', 'font_body' => 'Inter'],
        'institutional-navy' => ['label' => 'Institutional Navy', 'color_primary' => '#12284c', 'color_secondary' => '#1f6f8b', 'color_accent' => '#b8860b', 'color_bg' => '#ffffff', 'color_bg_alt' => '#f5f7fa', 'color_text' => '#1d2433', 'color_muted' => '#5b6475', 'font_heading' => 'Source Serif 4', 'font_body' => 'Inter'],
        'slate-crimson' => ['label' => 'Slate & Crimson', 'color_primary' => '#1f2937', 'color_secondary' => '#475569', 'color_accent' => '#9b1c31', 'color_bg' => '#ffffff', 'color_bg_alt' => '#f6f6f7', 'color_text' => '#111827', 'color_muted' => '#5f6673', 'font_heading' => 'Playfair Display', 'font_body' => 'Inter'],
        'modern-teal' => ['label' => 'Modern Teal', 'color_primary' => '#0f4c5c', 'color_secondary' => '#2a7f8f', 'color_accent' => '#d95d0f', 'color_bg' => '#ffffff', 'color_bg_alt' => '#f1f7f8', 'color_text' => '#14262b', 'color_muted' => '#586a70', 'font_heading' => 'Merriweather', 'font_body' => 'Source Sans 3'],
    ];

    protected function groups(): array
    {
        return ['theme'];
    }

    protected function permission(): string
    {
        return 'manage_theme';
    }

    public function form(Schema $schema): Schema
    {
        $fonts = array_combine(self::FONTS, self::FONTS);

        return $schema->statePath('data')->components([
            Section::make('Preset')->schema([
                Select::make('theme.preset')->label('Colour & typography preset')->live()
                    ->options(array_map(fn ($p) => $p['label'], self::PRESETS) + ['custom' => 'Custom'])
                    ->afterStateUpdated(function (?string $state, Set $set) {
                        foreach (self::PRESETS[$state] ?? [] as $key => $value) {
                            if ($key !== 'label') {
                                $set("theme.$key", $value);
                            }
                        }
                    })
                    ->helperText('Choosing a preset fills the fields below. You can still adjust them, then save.'),
            ]),
            Section::make('Colours')->columns(4)->schema([
                ColorPicker::make('theme.color_primary')->label('Primary')->required(),
                ColorPicker::make('theme.color_secondary')->label('Secondary')->required(),
                ColorPicker::make('theme.color_accent')->label('Accent')->required(),
                ColorPicker::make('theme.color_bg')->label('Page background')->required(),
                ColorPicker::make('theme.color_bg_alt')->label('Alternate background')->required(),
                ColorPicker::make('theme.color_text')->label('Text')->required(),
                ColorPicker::make('theme.color_muted')->label('Muted text')->required(),
            ]),
            Section::make('Typography')->columns(3)->schema([
                Select::make('theme.font_heading')->label('Heading font')->options($fonts)->required(),
                Select::make('theme.font_body')->label('Body font')->options($fonts)->required(),
                TextInput::make('theme.font_size_base')->label('Base font size (px)')->numeric()->minValue(14)->maxValue(22)->required(),
                TextInput::make('theme.line_height')->numeric()->step(0.05)->minValue(1.3)->maxValue(2)->required(),
            ]),
            Section::make('Layout & components')->columns(3)->schema([
                Select::make('theme.container_width')->label('Layout width (px)')->options(['1100' => '1100', '1200' => '1200', '1320' => '1320'])->required(),
                TextInput::make('theme.radius')->label('Border radius (px)')->numeric()->minValue(0)->maxValue(24)->required(),
                Select::make('theme.button_style')->options(['solid' => 'Solid', 'outline' => 'Outline', 'pill' => 'Pill'])->required(),
                Select::make('theme.header_style')->options(['light' => 'Light', 'dark' => 'Dark'])->required(),
                Select::make('theme.footer_style')->options(['dark' => 'Dark', 'light' => 'Light'])->required(),
            ]),
        ]);
    }
}
