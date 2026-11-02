<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use App\Support\Listing;
use App\Support\Schema;
use App\Support\Seo;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $category = (string) $request->query('category', '');
        $author = (int) $request->query('author', 0);
        $year = (int) $request->query('year', 0);
        $month = (int) $request->query('month', 0);
        $filtered = $q !== '' || $category !== '' || $author || $year;

        $query = Article::live()->with(['author', 'categories', 'media']);
        if ($q !== '') {
            $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $q).'%';
            $query->where(fn ($w) => $w->where('title', 'like', $like)->orWhere('subtitle', 'like', $like)->orWhere('excerpt', 'like', $like)
                ->orWhereHas('author', fn ($a) => $a->where('name', 'like', $like))
                ->orWhereHas('tags', fn ($t) => $t->where('name', 'like', $like)));
        }
        if ($category !== '') {
            $cat = Category::where('type', 'article')->where('slug', $category)->first();
            $ids = $cat ? Category::where('id', $cat->id)->orWhere('parent_id', $cat->id)->pluck('id') : collect([0]);
            $query->whereHas('categories', fn ($c) => $c->whereIn('categories.id', $ids));
        }
        if ($author) {
            $query->where('author_id', $author);
        }
        if ($year) {
            $query->whereYear('published_at', $year);
            if ($month >= 1 && $month <= 12) {
                $query->whereMonth('published_at', $month);
            }
        }

        $articles = $query->latestFirst()->paginate(9)->withQueryString();

        $featured = ! $filtered && $articles->currentPage() === 1
            ? Article::live()->where(fn ($w) => $w->where('is_featured', true)->orWhere('is_editors_choice', true))->with(['author', 'categories', 'media'])->latestFirst()->limit(3)->get()
            : collect();

        $page = Listing::page('articles');
        $crumbs = [['Home', '/'], ['Articles', '/articles']];

        return view('site.articles.index', [
            'page' => $page,
            'articles' => $articles,
            'featured' => $featured,
            'categories' => Category::active()->ofType('article')->whereNull('parent_id')->with('children')->orderBy('sort_order')->get(),
            'authors' => User::whereIn('id', Article::live()->whereNotNull('author_id')->select('author_id'))->orderBy('name')->get(['id', 'name']),
            'years' => Article::live()->selectRaw('YEAR(published_at) as y')->distinct()->orderByDesc('y')->pluck('y'),
            'filters' => compact('q', 'category', 'author', 'year', 'month'),
            'filtered' => $filtered,
            'crumbs' => $crumbs,
            'seo' => Seo::make($page, [
                'title' => $page?->title ?: 'Articles & Research',
                'description' => $page?->excerpt,
                'noindex' => $q !== '',
                'jsonld' => [Schema::breadcrumb($crumbs)],
            ]),
        ]);
    }

    public function show(Request $request, string $slug)
    {
        $article = Article::live()->where('slug', $slug)->with(['author', 'categories', 'tags', 'seo', 'media', 'event'])->firstOrFail();

        if (! $request->session()->has('viewed.article.'.$article->id)) {
            Article::whereKey($article->id)->increment('view_count');
            $request->session()->put('viewed.article.'.$article->id, true);
        }

        $catIds = $article->categories->pluck('id');
        $related = Article::live()->where('id', '!=', $article->id)->with(['author', 'media'])
            ->when($catIds->isNotEmpty(), fn ($q) => $q->whereHas('categories', fn ($c) => $c->whereIn('categories.id', $catIds)))
            ->latestFirst()->limit(3)->get();
        if ($related->count() < 3) {
            $more = Article::live()->where('id', '!=', $article->id)->whereNotIn('id', $related->pluck('id'))->with(['author', 'media'])->latestFirst()->limit(3 - $related->count())->get();
            $related = $related->concat($more);
        }

        $prev = Article::live()->where('published_at', '<', $article->published_at)->latestFirst()->first(['title', 'slug']);
        $next = Article::live()->where('published_at', '>', $article->published_at)->orderBy('published_at')->first(['title', 'slug']);

        $image = $article->getFirstMediaUrl('featured', 'large') ?: null;
        $cat = $article->categories->first();
        $crumbs = [['Home', '/'], ['Articles', '/articles']];
        if ($cat) {
            $crumbs[] = [$cat->name, '/articles?category='.$cat->slug];
        }
        $crumbs[] = [$article->title, '/articles/'.$article->slug];

        return view('site.articles.show', [
            'article' => $article,
            'related' => $related,
            'prev' => $prev,
            'next' => $next,
            'image' => $image,
            'crumbs' => $crumbs,
            'seo' => Seo::make($article, [
                'title' => $article->title,
                'description' => $article->excerpt ?: Str::limit(strip_tags((string) $article->body), 160),
                'image' => $image,
                'type' => 'article',
                'published' => $article->published_at?->toIso8601String(),
                'modified' => $article->updated_at?->toIso8601String(),
                'jsonld' => [Schema::article($article, $image), Schema::breadcrumb($crumbs)],
            ]),
        ]);
    }
}
