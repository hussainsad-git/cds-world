<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Video;
use App\Support\Listing;
use App\Support\Schema;
use App\Support\Seo;
use Illuminate\Http\Request;

class VideoController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $category = (string) $request->query('category', '');

        $query = Video::live()->with(['media', 'author', 'categories']);
        if ($q !== '') {
            $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $q).'%';
            $query->where(fn ($w) => $w->where('title', 'like', $like)->orWhere('description', 'like', $like)->orWhere('presenter', 'like', $like));
        }
        if ($category !== '') {
            $query->whereHas('categories', fn ($c) => $c->where('slug', $category)->where('type', 'video'));
        }

        $page = Listing::page('videos');
        $crumbs = [['Home', '/'], ['Videos', '/videos']];

        return view('site.videos.index', [
            'page' => $page,
            'videos' => $query->orderByDesc('is_featured')->latestFirst()->paginate(9)->withQueryString(),
            'categories' => Category::active()->ofType('video')->orderBy('name')->get(),
            'q' => $q, 'category' => $category,
            'crumbs' => $crumbs,
            'seo' => Seo::make($page, ['title' => $page?->title ?: 'Videos & Interviews', 'description' => $page?->excerpt, 'noindex' => $q !== '', 'jsonld' => [Schema::breadcrumb($crumbs)]]),
        ]);
    }

    public function show(string $slug)
    {
        $video = Video::live()->where('slug', $slug)->with(['media', 'author', 'categories', 'tags', 'seo'])->firstOrFail();
        $thumb = $video->getFirstMediaUrl('thumbnail', 'large') ?: ($video->external_id && $video->source?->value === 'youtube' ? 'https://i.ytimg.com/vi/'.$video->external_id.'/hqdefault.jpg' : null);
        $more = Video::live()->where('id', '!=', $video->id)->with('media')->latestFirst()->limit(3)->get();
        $crumbs = [['Home', '/'], ['Videos', '/videos'], [$video->title, '/videos/'.$video->slug]];

        return view('site.videos.show', [
            'video' => $video, 'thumb' => $thumb, 'more' => $more, 'crumbs' => $crumbs,
            'seo' => Seo::make($video, [
                'title' => $video->title, 'description' => $video->description, 'image' => $thumb, 'type' => 'video.other',
                'jsonld' => [Schema::video($video, $thumb), Schema::breadcrumb($crumbs)],
            ]),
        ]);
    }
}
