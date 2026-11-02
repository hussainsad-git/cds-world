<?php

namespace App\Enums;

enum TeamGroup: string
{
    case Board = 'board';
    case Leadership = 'leadership';
    case Research = 'research';
    case Contributor = 'contributor';
    case Staff = 'staff';

    public function label(): string
    {
        return match ($this) {
            self::Board => 'Board of Directors',
            self::Leadership => 'Leadership & Management',
            self::Research => 'Research Team',
            self::Contributor => 'Contributors',
            self::Staff => 'Staff',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
