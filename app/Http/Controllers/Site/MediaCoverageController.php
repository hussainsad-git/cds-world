<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\MediaCoverage;
use App\Models\MediaOutlet;
use App\Support\Listing;
use App\Support\Schema;
use App\Support\Seo;
use Illuminate\Http\Request;

class MediaCoverageController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $outlet = (int) $request->query('outlet', 0);
        $category = (string) $request->query('category', '');
        $year = (int) $request->query('year', 0);

        $query = MediaCoverage::live()->with(['outlet.media', 'media', 'categories']);
        if ($q !== '') {
            $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $q).'%';
            $query->where(fn ($w) => $w->where('headline', 'like', $like)->orWhere('summary', 'like', $like)->orWhereHas('outlet', fn ($o) => $o->where('name', 'like', $like)));
        }
        if ($outlet) {
            $query->where('media_outlet_id', $outlet);
        }
        if ($category !== '') {
            $query->whereHas('categories', fn ($c) => $c->where('slug', $category)->where('type', 'media_coverage'));
        }
        if ($year) {
            $query->whereYear('published_on', $year);
        }

        $page = Listing::page('media-coverage');
        $crumbs = [['Home', '/'], ['Media coverage', '/media-coverage']];

        return view('site.media-coverage', [
            'page' => $page,
            'items' => $query->orderByDesc('is_featured')->orderByDesc('published_on')->paginate(12)->withQueryString(),
            'outlets' => MediaOutlet::where('is_active', true)->whereHas('coverage', fn ($c) => $c->live())->orderBy('name')->get(['id', 'name']),
            'categories' => Category::active()->ofType('media_coverage')->orderBy('name')->get(),
            'years' => MediaCoverage::live()->selectRaw('YEAR(published_on) as y')->distinct()->orderByDesc('y')->pluck('y'),
            'filters' => compact('q', 'outlet', 'category', 'year'),
            'crumbs' => $crumbs,
            'seo' => Seo::make($page, ['title' => $page?->title ?: 'Media Coverage', 'description' => $page?->excerpt, 'noindex' => $q !== '', 'jsonld' => [Schema::breadcrumb($crumbs)]]),
        ]);
    }

    public function show(string $slug)
    {
        $item = MediaCoverage::live()->where('slug', $slug)->with(['outlet.media', 'media', 'categories', 'event', 'seo'])->firstOrFail();
        $image = $item->getFirstMediaUrl('thumbnail', 'large') ?: ($item->getFirstMediaUrl('thumbnail') ?: null);
        $crumbs = [['Home', '/'], ['Media coverage', '/media-coverage'], [$item->headline, '/media/'.$item->slug]];

        return view('site.media-show', [
            'item' => $item,
            'image' => $image,
            'crumbs' => $crumbs,
            'related' => MediaCoverage::live()->whereKeyNot($item->getKey())->with(['outlet.media', 'media'])
                ->orderByDesc('published_on')->limit(3)->get(),
            'seo' => Seo::make($item, [
                'title' => $item->headline,
                'description' => $item->summary,
                'image' => $image,
                'type' => 'article',
                'published' => $item->published_at?->toIso8601String(),
                'jsonld' => [Schema::breadcrumb($crumbs)],
            ]),
        ]);
    }
}
