<?php

namespace App\Filament\Support;

use App\Enums\ContentStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;

/** Status / schedule / featured controls shared by Events, Media Coverage, Videos and Albums. */
class PublishingFields
{
    /** @return array<int, \Filament\Schemas\Components\Component> */
    public static function make(bool $featured = true): array
    {
        $fields = [
            Select::make('status')->options([
                ContentStatus::Draft->value => 'Draft',
                ContentStatus::Scheduled->value => 'Scheduled',
                ContentStatus::Published->value => 'Published',
            ])->default(ContentStatus::Draft->value)->required()->live()
                ->helperText('Scheduled items go live automatically at the chosen date and time.'),
            DateTimePicker::make('published_at')->label('Publish at')->seconds(false)
                ->visible(fn (Get $get) => in_array($get('status'), ['scheduled', 'published'], true))
                ->required(fn (Get $get) => $get('status') === 'scheduled')
                ->after(fn (Get $get) => $get('status') === 'scheduled' ? 'now' : null)
                ->helperText('Leave empty on Published to use the current time.'),
        ];

        if ($featured) {
            $fields[] = Toggle::make('is_featured')->label('Featured');
        }

        return $fields;
    }
}
