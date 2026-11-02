<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Support\Schema;
use App\Support\Seo;

class PageController extends Controller
{
    public function home()
    {
        $page = Page::live()->where('slug', 'home')->with(['visibleSections', 'seo'])->firstOrFail();

        return view('site.page', [
            'page' => $page,
            'isHome' => true,
            'crumbs' => [],
            'seo' => Seo::make($page, [
                'title' => $page->title,
                'bare_title' => true,
                'description' => $page->excerpt,
                'jsonld' => [Schema::organization(), Schema::website()],
            ]),
        ]);
    }

    public function show(string $slug)
    {
        if ($slug === 'home') {
            return redirect('/', 301);
        }

        $page = Page::live()->where('slug', $slug)->with(['visibleSections', 'seo', 'media'])->firstOrFail();
        $crumbs = [['Home', '/'], [$page->title, '/'.$page->slug]];

        return view('site.page', [
            'page' => $page,
            'isHome' => false,
            'crumbs' => $crumbs,
            'seo' => Seo::make($page, [
                'title' => $page->title,
                'description' => $page->excerpt,
                'image' => $page->getFirstMediaUrl('featured', 'large') ?: null,
                'jsonld' => [Schema::breadcrumb($crumbs)],
            ]),
        ]);
    }
}
