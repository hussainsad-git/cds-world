<?php

namespace App\Enums;

enum VideoSource: string
{
    case YouTube = 'youtube';
    case Facebook = 'facebook';
    case Upload = 'upload';

    public function label(): string
    {
        return match ($this) {
            self::YouTube => 'YouTube',
            self::Facebook => 'Facebook',
            self::Upload => 'Uploaded file',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
