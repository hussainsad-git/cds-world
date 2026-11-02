<?php

namespace App\Filament\Support;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;

/** Reusable SEO form block bound to the polymorphic `seo` relationship (HasSeo). */
class SeoFields
{
    public static function make(): Group
    {
        return Group::make()->relationship('seo')->columns(2)->schema([
            TextInput::make('title')->label('SEO title')->maxLength(120)->helperText('Aim for 60 characters or fewer.'),
            TextInput::make('focus_keyword')->maxLength(120),
            Textarea::make('description')->label('Meta description')->rows(3)->maxLength(320)->helperText('Aim for 155 characters or fewer.')->columnSpanFull(),
            TextInput::make('canonical_url')->url()->maxLength(2048)->columnSpanFull(),
            TextInput::make('og_title')->label('Open Graph title')->maxLength(120),
            TextInput::make('og_description')->label('Open Graph description')->maxLength(320),
            FileUpload::make('og_image')->label('Social sharing image')->image()->disk('public')->directory('seo')->maxSize(2048)->columnSpanFull(),
            Toggle::make('robots_index')->label('Allow indexing')->default(true),
            Toggle::make('robots_follow')->label('Allow following links')->default(true),
        ]);
    }
}
