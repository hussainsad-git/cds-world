<?php

namespace App\Enums;

enum CategoryType: string
{
    case Article = 'article';
    case ResearchTopic = 'research_topic';
    case Event = 'event';
    case MediaCoverage = 'media_coverage';
    case Video = 'video';
    case Gallery = 'gallery';

    public function label(): string
    {
        return ucwords(str_replace('_', ' ', $this->value));
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
