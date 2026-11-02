<?php

namespace App\Support;

use App\Models\Page;

class Listing
{
    /** The CMS page that holds a listing's introduction and SEO (slug = articles, events, ...). */
    public static function page(string $slug): ?Page
    {
        return Page::live()->where('slug', $slug)->with(['visibleSections', 'seo'])->first();
    }
}
