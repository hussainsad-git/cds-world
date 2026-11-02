<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\GalleryAlbum;
use App\Support\Listing;
use App\Support\Schema;
use App\Support\Seo;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    public function index(Request $request)
    {
        $category = (string) $request->query('category', '');
        $query = GalleryAlbum::live()->with(['media', 'categories', 'event'])->withCount('items')
            ->with(['items' => fn ($q) => $q->where('type', 'image')->orderBy('sort_order')->limit(1)->with('media')]);
        if ($category !== '') {
            $query->whereHas('categories', fn ($c) => $c->where('slug', $category)->where('type', 'gallery'));
        }

        $page = Listing::page('gallery');
        $crumbs = [['Home', '/'], ['Gallery', '/gallery']];

        return view('site.gallery.index', [
            'page' => $page,
            'albums' => $query->orderByDesc('is_featured')->latestFirst()->paginate(12)->withQueryString(),
            'categories' => Category::active()->ofType('gallery')->orderBy('name')->get(),
            'category' => $category,
            'crumbs' => $crumbs,
            'seo' => Seo::make($page, ['title' => $page?->title ?: 'Gallery', 'description' => $page?->excerpt, 'jsonld' => [Schema::breadcrumb($crumbs)]]),
        ]);
    }

    public function show(Request $request, string $slug)
    {
        $album = GalleryAlbum::live()->where('slug', $slug)->with(['media', 'categories', 'event', 'seo'])->firstOrFail();
        $items = $album->items()->with('media')->orderBy('sort_order')->paginate(24)->withQueryString();
        $cover = $album->getFirstMediaUrl('cover', 'large') ?: $items->first()?->getFirstMediaUrl('file', 'large');
        $crumbs = [['Home', '/'], ['Gallery', '/gallery'], [$album->title, '/gallery/'.$album->slug]];

        return view('site.gallery.show', [
            'album' => $album, 'items' => $items, 'cover' => $cover, 'crumbs' => $crumbs,
            'seo' => Seo::make($album, [
                'title' => $album->title, 'description' => $album->description, 'image' => $cover,
                'jsonld' => [Schema::breadcrumb($crumbs), array_filter(['@context' => 'https://schema.org', '@type' => 'ImageGallery', 'name' => $album->title, 'description' => $album->description])],
            ]),
        ]);
    }
}
